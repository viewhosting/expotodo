<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de Configuración del Tema
 * Maneja soportes de tema, encolado de scripts y estilos base.
 */
class ThemeController {

    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'init', array( $this, 'debug_manual_triggers' ) );
        add_filter( 'woocommerce_get_privacy_policy_text', array( $this, 'fix_privacy_policy_placeholder' ), 20 );
        
        // Live Search
        add_action( 'wp_ajax_expotodo_live_search', array( $this, 'ajax_live_search' ) );
        add_action( 'wp_ajax_nopriv_expotodo_live_search', array( $this, 'ajax_live_search' ) );

        // Filtros para clases de menú Bootstrap 5
        add_filter( 'nav_menu_css_class', array( $this, 'add_li_class' ), 10, 3 );
        add_filter( 'nav_menu_link_attributes', array( $this, 'add_a_class' ), 10, 3 );
    }

    public function theme_setup() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
        
        register_nav_menus( array(
            'primary' => __( 'Menú Principal', 'expotodo' ),
        ) );
    }

    public function enqueue_assets() {
        // Estilos base y externos
        wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0' );
        wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );
        wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Open+Sans:wght@400;600&display=swap', array(), null );
        wp_enqueue_style( 'fancybox-css', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css', array(), '5.0' );
        wp_enqueue_script( 'fancybox-js', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js', array(), '5.0', true );

        // Estilos del tema
        wp_enqueue_style( 'expotodo-style', get_template_directory_uri() . '/assets/css/main.css', array('bootstrap', 'fancybox-css'), '1.1.0' );
        wp_enqueue_style( 'expotodo-filtro', get_template_directory_uri() . '/assets/css/filtro.css?ver='.rand(1,9999), array('expotodo-style'), '1.0.0' );

        if ( is_page_template('page-cuenta.php') || is_account_page() ) {
            wp_enqueue_style( 'expotodo-cuenta', get_template_directory_uri() . '/assets/css/cuenta.css?ver='.rand(1,9999), array('expotodo-style'), '1.0.0' );
        }

        if ( is_cart() || is_page_template('page-cart.php') ) {
            wp_enqueue_style( 'expotodo-carrito', get_template_directory_uri() . '/assets/css/pagina_carrito.css?ver='.rand(1,9999), array('expotodo-style'), '1.0.0' );
        }

        // Scripts base
        wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true );
        wp_enqueue_script( 'expotodo-script', get_template_directory_uri() . '/assets/js/script.js?ver='.rand(1,9999), array('jquery'), '1.0.0', true );
        
        wp_localize_script( 'expotodo-script', 'expotodo_globals', array(
            'ajax_url'       => admin_url( 'admin-ajax.php' ),
            'login_nonce'    => wp_create_nonce( 'expotodo_login_nonce' ),
            'profile_nonce'  => wp_create_nonce( 'expotodo_profile_nonce' ),
            'redirect_url'   => get_permalink( get_option('woocommerce_myaccount_page_id') ),
            'checkout_url'   => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/'),
            'checkout_nonce' => wp_create_nonce( 'woocommerce-process_checkout' ),
        ));

        wp_enqueue_script( 'expotodo-login-js', get_template_directory_uri() . '/assets/js/expotodo-login.js?ver=' . time(), array('jquery', 'expotodo-script'), '1.0.0', true );

        // Filtros de tienda
        if ( is_shop() || is_product_category() || is_page_template('page-lista-productos.php') || is_page('lista-productos') || is_page('productos') ) {
            wp_enqueue_script( 'expotodo-ajax-shop-js', get_template_directory_uri() . '/assets/js/ajax-shop.js?ver='.rand(1,9999), array('jquery'), '1.0.0', true );
            wp_localize_script( 'expotodo-ajax-shop-js', 'expotodo_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce('expotodo_filter_nonce')
            ));
        }

        // Thank you & Account Boutique
        if ( is_order_received_page() ) {
            wp_enqueue_style( 'expotodo-thankyou', get_template_directory_uri() . '/assets/css/pagina_gracias.css', array(), '1.1.0' );
        }
        if ( is_view_order_page() ) {
            wp_enqueue_style( 'expotodo-view-order', get_template_directory_uri() . '/assets/css/pagina_view_order.css', array(), '1.0.0' );
        }
        if ( is_account_page() ) {
            wp_enqueue_style( 'expotodo-account-boutique', get_template_directory_uri() . '/assets/css/pagina_cuenta.css', array(), '1.1.0' );
        }
    }

    public function debug_manual_triggers() {
        if ( isset( $_GET['test_toast'] ) ) {
            wc_add_notice( '¡Prueba de Toast exitosa! El sistema funciona.', 'success' );
            wp_redirect( remove_query_arg( 'test_toast' ) );
            exit;
        }
        if ( isset( $_GET['test_success'] ) ) {
            wc_add_notice( '¡Victoria! El sistema de avisos está vivo.', 'success' );
        }
    }

    public function fix_privacy_policy_placeholder( $text ) {
        $privacy_url = get_privacy_policy_url();
        if ( ! $privacy_url ) return $text;

        $link = '<a href="' . esc_url( $privacy_url ) . '" class="woocommerce-privacy-policy-link" target="_blank">' . __( 'política de privacidad', 'woocommerce' ) . '</a>';
        
        $text = str_replace( '[privacy_policy]', $link, $text );
        $text = str_replace( '[política_de_privacidad]', $link, $text );
        $text = str_replace( '[politica_de_privacidad]', $link, $text );
        
        return $text;
    }

    public function ajax_live_search() {
        $query = isset($_POST['query']) ? sanitize_text_field( $_POST['query'] ) : '';

        if ( empty($query) || strlen($query) < 2 ) {
            wp_send_json_success( array( 'html' => '' ) );
        }

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 8,
            'status'         => 'publish',
            's'              => $query,
        );

        $loop = new WP_Query( $args );

        ob_start();
        if ( $loop->have_posts() ) :
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                ?>
                <a href="<?php the_permalink(); ?>" class="search-result-item d-flex align-items-center p-3 text-decoration-none border-bottom">
                    <div class="search-result-image me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                        <?php 
                        if ( has_post_thumbnail() ) {
                            the_post_thumbnail( array(50, 50), array( 'class' => 'rounded shadow-sm w-100 h-100', 'style' => 'object-fit: cover;' ) );
                        } else {
                            echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="50" height="50" class="rounded shadow-sm w-100 h-100" style="object-fit: cover;">';
                        }
                        ?>
                    </div>
                    <div class="search-result-info overflow-hidden">
                        <h6 class="mb-0 text-dark text-truncate"><?php the_title(); ?></h6>
                        <small class="text-muted d-block mt-1">
                            <?php if ( $product->get_sku() ) : ?>
                                <span class="badge bg-light text-dark border me-1"><?php echo esc_html( $product->get_sku() ); ?></span>
                            <?php endif; ?>
                            <?php echo $product->get_price_html(); ?>
                        </small>
                    </div>
                </a>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<div class="p-4 text-center text-muted">No se encontraron productos para "' . esc_html($query) . '".</div>';
        endif;

        $content = ob_get_clean();
        wp_send_json_success( array( 'html' => $content ) );
        wp_die();
    }

    /**
     * Añade la clase 'nav-item' a los elementos <li> del menú
     */
    public function add_li_class( $classes, $item, $args ) {
        if ( isset( $args->theme_location ) && $args->theme_location == 'primary' ) {
            $classes[] = 'nav-item pt-3';
        }
        return $classes;
    }

    /**
     * Añade la clase 'nav-link' a los elementos <a> del menú
     */
    public function add_a_class( $atts, $item, $args ) {
        if ( isset( $args->theme_location ) && $args->theme_location == 'primary' ) {
            $atts['class'] = 'nav-link';
        }
        return $atts;
    }
}
