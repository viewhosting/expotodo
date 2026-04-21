<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( ! WC()->cart->is_empty() ) : ?>

    <div class="cart-items flex-grow-1 woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
        <?php
        do_action( 'woocommerce_before_mini_cart_contents' );

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_item_key );
                $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
                <div class="cart-item d-flex align-items-center mb-3 woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                    <div class="cart-item-image me-3">
                        <?php if ( ! empty( $product_permalink ) ) : ?>
                            <a href="<?php echo esc_url( $product_permalink ); ?>">
                                <?php echo $thumbnail; ?>
                            </a>
                        <?php else : ?>
                            <?php echo $thumbnail; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-grow-1 cart-item-details">
                        <h6 class="cart-item-name mb-0">
                            <?php if ( ! empty( $product_permalink ) ) : ?>
                                <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $product_name; ?></a>
                            <?php else : ?>
                                <?php echo $product_name; ?>
                            <?php endif; ?>
                        </h6>
                        
                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                        
                        <div class="cart-item-price">
                            <?php echo $product_price; ?>
                        </div>
                        
                        <div class="cart-item-actions">
                             <div class="cart-qty-control">
                                <?php
                                if ( $_product->is_sold_individually() ) {
                                    echo '<span class="px-2">1</span>';
                                } else {
                                    // Simple quantity display/control - full AJAX update requires more JS
                                    // For now, just display quantity
                                    echo '<span class="px-2 text-muted small">Cant: ' . esc_html( $cart_item['quantity'] ) . '</span>';
                                }
                                ?>
                             </div>
                             
                             <?php
                            echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a href="%s" class="remove remove_from_cart_button cart-remove-btn" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s"><i class="fas fa-trash"></i></a>',
                                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                    esc_attr__( 'Remove this item', 'woocommerce' ),
                                    esc_attr( $product_id ),
                                    esc_attr( $cart_item_key ),
                                    esc_attr( $_product->get_sku() )
                                ),
                                $cart_item_key
                            );
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }

        do_action( 'woocommerce_mini_cart_contents' );
        ?>
    </div>

    <div class="cart-summary mt-4">
        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span class="cart-subtotal"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
        </div>

        <?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

        <div class="mt-3 d-grid gap-2">
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn btn-outline-primary">
                <?php esc_html_e( 'Ver Carrito', 'woocommerce' ); ?>
            </a>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn btn-primary checkout">
                <?php esc_html_e( 'Proceder al Pago', 'woocommerce' ); ?>
            </a>
        </div>

        <?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>
    </div>

<?php else : ?>

    <div class="cart-empty-message text-muted small m-auto d-flex flex-column align-items-center justify-content-center h-100">
        <i class="fas fa-shopping-basket mb-3" style="font-size: 2rem; opacity: 0.3;"></i>
        <p class="mb-0"><?php esc_html_e( 'Tu carrito está vacío.', 'woocommerce' ); ?></p>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn-sm btn-primary mt-3">
            <?php esc_html_e( 'Ir a la tienda', 'woocommerce' ); ?>
        </a>
    </div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
