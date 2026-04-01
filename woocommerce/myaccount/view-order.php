<?php
/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // Verificamos que tenemos el objeto order

if ( ! $order ) {
	return;
}

$status = $order->get_status();
$status_name = wc_get_order_status_name( $status );
$date_created = wc_format_datetime( $order->get_date_created() );
$payment_method = $order->get_payment_method_title();

// Determinar el progreso para la línea de tiempo
$progress_width = '20%';
if ( in_array( $status, array( 'processing', 'on-hold' ) ) ) $progress_width = '50%';
if ( in_array( $status, array( 'completed' ) ) ) $progress_width = '100%';
if ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ) ) ) $progress_width = '0%';

?>

<div class="boutique-view-order py-4">
    
    <!-- Hero Header -->
    <div class="order-hero-card p-4 mb-5 shadow-sm border-0 mx-auto" style="max-width: 1000px;">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="h2 mb-0 fw-bold"><?php echo sprintf( esc_html__( 'Detalles del Pedido #%s', 'woocommerce' ), $order->get_order_number() ); ?></h1>
                <p class="text-muted mt-2 mb-0">
                    <i class="far fa-calendar-alt me-1"></i> Realizado el <?php echo $date_created; ?>
                </p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="order-status-badge status-<?php echo esc_attr( $status ); ?> shadow-sm">
                    <?php echo esc_html( $status_name ); ?>
                </span>
            </div>
        </div>

        <!-- Timeline Tracker -->
        <?php if ( ! in_array( $status, array( 'cancelled', 'failed', 'refunded' ) ) ) : ?>
        <div class="order-timeline mt-5 px-md-5 text-center">
            <div class="timeline-track"></div>
            <div class="timeline-progress" style="width: <?php echo $progress_width; ?>;"></div>
            
            <div class="timeline-steps">
                <div class="step-node completed">
                    <div class="node-icon"><i class="fas fa-shopping-basket"></i></div>
                    <div class="node-label">Recibido</div>
                </div>
                <div class="step-node <?php echo ( $progress_width >= '50%' ) ? 'completed' : 'active'; ?>">
                    <div class="node-icon"><i class="fas fa-box-open"></i></div>
                    <div class="node-label">Procesando</div>
                </div>
                <div class="step-node <?php echo ( $progress_width == '100' ) ? 'completed' : ( ( $progress_width == '50%' ) ? 'active' : '' ); ?>">
                    <div class="node-icon"><i class="fas fa-truck"></i></div>
                    <div class="node-label">Enviado</div>
                </div>
                <div class="step-node <?php echo ( $progress_width == '100%' ) ? 'completed' : ''; ?>">
                    <div class="node-icon"><i class="fas fa-check-double"></i></div>
                    <div class="node-label">Entregado</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MAIN COLUMN CONTENT -->
    <div class="container-fluid mx-auto" style="max-width: 1000px; padding: 0;">
        
        <!-- Order Items Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white p-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-bag me-2 text-primary" style="color: #b0d443 !important;"></i> Artículos del Pedido</h5>
            </div>
            <div class="card-body p-0">
                <!-- Grid Header (Desktop only) -->
                <div class="row g-0 px-4 py-3 bg-light text-muted small fw-bold text-uppercase d-none d-md-flex" style="letter-spacing: 1px;">
                    <div class="col-md-7">Producto</div>
                    <div class="col-md-2 text-center">Cant.</div>
                    <div class="col-md-3 text-end">Total</div>
                </div>

                <!-- SCROLLABLE VIEWPORT -->
                <div class="order-items-viewport">
                    <div class="order-items-grid">
                        <?php foreach ( $order->get_items() as $item_id => $item ) : 
                            $product = $item->get_product();
                            $thumbnail = $product ? $product->get_image(array(80, 80), array('class' => 'item-thumb rounded-3 shadow-sm')) : '';
                            ?>
                            <div class="order-item-row px-4 py-4 border-bottom">
                                <div class="row align-items-center g-3">
                                    <!-- Producto -->
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <?php echo $thumbnail; ?>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1 fw-bold text-dark"><?php echo $item->get_name(); ?></h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge bg-light text-muted border py-1 px-2 fw-normal" style="font-size: 0.7rem;">ID: <?php echo $item->get_product_id(); ?></span>
                                                    <?php if ( $product && $product->get_sku() ) : ?>
                                                        <span class="badge bg-light text-muted border py-1 px-2 fw-normal" style="font-size: 0.7rem;">SKU: <?php echo $product->get_sku(); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Cantidad -->
                                    <div class="col-md-2 text-center">
                                        <span class="d-md-none text-muted small me-2">Cantidad:</span>
                                        <span class="fw-bold bg-light px-3 py-1 rounded-pill border">x<?php echo $item->get_quantity(); ?></span>
                                    </div>
                                    <!-- Total -->
                                    <div class="col-md-3 text-end">
                                        <span class="d-md-none text-muted small d-block mb-1">Subtotal</span>
                                        <span class="h6 mb-0 fw-bold text-dark"><?php echo $order->get_formatted_line_subtotal( $item ); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW TOTALS & DETAILS CARD (SINGLE COLUMN) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <div class="row justify-content-end">
                    <div class="col-lg-7">
                        
                        <!-- Subtotal -->
                        <div class="summary-list-row">
                            <div class="summary-label">Subtotal:</div>
                            <div class="summary-value"><?php echo $order->get_subtotal_to_display(); ?></div>
                        </div>

                        <!-- Método de Pago -->
                        <div class="summary-list-row">
                            <div class="summary-label">Método de pago:</div>
                            <div class="summary-value"><?php echo $payment_method; ?></div>
                        </div>

                        <!-- TAXES (If any) -->
                        <?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
                            <div class="summary-list-row">
                                <div class="summary-label"><?php echo $tax->label; ?>:</div>
                                <div class="summary-value"><?php echo $tax->formatted_amount; ?></div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Total Final -->
                        <div class="summary-total-final d-flex justify-content-between align-items-center">
                            <div class="total-label-big">Total:</div>
                            <div class="total-value-big"><?php echo $order->get_formatted_order_total(); ?></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- ADDRESSES ROW (SIDE BY SIDE) -->
        <div class="row g-4 mb-5">
            <!-- Shipping Info -->
            <div class="col-md-6">
                <div class="info-card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                    <div class="info-card-header bg-white">
                        <i class="fas fa-truck"></i> DATOS DE ENVÍO
                    </div>
                    <div class="info-card-body">
                        <div class="row g-0">
                            <div class="col-12">
                                <p class="mb-2 fw-bold text-dark" style="font-size: 1rem; line-height: 1.4;">
                                    <?php echo $order->get_shipping_method(); ?>
                                </p>
                                <div class="text-muted small" style="line-height: 1.8;">
                                    <?php 
                                    // Mostramos el nombre del destinatario y la dirección formateada
                                    echo $order->get_formatted_shipping_full_name() . '<br>';
                                    echo $order->get_formatted_shipping_address() ?: 'Retiro en tienda'; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Info -->
            <div class="col-md-6">
                <div class="info-card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                    <div class="info-card-header bg-white">
                        <i class="fas fa-file-invoice-dollar"></i> DATOS DE FACTURACIÓN
                    </div>
                    <div class="info-card-body">
                        <p class="mb-2 fw-bold text-dark" style="font-size: 1rem;"><?php echo $order->get_formatted_billing_full_name(); ?></p>
                        <div class="text-muted small" style="line-height: 1.8;">
                            <?php echo $order->get_formatted_billing_address(); ?>
                        </div>
                        <hr class="my-3 opacity-25">
                        <div class="small">
                            <p class="mb-1"><strong>Email:</strong> <?php echo $order->get_billing_email(); ?></p>
                            <p class="mb-0"><strong>Tel:</strong> <?php echo $order->get_billing_phone(); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Notes (FULL WIDTH BELOW) -->
        <?php
        $notes = $order->get_customer_order_notes();
        if ( $notes ) :
            ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-sticky-note me-2 text-primary" style="color: #b0d443 !important;"></i> Actualizaciones del Pedido</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php foreach ( $notes as $note ) : ?>
                            <div class="col-md-6">
                                <div class="bg-light rounded-4 p-3 h-100">
                                    <p class="mb-1 small text-dark"><?php echo wpautop( wptexturize( $note->comment_content ) ); ?></p>
                                    <small class="text-muted d-block mt-2"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $note->comment_date ) ); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ACTION BUTTONS -->
        <div class="d-grid mt-5 text-center">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>orders/" class="btn btn-outline-dark btn-lg rounded-pill fw-bold px-5 mx-auto" style="border: 2px solid #edf2f7; width: fit-content;">
                <i class="fas fa-arrow-left me-2"></i> Volver a mis pedidos
            </a>
        </div>

    </div>
</div>
