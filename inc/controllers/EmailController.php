<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Emails y Motor SMTP Boutique
 * Gestiona la interceptación de WooCommerce, la cola de envíos y el servidor SMTP.
 */
class EmailController {

    public function __construct() {
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        add_action( 'after_setup_theme', array( $this, 'setup_queue_table' ) );
        
        // Hook para errores genéricos
        add_action( 'wp_mail_failed', array( $this, 'log_failed_email' ) );

        // 🛍️ Interceptar WooCommerce (Nuevos Pedidos y Pagos)
        add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'send_boutique_order_email' ), 10, 2 );
        add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'send_boutique_order_email' ), 10, 2 );
        add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'send_boutique_order_email' ), 10, 2 );

        // Desactivar correos nativos de WC (para que solo salga el nuestro boutique)
        add_filter( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
    }

    /**
     * Configura PHPMailer para usar el SMTP boutique
     */
    public function configure_smtp( $phpmailer ) {
        $host = get_option('expotodo_smtp_host');
        if ( empty($host) ) return;

        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = get_option('expotodo_smtp_port', '465');
        $phpmailer->Username   = get_option('expotodo_smtp_user');
        $phpmailer->Password   = get_option('expotodo_smtp_pass');
        $phpmailer->SMTPSecure = get_option('expotodo_smtp_secure', 'ssl');
        $phpmailer->From       = get_option('expotodo_smtp_user');
        $phpmailer->FromName   = get_option('expotodo_smtp_from_name', 'Expotodo Boutique');
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
     * Crea la tabla de cola de envío en la BD
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
            error_message text DEFAULT '',
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Registra un envío en la tabla de cola
     */
    private function log_queued_email($recipient, $subject, $status, $body = '') {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'expotodo_email_queue', array(
            'recipient'  => $recipient,
            'subject'    => $subject,
            'body'       => $body,
            'status'     => $status,
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
     * Renderiza la página de registro de cola
     */
    public static function render_queue_page() {
        if (!current_user_can('manage_options')) return;
        
        global $wpdb;
        $table = $wpdb->prefix . 'expotodo_email_queue';
        $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 50");
        ?>
        <div class="wrap expotodo-admin-wrap">
            <h1>Cola de Envío Boutique</h1>
            <p class="description">Historial de auditoría para los correos enviados vía SMTP y WooCommerce.</p>
            <hr class="wp-header-end">
            
            <div class="card" style="max-width: 100%; padding: 0; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: none;">
                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <th style="padding: 15px;">Fecha</th>
                            <th>Destinatario</th>
                            <th>Asunto</th>
                            <th>Estado</th>
                            <th style="width: 100px; text-align: center;">Accines</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs) : foreach ($logs as $log) : ?>
                            <tr>
                                <td style="padding: 12px 15px;"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at)); ?></td>
                                <td><strong><?php echo esc_html($log->recipient); ?></strong></td>
                                <td><?php echo esc_html($log->subject); ?></td>
                                <td>
                                    <?php if ($log->status === 'sent') : ?>
                                        <span class="status-pill" style="background: #e6fffa; color: #234e52; padding: 4px 12px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase;">Enviado</span>
                                    <?php else : ?>
                                        <span class="status-pill" style="background: #fff5f5; color: #742a2a; padding: 4px 12px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase;">Error</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button onclick="previewEmail(<?php echo $log->id; ?>)" class="button button-small" title="Ver contenido"><i class="fas fa-eye"></i></button>
                                    <div id="email-body-<?php echo $log->id; ?>" style="display:none;"><?php echo $log->body; ?></div>
                                </td>
                            </tr>
                        <?php endforeach; else : ?>
                            <tr><td colspan="5" style="padding: 30px; text-align: center;">No hay registros de envíos boutique todavía.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        function previewEmail(id) {
            let body = document.getElementById('email-body-' + id).innerHTML;
            let win = window.open("", "Expotodo Preview", "width=900,height=750");
            win.document.write('<html><head><title>Vista Previa Boutique</title><style>body{font-family:sans-serif;margin:0;background:#f8f9fa;} .container{max-width:700px;margin:40px auto;background:#fff;padding:40px;box-shadow:0 10px 40px rgba(0,0,0,0.1);border-radius:12px;}</style></head><body><div class="container">' + body + '</div></body></html>');
            win.document.close();
        }
        </script>
        <?php
    }
}
