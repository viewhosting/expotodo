<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador del Menú Administrativo Expotodo
 * Orquesta la jerarquía del menú y enlaza las páginas de otros controladores.
 */
class AdminController {

    public function __construct() {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Carga los estilos premium solo en las páginas de Expotodo
     */
    public function enqueue_admin_styles($hook) {
        // Solo cargamos si estamos en una de nuestras páginas
        if (strpos($hook, 'expotodo') === false) return;
        
        wp_enqueue_style(
            'expotodo-admin-css', 
            get_template_directory_uri() . '/assets/css/admin-style.css', 
            array(), 
            time() // 🧪 cache-busting durante desarrollo
        );
    }

    public function register_menu() {
        // Menú Principal
        add_menu_page(
            'Expotodo', 
            'Expotodo', 
            'manage_options', 
            'expotodo-main', 
            array( 'WishlistController', 'render_admin_page' ), 
            'dashicons-store', 
            25
        );

        // Submenús vinculados a sus respectivos controladores
        add_submenu_page(
            'expotodo-main', 
            'Wishlist', 
            'Wishlist', 
            'manage_options', 
            'expotodo-main', 
            array( 'WishlistController', 'render_admin_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Plantillas', 
            'Plantillas', 
            'manage_options', 
            'expotodo-templates', 
            array( 'ContactController', 'render_templates_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Mensajes de la web', 
            'Mensajes de la web', 
            'manage_options', 
            'expotodo-messages', 
            array( 'ContactController', 'render_messages_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Configuración', 
            'Configuración', 
            'manage_options', 
            'expotodo-settings', 
            array( $this, 'render_settings_page' )
        );

        add_submenu_page(
            'expotodo-main',
            'Cola de Envío',
            'Cola de Envío',
            'manage_options',
            'expotodo-queue',
            array('EmailController', 'render_queue_page')
        );
    }

    /**
     * Renderiza la página de configuración del template con PESTAÑAS
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) return;

        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

        // Procesar guardado si se envió el formulario
        if (isset($_POST['expotodo_save_settings'])) {
            if (check_admin_referer('expotodo_settings_verify')) {
                
                if ($active_tab === 'general') {
                    update_option('expotodo_whatsapp', isset($_POST['whatsapp']) ? sanitize_text_field($_POST['whatsapp']) : '');
                    update_option('expotodo_boutique_mode', isset($_POST['boutique_mode']) ? 'yes' : 'no');
                }

                if ($active_tab === 'smtp') {
                    if (isset($_POST['smtp_host'])) update_option('expotodo_smtp_host', sanitize_text_field($_POST['smtp_host']));
                    if (isset($_POST['smtp_port'])) update_option('expotodo_smtp_port', sanitize_text_field($_POST['smtp_port']));
                    if (isset($_POST['smtp_user'])) update_option('expotodo_smtp_user', sanitize_text_field($_POST['smtp_user']));
                    if (!empty($_POST['smtp_pass'])) {
                        update_option('expotodo_smtp_pass', sanitize_text_field($_POST['smtp_pass']));
                    }
                    if (isset($_POST['smtp_secure'])) update_option('expotodo_smtp_secure', sanitize_text_field($_POST['smtp_secure']));
                    if (isset($_POST['smtp_from_name'])) update_option('expotodo_smtp_from_name', sanitize_text_field($_POST['smtp_from_name']));
                }

                if ($active_tab === 'tracking') {
                    update_option('expotodo_google_analytics', isset($_POST['google_analytics']) ? sanitize_text_field($_POST['google_analytics']) : '');
                }

                echo '<div class="updated"><p>Ajustes de <strong>' . strtoupper($active_tab) . '</strong> actualizados.</p></div>';
            }
        }

        ?>
        <div class="wrap expotodo-admin-wrap">
            <h1>Configuración Boutique Expotodo</h1>
            <p class="description">Gestión centralizada del ecosistema digital.</p>
            <hr class="wp-header-end">

            <h2 class="nav-tab-wrapper">
                <a href="?page=expotodo-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=expotodo-settings&tab=smtp" class="nav-tab <?php echo $active_tab == 'smtp' ? 'nav-tab-active' : ''; ?>">Correo (SMTP)</a>
                <a href="?page=expotodo-settings&tab=tracking" class="nav-tab <?php echo $active_tab == 'tracking' ? 'nav-tab-active' : ''; ?>">Seguimiento</a>
            </h2>

            <form method="post" action="">
                <?php wp_nonce_field('expotodo_settings_verify'); ?>
                
                <div class="card" style="max-width: 900px; margin-top: 20px; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border:none; background:#fff;">
                    
                    <?php if ($active_tab === 'general') : 
                        $whatsapp = get_option('expotodo_whatsapp', '');
                        $boutique = get_option('expotodo_boutique_mode', 'yes');
                    ?>
                        <h2 class="title" style="margin-bottom:20px;">Identidad y Estilo</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="whatsapp">WhatsApp Directo</label></th>
                                <td>
                                    <input name="whatsapp" type="text" id="whatsapp" value="<?php echo esc_attr($whatsapp); ?>" class="regular-text" placeholder="Ej: 5219991234567">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Dashboard Boutique</th>
                                <td>
                                    <label class="switch">
                                        <input name="boutique_mode" type="checkbox" id="boutique_mode" value="yes" <?php checked('yes', $boutique); ?>>
                                        Activar visuales premium en el sitio.
                                    </label>
                                </td>
                            </tr>
                        </table>

                    <?php elseif ($active_tab === 'smtp') : 
                        $smtp_host = get_option('expotodo_smtp_host', '');
                        $smtp_port = get_option('expotodo_smtp_port', '587');
                        $smtp_user = get_option('expotodo_smtp_user', '');
                        $smtp_secure = get_option('expotodo_smtp_secure', 'tls');
                        $smtp_from_name = get_option('expotodo_smtp_from_name', 'Expotodo Boutique');
                    ?>
                        <h2 class="title" style="margin-bottom:20px;">Motor de Envío Profesional (SMTP)</h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Nombre Remitente</th>
                                <td><input type="text" name="smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Servidor Host</th>
                                <td><input type="text" name="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Puerto</th>
                                <td><input type="text" name="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="small-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Seguridad</th>
                                <td>
                                    <select name="smtp_secure">
                                        <option value="tls" <?php selected($smtp_secure, 'tls'); ?>>TLS (587)</option>
                                        <option value="ssl" <?php selected($smtp_secure, 'ssl'); ?>>SSL (465)</option>
                                        <option value="none" <?php selected($smtp_secure, 'none'); ?>>Ninguna</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Usuario</th>
                                <td><input type="text" name="smtp_user" value="<?php echo esc_attr($smtp_user); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row">Contraseña</th>
                                <td><input type="password" name="smtp_pass" value="" class="regular-text" placeholder="●●●●●●●●" /></td>
                            </tr>
                        </table>

                        <div style="margin-top: 30px; padding: 25px; background: #f0f4f8; border-radius: 12px; border-left: 5px solid #3b82f6;">
                            <h4 style="margin:0 0 10px 0; color:#1e293b;">🧪 Prueba de Envío Botique</h4>
                            <p style="color:#64748b; font-size:13px; margin-bottom:15px;">Verifica que tus credenciales funcionen antes de activarlo para los clientes.</p>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <input type="email" id="test_smtp_email" class="regular-text" style="margin:0;" placeholder="tu@correo.com" />
                                <button type="button" id="btn_test_smtp" class="button button-secondary">Realizar Prueba</button>
                                <span id="test_smtp_status" style="font-weight:bold;"></span>
                            </div>
                        </div>

                        <script>
                        jQuery(document).ready(function($) {
                            $('#btn_test_smtp').on('click', function() {
                                const email = $('#test_smtp_email').val();
                                if (!email) return alert('Ingresa un correo');
                                const btn = $(this);
                                btn.prop('disabled', true).text('⏳ Enviando...');
                                $('#test_smtp_status').html(' Procesando...').css('color', '#666');
                                $.post(ajaxurl, {
                                    action: 'expotodo_test_smtp',
                                    email: email,
                                    _ajax_nonce: '<?php echo wp_create_nonce("expotodo_test_smtp"); ?>'
                                }, function(res) {
                                    btn.prop('disabled', false).text('Realizar Prueba');
                                    if (res.success) $('#test_smtp_status').html(' ✅ Éxito').css('color', '#059669');
                                    else $('#test_smtp_status').html(' ❌ Error: ' + res.data).css('color', '#dc2626');
                                });
                            });
                        });
                        </script>

                    <?php elseif ($active_tab === 'tracking') : 
                        $ga = get_option('expotodo_google_analytics', '');
                    ?>
                        <h2 class="title" style="margin-bottom:20px;">Rastreo y Analítica</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="google_analytics">Google Analytics ID</label></th>
                                <td>
                                    <input name="google_analytics" type="text" id="google_analytics" value="<?php echo esc_attr($ga); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <p class="submit" style="margin-top:30px; border-top:1px solid #eee; padding-top:20px;">
                        <input type="submit" name="expotodo_save_settings" id="submit" class="button button-primary button-large" value="Guardar Cambios de <?php echo strtoupper($active_tab); ?>">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
}
