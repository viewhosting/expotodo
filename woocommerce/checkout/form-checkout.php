<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="row g-5">
        <!-- Columna Izquierda: Resumen de Compra + Información de Envío -->
        <div class="col-md-6">
            
            <!-- Sección 1: Resumen de tu compra (Tabla de productos) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary"><i class="fas fa-shopping-bag me-2"></i> <?php esc_html_e( 'Resumen de tu compra', 'woocommerce' ); ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 checkout-cart-table">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col" class="ps-4"><?php esc_html_e( 'Producto', 'woocommerce' ); ?></th>
                                    <th scope="col" style="width: 150px;"><?php esc_html_e( 'Cantidad', 'woocommerce' ); ?></th>
                                    <th scope="col" class="text-end pe-4"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                                    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                                        ?>
                                        <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <?php
                                                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', array( 'class' => 'checkout-product-img' ) ), $cart_item, $cart_item_key );
                                                        if ( ! $product_permalink ) {
                                                            echo $thumbnail; // PHPCS: XSS ok.
                                                        } else {
                                                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
                                                        }
                                                        ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
                                                        </h6>
                                                        <div class="product-price-small text-muted small mt-1">
                                                            <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                                                        </div>
                                                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="product-quantity">
                                                <?php
                                                if ( $_product->is_sold_individually() ) {
                                                    $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                                } else {
                                                    $product_quantity = woocommerce_quantity_input(
                                                        array(
                                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                                            'input_value'  => $cart_item['quantity'],
                                                            'max_value'    => $_product->get_max_purchase_quantity(),
                                                            'min_value'    => '0',
                                                            'product_name' => $_product->get_name(),
                                                            'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text', 'form-control', 'form-control-sm', 'checkout-qty-input' ), $_product ),
                                                        ),
                                                        $_product,
                                                        false
                                                    );
                                                }
                                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                                ?>
                                            </td>
                                            <td class="product-subtotal text-end pe-4">
                                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Información de Envío y Pago -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <?php if ( $checkout->get_checkout_fields() ) : ?>

                        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                        <div id="customer_details">
                            <h4 class="card-title mb-4"><?php esc_html_e( 'Información de Envío', 'woocommerce' ); ?></h4>
                            <div class="row g-3">
                                <?php do_action( 'woocommerce_checkout_billing' ); ?>
                                <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                            </div>
                        </div>

                        <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Tu Pedido (Totales) -->
        <div class="col-md-6">
            <div class="card shadow-sm position-sticky" style="top: 2rem;">
                <div class="card-body">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary"><?php esc_html_e( 'Tu pedido', 'woocommerce' ); ?></span>
                        <span class="badge bg-primary rounded-pill"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                    </h4>
                    
                    <?php
                    // Remove payment from the order review hook since we moved it
                    remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
                    ?>
                    
                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                    </div>

                    <hr class="my-4">

                    <h4 class="mb-3"><?php esc_html_e( 'Pago', 'woocommerce' ); ?></h4>
                    <div id="payment" class="woocommerce-checkout-payment">
                        <?php woocommerce_checkout_payment(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>