<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador del Menú Administrativo Expotodo
 * Orquesta la jerarquía del menú y enlaza las páginas de otros controladores.
 */
class AdminController {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
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
            'expotodo-email-queue', 
            array( 'EmailController', 'render_queue_page' )
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
                    update_option('expotodo_whatsapp', sanitize_text_field($_POST['whatsapp']));
                    update_option('expotodo_boutique_mode', isset($_POST['boutique_mode']) ? 'yes' : 'no');
                }

                if ($active_tab === 'smtp') {
                    update_option('expotodo_smtp_host', sanitize_text_field($_POST['smtp_host']));
                    update_option('expotodo_smtp_port', sanitize_text_field($_POST['smtp_port']));
                    update_option('expotodo_smtp_user', sanitize_text_field($_POST['smtp_user']));
                    if (!empty($_POST['smtp_pass'])) {
                        update_option('expotodo_smtp_pass', sanitize_text_field($_POST['smtp_pass']));
                    }
                    update_option('expotodo_smtp_secure', sanitize_text_field($_POST['smtp_secure']));
                    update_option('expotodo_smtp_from_name', sanitize_text_field($_POST['smtp_from_name']));
                }

                if ($active_tab === 'tracking') {
                    update_option('expotodo_google_analytics', sanitize_text_field($_POST['google_analytics']));
                }

                echo '<div class="updated"><p>¡Ajustes de la sección <strong>' . strtoupper($active_tab) . '</strong> actualizados correctamente!</p></div>';
            }
        }

        ?>
        <div class="wrap expotodo-admin-wrap">
            <h1>Configuración Boutique Expotodo</h1>
            <p class="description">Gestiona tu ecosistema digital desde un solo lugar.</p>
            <hr class="wp-header-end">

            <h2 class="nav-tab-wrapper">
                <a href="?page=expotodo-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=expotodo-settings&tab=smtp" class="nav-tab <?php echo $active_tab == 'smtp' ? 'nav-tab-active' : ''; ?>">Correo (SMTP)</a>
                <a href="?page=expotodo-settings&tab=tracking" class="nav-tab <?php echo $active_tab == 'tracking' ? 'nav-tab-active' : ''; ?>">Seguimiento</a>
            </h2>

            <form method="post" action="">
                <?php wp_nonce_field('expotodo_settings_verify'); ?>
                
                <div class="card" style="max-width: 900px; margin-top: 0; padding: 30px; border-top: none; border-radius: 0 0 12px 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                    
                    <?php if ($active_tab === 'general') : 
                        $whatsapp = get_option('expotodo_whatsapp', '');
                        $boutique = get_option('expotodo_boutique_mode', 'yes');
                    ?>
                        <h2 class="title">Identidad y Estilo</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="whatsapp">WhatsApp Directo</label></th>
                                <td>
                                    <input name="whatsapp" type="text" id="whatsapp" value="<?php echo esc_attr($whatsapp); ?>" class="regular-text" placeholder="Ej: 5219991234567">
                                    <p class="description">Número que alimenta el header y botones flotantes.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Capas Visuales Boutique</th>
                                <td>
                                    <label class="switch">
                                        <input name="boutique_mode" type="checkbox" id="boutique_mode" value="yes" <?php checked('yes', $boutique); ?>>
                                        Activar Dashboard avanzado y Toasts informativos en el Front-end.
                                    </label>
                                </td>
                            </tr>
                        </table>

                    <?php elseif ($active_tab === 'smtp') : 
                        $smtp_host = get_option('expotodo_smtp_host', '');
                        $smtp_port = get_option('expotodo_smtp_port', '465');
                        $smtp_user = get_option('expotodo_smtp_user', '');
                        $smtp_secure = get_option('expotodo_smtp_secure', 'ssl');
                        $smtp_from_name = get_option('expotodo_smtp_from_name', 'Expotodo Boutique');
                    ?>
                        <h2 class="title">Motor de Envío Profesional</h2>
                        <p class="description">Configura las credenciales de tu servidor de correo para evitar el SPAM.</p>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="smtp_host">Servidor SMTP</label></th>
                                <td><input name="smtp_host" type="text" id="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_port">Puerto</label></th>
                                <td><input name="smtp_port" type="text" id="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="small-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_user">Usuario (Email)</label></th>
                                <td><input name="smtp_user" type="text" id="smtp_user" value="<?php echo esc_attr($smtp_user); ?>" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_pass">Contraseña</label></th>
                                <td>
                                    <input name="smtp_pass" type="password" id="smtp_pass" value="" class="regular-text">
                                    <p class="description">Se mantiene la actual si se deja vacío.</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="smtp_secure">Cifrado</label></th>
                                <td>
                                    <select name="smtp_secure" id="smtp_secure">
                                        <option value="ssl" <?php selected('ssl', $smtp_secure); ?>>SSL</option>
                                        <option value="tls" <?php selected('tls', $smtp_secure); ?>>TLS</option>
                                        <option value="none" <?php selected('none', $smtp_secure); ?>>Sin cifrado</option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                    <?php elseif ($active_tab === 'tracking') : 
                        $ga = get_option('expotodo_google_analytics', '');
                    ?>
                        <h2 class="title">Analítica y Rastreo</h2>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row"><label for="google_analytics">Google Analytics ID</label></th>
                                <td>
                                    <input name="google_analytics" type="text" id="google_analytics" value="<?php echo esc_attr($ga); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                                    <p class="description">Pega tu ID para activar el rastreo de eventos boutique.</p>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <p class="submit">
                        <input type="submit" name="expotodo_save_settings" id="submit" class="button button-primary button-large" value="Guardar Cambios de <?php echo strtoupper($active_tab); ?>">
                    </p>
                </div>
            </form>
        </div>
        <?php
    }
}
