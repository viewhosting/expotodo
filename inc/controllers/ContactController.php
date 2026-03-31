<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Contacto y Mensajería
 * Maneja el formulario de contacto AJAX, el almacenamiento en BD y las plantillas de correo.
 */
class ContactController {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'after_setup_theme', array( $this, 'setup_table' ) );
        add_action( 'wp_ajax_expotodo_send_contact', array( $this, 'ajax_handle_contact' ) );
        add_action( 'wp_ajax_nopriv_expotodo_send_contact', array( $this, 'ajax_handle_contact' ) );
    }

    public function enqueue_assets() {
        if ( is_page_template('page-contacto.php') || is_page('contacto') ) {
            wp_enqueue_style( 'expotodo-contacto-css', get_template_directory_uri() . '/assets/css/contacto.css?ver='.rand(1,9999), array('expotodo-style'), '1.1.0' );
            wp_enqueue_script( 'expotodo-contacto-js', get_template_directory_uri() . '/assets/js/contacto.js?ver='.rand(1,9999), array('jquery', 'expotodo-wishlist-js'), '1.1.0', true );
            
            wp_localize_script( 'expotodo-contacto-js', 'expotodo_contact_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'expotodo_contact_nonce' ),
            ));
        }
    }

    public function setup_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'expotodo_messages';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            subject varchar(255) DEFAULT '',
            message text NOT NULL,
            created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function ajax_handle_contact() {
        check_ajax_referer('expotodo_contact_nonce', 'nonce');
        
        if (!empty($_POST['hp_field'])) {
            wp_send_json_error('Spam detectado.');
        }

        $name    = sanitize_text_field($_POST['name']);
        $email   = sanitize_email($_POST['email']);
        $subject = sanitize_text_field($_POST['subject']);
        $message = sanitize_textarea_field($_POST['message']);

        if (empty($name) || empty($email) || empty($message)) {
            wp_send_json_error('Por favor rellena todos los campos obligatorios.');
        }

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'expotodo_messages', array(
            'name'       => $name,
            'email'      => $email,
            'subject'    => $subject,
            'message'    => $message,
            'created_at' => current_time('mysql')
        ));

        $admin_email = get_option('admin_email');
        $template = get_option('expotodo_template_contact', "Nombre: {name}\nEmail: {email}\nAsunto: {subject}\n\nMensaje:\n{message}");
        
        $body = str_replace(
            array('{name}', '{email}', '{subject}', '{message}'),
            array($name, $email, $subject, $message),
            $template
        );
        
        wp_mail($admin_email, "Nuevo mensaje de contacto: " . $subject, $body);
        wp_send_json_success('¡Mensaje enviado con éxito! Nos pondremos en contacto pronto.');
    }

    public static function render_templates_page() {
        if (isset($_POST['expotodo_save_templates'])) {
            update_option('expotodo_template_contact', wp_kses_post($_POST['template_contact']));
            update_option('expotodo_template_purchase', wp_kses_post($_POST['template_purchase']));
            update_option('expotodo_template_invoice', wp_kses_post($_POST['template_invoice']));
            echo '<div class="updated"><p>Plantillas actualizadas correctamente.</p></div>';
        }

        $contact = get_option('expotodo_template_contact', "Nombre: {name}\nEmail: {email}\nAsunto: {subject}\n\nMensaje:\n{message}");
        $purchase = get_option('expotodo_template_purchase', "Hola {name}, gracias por tu compra de {amount}.");
        $invoice = get_option('expotodo_template_invoice', "Adjuntamos tu factura para el pedido {order_id}.");

        ?>
        <div class="wrap">
            <h1>Plantillas de Correo Dinámicas</h1>
            <form method="post">
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h3>Página de Contacto</h3>
                    <p>Usa tags: {name}, {email}, {subject}, {message}</p>
                    <textarea name="template_contact" rows="8" class="large-text" style="font-family: monospace;"><?php echo esc_textarea($contact); ?></textarea>
                </div>
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h3>Confirmación de Compra</h3>
                    <p>Usa tags: {name}, {amount}, {order_id}</p>
                    <textarea name="template_purchase" rows="8" class="large-text" style="font-family: monospace;"><?php echo esc_textarea($purchase); ?></textarea>
                </div>
                <div class="card" style="margin-bottom: 20px; padding: 20px;">
                    <h3>Facturación</h3>
                    <textarea name="template_invoice" rows="8" class="large-text" style="font-family: monospace;"><?php echo esc_textarea($invoice); ?></textarea>
                </div>
                <input type="submit" name="expotodo_save_templates" class="button button-primary" value="Guardar Plantillas">
            </form>
        </div>
        <?php
    }

    public static function render_messages_page() {
        global $wpdb;
        $messages = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}expotodo_messages ORDER BY created_at DESC");
        ?>
        <div class="wrap">
            <h1>Centro de Mensajes de la Web</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr><th width="150">Fecha</th><th width="200">De</th><th>Mensaje / Asunto</th></tr>
                </thead>
                <tbody>
                    <?php if ($messages) : foreach ($messages as $msg) : ?>
                        <tr>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($msg->created_at)); ?></td>
                            <td><strong><?php echo esc_html($msg->name); ?></strong><br><small><?php echo esc_html($msg->email); ?></small></td>
                            <td><strong><?php echo esc_html($msg->subject); ?></strong><br><?php echo nl2br(esc_html($msg->message)); ?></td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="3">No hay mensajes aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
