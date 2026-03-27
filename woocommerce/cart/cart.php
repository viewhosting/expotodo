<?php
/**
 * Cart Page (DIV Based for maximum flexibility)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.9.0
 */

defined( 'ABSPATH' ) || exit;

// DEPURE: Ver qué hay en la sesión de avisos (LIMPIEZA ACTIVA 360)
if ( function_exists('WC') && WC()->session ) {
    $session_notices = WC()->session->get('wc_notices', array());
    $is_dirty = false;

    foreach ( array('success', 'notice', 'error') as $cat ) {
        if ( ! empty( $session_notices[$cat] ) ) {
            foreach ( $session_notices[$cat] as $k => $n ) {
                if ( isset($n['notice']) && (stripos($n['notice'], 'Zona') !== false || stripos($n['notice'], 'México') !== false) ) {
                    unset( $session_notices[$cat][$k] );
                    $is_dirty = true;
                }
            }
            $session_notices[$cat] = array_values( $session_notices[$cat] );
        }
    }

    if ( $is_dirty ) {
        WC()->session->set('wc_notices', $session_notices);
    }

    echo '<pre style="background:#eee; padding:15px; border:1px solid #ccc; font-size:12px; margin-bottom:20px;">';
    echo '<strong>Contenido de la sesión de avisos (Depuración - Limpia):</strong><br>';
    print_r( WC()->session->get('wc_notices') );
    echo '</pre>';
}

do_action( 'woocommerce_before_cart' );
 ?>

<div class="woocommerce">
    <div class="row">
        <div class="col-12 col-lg-6">
            <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
                <?php do_action( 'woocommerce_before_cart_table' ); ?>

                <div class="cart-items-container woocommerce-cart-form__contents">
                    <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            ?>
                            <div class="cart-item-row <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                                <div class="product-thumbnail">
                                <?php
                                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                                if ( ! $product_permalink ) {
                                    echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                } else {
                                    printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                }
                                ?>
                                </div>

                                <div class="details-container">
                                    <div class="product-name">
                                        <?php
                                        if ( ! $product_permalink ) {
                                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                                        } else {
                                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                                        }

                                        do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                                        // Meta data.
                                        echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        ?>
                                    </div>

                                    <div class="quantity-price-row">
                                        <div class="product-quantity">
                                            <div class="quantity-wrapper">
                                                <?php
                                                if ( $_product->is_sold_individually() ) {
                                                    $min_quantity = 1;
                                                    $max_quantity = 1;
                                                } else {
                                                    $min_quantity = 0;
                                                    $max_quantity = $_product->get_max_purchase_quantity();
                                                }

                                                $product_quantity = woocommerce_quantity_input(
                                                    array(
                                                        'input_name'   => "cart[{$cart_item_key}][qty]",
                                                        'input_value'  => $cart_item['quantity'],
                                                        'max_value'    => $max_quantity,
                                                        'min_value'    => $min_quantity,
                                                        'product_name' => $_product->get_name(),
                                                    ),
                                                    $_product,
                                                    false
                                                );

                                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                ?>
                                                <div class="quantity-nav">
                                                    <div class="quantity-button quantity-up">+</div>
                                                    <div class="quantity-button quantity-down">-</div>
                                                </div>
                                            </div>
                                            <span class="qty-label">Cant.</span>
                                        </div>

                                        <div class="price-group">
                                            <div class="product-price">
                                                <?php
                                                    echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                ?>
                                            </div>
                                            <div class="product-subtotal">
                                                <?php
                                                    echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-remove">
                                    <?php
                                        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'woocommerce_cart_item_remove_link',
                                            sprintf(
                                                '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                                                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                                /* translators: %s is the product name */
                                                esc_html__( 'Remove %s from cart', 'woocommerce' ),
                                                esc_attr( $product_id ),
                                                esc_attr( $_product->get_sku() )
                                            ),
                                            $cart_item_key
                                        );
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <?php do_action( 'woocommerce_cart_contents' ); ?>

                    <div class="cart-actions-wrapper">
                        <div class="actions">
                            <?php if ( wc_coupons_enabled() ) { ?>
                                <div class="coupon">
                                    <label for="coupon_code" class="screen-reader-text">
                                        <?php esc_html_e( 'Coupon:', 'woocommerce' ); ?>
                                    </label>
                                    <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
                                    <button type="submit" class="button btn-card btn-outline-primary w-100 ajax_add_to_cart" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>">
                                        <?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?>
                                    </button>
                                    <?php do_action( 'woocommerce_cart_coupon' ); ?>
                                </div>
                            <?php } ?>

                            <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

                            <?php do_action( 'woocommerce_cart_actions' ); ?>
                            <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                        </div>
                    </div>

                    <?php do_action( 'woocommerce_after_cart_contents' ); ?>
                </div>
                <?php do_action( 'woocommerce_after_cart_table' ); ?>
            </form>
        </div>

        <div class="col-12 col-lg-6">
            <?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

            <div class="cart-collaterals">
                <?php
                    /**
                     * Cart collaterals hook.
                     *
                     * @hooked woocommerce_cross_sell_display
                     * @hooked woocommerce_cart_totals - 10
                     */
                    do_action( 'woocommerce_cart_collaterals' );
                ?>
            </div>
        </div>
    </div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>


<!-- Sección de Productos Destacados -->
    <section class="featured-section py-5" id="coleccion">
        <div class="container">
            <h2 class="section-title text-center mb-5">Nuestra Colección</h2>
            
            <div class="row g-4 mb-5">
                <?php
                // Consulta de productos destacados aleatorios de WooCommerce
                $args = array(
                    'post_type'      => 'product',
                    'posts_per_page' => 4,
                    'orderby'        => 'rand',
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'product_visibility',
                            'field'    => 'name',
                            'terms'    => 'featured',
                        ),
                    ),
                );

                $featured_products = new WP_Query($args);

                if ($featured_products->have_posts()) :
                    while ($featured_products->have_posts()) : $featured_products->the_post();
                        global $product;
                        $product_id = get_the_ID();
                        $product_name = get_the_title();
                        $product_price = $product->get_price();
                        
                        // Obtener categoría principal
                        $terms = get_the_terms($product_id, 'product_cat');
                        $category_name = !empty($terms) && !is_wp_error($terms) ? $terms[0]->name : 'Producto';
                        
                        // Obtener imagen
                        $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url($product_id, 'large') : 'https://via.placeholder.com/400';
                ?>
                <div class="col-md-3">
                    <article class="product-card h-100" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-name="<?php echo esc_attr($product_name); ?>" data-product-price="<?php echo esc_attr($product_price); ?>">
                        <div class="product-image-container">
                            <div class="product-category"><?php echo esc_html($category_name); ?></div>
                            <?php if ($product->is_on_sale()) : ?>
                                <div class="product-category sale" style="top: 40px; background-color: #dc3545;">Oferta</div>
                            <?php endif; ?>
                            <button type="button" class="btn-add-wishlist" title="Agregar a lista de deseos">
                                <i class="far fa-heart"></i>
                            </button>
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 class="product-image" 
                                 alt="<?php echo esc_attr($product_name); ?>">
                        </div>
                        <div class="product-content p-3">
                            <h3 class="product-title"><?php echo esc_html($product_name); ?></h3>
                            <p class="product-description">
                                <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                            </p>
                            <div class="product-price mb-3">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            <a href="<?php echo get_permalink(); ?>" class="btn-card btn-primary">
                                <i class="fas fa-eye me-2"></i> Ver detalles
                            </a>
                            <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn-card btn-primary mt-2 <?php echo $product->is_type('simple') ? 'ajax_add_to_cart' : ''; ?>" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>" aria-label="Agregar “<?php the_title_attribute(); ?>” al carrito">
                                <i class="fas fa-shopping-cart me-2"></i> <?php echo $product->is_type('variable') ? 'Seleccionar opciones' : 'Agregar al carrito'; ?>
                            </a>
                        </div>
                    </article>
                </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    // Fallback si no hay productos (o WooCommerce no está activo/configurado)
                    // Mantenemos el contenido estático como respaldo si se desea, 
                    // o mostramos un mensaje. Por ahora, mostramos un mensaje vacío.
                    echo '<div class="col-12 text-center"><p>No hay productos destacados disponibles en este momento.</p></div>';
                endif;
                ?>
            </div>
            
            <div class="text-center">
                <a href="<?php echo home_url('/productos'); ?>" class="btn btn-outline-dark btn-lg px-5">
                    Ver Catálogo Completo
                </a>
            </div>
        </div>
    </section>


<script>
jQuery( function($) {
    // Function to handle quantity clicks
    $(document).on('click', '.quantity-button', function() {
        var $button = $(this);
        var $wrapper = $button.closest('.quantity-wrapper');
        var $input = $wrapper.find('input.qty');
        var val = parseFloat($input.val());
        var max = parseFloat($input.attr('max'));
        var min = parseFloat($input.attr('min'));
        var step = parseFloat($input.attr('step'));

        if ($button.hasClass('quantity-up')) {
            if (max && (val >= max)) {
                $input.val(max);
            } else {
                $input.val(val + step);
            }
        } else {
            if (min && (val <= min)) {
                $input.val(min);
            } else if (val > 0) {
                $input.val(val - step);
            }
        }

        $input.trigger('change');
    });

    // Automatically update cart on quantity change
    var timeout;
    $(document).on('change', 'input.qty', function() {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(function() {
            $("[name='update_cart']").prop('disabled', false).trigger('click');
        }, 600);
    });
});
</script>
