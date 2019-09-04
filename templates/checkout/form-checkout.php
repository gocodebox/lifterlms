<?php
/**
 * Checkout Form
 *
 * @since    1.0.0
 * @version  3.27.0
 */

defined( 'ABSPATH' ) || exit;

$free   = $plan->has_free_checkout();
$fields = LLMS_Person_Handler::get_available_fields( 'checkout', $field_data );
?>

<?php do_action( 'lifterlms_pre_checkout_form' ); ?>

<form action="" class="llms-checkout llms-checkout-cols-<?php echo apply_filters( 'llms_checkout_columns', ( $free || ! $fields ) ? 1 : $cols, $plan ); ?>" method="POST" id="llms-product-purchase-form">

	<?php do_action( 'lifterlms_before_checkout_form' ); ?>

	<?php if ( $fields ) : ?>
		<div class="llms-checkout-col llms-col-1">

			<section class="llms-checkout-section billing-information">

				<h4 class="llms-form-heading">
					<?php if ( ! $free ) : ?>
						<?php _e( 'Billing Information', 'lifterlms' ); ?>
					<?php else : ?>
						<?php _e( 'Student Information', 'lifterlms' ); ?>
					<?php endif; ?>
				</h4>

				<div class="llms-checkout-section-content llms-form-fields">
					<?php do_action( 'lifterlms_checkout_before_billing_fields' ); ?>
					<?php foreach ( $fields as $field ) : ?>
						<?php llms_form_field( $field ); ?>
					<?php endforeach; ?>
					<?php do_action( 'lifterlms_checkout_after_billing_fields' ); ?>
				</div>

			</section>

		</div>
	<?php endif; ?>

	<div class="llms-checkout-col llms-col-2">

		<?php if ( ! $free ) : ?>
			<section class="llms-checkout-section order-summary">

				<h4 class="llms-form-heading"><?php _e( 'Order Summary', 'lifterlms' ); ?></h4>

				<div class="llms-checkout-section-content">

					<?php
					llms_get_template(
						'checkout/form-summary.php',
						array(
							'coupon'  => $coupon,
							'plan'    => $plan,
							'product' => $product,
						)
					);
					?>

					<?php
					llms_get_template(
						'checkout/form-coupon.php',
						array(
							'coupon' => $coupon,
							'plan'   => $plan,
						)
					);
					?>

				</div>

			</section>
		<?php endif; ?>

		<section class="llms-checkout-section payment-details">

			<h4 class="llms-form-heading">
				<?php if ( ! $free ) : ?>
					<?php _e( 'Payment Details', 'lifterlms' ); ?>
				<?php else : ?>
					<?php _e( 'Enrollment Confirmation', 'lifterlms' ); ?>
				<?php endif; ?>
			</h4>


			<div class="llms-checkout-section-content llms-form-fields">

				<?php
				llms_get_template(
					'checkout/form-gateways.php',
					array(
						'coupon'           => $coupon,
						'gateways'         => $gateways,
						'selected_gateway' => $selected_gateway,
						'plan'             => $plan,
					)
				);
				?>

				<footer class="llms-checkout-confirm llms-form-fields flush">

					<?php do_action( 'llms_checkout_footer_before' ); ?>

					<?php
						/**
						 * Hook: llms_registration_privacy
						 *
						 * @hooked llms_privacy_policy_form_field - 10
						 * @hooked llms_agree_to_terms_form_field - 20
						 */
						do_action( 'llms_registration_privacy' );
					?>

					<?php
					llms_form_field(
						array(
							'columns'     => 12,
							'classes'     => 'llms-button-action',
							'id'          => 'llms_create_pending_order',
							'value'       => apply_filters( 'lifterlms_checkout_buy_button_text', ! $free ? __( 'Buy Now', 'lifterlms' ) : __( 'Enroll Now', 'lifterlms' ) ),
							'last_column' => true,
							'required'    => false,
							'type'        => 'submit',
						)
					);
					?>

					<?php do_action( 'llms_checkout_footer_after' ); ?>

				</footer>

			</div>

		</section>

	</div>

	<?php wp_nonce_field( 'create_pending_order', '_llms_checkout_nonce' ); ?>
	<input name="action" type="hidden" value="create_pending_order">
	<input id="llms-plan-id" name="llms_plan_id" type="hidden" value="<?php echo $plan->get( 'id' ); ?>">
	<input id="llms-order-key" name="llms_order_key" type="hidden" value="<?php echo $order_key; ?>">

	<?php do_action( 'lifterlms_after_checkout_form' ); ?>

</form>

<?php do_action( 'lifterlms_post_checkout_form' ); ?>
