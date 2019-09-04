<?php
/**
 * Membership price template
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$llms_product = new LLMS_Product( $post->ID );
?>

<?php if ( ! llms_is_user_enrolled( get_current_user_id(), $post->ID ) ) : ?>

	<div class="llms-price-wrapper">

		<?php foreach ( $llms_product->get_payment_options() as $option ) : ?>

			<?php if ( 'single' === $option || 'free' === $option ) : ?>

				<h4 class="llms-price"><span><?php echo apply_filters( 'lifterlms_single_payment_text', $llms_product->get_single_price_html(), $llms_product ); ?></span></h4>

			<?php elseif ( 'recurring' === $option ) : ?>

				<?php foreach ( $llms_product->get_subscriptions() as $sub ) : ?>

					<?php if ( count( $llms_product->get_payment_options() ) > 1 ) : ?>

						<span class="llms-price-option-separator"><?php echo apply_filters( 'lifterlms_price_option_separator', __( 'or', 'lifterlms' ), $llms_product ); ?></span>

					<?php endif; ?>

					<h4 class="llms-price"><span><?php echo $llms_product->get_subscription_price_html( $sub ); ?></span></h4>

				<?php endforeach; ?>

			<?php endif; ?>

			<?php
			/**
			 * Allow addons / plugins / themes to define custom payment options
			 * This action will be called to allow them to output some custom html for the payment options
			 */
			?>
			<?php do_action( 'lifterlms_product_payment_option_' . $option, $llms_product ); ?>

		<?php endforeach; ?>

	</div>

<?php endif; ?>
