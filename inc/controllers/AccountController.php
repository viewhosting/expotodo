<?php
if (!defined('ABSPATH')) exit;

/**
 * Account Controller
 * Gestiona el Dashboard y las estadísticas del usuario
 */
class AccountController {

    public function __construct() {
        // Acciones AJAX si fueran necesarias
    }

    /**
     * Obtiene estadísticas de la cuenta del usuario para el Dashboard
     */
    public static function get_user_stats($user_id) {
        $stats = array(
            'orders_count'    => 0,
            'wishlist_count'  => 0,
            'addresses_set'   => 0,
            'total_spent'     => '0.00 €',
            'last_order_status' => 'Sin pedidos'
        );

        if (!$user_id) return $stats;

        // Contar pedidos
        $customer_orders = wc_get_orders(array(
            'customer' => $user_id,
            'limit' => -1,
            'status' => array_keys(wc_get_order_statuses())
        ));
        $stats['orders_count'] = count($customer_orders);

        if (!empty($customer_orders)) {
            $last_order = reset($customer_orders);
            $stats['last_order_status'] = wc_get_order_status_name($last_order->get_status());
        }

        // Contar wishlist
        $wishlist = function_exists('expotodo_get_user_wishlist') ? expotodo_get_user_wishlist() : array();
        $stats['wishlist_count'] = count($wishlist);

        // Verificar direcciones
        $billing_address = get_user_meta($user_id, 'billing_address_1', true);
        $shipping_address = get_user_meta($user_id, 'shipping_address_1', true);
        if (!empty($billing_address)) $stats['addresses_set']++;
        if (!empty($shipping_address)) $stats['addresses_set']++;

        // Gasto total (Opcional, estilo premium)
        if (function_exists('wc_get_customer_total_spent')) {
            $stats['total_spent'] = wc_price(wc_get_customer_total_spent($user_id));
        }

        return $stats;
    }

    /**
     * Renderiza el Dashboard con las tarjetas premium
     */
    public function render_dashboard_stats($user_id) {
        $stats = self::get_user_stats($user_id);
        $current_user = get_userdata($user_id);
        
        ob_start(); ?>
        <div class="account-welcome mb-5">
            <h2 class="fw-bold mb-1">¡Hola, <?php echo esc_html($current_user->first_name ?: $current_user->display_name); ?>! 👋</h2>
            <p class="text-muted">Desde tu escritorio puedes ver tus pedidos recientes, gestionar tus direcciones y editar tu contraseña.</p>
        </div>

        <div class="account-dashboard-grid">
            <!-- Pedidos -->
            <a href="#pedidos" class="stat-card" onclick="jQuery('#tab-pedidos-link').tab('show'); return false;">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-value"><?php echo esc_html($stats['orders_count']); ?></div>
                <div class="stat-label">Pedidos Realizados</div>
            </a>

            <!-- Wishlist -->
            <a href="#wishlist" class="stat-card" onclick="jQuery('#tab-wishlist-link').tab('show'); return false;">
                <div class="stat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-value"><?php echo esc_html($stats['wishlist_count']); ?></div>
                <div class="stat-label">Productos en Wishlist</div>
            </a>

            <!-- Direcciones -->
            <a href="#direcciones" class="stat-card" onclick="jQuery('#tab-direcciones-link').tab('show'); return false;">
                <div class="stat-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="stat-value"><?php echo esc_html($stats['addresses_set']); ?>/2</div>
                <div class="stat-label">Direcciones Perfiladas</div>
            </a>

            <!-- Inversión Total -->
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_spent']; ?></div>
                <div class="stat-label">Inversión en la tienda</div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Helper global
if (!function_exists('expotodo_render_account_dashboard')) {
    function expotodo_render_account_dashboard($user_id) {
        $controller = new AccountController();
        return $controller->render_dashboard_stats($user_id);
    }
}
