<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Lista de Deseos (Wishlist)
 * Maneja la lógica de favoritos, panel lateral y portal administrativo.
 */
class WishlistController {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_expotodo_toggle_wishlist', array( $this, 'ajax_toggle_wishlist' ) );
        add_action( 'wp_ajax_expotodo_get_wishlist_panel_content', array( $this, 'ajax_get_panel_content' ) );
        add_action( 'wp_ajax_nopriv_expotodo_get_wishlist_panel_content', array( $this, 'ajax_get_panel_content' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_script( 'expotodo-wishlist-js', get_template_directory_uri() . '/assets/js/wishlist-boutique.js?ver='.rand(1,9999), array('jquery'), '1.1.0', true );
        wp_localize_script( 'expotodo-wishlist-js', 'expotodo_wishlist_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'expotodo_wishlist_nonce' ),
        ));
    }

    public static function get_user_wishlist($user_id = 0) {
        if (!$user_id) $user_id = get_current_user_id();
        if (!$user_id) return array();
        
        $wishlist = get_user_meta($user_id, '_expotodo_wishlist', true);
        return is_array($wishlist) ? $wishlist : array();
    }

    public function ajax_toggle_wishlist() {
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $user_id = get_current_user_id();

        if (!$user_id) {
            wp_send_json_error(array('message' => 'Inicia sesión para guardar favoritos', 'require_login' => true));
        }

        if (!$product_id) wp_send_json_error('Producto no válido.');

        $wishlist = self::get_user_wishlist($user_id);
        $action = '';

        if (in_array($product_id, $wishlist)) {
            $wishlist = array_diff($wishlist, array($product_id));
            $action = 'removed';
        } else {
            $wishlist[] = $product_id;
            $action = 'added';
        }

        update_user_meta($user_id, '_expotodo_wishlist', $wishlist);
        
        wp_send_json_success(array(
            'action' => $action,
            'count'  => count($wishlist),
            'html'   => self::get_wishlist_items_html($user_id)
        ));
    }

    public function ajax_get_panel_content() {
        $user_id = get_current_user_id();
        $html = self::get_wishlist_items_html($user_id);
        $count = count(self::get_user_wishlist($user_id));
        
        wp_send_json_success(array('html' => $html, 'count' => $count));
    }

    public static function get_wishlist_items_html($user_id = 0, $view = 'sidebar') {
        $wishlist = self::get_user_wishlist($user_id);
        
        if (empty($wishlist)) {
            return '<div class="p-5 text-center text-muted"><i class="far fa-heart fa-3x mb-3 opacity-25"></i><p>Tu lista de deseos está vacía.</p></div>';
        }

        ob_start();
        $container_class = ($view === 'grid') ? 'row g-3 p-3' : 'wishlist-sidebar-list';
        ?>
        <div class="wishlist-wrapper <?php echo $container_class; ?>">
            <?php foreach ($wishlist as $item_id) : 
                $product = wc_get_product($item_id);
                if (!$product) continue;
                
                if ($view === 'grid') : ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="wishlist-grid-item card h-100 border-0 shadow-sm overflow-hidden">
                            <div class="position-relative">
                                <a href="<?php echo get_permalink($item_id); ?>">
                                    <?php echo $product->get_image('medium', array('class' => 'card-img-top', 'style' => 'height: 200px; object-fit: cover;')); ?>
                                </a>
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-2 btn-remove-wishlist" data-id="<?php echo $item_id; ?>">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="card-title mb-1 text-truncate"><?php echo $product->get_name(); ?></h6>
                                <div class="price mb-3 fw-bold text-primary"><?php echo $product->get_price_html(); ?></div>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn btn-primary btn-sm ajax_add_to_cart" data-product_id="<?php echo $item_id; ?>">Al carrito</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="wishlist-item d-flex align-items-center p-3 border-bottom">
                        <div class="wishlist-item-image me-3" style="width: 70px; height: 70px; overflow: hidden; border-radius: 12px;">
                            <a href="<?php echo get_permalink($item_id); ?>">
                                <?php echo $product->get_image('thumbnail', array('class' => 'img-fluid h-100', 'style' => 'object-fit: cover;')); ?>
                            </a>
                        </div>
                        <div class="wishlist-item-details flex-grow-1">
                            <h6 class="mb-1 small fw-bold text-truncate"><?php echo $product->get_name(); ?></h6>
                            <div class="wishlist-item-price text-primary fw-bold small"><?php echo $product->get_price_html(); ?></div>
                        </div>
                        <div class="ms-2">
                            <button class="btn btn-sm btn-light p-2 btn-remove-wishlist" data-id="<?php echo $item_id; ?>">
                                <i class="fas fa-times text-muted"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_admin_page() {
        ?>
        <div class="wrap expotodo-admin-boutique">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-heart" style="color: #dc3545;"></span> 
                Smart Wishlist Insights
            </h1>
            <div class="expotodo-admin-dashboard" style="background: #fff; border: 1px solid #ccd0d4; margin-top: 25px;">
                <div class="dashboard-header" style="background: #f8f9fa; border-bottom: 1px solid #ccd0d4; padding: 15px 20px; display: flex; font-weight: 600;">
                    <div style="width: 250px;">CLIENTE</div>
                    <div style="flex-grow: 1;">PRODUCTOS EN LISTA DE DESEOS</div>
                </div>
                <?php
                $users = get_users(array('meta_key' => '_expotodo_wishlist', 'meta_compare' => 'EXISTS'));
                if (empty($users)) : ?>
                    <div style="padding: 40px; text-align: center;">No hay listas registradas.</div>
                <?php else :
                    foreach ($users as $user) :
                        $wishlist = self::get_user_wishlist($user->ID);
                        if (empty($wishlist)) continue;
                        ?>
                        <div style="display: flex; border-bottom: 1px solid #f0f0f1;">
                            <div style="width: 250px; padding: 20px; border-right: 1px solid #f0f0f1; background: #fdfdfd;">
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <small><?php echo esc_html($user->user_email); ?></small>
                            </div>
                            <div style="flex-grow: 1; padding: 20px;">
                                <?php foreach ($wishlist as $pid) : 
                                    $p = wc_get_product($pid);
                                    if ($p) : ?>
                                        <span style="display: inline-block; padding: 4px 10px; background: #f0f6fb; border-radius: 12px; margin: 2px;"><?php echo $p->get_name(); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
        <?php
    }
}
