<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Correo y Cola de Envío
 * Gestiona la configuración SMTP y el monitoreo de mensajes salientes.
 */
class EmailController {

    public function __construct() {
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        add_action( 'after_setup_theme', array( $this, 'setup_queue_table' ) );
        
        // Hook para errores
        add_action( 'wp_mail_failed', array( $this, 'log_failed_email' ) );

        // 🛍️ Interceptar WooCommerce (Nuevos Pedidos y Pagos)
        add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'send_boutique_order_email' ), 10, 2 );
        add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'send_boutique_order_email' ), 10, 2 );
        add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'send_boutique_order_email' ), 10, 2 );

        // Desactivar correos nativos de WC (para que solo salga el nuestro boutique)
        add_filter( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
    }

    /**
     * Envía un correo boutique basado en la plantilla de compra
     */
    public function send_boutique_order_email( $order_id, $order ) {
        $user_email = $order->get_billing_email();
        $user_name  = $order->get_billing_first_name();
        $total      = $order->get_formatted_order_total();
        
        $template = get_option('expotodo_template_purchase');
        if ( empty($template) ) return; // Si no hay plantilla, no interceptamos

        $subject = "¡Confirmación de tu pedido boutique #{$order_id}!";
        
        // Reemplazar tags
        $body = str_replace(
            array('{name}', '{amount}', '{order_id}'),
            array($user_name, $total, $order_id),
            $template
        );

        // Intentar enviar
        $sent = wp_mail($user_email, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));

        // Registrar en cola (Éxito o Fallo)
        $this->log_queued_email($user_email, $subject, $sent ? 'sent' : 'failed');
    }

    /**
     * Registra un envío en la tabla de cola
     */
    private function log_queued_email($recipient, $subject, $status) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'expotodo_email_queue', array(
            'recipient'  => $recipient,
            'subject'    => $subject,
            'status'     => $status,
            'created_at' => current_time('mysql')
        ));
    }

    /**
     * Configura PHPMailer para usar SMTP según los ajustes del panel
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
     * Crea la tabla de cola de envíos si no existe
     */
    public function setup_queue_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_email_queue';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            recipient varchar(255) NOT NULL,
            subject varchar(255) NOT NULL,
            status varchar(50) DEFAULT 'pending',
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Registra un error si el correo falla
     */
    public function log_failed_email( $wp_error ) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'expotodo_email_queue', array(
            'recipient'     => isset($wp_error->error_data['wp_mail_failed']['to'][0]) ? $wp_error->error_data['wp_mail_failed']['to'][0] : 'Desconocido',
            'subject'       => isset($wp_error->error_data['wp_mail_failed']['subject']) ? $wp_error->error_data['wp_mail_failed']['subject'] : 'Sin asunto',
            'status'        => 'failed',
            'error_message' => $wp_error->get_error_message(),
            'created_at'    => current_time('mysql')
        ));
    }

    /**
     * Renderiza la página de Cola de Envío en el Admin
     */
    public static function render_queue_page() {
        global $wpdb;
        $queue = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}expotodo_email_queue ORDER BY created_at DESC LIMIT 50");
        ?>
        <div class="wrap">
            <h1>Cola de Envío Boutique</h1>
            <p class="description">Aquí puedes monitorear los correos enviados por el sistema y detectar posibles errores de SMTP.</p>

            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th width="180">Fecha/Hora</th>
                        <th width="250">Destinatario</th>
                        <th>Asunto</th>
                        <th width="120">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($queue) : foreach ($queue as $item) : 
                        $status_class = $item->status === 'failed' ? 'text-danger' : 'text-success';
                    ?>
                        <tr>
                            <td><?php echo $item->created_at; ?></td>
                            <td><?php echo esc_html($item->recipient); ?></td>
                            <td>
                                <?php echo esc_html($item->subject); ?>
                                <?php if ($item->error_message) : ?>
                                    <div class="small text-danger" style="margin-top: 5px;">
                                        <strong>Error:</strong> <?php echo esc_html($item->error_message); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="<?php echo $status_class; ?> fw-bold"><?php echo strtoupper($item->status); ?></span></td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="4">No hay envíos registrados recientemente o todo funciona correctamente (solo se registran fallos y envíos manuales).</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
