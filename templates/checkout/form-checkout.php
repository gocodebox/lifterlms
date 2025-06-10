<?php
/**
 * Checkout Form
 *
 * @package LifterLMS/Templates/Checkout
 *
 * @since 1.0.0
 * @since 5.0.0 Moved all variable declarations to the checkout shortcode controller.
 *               Updated to utilize fields from LLMS_Forms class.
 * @version 5.0.0
 *
 * @var int $cols Number of columns to use for the form layout.
 * @var LLMS_Payment_Gateway[] $gateways Array of enabled payment gateway instances.
 * @var string $selected_gateway ID of the currently selected/default payment gateway.
 * @var string $order_key Current order key. Empty string for new orders.
 * @var LLMS_Coupon|false $coupon Coupon currently applied to the session or `false` when none found.
 * @var LLMS_Access_Plan $plan Access plan object.
 * @var LLMS_Product $product Product object.
 * @var bool $is_free Whether or not the access plan is a free plan.
 * @var string $form_location Form location id.
 * @var string $form_title Form title.
 * @var array $form_fields Array of LifterLMS Form Fields.
 */

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'lifterlms_pre_checkout_form' ); ?>

<form action="" class="llms-checkout llms-checkout-cols-<?php echo esc_attr( $cols ); ?>" method="POST" id="llms-product-purchase-form">

	<?php do_action( 'lifterlms_before_checkout_form' ); ?>

	<?php if ( $form_fields ) : ?>
		<div class="llms-checkout-col llms-col-1">

			<section class="llms-checkout-section billing-information">

				<?php if ( $form_title ) : ?>
					<h4 class="llms-form-heading"><?php echo esc_html( $form_title ); ?></h4>
				<?php endif; ?>

				<div class="llms-checkout-section-content llms-form-fields">
					<?php do_action( 'lifterlms_checkout_before_billing_fields' ); ?>
					<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $form_fields;
					?>
					<?php do_action( 'lifterlms_checkout_after_billing_fields' ); ?>
				</div>

			</section>

		</div>
	<?php endif; ?>

	<div class="llms-checkout-col llms-col-2">

		<?php if ( ! $is_free ) : ?>
			<section class="llms-checkout-section order-summary">

				<h4 class="llms-form-heading"><?php esc_html_e( 'Order Summary', 'lifterlms' ); ?></h4>

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
				<?php if ( ! $is_free ) : ?>
					<?php esc_html_e( 'Payment Details', 'lifterlms' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Enrollment Confirmation', 'lifterlms' ); ?>
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
							'classes' => 'llms-button-action',
							'id'      => 'llms_create_pending_order',
							'value'   => apply_filters( 'lifterlms_checkout_buy_button_text', ! $is_free ? __( 'Buy Now', 'lifterlms' ) : __( 'Enroll Now', 'lifterlms' ) ),
							'type'    => 'submit',
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
	<input id="llms-plan-id" name="llms_plan_id" type="hidden" value="<?php echo esc_attr( $plan->get( 'id' ) ); ?>">
	<input id="llms-order-key" name="llms_order_key" type="hidden" value="<?php echo esc_attr( $order_key ); ?>">

	<?php do_action( 'lifterlms_after_checkout_form' ); ?>

</form>

<?php do_action( 'lifterlms_post_checkout_form' ); ?>
