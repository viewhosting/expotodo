<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador del Menú Administrativo Expotodo
 * Orquesta la jerarquía del menú y enlaza las páginas de otros controladores.
 */
class AdminController {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        
        // Estilos personalizados para la página de login de WordPress (wp-login.php)
        add_action( 'login_enqueue_scripts', array( $this, 'custom_login_styles' ) );
        add_filter( 'login_headerurl', array( $this, 'custom_login_logo_url' ) );
        add_filter( 'login_headertext', array( $this, 'custom_login_logo_title' ) );
        
        // Desactivar selector de idioma en login
        add_filter( 'login_display_language_dropdown', '__return_false' );
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
            'Reseñas', 
            'Reseñas', 
            'manage_options', 
            'expotodo-reviews', 
            array( 'ReviewsController', 'render_reviews_page' )
        );

        add_submenu_page(
            'expotodo-main', 
            'Cortafuegos', 
            'Cortafuegos', 
            'manage_options', 
            'expotodo-firewall', 
            function() {
                if ( class_exists( 'FirewallController' ) ) {
                    FirewallController::render_admin_page();
                } else {
                    echo '<div class="wrap"><h1>Cortafuegos no cargado.</h1></div>';
                }
            }
        );
    }

    public function custom_login_styles() {
        ?>
        <style type="text/css">
            body.login {
                background-image: linear-gradient(135deg, rgba(140, 168, 53, 0.4) 0%, rgba(176, 212, 67, 0.85) 100%), url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/login_hero.png' ); ?>') !important;
                background-size: cover !important;
                background-position: center center !important;
                background-repeat: no-repeat !important;
                background-attachment: fixed !important;
                color: #1e293b !important;
                font-family: 'Roboto', 'Segoe UI', sans-serif !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: 100vh !important;
                padding: 0 !important;
                margin: 0 !important;
                position: relative !important;
                backdrop-filter: blur(5px) !important;
            }
            body.login::before {
                content: "" !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                background: rgba(0, 0, 0, 0.2) !important;
                z-index: 1 !important;
                pointer-events: none !important;
            }
            body.login div#login {
                padding: 40px 0 !important;
                width: 400px !important;
                margin: auto !important;
                position: relative !important;
                z-index: 10 !important;
            }
            body.login h1 a {
                background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>') !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                width: 100% !important;
                height: 80px !important;
                margin-bottom: 30px !important;
                display: block !important;
            }
            body.login form#loginform {
                background: rgba(255, 255, 255, 0.96) !important;
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
                border-radius: 20px !important;
                box-shadow: 0 10px 20px rgba(255, 255, 255, 0.7) !important;
                padding: 40px 30px !important;
                backdrop-filter: blur(10px) !important;
                -webkit-backdrop-filter: blur(10px) !important;
            }
            body.login label {
                color: #334155 !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                display: block !important;
                margin-bottom: 8px !important;
            }
            body.login .input {
                border: 1px solid #cbd5e1 !important;
                border-radius: 10px !important;
                padding: 12px 16px !important;
                font-size: 15px !important;
                color: #1e293b !important;
                background-color: #ffffff !important;
                box-shadow: none !important;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                margin-top: 5px !important;
                margin-bottom: 20px !important;
                width: 100% !important;
                height: 48px !important;
                box-sizing: border-box !important;
            }
            body.login .input:focus {
                border-color: #8ca835 !important;
                box-shadow: 0 0 0 3px rgba(176, 212, 67, 0.25) !important;
                outline: none !important;
            }
            body.login .wp-core-ui .button-primary {
                background: #b0d443 !important;
                border: 1px solid #8ca835 !important;
                color: #1e293b !important;
                font-weight: 700 !important;
                font-size: 15px !important;
                height: 46px !important;
                line-height: 44px !important;
                padding: 0 20px !important;
                border-radius: 10px !important;
                box-shadow: 0 4px 10px rgba(176, 212, 67, 0.2) !important;
                text-shadow: none !important;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                width: 100% !important;
                margin-top: 15px !important;
                float: none !important;
                display: block !important;
            }
            body.login .wp-core-ui .button-primary:hover {
                background: #8ca835 !important;
                border-color: #8ca835 !important;
                color: #ffffff !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 6px 15px rgba(140, 168, 53, 0.35) !important;
            }
            body.login .wp-core-ui .button-primary:active {
                transform: translateY(0) !important;
            }
            body.login #nav, body.login #backtoblog {
                text-align: center !important;
                padding: 0 !important;
                margin: 20px 0 0 !important;
            }
            body.login #nav a, body.login #backtoblog a {
                color: #f1f5f9 !important;
                font-size: 13px !important;
                font-weight: 500 !important;
                transition: color 0.2s ease !important;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5) !important;
            }
            body.login #nav a:hover, body.login #backtoblog a:hover {
                color: #b0d443 !important;
            }
            .privacy-policy-page-link {
                text-align: center !important;
                margin-top: 20px !important;
            }
            .privacy-policy-page-link a {
                color: #f1f5f9 !important;
                font-size: 12px !important;
                font-weight: 500 !important;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5) !important;
            }
            .privacy-policy-page-link a:hover {
                color: #b0d443 !important;
            }
            /* Checkbox */
            body.login form#loginform .forgetmenot {
                float: none !important;
                margin-bottom: 20px !important;
                display: flex !important;
                align-items: center !important;
            }
            body.login form#loginform .forgetmenot label {
                margin: 0 0 0 8px !important;
                font-weight: 500 !important;
                display: inline-block !important;
                cursor: pointer !important;
            }
            body.login form#loginform input[type="checkbox"] {
                margin: 0 !important;
                border-radius: 4px !important;
                border: 1px solid #cbd5e1 !important;
                width: 18px !important;
                height: 18px !important;
                cursor: pointer !important;
            }
            body.login form#loginform input[type="checkbox"]:focus {
                border-color: #8ca835 !important;
                box-shadow: 0 0 0 2px rgba(176, 212, 67, 0.2) !important;
            }
            /* Ocultar selector de idioma en caso de que falle el filtro de php */
            #language-switcher, .language-switcher {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Cambia la URL del logo al Home del sitio
     */
    public function custom_login_logo_url() {
        return home_url();
    }

    /**
     * Cambia el título emergente del logo al nombre de la tienda
     */
    public function custom_login_logo_title() {
        return get_bloginfo( 'name' );
    }
}
