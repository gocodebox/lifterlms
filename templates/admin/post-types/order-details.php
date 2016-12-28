<?php
/**
 * Order Details metabox for Order on Admin Panel
 *
 * @since  3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }
?>
<div class="llms-metabox">

	<?php if ( 'test' === $order->get( 'gateway_api_mode' ) ) : ?>
		<h6 class="llms-transaction-test-mode"><?php _e( 'This order was processed in the gateway\'s testing mode', 'lifterlms' ); ?></h6>
	<?php endif; ?>

	<?php do_action( 'lifterlms_before_order_meta_box', $order ); ?>


	<h2><?php printf( __( 'Order #%s', 'lifterlms' ), $order->get( 'id' ) ); ?></h2>
	<h3><?php printf( __( 'Processed by %s', 'lifterlms' ), is_wp_error( $gateway ) ? $order->get( 'payment_gateway' ) : $gateway->get_admin_title() ); ?></h3>


	<?php do_action( 'lifterlms_order_meta_box_after_header', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php _e( 'Order Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Order Date:', 'lifterlms' ) ?></label>
			<?php echo $order->get_date( 'date', 'm/d/Y h:ia' ); ?>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Order Status:', 'lifterlms' ) ?></label>
			<?php echo $order->get_status_name(); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_order_information', $order ); ?>


		<?php do_action( 'lifterlms_order_meta_box_before_plan_information', $order ); ?>

		<?php if ( $order->get( 'plan_id' ) ) : ?>

			<h4><?php _e( 'Access Plan Information', 'lifterlms' ); ?></h4>

			<div class="llms-metabox-field">
				<label><?php _e( 'Name:', 'lifterlms' ); ?></label>
				<?php echo $order->get( 'plan_title' ); ?>
				<small>(#<?php echo $order->get( 'plan_id' ); ?>)</small>
			</div>

			<div class="llms-metabox-field">
				<label><?php _e( 'SKU:', 'lifterlms' ); ?></label>
				<?php echo $order->get( 'plan_sku' ); ?>
			</div>

		<?php endif; ?>

		<?php do_action( 'lifterlms_order_meta_box_after_plan_information', $order ); ?>

		<?php do_action( 'lifterlms_order_meta_box_before_product_information', $order ); ?>

		<h4><?php _e( 'Product Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Name:', 'lifterlms' ); ?></label>
			<a href="<?php echo get_edit_post_link( $order->get( 'product_id' ) ); ?>"><?php echo $order->get( 'product_title' ); ?></a>
			<small>(<?php echo ucfirst( $order->get( 'product_type' ) ); ?>)</small>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'SKU:', 'lifterlms' ); ?></label>
			<?php echo $order->get( 'product_sku' ); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_product_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_payment_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<?php if ( $order->has_trial() ) : ?>

			<h4><?php _e( 'Trial Information', 'lifterlms' ); ?></h4>

			<?php if ( $order->has_coupon() && $order->get( 'coupon_amount_trial' ) ) : ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Original Total:', 'lifterlms' ) ?></label>
					<?php echo $order->get_price( 'trial_original_total' ); ?>
				</div>

				<div class="llms-metabox-field">
					<label><?php _e( 'Coupon Discount:', 'lifterlms' ) ?></label>
					<?php echo $order->get_coupon_amount( 'trial' ); ?>
					(<?php echo llms_price( $order->get_price( 'coupon_value_trial', array(), 'float' ) * - 1 ); ?>)
					[<a href="<?php echo get_edit_post_link( $order->get( 'coupon_id' ) ); ?>"><?php echo $order->get( 'coupon_code' ); ?></a>]
				</div>
			<?php endif; ?>

			<div class="llms-metabox-field">
				<label><?php _e( 'Total:', 'lifterlms' ); ?></label>
				<?php echo $order->get_price( 'trial_total' ); ?>
				<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'trial_length' ), 'lifterlms' ), $order->get( 'trial_length' ), $order->get( 'trial_period' ) ); ?>
			</div>

			<div class="llms-metabox-field">
				<label><?php _e( 'Trial End Date:', 'lifterlms' ); ?></label>
				<?php echo $order->get_trial_end_date( 'm/d/Y h:ia' ); ?>
			</div>
		<?php endif; ?>

		<h4><?php _e( 'Payment Information', 'lifterlms' ); ?></h4>

		<?php if ( $order->has_discount() ) : ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'Original Total:', 'lifterlms' ) ?></label>
				<?php echo $order->get_price( 'original_total' ); ?>
			</div>

			<?php if ( $order->has_sale() ) : ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Sale Discount:', 'lifterlms' ) ?></label>
					<?php echo $order->get_price( 'sale_price' ); ?>
					(<?php echo llms_price( $order->get_price( 'sale_value', array(), 'float' ) * -1 ); ?>)
				</div>
			<?php endif; ?>

			<?php if ( $order->has_coupon() ) : ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Coupon Discount:', 'lifterlms' ) ?></label>
					<?php echo $order->get_coupon_amount( 'regular' ); ?>
					(<?php echo llms_price( $order->get_price( 'coupon_value', array(), 'float' ) * - 1 ); ?>)
					[<a href="<?php echo get_edit_post_link( $order->get( 'coupon_id' ) ); ?>"><?php echo $order->get( 'coupon_code' ); ?></a>]
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="llms-metabox-field">
			<label><?php _e( 'Total:', 'lifterlms' ); ?></label>
			<?php echo $order->get_price( 'total' ); ?>
			<?php if ( $order->is_recurring() ) : ?>
				<?php printf( _n( 'Every %2$s', 'Every %1$d %2$ss', $order->get( 'billing_frequency' ), 'lifterlms' ), $order->get( 'billing_frequency' ), $order->get( 'billing_period' ) ); ?>
				<?php if ( $order->get( 'billing_cycle' ) > 0 ) : ?>
					<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'billing_cycle' ), 'lifterlms' ), $order->get( 'billing_cycle' ), $order->get( 'billing_period' ) ); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php _e( 'One-time', 'lifterlms' ); ?>
			<?php endif; ?>
		</div>

		<?php if ( $order->is_recurring() ) : ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'Next Payment Due Date:', 'lifterlms' ); ?></label>
				<?php if ( $date = $order->get_next_payment_due_date( 'm/d/Y h:ia' ) ) : ?>
					<?php echo ( is_wp_error( $date ) ) ? '&ndash;' : $date; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'lifterlms_order_meta_box_after_payment_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_customer_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php _e( 'Customer Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Name:', 'lifterlms' ) ?></label>
			<a href="<?php echo get_edit_user_link( $order->get( 'user_id' ) ); ?>"><?php echo $order->get_customer_name(); ?></a>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Email:', 'lifterlms' ) ?></label>
			<a href="mailto:<?php echo $order->get( 'billing_email' ); ?>"><?php echo $order->get( 'billing_email' ); ?></a>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Address:', 'lifterlms' ) ?></label>
			<?php echo $order->get( 'billing_address_1' ); ?><br>
			<?php if ( isset( $order->billing_address_2 ) ) : ?>
				<?php echo $order->get( 'billing_address_2' ); ?><br>
			<?php endif; ?>
			<?php echo $order->get( 'billing_city' ); ?>,
			<?php echo $order->get( 'billing_state' ); ?>,
			<?php echo $order->get( 'billing_zip' ); ?><br>
			<?php echo $order->get( 'billing_country' ); ?>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer IP Address:', 'lifterlms' ) ?></label>
			<?php echo $order->get( 'user_ip_address' ); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_customer_information', $order ); ?>

	</div>

	<div class="clear"></div>


	<?php do_action( 'lifterlms_order_meta_box_before_gateway_information', $order ); ?>

	<?php if ( $gateway ) : ?>

		<div class="llms-metabox-section d-all">

			<h4><?php _e( 'Gateway Information', 'lifterlms' ); ?></h4>

			<div class="llms-metabox-field d-1of4">
				<label><?php _e( 'Name:', 'lifterlms' ); ?></label>
				<?php echo is_wp_error( $gateway ) ? $order->get( 'payment_gateway' ) : $gateway->get_admin_title(); ?>
			</div>


			<?php if ( isset( $order->transaction_id ) ) : ?>
				<?php $transaction = is_wp_error( $gateway ) ? $order->get( 'transaction_id' ) : $gateway->get_transaction_url( $order->get( 'transaction_id' ) ); ?>
				<div class="llms-metabox-field d-1of4">
					<label><?php _e( 'Transaction ID:', 'lifterlms' ); ?></label>
					<?php if ( false === filter_var( $transaction, FILTER_VALIDATE_URL ) ) : ?>
						<?php echo $transaction; ?>
					<?php else : ?>
						<a href="<?php echo $transaction; ?>" target="_blank"><?php echo $order->get( 'transaction_id' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>


			<?php if ( isset( $order->gateway_customer_id ) ) : ?>
				<?php $customer = is_wp_error( $gateway ) ? $order->get( 'gateway_customer_id' ) : $gateway->get_customer_url( $order->get( 'gateway_customer_id' ), $order->get( 'gateway_api_mode' ) ); ?>
				<div class="llms-metabox-field d-1of4">
					<label><?php _e( 'Customer ID:', 'lifterlms' ); ?></label>
					<?php if ( false === filter_var( $customer, FILTER_VALIDATE_URL ) ) : ?>
						<?php echo $customer; ?>
					<?php else : ?>
						<a href="<?php echo $customer; ?>" target="_blank"><?php echo $order->get( 'gateway_customer_id' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( isset( $order->gateway_subscription_id ) ) : ?>
				<?php $subscription = is_wp_error( $gateway ) ? $order->get( 'gateway_subscription_id' ) : $gateway->get_subscription_url( $order->get( 'gateway_subscription_id' ), $order->get( 'gateway_api_mode' ) ); ?>
				<div class="llms-metabox-field d-1of4">
					<label><?php _e( 'Subscription ID:', 'lifterlms' ); ?></label>
					<?php if ( false === filter_var( $subscription, FILTER_VALIDATE_URL ) ) : ?>
						<?php echo $subscription; ?>
					<?php else : ?>
						<a href="<?php echo $subscription; ?>" target="_blank"><?php echo $order->get( 'gateway_subscription_id' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'lifterlms_order_meta_box_after_gateway_information', $order ); ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_order_meta_box', $order ); ?>

</div>
