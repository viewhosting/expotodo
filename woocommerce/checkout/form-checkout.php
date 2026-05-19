<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
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

    <div class="cart-wrapper row g-4">
        <!-- Columna Izquierda: Resumen de Compra + Información de Envío -->
        <div class="col-12 col-lg-6 cart-items-container">
            
            <!-- <h2 class="cart-title mb-4" style="font-size: 1.5rem; font-weight: 800; color: #1a1a1a; text-transform: uppercase;">Resumen de tu compra</h2> -->
            
            <!-- Lista de Productos idéntica al Carrito -->
            <div class="cart-items-list mb-5">
                <?php
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        ?>
                        <div class="cart-item-row <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
                            <div class="product-thumbnail">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            if ( ! $product_permalink ) {
                                echo $thumbnail;
                            } else {
                                printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
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
                                    echo wc_get_formatted_cart_item_data( $cart_item );
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
                                                    'classes'      => array('input-text', 'qty', 'checkout-qty-input'),
                                                ),
                                                $_product,
                                                false
                                            );
                                            echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                            ?>
                                            <div class="quantity-nav">
                                                <div class="quantity-button qty-checkout-up" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">+</div>
                                                <div class="quantity-button qty-checkout-down" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">-</div>
                                            </div>
                                        </div>
                                        <span class="qty-label">Cant.</span>
                                    </div>

                                    <div class="price-group">
                                        <div class="product-price">
                                            <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                                        </div>
                                        <div class="product-subtotal">
                                            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="product-remove">
                                <?php
                                    echo apply_filters(
                                        'woocommerce_cart_item_remove_link',
                                        sprintf(
                                            '<a href="%s" class="remove remove-checkout-item" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                            esc_html__( 'Remove %s from cart', 'woocommerce' ),
                                            esc_attr( $product_id ),
                                            esc_attr($cart_item_key),
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
            </div>

           
            
            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
        </div>

        <!-- Columna Derecha: Tu Pedido (Totales Premium) -->
        <div class="col-12 col-lg-6 cart-totals-container">
            <div class="cart-collaterals cart_totals" style="position: sticky; top: 2rem;">
                
                <!-- <h2 style="margin-bottom: 25px !important;"><?php esc_html_e( 'Resumen del Pedido', 'woocommerce' ); ?></h2> -->
                
                <div class="shop_totals_list">
                    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                        <div class="shipping-options-section mb-4">
                            <div class="cart-config-actions mb-4">
                                <div class="shipping-action-wrap d-flex align-items-center flex-wrap gap-2">
                                    <?php wc_cart_totals_shipping_html(); ?>
                                    <button type="button" class="change-address-btn" id="toggle_address_fields">
                                        <i class="fas fa-map-marker-alt"></i> <?php _e( 'Cambiar Dirección', 'woocommerce' ); ?>
                                    </button>
                                </div>
                                <div class="billing-request-toggle mt-3">
                                    <label class="premium-checkbox-container">
                                        <input type="checkbox" id="request_invoice_checkout">
                                        <span class="checkmark"></span>
                                        <?php _e( '¿Deseas factura?', 'woocommerce' ); ?>
                                    </label>
                                </div>
                            </div>
                        <hr class="mb-4">

                             <!-- Sección 1: Datos de Contacto e Identidad (Siempre Visibles) -->
            <div id="contact_details_section" class="customer-details-section mt-4 mb-4">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; margin-bottom: 20px;"><?php esc_html_e( 'Datos de Contacto', 'woocommerce' ); ?></h2>
                <div class="row g-3">
                    <div class="col-12 col-md-11 mx-auto contact-fields-grid">
                        <?php 
                        $fields = $checkout->get_checkout_fields( 'billing' );
                        
                        // Identidad (Siempre visible)
                        woocommerce_form_field( 'billing_first_name', $fields['billing_first_name'], $checkout->get_value( 'billing_first_name' ) );
                        woocommerce_form_field( 'billing_last_name', $fields['billing_last_name'], $checkout->get_value( 'billing_last_name' ) );
                        woocommerce_form_field( 'billing_email', $fields['billing_email'], $checkout->get_value( 'billing_email' ) );
                        woocommerce_form_field( 'billing_phone', $fields['billing_phone'], $checkout->get_value( 'billing_phone' ) );

                        // Campos ocultos para estabilidad de validación (No se muestran pero se envían)
                        echo '<div style="display:none !important;">';
                        woocommerce_form_field( 'billing_country', array( 'type' => 'hidden', 'value' => 'MX' ), 'MX' );
                        woocommerce_form_field( 'shipping_country', array( 'type' => 'hidden', 'value' => 'MX' ), 'MX' );
                        echo '</div>';
                        ?>

                        <!-- Campos de Ubicación Dinámicos (Se muestran solo en Recogida Local para Mercado Pago) -->
                        <div id="pickup_location_fields" class="pickup-address-wrap mt-3" style="display: none; width: 100%; grid-column: span 2;">
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin: 15px 0 10px; border-top: 1px solid #eee; padding-top: 15px;">
                                <i class="fas fa-map-marker-alt"></i> <?php _e( 'Datos para el recibo de pago', 'woocommerce' ); ?>
                            </h3>
                            
                            <div class="pickup-fields-layout">
                                <div class="pickup-full-width">
                                    <?php woocommerce_form_field( 'billing_address_1', $fields['billing_address_1'], $checkout->get_value( 'billing_address_1' ) ); ?>
                                </div>
                                <div class="pickup-three-cols">
                                    <?php 
                                    woocommerce_form_field( 'billing_city', $fields['billing_city'], $checkout->get_value( 'billing_city' ) );
                                    woocommerce_form_field( 'billing_state', $fields['billing_state'], $checkout->get_value( 'billing_state' ) );
                                    woocommerce_form_field( 'billing_postcode', $fields['billing_postcode'], $checkout->get_value( 'billing_postcode' ) );
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Información de Envío (Entrega) -->
            <div id="shipping_details_section" class="customer-details-section mb-4" >
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; margin-bottom: 20px;"><?php esc_html_e( 'Dirección de Entrega', 'woocommerce' ); ?></h2>
                <div class="row g-3">
                    <div class="col-12 col-md-11 mx-auto">
                        <?php if ( WC()->cart->needs_shipping_address() ) : ?>
                            <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sección 3: Información de Facturación (Legal) -->
            <div id="billing_details_section" class="customer-details-section mb-4" style="display: none;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); text-transform: uppercase; margin-bottom: 20px;"><?php esc_html_e( 'Datos de Facturación', 'woocommerce' ); ?></h2>
                <div class="row g-3">
                    <div class="col-12 col-md-11 mx-auto">
                        <?php 
                        // Renderizado Manual de campos de Factura (Evita duplicados de S1/S2)
                        // Estos campos están definidos en functions.php vía 'woocommerce_checkout_fields'
                        $billing_fields = $checkout->get_checkout_fields( 'billing' );

                        // Renderizamos solo los campos específicos de factura
                        if ( isset( $billing_fields['billing_rfc'] ) ) {
                            woocommerce_form_field( 'billing_rfc', $billing_fields['billing_rfc'], $checkout->get_value( 'billing_rfc' ) );
                        }
                        if ( isset( $billing_fields['billing_company_name'] ) ) {
                            woocommerce_form_field( 'billing_company_name', $billing_fields['billing_company_name'], $checkout->get_value( 'billing_company_name' ) );
                        }
                        if ( isset( $billing_fields['billing_cfdi_usage'] ) ) {
                            woocommerce_form_field( 'billing_cfdi_usage', $billing_fields['billing_cfdi_usage'], $checkout->get_value( 'billing_cfdi_usage' ) );
                        }
                        if ( isset( $billing_fields['billing_payment_form'] ) ) {
                            woocommerce_form_field( 'billing_payment_form', $billing_fields['billing_payment_form'], $checkout->get_value( 'billing_payment_form' ) );
                        }
                        ?>
                    </div>
                </div>
            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    // Mover el total review para procesar hooks
                    remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
                    ?>


                        









                    
                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <div class="cart-summary-footer-bar">
                            
                            <!-- Sección de Cupón Estilo Carrito -->
                            <?php if ( wc_coupons_enabled() ) { ?>
                                <div class="checkout-coupon-wrapper mb-4">
                                    <div class="coupon d-flex gap-2">
                                        <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Código de cupón', 'woocommerce' ); ?>" style="flex: 1; height: 48px; border-radius: 12px; border: 1px solid #e2e8f0; padding: 0 15px;" />
                                        <button type="submit" class="button btn-card btn-outline-primary" name="apply_coupon" value="<?php esc_attr_e( 'Aplicar', 'woocommerce' ); ?>" style="height: 48px; border-radius: 12px; font-weight: 800; padding: 0 20px;">
                                            <?php esc_html_e( 'Aplicar', 'woocommerce' ); ?>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="totals-grid-row mt-4 mb-2">
                                <!-- Columna 1: Total (Izquierda en Escritorio) -->
                                <div class="totals-grid-col">
                                    <div class="order-total total-row main-total">
                                        <span class="label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
                                        <span class="value total-value"><?php wc_cart_totals_order_total_html(); ?></span>
                                    </div>
                                    <div class="barra"></div>
                                </div>

                                <!-- Columna 2: Detalles (Derecha en Escritorio) -->
                                <div class="totals-grid-col details-col-hidden-mobile">
                                    <div class="cart-subtotal subtotal-row">
                                        <span class="label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
                                        <span class="value subtotal-value"><?php wc_cart_totals_subtotal_html(); ?></span>
                                    </div>

                                    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                                        <div class="shipping-cost-row subtotal-row">
                                            <span class="label"><?php esc_html_e( 'Envío', 'woocommerce' ); ?></span>
                                            <span class="value shipping-value"><?php echo WC()->cart->get_cart_shipping_total(); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" style="border-top:1px dashed #ccc;">


                    <h4 class="mb-3 mt-4" style="font-size: 1.1rem; font-weight: 800;"><?php esc_html_e( 'Método de Pago', 'woocommerce' ); ?></h4>
                    <div id="payment" class="woocommerce-checkout-payment">
                        <?php woocommerce_checkout_payment(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>