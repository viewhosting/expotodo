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
    }
}
