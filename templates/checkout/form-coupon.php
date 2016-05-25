<?php
/**
 * Coupon Form Part
 *
 * Included via "checkout/form-checkout.php"
 * and returned by AJAX when applying or removing a coupon
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<div class="llms-form-wrapper" id="llms-coupon-form">
	<div class="llms-coupon-entry llms-notice-box">

		<div class="llms-coupon-notice">
			<?php if ( $coupon ) : ?>
				<?php printf( __( 'Coupon code "%s" has been applied to your order.', 'lifterlms' ), $coupon->get_code() ); ?>
				<a href="#" class="llms-button-text" id="llms-remove-coupon">[<?php _e( 'Remove', 'lifterlms' ); ?>]</a>
			<?php else : ?>
				<?php echo apply_filters( 'lifterlms_checkout_coupon_message', __( 'Have a coupon?', 'lifterlms' ) ); ?>
				<a href="#" class="llms-coupon-toggle-button llms-button-text" id="show-coupon"><?php _e( 'Click here to enter your code', 'lifterlms' ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $coupon ) : ?>

			<input type="hidden" name="coupon_code" value="<?php echo $coupon->get_code(); ?>">

		<?php else : ?>

			<div class="llms-checkout-coupon" id="llms-checkout-coupon">
				<input disabled="disabled" type="text" name="coupon_code" class="llms-input-text" placeholder="<?php _e( 'Enter coupon code', 'lifterlms' ); ?>" id="llms-coupon-code" required="required">
				<div class="llms-clear-box llms-center-content">
					<button type="button" class="button llms-button" id="llms-apply-coupon"><?php _e( 'Apply Coupon', 'lifterlms' ); ?></button>
					<a class="llms-coupon-toggle-button llms-button-text" href="#"><?php _e( 'Cancel', 'lifterlms' ); ?></a>
				</div>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

	</div><!-- .llms-coupon-entry.llms-notice-box -->

</div><!-- .llms-form-wrapper -->
