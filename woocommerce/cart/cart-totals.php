<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2><?php esc_html_e( 'Resumen del Pedido', 'woocommerce' ); ?></h2>

	<div class="shop_totals_list">

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?> total-row coupon-applied-row">
				<span class="label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="value" data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			
			<div class="shipping-options-section">
				<!-- FILA DE ACCIONES: BOTÓN + FACTURA -->
				<div class="cart-config-actions">
					<div class="shipping-action-wrap">
						<?php wc_cart_totals_shipping_html(); ?>
					</div>

					<div class="billing-request-toggle">
						<label class="premium-checkbox-container">
							<input type="checkbox" id="request_invoice">
							<span class="checkmark"></span>
							<?php _e( 'Solicitar Factura', 'woocommerce' ); ?>
						</label>
					</div>
				</div>

				<div id="extra_billing_fields" style="display: none;">
					<div class="billing-field">
						<input type="text" name="billing_rfc" id="billing_rfc" placeholder="RFC (ej: ABC123456XYZ)">
					</div>
					<div class="billing-field">
						<input type="text" name="billing_company_name" id="billing_company_name" placeholder="Razón Social / Nombre Legal">
					</div>
					<div class="billing-field">
						<input type="text" name="billing_zip" id="billing_zip" placeholder="Código Postal Fiscal">
					</div>
					<div class="billing-field">
						<select name="billing_cfdi_usage" id="billing_cfdi_usage">
							<option value="G01"><?php _e( 'Adquisición de mercancías (G01)', 'woocommerce' ); ?></option>
							<option value="G03" selected><?php _e( 'Gastos en general (G03)', 'woocommerce' ); ?></option>
							<option value="S01"><?php _e( 'Sin efectos fiscales (S01)', 'woocommerce' ); ?></option>
							<option value="D01"><?php _e( 'Honorarios médicos, dentales y gastos hospitalarios (D01)', 'woocommerce' ); ?></option>
						</select>
					</div>
				</div>
			</div>

			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="fee total-row">
				<span class="label"><?php echo esc_html( $fee->name ); ?></span>
				<span class="value" data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<div class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?> total-row">
						<span class="label"><?php echo esc_html( $tax->label ); ?></span>
						<span class="value" data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="tax-total total-row">
					<span class="label"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
					<span class="value" data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
						<div class="clear_fix"></div>

		<div class="cart-summary-footer-bar">
			<div class="totals-grid-row mt-4 mb-2">
				<div class="totals-grid-col">
					<div class="order-total total-row main-total">
						<span class="label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
						<span class="value" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>"><?php wc_cart_totals_order_total_html(); ?></span>
					</div>
					<div class="barra"></div>
					<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
				</div>

				<div class="totals-grid-col details-col-hidden-mobile">
					<div class="cart-subtotal subtotal-row">
						<span class="label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
						<span class="value" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></span>
					</div>

					<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
						<div class="shipping-cost-row subtotal-row">
							<span class="label"><?php esc_html_e( 'Envío', 'woocommerce' ); ?></span>
							<span class="value" data-title="<?php esc_attr_e( 'Envío', 'woocommerce' ); ?>"><?php echo WC()->cart->get_cart_shipping_total(); ?></span>
						</div>
					<?php endif; ?>

					<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
				</div>
			</div>

			<div class="wc-proceed-to-checkout mt-2">
				<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
			</div>
		</div>

	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>
<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
