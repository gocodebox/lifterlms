<?php
/**
 * Order Summary area of the checkout form
 *
 * @since     2.4.2
 * @since     [version] Added the `llms_checkout_summary_main_coupon_discount` filter and the
 *            `llms_checkout_order_summary_before_terms` action so add-ons can adjust the pricing preview.
 * @version   [version]
 */
defined( 'ABSPATH' ) || exit;

/**
 * Filter whether the applied coupon's discount is shown against the recurring "Terms" line.
 *
 * Add-ons that discount only part of the order (for example only the first payment) can return false so
 * the recurring line shows the normal price.
 *
 * @since [version]
 *
 * @param boolean              $show_main_coupon_discount Whether to show the coupon discount on the terms line.
 * @param LLMS_Access_Plan     $plan                      The access plan being purchased.
 * @param LLMS_Coupon|boolean  $coupon                    The applied coupon or `false`.
 */
$show_main_coupon_discount = apply_filters( 'llms_checkout_summary_main_coupon_discount', ( $coupon && $coupon->has_main_discount() ), $plan, $coupon );
?>
<ul class="llms-order-summary<?php echo $plan->is_on_sale() ? ' on-sale' : ''; ?><?php echo $coupon ? ' has-coupon' : ''; ?>">
	<li><span class="llms-label"><?php echo esc_html( $product->get_post_type_label( 'singular_name' ) ); ?>:</span> <?php echo esc_html( $product->get( 'title' ) ); ?></li>
	<li><span class="llms-label"><?php esc_html_e( 'Access Plan', 'lifterlms' ); ?>:</span> <?php echo esc_html( $plan->get( 'title' ) ); ?></li>
	<?php if ( $plan->has_trial() ) : ?>
		<li class="llms-pricing llms-pricing-trial<?php echo ( $coupon && $coupon->has_trial_discount() ) ? ' has-coupon' : ''; ?>">
			<span class="llms-label"><?php esc_html_e( 'Trial', 'lifterlms' ); ?>:</span>
			<span class="price-regular price-trial"><?php echo wp_kses( $plan->get_price( 'trial_price' ), LLMS_ALLOWED_HTML_PRICES ); ?></span>
			<?php if ( $coupon && $coupon->has_trial_discount() ) : ?>
				<span class="price-coupon"><?php echo wp_kses( $plan->get_price_with_coupon( 'trial_price', $coupon ), LLMS_ALLOWED_HTML_PRICES ); ?></span>
			<?php endif; ?>
			<?php echo esc_html( $plan->get_trial_details() ); ?>
		</li>
	<?php endif; ?>
	<?php
		/**
		 * Action fired before the recurring "Terms" line in the checkout Order Summary.
		 *
		 * Allows add-ons to output additional pricing rows (for example a discounted first payment line).
		 *
		 * @since [version]
		 *
		 * @param LLMS_Access_Plan    $plan    The access plan being purchased.
		 * @param LLMS_Post_Model     $product The product (course/membership) being purchased.
		 * @param LLMS_Coupon|boolean $coupon  The applied coupon or `false`.
		 */
		do_action( 'llms_checkout_order_summary_before_terms', $plan, $product, $coupon );
	?>
	<li class="llms-pricing llms-pricing-main<?php echo $plan->is_on_sale() ? ' on-sale' : ''; ?><?php echo $show_main_coupon_discount ? ' has-coupon' : ''; ?>">
		<span class="llms-label"><?php esc_html_e( 'Terms', 'lifterlms' ); ?>:</span>
		<span class="price-regular"><?php echo wp_kses( $plan->get_price( 'price' ), LLMS_ALLOWED_HTML_PRICES ); ?></span>
		<?php if ( $show_main_coupon_discount ) : ?>
			<span class="price-coupon"><?php echo wp_kses( $plan->get_price_with_coupon( $plan->is_on_sale() ? 'sale_price' : 'price', $coupon ), LLMS_ALLOWED_HTML_PRICES ); ?></span>
		<?php else : ?>
			<?php if ( $plan->is_on_sale() ) : ?>
				<span class="price-sale"><?php echo wp_kses( $plan->get_price( 'sale_price' ), LLMS_ALLOWED_HTML_PRICES ); ?></span>
			<?php endif; ?>
		<?php endif; ?>
		<?php
		$schedule = $plan->get_schedule_details();
		if ( $schedule ) :
			?>
			<?php echo esc_html( $schedule ); ?>
		<?php endif; ?>
	</li>
	<?php
	$expires = $plan->get_expiration_details();
	if ( $expires ) :
		?>
		<li><span class="llms-label"><?php esc_html_e( 'Access', 'lifterlms' ); ?>:</span> <?php echo esc_html( $expires ); ?></li>
	<?php endif; ?>
	<?php
		/**
		 * Action hook fired at the end of the checkout Order Summary area.
		 */
		do_action( 'llms_checkout_order_summary_end', $plan, $product, $coupon );
	?>
</ul>
