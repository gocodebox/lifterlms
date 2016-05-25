<?php
/**
 * Order Details metabox for Order on Admin Panel
 *
 * @since  3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$type = $order->get_type();
$discount = $order->get_discount_type();
$gateway = $order->get_payment_gateway_instance();

// if this is set the "Sync Now" link was clicked
if ( isset( $_GET['llms_subscription_sync'] ) ) {
	global $post;
	// set the last sync to 0 so that this will be the first item in the query
	$order->subscription_last_sync = 0;
	// run the associated check action directly, pass 1 for count so we only check this item
	do_action( 'lifterlms_' . $gateway->id . '_order_sync', 1 );
	// re-instantiate the order object to avoid cached data
	$order = new LLMS_Order( $post->ID );
}
?>
<div class="llms-metabox">

	<?php if ( 'test' === $order->get_transaction_api_mode() ): ?>
		<h6 class="llms-transaction-test-mode"><?php _e( 'This order was processed in the gateway\'s testing mode', 'lifterlms' ); ?></h6>
	<?php endif; ?>

	<?php do_action( 'lifterlms_before_order_meta_box', $order ); ?>

	<h2><?php printf( __( 'Order #%s details', 'lifterlms' ), $order->get_id() ); ?></h2>
	<h3><?php printf( __( '%s Payment via %s', 'lifterlms' ), ucfirst( $order->get_type() ), $order->get_payment_gateway_title() ); ?></h3>

	<?php do_action( 'lifterlms_order_meta_box_after_header', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php _e( 'Order Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Order Date:', 'lifterlms' ) ?></label>
			<?php echo $order->get_date( 'm/d/Y h:ia' ); ?>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Order Status:', 'lifterlms' ) ?></label>
			<?php echo llms_get_formatted_order_status( $order->get_status() ); ?>
		</div>

		<?php if ( $gateway->supports( 'recurring_sync' ) && 'llms-active' === $order->get_status() && isset( $order->subscription_last_sync ) ): ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'Last Gateway Sync:', 'lifterlms' ) ?></label>
				<?php echo $order->get_subscription_last_sync( 'm/d/Y h:ia' ); ?>
				<a href="<?php echo add_query_arg( 'llms_subscription_sync', '1',get_edit_post_link( $order->get_id() ) ); ?>">Sync Now</a>
			</div>
		<?php endif; ?>

		<?php do_action( 'lifterlms_order_meta_box_after_order_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_customer_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php _e( 'Customer Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Name:', 'lifterlms' ) ?></label>
			<a href="<?php echo get_edit_user_link( $order->get_user_id() ); ?>"><?php echo $order->get_billing_name(); ?></a>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Email:', 'lifterlms' ) ?></label>
			<a href="mailto:<?php echo $order->get_billing_email(); ?>"><?php echo $order->get_billing_email(); ?></a>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Address:', 'lifterlms' ) ?></label>
			<?php echo $order->get_billing_address_1(); ?><br>
			<?php if ( isset( $order->billing_address_2 ) ) : ?>
				<?php echo $order->get_billing_address_2(); ?><br>
			<?php endif; ?>
			<?php echo $order->get_billing_city(); ?>,
			<?php echo $order->get_billing_state(); ?>,
			<?php echo $order->get_billing_zip(); ?><br>
			<?php echo $order->get_billing_country(); ?>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer IP Address:', 'lifterlms' ) ?></label>
			<?php echo $order->get_user_ip_address(); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_customer_information', $order ); ?>
	</div>

	<?php if ( 'single' === $type ): ?>

		<?php do_action( 'lifterlms_order_meta_box_before_single_payment_information', $order ); ?>

		<div class="llms-metabox-section d-1of3">

			<h4><?php _e( 'Payment Information', 'lifterlms' ); ?></h4>

			<?php if ( $discount ): ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Original Total:', 'lifterlms' ) ?></label>
					<?php echo $order->format_price( $order->get_original_total() ); ?>
				</div>

				<div class="llms-metabox-field">
					<label><?php _e( 'Discount:', 'lifterlms' ) ?></label>
					-<?php echo $order->format_price( $order->get_discount_value() ); ?>
					<small>(<?php echo ( 'coupon' === $discount ) ? $order->get_formatted_coupon_amount( 'single' ) . ' ' : ''; ?><?php echo $discount; ?>)</small>
				</div>
			<?php endif; ?>

			<div class="llms-metabox-field">
				<label><?php _e( 'Total:', 'lifterlms' ); ?></label>
				<?php echo $order->format_price( $order->get_total() ); ?>
			</div>

			<?php do_action( 'lifterlms_order_meta_box_after_single_payment_information', $order ); ?>

		</div>

	<?php elseif( 'recurring' === $type ): ?>

		<?php do_action( 'lifterlms_order_meta_box_before_recurring_payment_information', $order ); ?>

		<div class="llms-metabox-section d-1of3">

			<h4><?php _e( 'Subscription Information', 'lifterlms' ); ?></h4>

			<div class="llms-metabox-field">
				<label><?php _e( 'Subscription Terms:', 'lifterlms' ); ?></label>
				<?php printf( _n( 'Every %2$s', 'Every %1$d %2$ss', $order->get_billing_frequency(), 'lifterlms' ), $order->get_billing_frequency(), $order->get_billing_period() ); ?>
				<?php if ( $order->get_billing_cycle() > 0 ) : ?>
					<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get_billing_cycle(), 'lifterlms' ), $order->get_billing_cycle(), $order->get_billing_period() ); ?>
				<?php endif; ?>
			</div>

			<?php if ( $discount ): ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Original First Payment:', 'lifterlms' ) ?></label>
					<?php echo $order->format_price( $order->get_first_payment_original_total() ); ?>
				</div>

				<div class="llms-metabox-field">
					<label><?php _e( 'First Payment Discount:', 'lifterlms' ) ?></label>
					-<?php echo $order->format_price( $order->get_coupon_first_payment_value() ); ?>
					<small>(<?php echo $order->get_formatted_coupon_amount( 'first' ); ?> <?php echo $discount; ?>)</small>
				</div>
			<?php endif; ?>

			<div class="llms-metabox-field">
				<label><?php _e( 'First Payment:', 'lifterlms' ); ?></label>
				<?php echo $order->format_price( $order->get_first_payment_total() ); ?>
			</div>

			<?php if ( $discount ): ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Original Recurring Payment:', 'lifterlms' ) ?></label>
					<?php echo $order->format_price( $order->get_recurring_payment_original_total() ); ?>
				</div>

				<div class="llms-metabox-field">
					<label><?php _e( 'Recurring Payment Discount:', 'lifterlms' ) ?></label>
					-<?php echo $order->format_price( $order->get_coupon_recurring_payment_value() ); ?>
					<small>(<?php echo $order->get_formatted_coupon_amount( 'recurring' ); ?> <?php echo $discount; ?>)</small>
				</div>
			<?php endif; ?>

			<div class="llms-metabox-field">
				<label><?php _e( 'Recurring Payment:', 'lifterlms' ); ?></label>
				<?php echo $order->format_price( $order->get_recurring_payment_total() ); ?>
			</div>

			<?php do_action( 'lifterlms_order_meta_box_after_recurring_payment_information', $order ); ?>

		</div>

	<?php endif; ?>

	<div class="clear"></div>

	<?php do_action( 'lifterlms_order_meta_box_before_coupon_information', $order ); ?>

	<?php if( 'coupon' === $discount ): ?>

		<div class="llms-metabox-section d-1of3 last-col">

			<h4><?php _e( 'Coupon Information', 'lifterlms' ); ?></h4>

			<div class="llms-metabox-field">
				<label><?php _e( 'Coupon Code:', 'lifterlms' ); ?></label>
				<a href="<?php echo get_edit_post_link( $order->get_coupon_id() ); ?>"><?php echo $order->get_coupon_code(); ?></a>
			</div>

			<?php do_action( 'lifterlms_order_meta_box_after_coupon_information', $order ); ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'lifterlms_order_meta_box_before_product_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php _e( 'Product Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Name:', 'lifterlms' ); ?></label>
			<a href="<?php echo get_edit_post_link( $order->get_product_id() ); ?>"><?php echo $order->get_product_title(); ?></a>
			<small>(<?php echo ucfirst( $order->get_product_type() ); ?>)</small>
		</div>

		<?php if ( isset ( $order->product_sku ) ): ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'SKU:', 'lifterlms' ); ?></label>
				<?php echo $order->get_product_sku(); ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'lifterlms_order_meta_box_after_product_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_gateway_information', $order ); ?>

	<?php if ( $gateway ) : ?>

		<div class="llms-metabox-section d-1of3">

			<h4><?php _e( 'Gateway Information', 'lifterlms' ); ?></h4>

			<div class="llms-metabox-field">
				<label><?php _e( 'Name:', 'lifterlms' ); ?></label>
				<?php echo $order->get_payment_gateway_title(); ?>
			</div>


			<?php if ( isset( $order->transaction_id ) ) : ?>
				<?php $transaction = $gateway->get_transaction_url( $order->get_transaction_id() ); ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Transaction ID:', 'lifterlms' ); ?></label>
					<?php if ( false === filter_var( $transaction, FILTER_VALIDATE_URL ) ): ?>
						<?php echo $transaction; ?>
					<?php else: ?>
						<a href="<?php echo $transaction; ?>" target="_blank"><?php echo $order->get_transaction_id(); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>


			<?php if ( isset( $order->transaction_customer_id ) ) : ?>
				<?php $customer = $gateway->get_customer_url( $order->get_transaction_customer_id() ); ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Customer ID:', 'lifterlms' ); ?></label>
					<?php if ( false === filter_var( $customer, FILTER_VALIDATE_URL ) ): ?>
						<?php echo $customer; ?>
					<?php else: ?>
						<a href="<?php echo $customer; ?>" target="_blank"><?php echo $order->get_transaction_customer_id(); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( isset( $order->subscription_id ) ) : ?>
				<?php $subscription = $gateway->get_subscription_url( $order->get_subscription_id() ); ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Subscription ID:', 'lifterlms' ); ?></label>
					<?php if ( false === filter_var( $subscription, FILTER_VALIDATE_URL ) ): ?>
						<?php echo $subscription; ?>
					<?php else: ?>
						<a href="<?php echo $subscription; ?>" target="_blank"><?php echo $order->get_subscription_id(); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'lifterlms_order_meta_box_after_gateway_information', $order ); ?>

		</div>

	<?php endif; ?>

 	<?php do_action( 'lifterlms_after_order_meta_box', $order ); ?>

</div>
