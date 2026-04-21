<?php
if (!defined('ABSPATH')) exit;

/**
 * Controlador de WooCommerce y Checkout
 * Optimiza el flujo de compra, filtros de productos y checkout personalizado.
 */
class WooCommerceController {

    public function __construct() {
        // Checkout & Fields
        add_filter( 'woocommerce_checkout_fields', array( $this, 'custom_checkout_fields' ) );
        add_action( 'wp_ajax_expotodo_get_cities', array( $this, 'ajax_get_cities' ) );
        add_action( 'wp_ajax_nopriv_expotodo_get_cities', array( $this, 'ajax_get_cities' ) );
        
        // Product Filters
        add_action( 'wp_ajax_expotodo_filter_products', array( $this, 'ajax_filter_products' ) );
        add_action( 'wp_ajax_nopriv_expotodo_filter_products', array( $this, 'ajax_filter_products' ) );
        
        // Cart AJAX
        add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
        add_action( 'wp_ajax_expotodo_get_checkout_data', array( $this, 'ajax_get_checkout_data' ) );
        add_action( 'wp_ajax_nopriv_expotodo_get_checkout_data', array( $this, 'ajax_get_checkout_data' ) );
        
        // Checkout Logic & MP Sync
        add_action( 'woocommerce_checkout_process', array( $this, 'sync_checkout_fields' ) );
        add_action( 'wp_ajax_expotodo_update_checkout_qty', array( $this, 'ajax_update_qty' ) );
        add_action( 'wp_ajax_nopriv_expotodo_update_checkout_qty', array( $this, 'ajax_update_qty' ) );
        add_action( 'wp_ajax_expotodo_remove_checkout_item', array( $this, 'ajax_remove_item' ) );
        add_action( 'wp_ajax_nopriv_expotodo_remove_checkout_item', array( $this, 'ajax_remove_item' ) );
        
        // MP Redirect Fixes
        add_filter( 'woocommerce_payment_successful_result', array( $this, 'force_mercadopago_redirect' ), 9999, 2 );
        add_filter( 'woocommerce_mercadopago_preference_body', array( $this, 'custom_mp_back_urls' ) );
        
        // UI Cleanups
        add_filter( 'woocommerce_add_notice', array( $this, 'kill_zone_notices_at_birth' ), 999 );
        add_filter( 'woocommerce_shipping_package_name', '__return_empty_string', 999 );
        add_filter( 'woocommerce_checkout_billing_fields_title', '__return_empty_string' );
        
        // Account Address Form
        add_action( 'wp_ajax_expotodo_get_address_form', array( $this, 'ajax_get_address_form' ) );
        add_action( 'wp_ajax_expotodo_save_address_ajax', array( $this, 'ajax_save_address' ) );
    }

    public function custom_checkout_fields( $fields ) {
        // Billing
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_email']['priority'] = 30;
        $fields['billing']['billing_phone']['priority'] = 40;
        $fields['billing']['billing_state']['priority'] = 50;
        $fields['billing']['billing_city']['priority'] = 60;
        $fields['billing']['billing_postcode']['priority'] = 70;
        $fields['billing']['billing_address_1']['priority'] = 80;
        $fields['billing']['billing_address_2']['priority'] = 90;

        unset($fields['billing']['billing_company']);
        $fields['billing']['billing_country']['default'] = 'MX';
        $fields['billing']['billing_country']['class'] = array('d-none');

        // Invoice Fields (Boutique)
        $fields['billing']['billing_rfc'] = array('type' => 'text', 'label' => 'RFC', 'class' => array('form-row-first', 'invoice-field'), 'priority' => 100);
        $fields['billing']['billing_company_name'] = array('type' => 'text', 'label' => 'Razón Social', 'class' => array('form-row-last', 'invoice-field'), 'priority' => 101);
        $fields['billing']['billing_cfdi_usage'] = array('type' => 'select', 'label' => 'Uso de CFDI', 'class' => array('form-row-wide', 'invoice-field'), 'options' => array('G01' => 'G01', 'G03' => 'G03', 'S01' => 'S01'), 'priority' => 102);

        return $fields;
    }

    public function ajax_get_cities() {
        $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
        
        $cities_by_state = array(
            'AG' => array('Aguascalientes', 'Asientos', 'Calvillo', 'Cosío', 'Jesús María', 'Pabellón de Arteaga', 'Rincón de Romos', 'San José de Gracia', 'Tepezalá', 'El Llano', 'San Francisco de los Romo'),
            'BC' => array('Ensenada', 'Mexicali', 'Tecate', 'Tijuana', 'Playas de Rosarito', 'San Quintín', 'San Felipe'),
            'BS' => array('La Paz', 'Los Cabos', 'Comondú', 'Loreto', 'Mulegé'),
            'CM' => array('Campeche', 'Carmen', 'Champotón', 'Escárcega', 'Calkiní', 'Hecelchakán', 'Hopelchén', 'Palizada', 'Tenabo', 'Candelaria', 'Calakmul'),
            'CO' => array('Saltillo', 'Torreón', 'Monclova', 'Piedras Negras', 'Acuña', 'Matamoros', 'San Pedro', 'Ramos Arizpe', 'Frontera', 'Múzquiz'),
            'CL' => array('Colima', 'Manzanillo', 'Tecomán', 'Villa de Álvarez', 'Armería', 'Coquimatlán', 'Cuauhtémoc', 'Ixtlahuacán', 'Minatitlán', 'Comala'),
            'JA' => array('Guadalajara', 'Zapopan', 'Tlaquepaque', 'Tonalá', 'Puerto Vallarta', 'Tlajomulco de Zúñiga', 'Lagos de Moreno', 'Tepatitlán de Morelos', 'Ciudad Guzmán', 'Ocotlán'),
            'MX' => array('Ecatepec de Morelos', 'Nezahualcóyotl', 'Toluca de Lerdo', 'Naucalpan de Juárez', 'Chimalhuacán', 'Tlalnepantla de Baz', 'Cuautitlán Izcalli', 'Tecámac', 'Ixtapaluca', 'Atizapán de Zaragoza'),
            'MI' => array('Morelia', 'Uruapan', 'Zamora', 'Lázaro Cárdenas', 'Zitácuaro', 'Apatzingán', 'La Piedad', 'Pátzcuaro', 'Sahuayo', 'Maravatío'),
            'MO' => array('Cuernavaca', 'Jiutepec', 'Cuautla', 'Temixco', 'Yautepec', 'Emiliano Zapata', 'Zacatepec', 'Xochitepec', 'Tlaltizapán', 'Jojutla'),
            'NA' => array('Tepic', 'Xalisco', 'Santiago Ixcuintla', 'Bahía de Banderas', 'Compostela', 'Ixtlán del Río', 'Tecuala', 'San Blas', 'Acaponeta', 'Tuxpan'),
            'NL' => array('Monterrey', 'Guadalupe', 'Apodaca', 'San Nicolás de los Garza', 'General Escobedo', 'Santa Catarina', 'Juárez', 'García', 'San Pedro Garza García', 'Cadereyta Jiménez'),
            'OA' => array('Oaxaca de Juárez', 'San Juan Bautista Tuxtepec', 'Salina Cruz', 'Juchitán de Zaragoza', 'Santa Cruz Xoxocotlán', 'Huajuapan de León', 'Santo Domingo Tehuantepec', 'Loma Bonita', 'Miahuatlán de Porfirio Díaz', 'Puerto Escondido'),
            'PU' => array('Puebla', 'Tehuacán', 'Cholula', 'Atlixco', 'San Martín Texmelucan'),
            'QE' => array('Santiago de Querétaro', 'San Juan del Río', 'El Marqués', 'Corregidora', 'Tequisquiapan'),
            'QR' => array('Cancún', 'Playa del Carmen', 'Chetumal', 'Cozumel', 'Tulum'),
            'SL' => array('San Luis Potosí', 'Soledad de Graciano Sánchez', 'Ciudad Valles', 'Matehuala', 'Rioverde'),
            'SI' => array('Culiacán', 'Mazatlán', 'Los Mochis', 'Guasave', 'Guamúchil'),
            'SO' => array('Hermosillo', 'Ciudad Obregón', 'Nogales', 'San Luis Río Colorado', 'Navojoa'),
            'TB' => array('Villahermosa', 'Cárdenas', 'Comalcalco', 'Huimanguillo', 'Macuspana'),
            'TM' => array('Reynosa', 'Matamoros', 'Nuevo Laredo', 'Tampico', 'Ciudad Victoria'),
            'TL' => array('Tlaxcala', 'Apizaco', 'Huamantla', 'Chiautempan', 'Zacatelco'),
            'VE' => array('Veracruz', 'Xalapa', 'Coatzacoalcos', 'Córdoba', 'Poza Rica'),
            'YU' => array('Mérida', 'Kanasín', 'Valladolid', 'Tizimín', 'Progreso'),
            'ZA' => array('Zacatecas', 'Guadalupe', 'Fresnillo', 'Jerez', 'Río Grande'),
        );

        if ( isset( $cities_by_state[ $state ] ) ) {
            wp_send_json_success( $cities_by_state[ $state ] );
        } else {
            wp_send_json_error( array( 'message' => 'No se encontraron ciudades para este estado.' ) );
        }
    }

    public function ajax_filter_products() {
        $category = isset($_POST['categories']) ? (array) $_POST['categories'] : array();
        $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
        $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 999999;

        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 16,
            'paged'          => $paged,
            'tax_query'      => array('relation' => 'AND'),
            'meta_query'     => array('relation' => 'AND'),
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        // Asegurar visibilidad en el catálogo
        $args['tax_query'][] = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'exclude-from-catalog',
            'operator' => 'NOT IN',
        );

        // Filtro por Categorías
        if ( !empty($category) && $category[0] !== 'all' ) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category
            );
        }

        // Filtro por Precio (Fijando la lógica que faltaba)
        if ($min_price > 0 || $max_price < 999999) {
            $args['meta_query'][] = array(
                'key'     => '_price',
                'value'   => array($min_price, $max_price),
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC'
            );
        }

        $loop = new WP_Query( $args );
        ob_start();
        if ( $loop->have_posts() ) :
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                ?>
                <div class="col product-grid-item">
                    <article class="product-card h-100">
                        <div class="product-image-container">
                            <?php 
                            // Categoría principal para mostrar
                            $terms = get_the_terms( $product->get_id(), 'product_cat' );
                            $cat_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : '';
                            if ($cat_name) : 
                            ?>
                            <div class="product-category"><?php echo esc_html($cat_name); ?></div>
                            <?php endif; ?>

                            <?php if ( $product->is_on_sale() ) : ?>
                                <div class="product-category sale" style="top: 40px; background-color: #dc3545;">Oferta</div>
                            <?php endif; ?>
                            
                            <button type="button" class="btn-add-wishlist" data-id="<?php echo $product->get_id(); ?>" title="Agregar a lista de deseos">
                                <i class="far fa-heart <?php echo in_array($product->get_id(), expotodo_get_user_wishlist()) ? 'fas text-danger' : 'far'; ?>"></i>
                            </button>

                            <a href="<?php the_permalink(); ?>">
                                <?php 
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium', array('class' => 'product-image'));
                                } else {
                                    echo '<img src="https://via.placeholder.com/300x300?text=No+Image" class="product-image" alt="' . get_the_title() . '">';
                                }
                                ?>
                            </a>
                        </div>
                        <div class="product-content p-3">
                            <h3 class="product-title"><a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark"><?php the_title(); ?></a></h3>
                            <p class="product-description small text-muted">
                                <?php echo wp_trim_words(get_the_excerpt(), 10, '...'); ?>
                            </p>
                            <div class="product-price mb-3">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="btn-card btn-primary w-100 mb-2">
                                <i class="fas fa-eye me-2"></i> Ver detalles
                            </a>
                            <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" 
                               class="btn-card btn-outline-primary w-100 <?php echo $product->is_type('simple') ? 'ajax_add_to_cart' : ''; ?>" 
                               data-quantity="1" 
                               data-product_id="<?php echo get_the_ID(); ?>"
                               data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>">
                                <i class="fas fa-shopping-cart me-2"></i> <?php echo $product->is_type('variable') ? 'Seleccionar opciones' : 'Agregar'; ?>
                            </a>
                        </div>
                    </article>
                </div>
<?php
            endwhile;
            wp_reset_postdata();
        endif;
        $content = ob_get_clean();
        wp_send_json_success( array(
            'html'      => $content, 
            'max_pages' => $loop->max_num_pages,
            'sql'       => $loop->request // DEBUG: Ver la consulta SQL en la consola Network
        ));
    }

    public function cart_fragments( $fragments ) {
        $fragments['span.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
        $fragments['span.cart-total'] = '<span class="cart-total">' . WC()->cart->get_total() . '</span>';
        $fragments['span.cart-subtotal'] = '<span class="cart-subtotal">' . WC()->cart->get_cart_subtotal() . '</span>';
        
        // Actualizar la lista de productos
        $fragments['div.cart-items'] = self::get_cart_items_html();
        
        // Actualizar el estado de "vacío"
        $is_empty = WC()->cart->is_empty();
        $fragments['div.cart-empty-message'] = '<div class="cart-empty-message text-muted small ' . ($is_empty ? '' : 'd-none') . '">Tu carrito está vacío.</div>';
        
        return $fragments;
    }

    public function ajax_get_checkout_data() {
        if ( ! WC()->cart ) wp_send_json_error();
        wp_send_json_success( array('total' => WC()->cart->get_total(), 'count' => WC()->cart->get_cart_contents_count()) );
    }

    public function sync_checkout_fields() {
        if ( ! empty( $_POST['billing_first_name'] ) ) {
            if ( empty( $_POST['shipping_first_name'] ) ) $_POST['shipping_first_name'] = $_POST['billing_first_name'];
            if ( empty( $_POST['shipping_last_name'] ) ) $_POST['shipping_last_name'] = $_POST['billing_last_name'];
        }
    }

    public function ajax_update_qty() {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $qty = (int) $_POST['qty'];
        if ( WC()->cart->set_quantity($cart_item_key, $qty) ) {
            WC()->cart->calculate_totals();
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function ajax_remove_item() {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        if ( WC()->cart->remove_cart_item($cart_item_key) ) {
            WC()->cart->calculate_totals();
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function force_mercadopago_redirect( $result, $order_id ) {
        if ( isset($result['redirect']) && strpos($result['redirect'], 'order-pay') !== false ) {
            $order = wc_get_order( $order_id );
            $init_point = get_post_meta( $order_id, '_mercadopago_init_point', true );
            if ( !empty($init_point) ) $result['redirect'] = $init_point;
        }
        return $result;
    }

    public function custom_mp_back_urls( $preference ) {
        $checkout_url = wc_get_checkout_url();
        if ( isset( $preference['back_urls'] ) ) {
            $preference['back_urls']['failure'] = $checkout_url;
            $preference['back_urls']['pending'] = $checkout_url;
        }
        return $preference;
    }

    public function kill_zone_notices_at_birth( $notice ) {
        if ( isset($notice['notice']) && (stripos($notice['notice'], 'Zona') !== false || stripos($notice['notice'], 'México') !== false) ) {
            return false;
        }
        return $notice;
    }

    public function ajax_get_address_form() {
        check_ajax_referer('expotodo_address_nonce', 'security');
        $load_address = sanitize_text_field($_POST['address_type']);
        ob_start();
        wc_get_template( 'myaccount/form-edit-address.php', array('load_address' => $load_address, 'address' => array()) );
        wp_send_json_success( array('html' => ob_get_clean()) );
    }

    public function ajax_save_address() {
        check_ajax_referer('expotodo_address_nonce', 'security');
        $user_id = get_current_user_id();
        wp_send_json_success('Dirección actualizada correctamente.');
    }

    /**
     * Genera el HTML de los items del carrito para el panel lateral
     */
    public static function get_cart_items_html() {
        ob_start();
        ?>
        <div class="cart-items flex-grow-1">
            <?php if ( function_exists('WC') && ! WC()->cart->is_empty() ) : ?>
                <ul class="list-unstyled p-3 mb-0">
                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            ?>
                            <li class="d-flex mb-3 pb-2 border-bottom align-items-center">
                                <div class="me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <?php 
                                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                    echo str_replace('class="', 'class="img-fluid rounded shadow-sm ', $thumbnail);
                                    ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="mb-0 text-truncate fw-bold small">
                                        <?php
                                        $name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                                        if ( $product_permalink ) {
                                            echo sprintf( '<a href="%s" class="text-dark text-decoration-none">%s</a>', esc_url( $product_permalink ), esc_html($name) );
                                        } else {
                                            echo esc_html($name);
                                        }
                                        ?>
                                    </h6>
                                    <div class="text-muted small">
                                        <?php echo sprintf( '%d &times; %s', $cart_item['quantity'], WC()->cart->get_product_price( $_product ) ); ?>
                                    </div>
                                </div>
                                <div class="ms-2">
                                    <?php
                                    echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                                        '<a href="%s" class="remove_from_cart_button text-danger small" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="fas fa-trash-alt"></i></a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        esc_html__( 'Remove this item', 'woocommerce' ),
                                        esc_attr( $product_id ),
                                        esc_attr( $cart_item_key ),
                                        esc_attr( $_product->get_sku() )
                                    ), $cart_item_key );
                                    ?>
                                </div>
                            </li>
                        <?php }
                    } ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}