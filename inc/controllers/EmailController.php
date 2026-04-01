<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Emails y Motor SMTP Boutique
 * Gestiona la interceptación de WooCommerce, la cola de envíos y el servidor SMTP.
 */
class EmailController {

    private $is_intercepting = false;

    public function __construct() {
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        add_action( 'after_setup_theme', array( $this, 'setup_queue_table' ) );
        
        // 🛡️ INTERCEPTOR UNIVERSAL
        add_filter( 'wp_mail', array( $this, 'global_intercept_and_log' ), 5 );

        // 🚑 Lógica de Fallos
        add_action( 'wp_mail_failed', array( $this, 'handle_mail_failure' ) );

        // 🧪 AJAX: Prueba y Gestión de Cola
        add_action( 'wp_ajax_expotodo_test_smtp', array( $this, 'ajax_test_smtp' ) );
        add_action( 'wp_ajax_expotodo_fetch_queue', array( $this, 'ajax_fetch_queue' ) );
        add_action( 'wp_ajax_expotodo_queue_action', array( $this, 'ajax_queue_action' ) );
    }

    /**
     * AJAX: Obtiene los elementos de la cola formateados en el nuevo Grid
     */
    public function ajax_fetch_queue() {
        if (!current_user_can('manage_options')) wp_send_json_error('No autorizado');

        global $wpdb;
        $table = $wpdb->prefix . 'expotodo_email_queue';
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 20");

        if (!$logs) {
            wp_send_json_success('<div style="grid-column: 1/-1; padding: 100px; text-align: center; color: #94a3b8;">No hay correos en la cola boutique todavía.</div>');
        }

        ob_start();
        foreach ($logs as $log) : 
            $status_class = 'status-' . strtolower($log->status);
            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at));
            ?>
            <div class="exp-card">
                <div class="exp-status <?php echo $status_class; ?>"><?php echo esc_html($log->status); ?></div>
                <h3 id="email-sub-<?php echo $log->id; ?>"><?php echo esc_html($log->subject); ?></h3>
                <div class="meta">
                    <strong>Para:</strong> <?php echo esc_html($log->recipient); ?><br>
                    <strong>Fecha:</strong> <?php echo $date; ?>
                </div>
                
                <div id="email-body-<?php echo $log->id; ?>" style="display:none;"><?php echo $log->body; ?></div>
                <div id="email-log-<?php echo $log->id; ?>" style="display:none;"><?php echo $log->technical_log; ?></div>

                <div class="exp-actions">
                    <button onclick="showPreview(<?php echo $log->id; ?>)" class="exp-btn btn-view">Ver Correo</button>
                    <button onclick="showLog(<?php echo $log->id; ?>)" class="exp-btn btn-log">Ver Log</button>
                    
                    <?php if ($log->status === 'Failed' || $log->status === 'Pending') : ?>
                        <button onclick="performAction('send', <?php echo $log->id; ?>)" class="exp-btn btn-send">Enviar Ahora</button>
                    <?php else : ?>
                        <button onclick="performAction('resend', <?php echo $log->id; ?>)" class="exp-btn btn-send">Reenviar</button>
                    <?php endif; ?>
                    
                    <button onclick="performAction('cancel', <?php echo $log->id; ?>)" class="exp-btn btn-cancel">Cancelar</button>
                </div>
            </div>
            <?php
        endforeach;
        $html = ob_get_clean();
        wp_send_json_success($html);
    }

    /**
     * AJAX: Procesa acciones (Enviar, Reenviar, Cancelar)
     */
    public function ajax_queue_action() {
        if (!current_user_can('manage_options')) wp_send_json_error('No autorizado');

        $action = $_POST['email_action'];
        $id = intval($_POST['id']);
        global $wpdb;
        $table = $wpdb->prefix . 'expotodo_email_queue';
        $email = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));

        if (!$email) wp_send_json_error('Correo no encontrado');

        $log_entry = "\n[" . current_time('mysql') . "] Acción manual detectada: " . strtoupper($action);

        if ($action === 'cancel') {
            $wpdb->update($table, array(
                'status' => 'Cancelled',
                'technical_log' => $email->technical_log . $log_entry
            ), array('id' => $id));
            wp_send_json_success('Envío cancelado correctamente.');
        }

        if ($action === 'send' || $action === 'resend') {
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $sent = wp_mail($email->recipient, $email->subject, $email->body, $headers);
            
            if ($sent) {
                $wpdb->update($table, array(
                    'status' => 'Sent',
                    'technical_log' => $email->technical_log . $log_entry . " -> Éxito en el reenvío."
                ), array('id' => $id));
                wp_send_json_success('Correo enviado con éxito.');
            } else {
                $wpdb->update($table, array(
                    'status' => 'Failed',
                    'technical_log' => $email->technical_log . $log_entry . " -> El servidor SMTP volvió a fallar."
                ), array('id' => $id));
                wp_send_json_error('El reenvío falló nuevamente.');
            }
        }
    }

    /**
     * Intercepta CUALQUIER correo de WordPress y le aplica el diseño Boutique
     */
    public function global_intercept_and_log( $args ) {
        // Evitar doble interceptación
        if ( isset($args['boutique_processed']) || defined('INTERCEPTING_MAIL') ) {
            return $args;
        }

        define('INTERCEPTING_MAIL', true);

        $to = $args['to'];
        $subject = $args['subject'];
        $original_message = $args['message'];

        // Si ya es HTML bonito (por ejemplo los que ya procesamos), no lo tocamos
        if ( strpos($original_message, 'expotodo-boutique-container') !== false ) {
            return $args;
        }

        // 💎 Envolvemos el mensaje en el Layout Boutique
        $boutique_message = $this->wrap_in_boutique_layout( $original_message, $subject );

        $args['message'] = $boutique_message;
        $args['headers'] = array('Content-Type: text/html; charset=UTF-8');
        $args['boutique_processed'] = true;

        // Registramos en la cola para auditoría
        $this->log_queued_email($to, $subject, 'Sent', $boutique_message);

        return $args;
    }

    /**
     * Envuelve un mensaje plano o HTML simple en el diseño Premium
     */
    private function wrap_in_boutique_layout( $content, $title ) {
        return '
        <div class="expotodo-boutique-container" style="background:#f8f9fa; padding:40px; font-family:sans-serif;">
            <div style="max-width:600px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 10px 40px rgba(0,0,0,0.05);">
                <div style="background:#8b5cf6; padding:40px; text-align:center; color:#fff;">
                    <h1 style="margin:0; font-size:24px; letter-spacing:-0.5px;">' . esc_html($title) . '</h1>
                </div>
                <div style="padding:40px; color:#1e293b; line-height:1.6;">
                    ' . $content . '
                </div>
                <div style="padding:20px; text-align:center; background:#f1f5f9; color:#64748b; font-size:12px;">
                    © ' . date('Y') . ' Expotodo Boutique. Todos los derechos reservados.
                </div>
            </div>
        </div>';
    }

    /**
     * Configura PHPMailer para usar SMTP personalizado
     */
    public function configure_smtp( $phpmailer ) {
        $host = get_option('expotodo_smtp_host');
        $port = get_option('expotodo_smtp_port');
        $user = get_option('expotodo_smtp_user');
        $pass = get_option('expotodo_smtp_pass');
        $secure = get_option('expotodo_smtp_secure');
        $from_name = get_option('expotodo_smtp_from_name', 'Expotodo Boutique');

        // 🛡️ MODO SALVA-VIDAS: Si no hay datos, no toques nada (deja el correo nativo)
        if ( empty($host) || empty($user) || empty($pass) ) {
            return; 
        }

        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->SMTPAuth = true;
        $phpmailer->Port = $port ? intval($port) : 587;
        $phpmailer->Username = $user;
        $phpmailer->Password = $pass;
        $phpmailer->SMTPSecure = $secure ? $secure : 'tls';
        $phpmailer->FromName = $from_name;
        
        // 🔍 DEBUG: Si el envío falla, esto guardará el error real en los logs del servidor
        $phpmailer->SMTPDebug = 2; 
        $phpmailer->Debugoutput = function($str, $level) {
            error_log("SMTP-DEBUG: $str");
        };
    }

    /**
     * Intercepta pedidos de WC y envía versión Boutique
     */
    public function send_boutique_order_email( $order_id, $order ) {
        $user_email = $order->get_billing_email();
        $user_name  = $order->get_billing_first_name();
        $total      = $order->get_formatted_order_total();
        
        $template = get_option('expotodo_template_purchase');
        if ( empty($template) ) return;

        $subject = "Confirmación de Pedido Boutique #{$order_id}";
        
        // Generar lista de productos simplificada
        $items_text = "";
        foreach ( $order->get_items() as $item_id => $item ) {
            $items_text .= "• " . $item->get_name() . " (x" . $item->get_quantity() . ")<br>";
        }

        $body = str_replace(
            array('{name}', '{amount}', '{order_id}', '{order_details}'),
            array($user_name, $total, $order_id, $items_text),
            $template
        );

        $sent = wp_mail($user_email, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
        $this->log_queued_email($user_email, $subject, $sent ? 'sent' : 'failed', $body);
    }

    /**
     * Crea/Actualiza la tabla de cola de envío con soporte para Logs Técnicos
     */
    public function setup_queue_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_email_queue';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            recipient varchar(100) NOT NULL,
            subject varchar(200) NOT NULL,
            body longtext NOT NULL,
            status varchar(20) DEFAULT 'pending' NOT NULL,
            technical_log longtext DEFAULT '',
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Genera el HTML boutique del pedido (Diseño WOW)
     */
    public function get_order_template( $order ) {
        $items = $order->get_items();
        $items_html = '';
        foreach ( $items as $item_id => $item ) {
            $product = $item->get_product();
            $items_html .= '<div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #f1f5f9;">';
            $items_html .= '<div style="font-size: 14px; color: #1e293b;"><strong>' . esc_html( $item->get_name() ) . '</strong> <span style="color: #64748b;">x' . $item->get_quantity() . '</span></div>';
            $items_html .= '<div style="font-size: 14px; font-weight: 700; color: #8b5cf6;">' . $order->get_formatted_line_subtotal( $item ) . '</div>';
            $items_html .= '</div>';
        }

        $brand_color = '#8b5cf6';
        $order_num = $order->get_order_number();
        $status = wc_get_order_status_name( $order->get_status() );
        $total = $order->get_formatted_order_total();
        $customer = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

        return "
        <div style='background: #f8fafc; padding: 40px; border-radius: 24px; border: 1px solid #e2e8f0;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <span style='background: {$brand_color}20; color: {$brand_color}; padding: 8px 20px; border-radius: 100px; font-size: 12px; font-weight: 800; text-transform: uppercase;'>Pedido #{$order_num}</span>
                <h1 style='color: #0f172a; font-size: 28px; margin-top: 15px; letter-spacing: -1px;'>¡Gracias por tu compra, {$customer}!</h1>
                <p style='color: #64748b; font-size: 16px;'>Tu pedido está siendo procesado con cuidado artesanal.</p>
            </div>
            
            <div style='background: #ffffff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);'>
                <h2 style='font-size: 16px; color: #1e293b; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;'>Detalles del Pedido</h2>
                {$items_html}
                <div style='display: flex; justify-content: space-between; padding-top: 25px; margin-top: 10px;'>
                    <div style='font-size: 18px; color: #0f172a; font-weight: 800;'>Total Final</div>
                    <div style='font-size: 22px; color: {$brand_color}; font-weight: 800;'>{$total}</div>
                </div>
            </div>

            <div style='margin-top: 30px; text-align: center;'>
                <p style='font-size: 14px; color: #94a3b8;'>Estado actual: <strong style='color: #1e293b;'>{$status}</strong></p>
            </div>
        </div>";
    }

    /**
     * Registra un envío en la tabla de cola (Atemporal para auditoría)
     */
    public function log_queued_email($recipient, $subject, $status, $body = '') {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'expotodo_email_queue', array(
            'recipient'  => $recipient,
            'subject'    => $subject,
            'status'     => $status,
            'body'       => $body,
            'created_at' => current_time('mysql')
        ));
    }

    /**
     * Callback para registrar errores de wp_mail
     */
    public function log_failed_email( $wp_error ) {
        // Podríamos loguear detalles técnicos aquí si fuera necesario
    }

    /**
     * Renderiza la Suite de Comunicación Boutique (Modern Grid UI)
     */
    public static function render_queue_page() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap expotodo-dashboard">
            <div class="exp-header">
                <div>
                    <h1 style="font-weight: 800; letter-spacing: -1px;">Cola de Envío</h1>
                    <p style="color: #64748b; margin: 0;">Supervisión en tiempo real de las comunicaciones de Expotodo.</p>
                </div>
                <div id="sync-indicator" style="font-size: 12px; color: #94a3b8;">
                    <span class="spinner is-active" style="float:none; margin:0 5px 0 0;"></span> Sincronizando...
                </div>
            </div>

            <div id="email-grid-container" class="exp-grid">
                <!-- Se carga vía AJAX -->
            </div>
        </div>

        <!-- Modales -->
        <div id="previewModal" class="exp-modal" onclick="closeModal('previewModal')">
            <div class="exp-modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h2 id="previewSub" style="margin:0; font-size:18px;">Vista Previa</h2>
                    <button class="button" onclick="closeModal('previewModal')">Cerrar</button>
                </div>
                <div class="modal-body" id="previewBody"></div>
            </div>
        </div>

        <div id="logModal" class="exp-modal" onclick="closeModal('logModal')">
            <div class="exp-modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h2 style="margin:0; font-size:18px;">Log de Auditoría Técnica</h2>
                    <button class="button" onclick="closeModal('logModal')">Cerrar</button>
                </div>
                <div class="modal-body"><pre id="logContent" style="background:#f8fafc; padding:20px; border-radius:10px; overflow:auto;"></pre></div>
            </div>
        </div>

        <script>
        let lastId = 0;

        function refreshGrid() {
            jQuery.post(ajaxurl, { action: 'expotodo_fetch_queue' }, function(res) {
                if (res.success) {
                    jQuery('#email-grid-container').html(res.data);
                    jQuery('#sync-indicator').html('✅ Actualizado ahora');
                }
            });
        }

        function showPreview(id) {
            let body = jQuery('#email-body-' + id).html();
            let sub = jQuery('#email-sub-' + id).text();
            jQuery('#previewSub').text(sub);
            jQuery('#previewBody').html(body);
            jQuery('#previewModal').fadeIn(200);
        }

        function showLog(id) {
            let log = jQuery('#email-log-' + id).html();
            jQuery('#logContent').text(log || 'No hay logs técnicos registrados todavía para este envío.');
            jQuery('#logModal').fadeIn(200);
        }

        function closeModal(modalId) { jQuery('#' + modalId).fadeOut(200); }

        function performAction(action, id) {
            if (!confirm('¿Estás seguro de ' + action + ' este envío?')) return;
            jQuery.post(ajaxurl, { action: 'expotodo_queue_action', email_action: action, id: id }, function(res) {
                alert(res.data);
                refreshGrid();
            });
        }

        // Auto-refresh cada 10 segundos
        setInterval(refreshGrid, 10000);
        refreshGrid();
        </script>
        <?php
    }

    /**
     * Maneja el fallo de wp_mail y actualiza la cola con el error real
     */
    public function handle_mail_failure( $error ) {
        global $wpdb;
        $error_message = $error->get_error_message();
        $table = $wpdb->prefix . 'expotodo_email_queue';
        
        // Buscamos el último envío para documentar el fallo técnico
        $last_entry = $wpdb->get_row("SELECT id, technical_log FROM $table ORDER BY id DESC LIMIT 1");

        if ($last_entry) {
            $log_entry = "\n[" . current_time('mysql') . "] ERROR TÉCNICO DETECTADO: " . $error_message;
            $wpdb->update($table, array(
                'status' => 'Failed',
                'technical_log' => $last_entry->technical_log . $log_entry
            ), array('id' => $last_entry->id));
        }
    }

    /**
     * AJAX: Realiza un envío de prueba
     */
    public function ajax_test_smtp() {
        check_ajax_referer('expotodo_test_smtp');
        
        if ( !current_user_can('manage_options') ) {
            wp_send_json_error('No tienes permisos');
        }

        $to = sanitize_email($_POST['email']);
        $subject = '🧪 Prueba de Configuración Boutique - ' . get_bloginfo('name');
        $message = '<h1>¡Hola Emanuel!</h1><p>Si estás leyendo esto, tu configuración de correo boutique está <strong>OPERATIVA</strong>.</p>';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $sent = wp_mail( $to, $subject, $message, $headers );

        if ( $sent ) {
            $this->log_queued_email($to, $subject, 'Sent (Test)', $message);
            wp_send_json_success('Correo enviado con éxito');
        } else {
            $this->log_queued_email($to, $subject, 'Failed (Test)', $message);
            wp_send_json_error('Error al enviar. Revisa la Cola de Envío para más detalles.');
        }
    }
}
