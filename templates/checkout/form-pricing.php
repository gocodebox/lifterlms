<?php
/**
 * Pricing area of the Checkout Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 *
 * @since  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
$payment_options = $product->get_payment_options();
$coupon = isset( $coupon ) ? $coupon : null;
?>

<div class="llms-price-wrapper" id="llms-payment-options">

	<div class="llms-payment-options llms-notice-box">

		<?php do_action( 'lifterlms_checkout_form_before_payment_options' ); ?>

		<?php $i = 0; $checked = false; ?>

		<?php foreach ($payment_options as $key => $value) : ?>

			<?php if ( 'single' == $value || 'free' == $value ) : $i++; ?>
				<?php if ( 1 == $i ) { $checked = true; } ?>
				<p class="llms-payment-option llms-option">
					<input class="llms-price-option-radio" id="llms-payment-option_<?php echo $value; ?>" name="payment_option" type="radio" value="<?php echo $value . '_' . $key; ?>"<?php echo ( 1 == $i ) ? ' checked' : ''; ?>>
					<label for="llms-payment-option_<?php echo $value; ?>">
						<span class="llms-radio"></span>
						<?php echo ucfirst( $product->get_single_price_html( $coupon ) ); ?>
					</label>
				</p>

			<?php elseif ( 'recurring' == $value ) : $i++; ?>

				<?php $subs = $product->get_subscriptions(); ?>

				<?php if ( ! empty( $subs ) ) : ?>

					<?php foreach ( $subs as $id => $sub ) : ?>
						<p class="llms-payment-option llms-option">
							<input class="llms-price-option-radio" id="llms-payment-option_<?php echo $value . '_' . $id; ?>" name="payment_option" type="radio" value="<?php echo $value . '_' . $id; ?>"<?php echo ( 1 == $i && ! $checked ) ? ' checked' : ''; ?>>
							<label for="llms-payment-option_<?php echo $value . '_' . $id; ?>">
								<span class="llms-radio"></span>
								<?php echo $product->get_subscription_price_html( $sub, $coupon ); ?>
							</label>
						</p>

					<?php endforeach; ?>

				<?php endif; ?>

			<?php else : ?>

				<?php
				/**
				 * Allow addons / plugins / themes to define custom payment options
				 * This action will be called to allow them to output some custom html for the payment options
				 */
				?>
				<?php do_action( 'lifterlms_checkout_payment_option_' . $value, $product, $value ); ?>

			<?php endif; ?>

		<?php endforeach; ?>

		<?php do_action( 'lifterlms_checkout_form_after_payment_options' ); ?>

	</div><!-- .llms-payment-options.llms-notice-box -->

</div><!-- .llms-price-wrapper -->
