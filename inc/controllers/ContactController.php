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
        
        // Acciones Admin para mensajes
        add_action( 'wp_ajax_expotodo_delete_message', array( $this, 'ajax_delete_message' ) );
        add_action( 'wp_ajax_expotodo_reply_message', array( $this, 'ajax_reply_message' ) );
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

    /**
     * AJAX: Eliminar mensaje
     */
    public function ajax_delete_message() {
        check_ajax_referer('expotodo_admin_nonce', 'security');
        if (!current_user_can('manage_options')) wp_send_json_error();

        global $wpdb;
        $id = intval($_POST['id']);
        $wpdb->delete($wpdb->prefix . 'expotodo_messages', array('id' => $id));
        wp_send_json_success();
    }

    /**
     * AJAX: Responder mensaje (vía correo con plantilla)
     */
    public function ajax_reply_message() {
        check_ajax_referer('expotodo_admin_nonce', 'security');
        if (!current_user_can('manage_options')) wp_send_json_error();

        $email         = sanitize_email($_POST['email']);
        $name          = sanitize_text_field($_POST['name']);
        $reply_content = wp_kses_post($_POST['reply_content']);

        $template = get_option('expotodo_template_contact', "Hola {name},\n\n{message}");
        
        // Reemplazar tags en la plantilla de contacto
        $body = str_replace(
            array('{name}', '{email}', '{message}', '{subject}'),
            array($name, $email, $reply_content, "Re: Consulta Expotodo"),
            $template
        );

        $sent = wp_mail($email, "Re: Tu mensaje en Expotodo", $body, array('Content-Type: text/html; charset=UTF-8'));

        if ($sent) {
            // Registrar en la cola boutique para auditoría
            if (class_exists('EmailController')) {
                global $wpdb;
                $wpdb->insert($wpdb->prefix . 'expotodo_email_queue', array(
                    'recipient'  => $email,
                    'subject'    => "Re: Tu mensaje en Expotodo",
                    'body'       => $body,
                    'status'     => 'sent',
                    'created_at' => current_time('mysql')
                ));
            }
            wp_send_json_success('Respuesta enviada y guardada en la cola de auditoría.');
        } else {
            wp_send_json_error('Error al enviar el correo a través del servidor SMTP.');
        }
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
        
        wp_mail($admin_email, "Nuevo mensaje de contacto: " . $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
        wp_send_json_success('¡Mensaje enviado con éxito! Nos pondremos en contacto pronto.');
    }

    public static function render_templates_page() {
        if (!current_user_can('manage_options')) return;

        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'contact';

        if (isset($_POST['expotodo_save_templates'])) {
            // Guardar solo el campo de la pestaña activa para evitar sobrescribir otros
            if ($active_tab === 'contact') {
                update_option('expotodo_template_contact', $_POST['template_contact']);
            } elseif ($active_tab === 'purchase') {
                update_option('expotodo_template_purchase', $_POST['template_purchase']);
            } elseif ($active_tab === 'invoice') {
                update_option('expotodo_template_invoice', $_POST['template_invoice']);
            }
            echo '<div class="updated"><p>¡Plantilla de <strong>' . strtoupper($active_tab) . '</strong> guardada correctamente con tecnología visual!</p></div>';
        }

        $contact  = get_option('expotodo_template_contact', "<h2>Hola {name}</h2><p>{message}</p>");
        $purchase = get_option('expotodo_template_purchase', "<h2>¡Gracias por tu pedido, {name}!</h2><p>Tu compra de {amount} está siendo procesada.</p>");
        $invoice  = get_option('expotodo_template_invoice', "<h2>Aquí tienes tu factura</h2><p>Adjuntamos el comprobante del pedido #{order_id}.</p>");

        $editor_settings = array(
            'textarea_rows' => 18,
            'media_buttons' => true,
            'tinymce'       => true,
            'quicktags'     => true
        );

        ?>
        <div class="wrap">
            <h1>Gestor de Comunicaciones Boutique</h1>
            <p class="description">Personaliza la experiencia visual de cada correo que sale de tu tienda.</p>
            <hr class="wp-header-end">

            <h2 class="nav-tab-wrapper" style="margin-bottom: 0;">
                <a href="?page=expotodo-templates&tab=contact" class="nav-tab <?php echo $active_tab == 'contact' ? 'nav-tab-active' : ''; ?>">Respuesta (Contacto)</a>
                <a href="?page=expotodo-templates&tab=purchase" class="nav-tab <?php echo $active_tab == 'purchase' ? 'nav-tab-active' : ''; ?>">Confirmación de Pedido</a>
                <a href="?page=expotodo-templates&tab=invoice" class="nav-tab <?php echo $active_tab == 'invoice' ? 'nav-tab-active' : ''; ?>">Facturación</a>
            </h2>

            <form method="post">
                <div class="card" style="max-width: 95%; margin-top: 0; padding: 35px; border-top: none; border-radius: 0 0 12px 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    
                    <?php if ($active_tab === 'contact') : ?>
                        <h2 class="title">Plantilla de Respuesta Manual</h2>
                        <p class="description">Esta plantilla se pre-carga cuando respondes a un cliente desde el Centro de Mensajes. <br>Tags permitidos: <code>{name}</code>, <code>{message}</code> (Cuerpo de respuesta), <code>{email}</code>, <code>{subject}</code></p>
                        <div style="margin-top: 20px;">
                            <?php wp_editor($contact, 'template_contact', $editor_settings); ?>
                        </div>

                    <?php elseif ($active_tab === 'purchase') : ?>
                        <h2 class="title">Confirmación de Pedido</h2>
                        <p class="description">Se envía automáticamente tras una compra exitosa. <br>Tags permitidos: <code>{name}</code>, <code>{amount}</code>, <code>{order_id}</code>, <code>{order_details}</code></p>
                        <div style="margin-top: 20px;">
                            <?php wp_editor($purchase, 'template_purchase', $editor_settings); ?>
                        </div>

                    <?php elseif ($active_tab === 'invoice') : ?>
                        <h2 class="title">Plantilla de Facturación</h2>
                        <p class="description">Formato para el envío de comprobantes fiscales o recibos. <br>Tags permitidos: <code>{name}</code>, <code>{order_id}</code>, <code>{invoice_url}</code></p>
                        <div style="margin-top: 20px;">
                            <?php wp_editor($invoice, 'template_invoice', $editor_settings); ?>
                        </div>
                    <?php endif; ?>

                    <p class="submit" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                        <input type="submit" name="expotodo_save_templates" class="button button-primary button-large" value="Guardar Plantilla de <?php echo strtoupper($active_tab); ?>">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }

    public static function render_messages_page() {
        global $wpdb;
        $messages = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}expotodo_messages ORDER BY created_at DESC");
        ?>
        <div class="wrap">
            <h1>Centro de Comunicaciones Directas</h1>
            <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                <thead>
                    <tr><th width="150">Fecha</th><th width="200">De</th><th>Mensaje / Asunto</th><th width="180">Acciones Boutique</th></tr>
                </thead>
                <tbody>
                    <?php if ($messages) : foreach ($messages as $msg) : ?>
                        <tr id="message-row-<?php echo $msg->id; ?>">
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($msg->created_at)); ?></td>
                            <td><strong><?php echo esc_html($msg->name); ?></strong><br><small><?php echo esc_html($msg->email); ?></small></td>
                            <td>
                                <strong><?php echo esc_html($msg->subject); ?></strong><br>
                                <div style="display: block; padding-top: 10px; color: #555;"><?php echo nl2br(esc_html($msg->message)); ?></div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="button button-primary btn-reply" 
                                            data-id="<?php echo $msg->id; ?>" 
                                            data-email="<?php echo esc_attr($msg->email); ?>" 
                                            data-name="<?php echo esc_attr($msg->name); ?>">Responder</button>
                                    <button class="button button-link text-danger btn-delete" data-id="<?php echo $msg->id; ?>">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="4">No hay mensajes aún.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Modal de Respuesta Boutique -->
            <div id="replyModal" style="display:none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);">
                <div style="background-color: #fff; margin: 5% auto; padding: 25px; border-radius: 12px; width: 60%; max-width: 700px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <h2 id="replyTitle">Respondiendo a...</h2>
                    <hr>
                    <div style="margin-bottom: 15px;">
                        <label>Escribe tu mensaje boutique:</label>
                        <textarea id="replyContent" rows="10" style="width: 100%; margin-top: 10px; border-radius: 8px; padding: 10px; border: 1px solid #ddd;"></textarea>
                    </div>
                    <div style="text-align: right;">
                        <button class="button" onclick="jQuery('#replyModal').hide()">Cancelar</button>
                        <button class="button button-primary" id="btn-send-reply">Enviar Respuesta Profesional</button>
                    </div>
                    <input type="hidden" id="replyEmail">
                    <input type="hidden" id="replyName">
                </div>
            </div>
        </div>
        <?php
    }
}
