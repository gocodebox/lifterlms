<?php
/**
 * Coupon area of the checkout form
 *
 * @package LifterLMS/Templates/Checkout
 *
 * @since Unknown
 * @version 4.4.0
 */
defined( 'ABSPATH' ) || exit;

$coupon        = isset( $coupon ) ? $coupon : false;
$show_gateways = true;

// Don't display for free plans or plans which do not require any payment.
if ( isset( $plan ) && $plan->is_free() || ! $plan->requires_payment( $coupon ) ) {
	$show_gateways = false;
}

$supports            = $plan->is_recurring() ? 'recurring_payments' : 'single_payments';
$supporting_gateways = 0;
?>
<ul class="llms-payment-gateways">
	<?php if ( $show_gateways ) : ?>
		<?php if ( ! $gateways ) : ?>
			<li class="llms-payment-gateway-error"><?php _e( 'Payment processing is currently disabled.', 'lifterlms' ); ?></li>
		<?php else : ?>
			<?php foreach ( $gateways as $gateway ) : ?>
				<?php if ( $gateway->supports( $supports ) ) : ?>
					<li class="llms-payment-gateway <?php echo $gateway->get_id(); ?><?php echo ( $selected_gateway === $gateway->get_id() ) ? ' is-selected' : ''; ?>">
					<?php
					llms_form_field(
						array(
							'columns'         => 12,
							'classes'         => '',
							'description'     => $gateway->get_icon(),
							'default'         => '',
							'id'              => 'llms_payment_gateway_' . $gateway->get_id(),
							'label'           => $gateway->get_title(),
							'last_column'     => true,
							'name'            => 'llms_payment_gateway',
							'options'         => array(),
							'placeholder'     => '',
							'selected'        => ( $selected_gateway === $gateway->get_id() ),
							'required'        => false,
							'type'            => 'radio',
							'value'           => $gateway->get_id(),
							'wrapper_classes' => 'llms-payment-gateway-option',
						)
					);
					?>
					<?php if ( $gateway->get_description() ) : ?>
						<div class="llms-gateway-description"><?php echo wpautop( wptexturize( $gateway->get_description() ) ); ?></div>
					<?php endif; ?>
					<?php if ( $gateway->supports( 'checkout_fields' ) ) : ?>
						<div class="llms-gateway-fields"><?php echo $gateway->get_fields(); ?></div>
					<?php endif; ?>
					</li>
					<?php $supporting_gateways++; ?>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ( ! $supporting_gateways ) : ?>
				<li class="llms-payment-gateway-error"><?php _e( 'There are no gateways enabled which can support the necessary transaction type for this access plan.', 'lifterlms' ); ?></li>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
</ul>
