<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Checkout Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */
?>

<form action="" class="llms-checkout llms-checkout-cols-<?php echo $cols; ?>" method="POST" id="llms-product-purchase-form">

	<?php do_action( 'lifterlms_before_checkout_form' ); ?>

	<div class="llms-checkout-col llms-col-1">

		<section class="llms-checkout-section">

			<h4 class="llms-form-heading"><?php _e( 'Billing Information', 'lifterlms' ); ?></h4>

			<div class="llms-checkout-section-content llms-form-fields">
				<?php do_action( 'lifterlms_checkout_before_billing_fields' ); ?>
				<?php foreach ( LLMS_Person_Handler::get_available_fields( 'checkout', $field_data ) as $field ) : ?>
					<?php llms_form_field( $field ); ?>
				<?php endforeach; ?>
				<?php do_action( 'lifterlms_checkout_after_billing_fields' ); ?>
			</div>

		</section>

	</div>

	<div class="llms-checkout-col llms-col-2">

		<section class="llms-checkout-section">

			<h4 class="llms-form-heading"><?php _e( 'Order Summary', 'lifterlms' ); ?></h4>

			<div class="llms-checkout-section-content">

				<?php llms_get_template( 'checkout/form-summary.php', array(
					'coupon' => $coupon,
					'plan' => $plan,
					'product' => $product,
				) ); ?>

				<?php llms_get_template( 'checkout/form-coupon.php', array(
					'coupon' => $coupon,
					'plan' => $plan,
				) ); ?>

			</div>

		</section>

		<section class="llms-checkout-section">

			<h4 class="llms-form-heading"><?php _e( 'Payment Details', 'lifterlms' ); ?></h4>
			<div class="llms-checkout-section-content llms-form-fields">

				<?php llms_get_template( 'checkout/form-gateways.php', array(
					'coupon' => $coupon,
					'gateways' => $gateways,
					'selected_gateway' => $selected_gateway,
					'plan' => $plan,
				) ); ?>

				<footer class="llms-checkout-confirm llms-form-fields flush">

					<?php llms_agree_to_terms_form_field(); ?>
					<?php llms_form_field( array(
						'columns' => 12,
						'classes' => 'llms-button-action',
						'id' => 'llms_create_pending_order',
						'value' => apply_filters( 'lifterlms_checkout_buy_button_text', __( 'Buy Now', 'lifterlms' ) ),
						'last_column' => true,
						'required' => false,
						'type'  => 'submit',
					) ); ?>

				</footer>

			</div>

		</section>

	</div>

	<?php wp_nonce_field( 'create_pending_order' ); ?>
	<input name="action" type="hidden" value="create_pending_order">
	<input id="llms-plan-id" name="llms_plan_id" type="hidden" value="<?php echo $plan->get( 'id' ); ?>">
	<input id="llms-order-key" name="llms_order_key" type="hidden" value="<?php echo $order_key; ?>">

	<?php do_action( 'lifterlms_after_checkout_form' ); ?>

</form>
