<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order boutique-thankyou-page py-5 bg-light" style="min-height: 80vh;">

    <?php
    if ( $order ) :

        do_action( 'woocommerce_before_thankyou', $order->get_id() );
        ?>

        <?php if ( $order->has_status( 'failed' ) ) : ?>

            <div class="container text-center mb-5">
                <div class="card shadow-sm mx-auto" style="max-width: 600px; border-radius: 20px;">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h1 class="h2 mb-3"><?php esc_html_e( 'Pedido no procesado', 'woocommerce' ); ?></h1>
                        <p class="text-muted mb-4"><?php esc_html_e( 'Lamentablemente, no pudimos procesar tu pedido ya que el banco o el procesador de pagos rechazó la transacción. Por favor, intenta de nuevo.', 'woocommerce' ); ?></p>
                        
                        <div class="d-grid gap-2 d-sm-flex justify-content-center">
                            <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn-primary px-4 checkout-button pay"><?php esc_html_e( 'Pagar de nuevo', 'woocommerce' ); ?></a>
                            <?php if ( is_user_logged_in() ) : ?>
                                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="btn btn-outline-dark px-4"><?php esc_html_e( 'Mi Cuenta', 'woocommerce' ); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php else : ?>

            <div class="container text-center mb-5">
                <!-- HERO CARD BOUTIQUE -->
                <div class="card shadow-sm mx-auto border-0" style="max-width: 650px; border-radius: 24px; overflow: hidden;">
                    <div class="card-body p-5" style="background: #ffffff;">
                        <div class="mb-4">
                            <i class="fas fa-check-circle" style="font-size: 5.5rem; color: #b0d443;"></i>
                        </div>
                        <h1 class="h2 mb-3" style="font-weight: 800; color: #1a1a1a;"><?php esc_html_e( '¡Gracias por tu compra!', 'woocommerce' ); ?></h1>
                        <p class="text-muted mb-4" style="font-size: 1.1rem;"><?php esc_html_e( 'Hemos recibido tu pedido correctamente. Te hemos enviado un correo electrónico con los detalles.', 'woocommerce' ); ?></p>
                        
                        <!-- Mini Order Stats -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="bg-light p-3 rounded-4 border-0">
                                    <p class="mb-1 small text-uppercase text-muted fw-bold"><?php esc_html_e( 'Número de pedido', 'woocommerce' ); ?></p>
                                    <h4 class="mb-0 font-monospace text-dark">#<?php echo $order->get_order_number(); ?></h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-3 rounded-4 border-0">
                                    <p class="mb-1 small text-uppercase text-muted fw-bold"><?php esc_html_e( 'Fecha', 'woocommerce' ); ?></p>
                                    <h4 class="mb-0 text-dark"><?php echo wc_format_datetime( $order->get_date_created() ); ?></h4>
                                </div>
                            </div>
                        </div>

                        <!-- DESGLOSE DE PRODUCTOS (Limpio y Premium) -->
                        <div class="order-items-boutique text-start mb-5">
                            <h5 class="mb-4 pb-2 border-bottom fw-bold" style="color: #1a1a1a; letter-spacing: -0.5px;"><?php esc_html_e( 'Detalles de tu compra', 'woocommerce' ); ?></h5>
                            
                            <?php foreach ( $order->get_items() as $item_id => $item ) : 
                                $product = $item->get_product();
                                $thumbnail = $product ? $product->get_image(array(80, 80), array('class' => 'rounded-3 shadow-sm')) : '';
                                ?>
                                <div class="d-flex align-items-center mb-4">
                                    <div class="flex-shrink-0 me-3">
                                        <?php echo $thumbnail; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold" style="color: #2D3748;"><?php echo $item->get_name(); ?></h6>
                                        <p class="mb-0 text-muted small">Cantidad: <?php echo $item->get_quantity(); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold" style="color: #1a1a1a;"><?php echo $order->get_formatted_line_subtotal( $item ); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Totales -->
                            <div class="order-totals-boutique pt-3 mt-2 border-top">
                                <div class="payment-method-info-card mb-4">
                                    <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
                                </div>
                                <?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
                                    <div class="d-flex justify-content-between mb-2 <?php echo ($key === 'order_total') ? 'mt-3 pt-2 border-top fw-bold h5' : 'text-muted small'; ?>">
                                        <span><?php echo $total['label']; ?></span>
                                        <span class="<?php echo ($key === 'order_total') ? 'text-dark' : ''; ?>"><?php echo $total['value']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-sm-flex justify-content-center pt-3">
                            <a href="<?php echo esc_url( home_url('/seguimiento') ); ?>" class="btn btn-outline-dark px-4 rounded-pill fw-bold"><?php esc_html_e( 'Seguimiento de Pedido', 'woocommerce' ); ?></a>
                            <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm border-0" style="background: #b0d443;"><?php esc_html_e( 'Volver a la Tienda', 'woocommerce' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
        <!-- <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?> -->

    <?php else : ?>
        <div class="container text-center pt-5">
            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
        </div>

    <?php endif; ?>

    <!-- SECCIÓN DE PRODUCTOS RECOMENDADOS BASADOS EN LA COMPRA -->
    <?php if ( $order ) : ?>
        <section class="featured-section py-5 mt-5" style="background: #fff; border-top: 1px solid #edf2f7;">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase tracking-wider text-muted fw-bold small">También podría interesarte</span>
                    <h2 class="section-title mt-2 mb-0" style="font-weight: 800; font-size: 2.2rem; letter-spacing: -1px;"><?php esc_html_e( 'Completa tu estilo', 'woocommerce' ); ?></h2>
                </div>
                
                <div class="row g-4 justify-content-center">
                    <?php
                    $items = $order->get_items();
                    $base_product_id = 0;
                    if ( ! empty( $items ) ) {
                        $first_item = reset( $items );
                        $base_product_id = $first_item->get_product_id();
                    }

                    $related_ids = $base_product_id ? wc_get_related_products( $base_product_id, 4 ) : array();

                    if ( ! empty( $related_ids ) ) :
                        $args = array(
                            'post_type'      => 'product',
                            'post__in'       => $related_ids,
                            'posts_per_page' => 4,
                            'orderby'        => 'post__in'
                        );
                        $related_query = new WP_Query( $args );

                        if ( $related_query->have_posts() ) :
                            while ( $related_query->have_posts() ) : $related_query->the_post();
                                $_rel_product = wc_get_product( get_the_ID() );
                                if ( ! $_rel_product ) continue;
                                ?>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <?php get_template_part('template-parts/content-product'); ?>
                                </div>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                    else :
                        echo '<div class="col-12 text-center text-muted"><p>Descubre más novedades en nuestra tienda online.</p></div>';
                    endif;
                    ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

</div>
