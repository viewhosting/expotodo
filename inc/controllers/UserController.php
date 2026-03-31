<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Usuarios y Autenticación
 * Gestiona el inicio de sesión AJAX, registro y gestión de perfiles.
 */
class UserController {

    public function __construct() {
        add_action( 'wp_ajax_expotodo_login', array( $this, 'ajax_login' ) );
        add_action( 'wp_ajax_nopriv_expotodo_login', array( $this, 'ajax_login' ) );
        add_action( 'wp_ajax_expotodo_update_profile', array( $this, 'ajax_update_profile' ) );
        add_action( 'wp_ajax_expotodo_change_password', array( $this, 'ajax_change_password' ) );
        add_action( 'wp_ajax_expotodo_get_address_form', array( $this, 'ajax_get_address_form' ) );
        add_action( 'wp_ajax_expotodo_save_address_ajax', array( $this, 'ajax_save_address' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_filter( 'show_admin_bar', '__return_false' );
    }

    public function enqueue_assets() {
        if ( is_page_template('page-cuenta.php') || is_account_page() ) {
            // Dashboard & Boutique Global Assets
            wp_enqueue_style( 'expotodo-account-dashboard', get_template_directory_uri() . '/assets/css/account_dashboard.css?ver=' . time() );
            wp_enqueue_script( 'expotodo-account-dashboard', get_template_directory_uri() . '/assets/js/account_dashboard.js?ver=' . time(), array('jquery'), '1.0.0', true );
            
            // Address Handling
            wp_enqueue_script( 'expotodo-account-address', get_template_directory_uri() . '/assets/js/account-address.js?ver=' . time(), array('jquery', 'bootstrap-js'), '1.0.0', true );
            wp_localize_script( 'expotodo-account-address', 'expotodo_account_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'expotodo_account_nonce' ),
            ));
        }
    }

    public function ajax_get_address_form() {
        check_ajax_referer( 'expotodo_account_nonce', 'security' );
        $address_type = isset($_POST['address_type']) ? sanitize_text_field($_POST['address_type']) : 'billing';
        
        ob_start();
        woocommerce_account_edit_address($address_type);
        $html = ob_get_clean();
        
        // Limpiar encabezados que WooCommerce inyecta por defecto (h2, h3) para que no ensucien el modal
        $html = preg_replace('/<(h2|h3)[^>]*>.*?<\/(h2|h3)>/si', '', $html);
        
        wp_send_json_success(array(
            'html' => trim($html),
            'address_type' => $address_type
        ));
    }

    public function ajax_save_address() {
        check_ajax_referer( 'expotodo_account_nonce', 'security' );
        $user_id = get_current_user_id();
        $address_type = isset($_POST['address_type']) ? sanitize_text_field($_POST['address_type']) : 'billing';

        foreach ($_POST as $key => $value) {
            if (strpos($key, $address_type . '_') === 0) {
                update_user_meta($user_id, $key, sanitize_text_field($value));
            }
        }
        
        wp_send_json_success('Dirección actualizada correctamente.');
    }

    public function ajax_login() {
        check_ajax_referer( 'expotodo_account_nonce', 'security' );

        $info = array();
        $info['user_login']    = isset($_POST['username']) ? sanitize_text_field( $_POST['username'] ) : '';
        $info['user_password'] = isset($_POST['password']) ? $_POST['password'] : ''; 
        $info['remember']      = true;

        $user_signon = wp_signon( $info, false );

        if ( is_wp_error( $user_signon ) ) {
            wp_send_json_error( array( 'message' => $user_signon->get_error_message() ) );
        } else {
            wp_send_json_success( array( 'message' => 'Inicio de sesión exitoso. Redirigiendo...' ) );
        }
    }

    public function ajax_update_profile() {
        check_ajax_referer( 'expotodo_account_nonce', 'security' );

        $current_user_id = get_current_user_id();
        if ( ! $current_user_id ) wp_send_json_error( array( 'message' => 'No tienes permiso.' ) );

        $first_name = isset($_POST['first_name']) ? sanitize_text_field( $_POST['first_name'] ) : '';
        $last_name  = isset($_POST['last_name']) ? sanitize_text_field( $_POST['last_name'] ) : '';

        if ( empty($first_name) || empty($last_name) ) {
            wp_send_json_error( array( 'message' => 'Faltan campos requeridos.' ) );
        }

        $user_id = wp_update_user( array(
            'ID'           => $current_user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
        ));
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        } else {
            wp_send_json_success( array( 'message' => 'Perfil actualizado correctamente.' ) );
        }
    }

    public function ajax_change_password() {
        check_ajax_referer( 'expotodo_account_nonce', 'security' );
        
        $user_id = get_current_user_id();
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if ( empty($password) || empty($confirm) ) {
            wp_send_json_error('Por favor, completa ambos campos.');
        }

        if ( $password !== $confirm ) {
            wp_send_json_error('Las contraseñas no coinciden.');
        }

        if ( strlen($password) < 6 ) {
            wp_send_json_error('La contraseña debe tener al menos 6 caracteres.');
        }

        reset_password(get_userdata($user_id), $password);
        
        wp_send_json_success('Contraseña actualizada con éxito.');
    }
}
