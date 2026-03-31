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
    }

    public function ajax_login() {
        check_ajax_referer( 'expotodo_login_nonce', 'security' );

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
        check_ajax_referer( 'expotodo_profile_nonce', 'security' );

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
}
