<?php
/**
 * ExpoTodo Theme Functions
 * Arquitectura Modular v2.0 - Refactorización por Controladores
 */

if (!defined('ABSPATH')) exit;

// 1. Cargador Maestro de Módulos
// Escanea 'inc/controllers/*.php' e inicializa cada clase automáticamente.
require_once get_template_directory() . '/inc/class-expotodo-loader.php';
new Expotodo_Loader();

/**
 * 2. Funciones globales de ayuda para compatibilidad con plantillas (Wrappers)
 * Estas funciones actúan como puente hacia los controladores correspondientes
 * para evitar romper la lógica de los archivos del tema (.php).
 */

/**
 * Obtiene la lista de deseos del usuario (Wrapper de WishlistController)
 */
function expotodo_get_user_wishlist($user_id = 0) {
    if (class_exists('WishlistController')) {
        return WishlistController::get_user_wishlist($user_id);
    }
    return array();
}

/**
 * Obtiene el HTML de la wishlist para el panel (Wrapper de WishlistController)
 */
function expotodo_get_wishlist_items_html($user_id = 0, $view = 'sidebar') {
    if (class_exists('WishlistController')) {
        return WishlistController::get_wishlist_items_html($user_id, $view);
    }
    return '';
}
