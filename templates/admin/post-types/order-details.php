<?php
/**
 * Order Details metabox for Order on Admin Panel
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.0.0
 * @since 3.18.0 Unknown.
 * @since 3.36.2 Prevent fatal error when reviewing an order placed with a payment gateway that's been deactivated.
 * @version 3.36.2
 */

defined( 'ABSPATH' ) || exit;

is_admin() || exit;

// Used to allow admins to switch payment gateways.
$gateway_feature           = $order->is_recurring() ? 'recurring_payments' : 'single_payments';
$switchable_gateways       = array();
$switchable_gateway_fields = array();
foreach ( LLMS()->payment_gateways()->get_supporting_gateways( $gateway_feature ) as $id => $gateway_obj ) {
	$switchable_gateways[ $id ]       = $gateway_obj->get_admin_title();
	$switchable_gateway_fields[ $id ] = $gateway_obj->get_admin_order_fields();
}
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

		<?php
			/**
			 * THIS ACTION HOOK TO BE DEPRECATED!
			 */
			do_action( 'lifterlms_order_meta_box_after_order_information', $order );
		?>

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
					<label><?php _e( 'Original Total:', 'lifterlms' ); ?></label>
					<?php echo $order->get_price( 'trial_original_total' ); ?>
				</div>

				<div class="llms-metabox-field">
					<label><?php _e( 'Coupon Discount:', 'lifterlms' ); ?></label>
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

		<?php endif; ?>

		<h4><?php _e( 'Payment Information', 'lifterlms' ); ?></h4>

		<?php if ( $order->has_discount() ) : ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'Original Total:', 'lifterlms' ); ?></label>
				<?php echo $order->get_price( 'original_total' ); ?>
			</div>

			<?php if ( $order->has_sale() ) : ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Sale Discount:', 'lifterlms' ); ?></label>
					<?php echo $order->get_price( 'sale_price' ); ?>
					(<?php echo llms_price( $order->get_price( 'sale_value', array(), 'float' ) * -1 ); ?>)
				</div>
			<?php endif; ?>

			<?php if ( $order->has_coupon() ) : ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Coupon Discount:', 'lifterlms' ); ?></label>
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
				<?php
				//phpcs:disable WordPress.WP.I18n.MissingSingularPlaceholder -- We don't output the number so it's throwing an error but it's not broken.
				printf(
					_n( 'Every %2$s', 'Every %1$d %2$ss', $order->get( 'billing_frequency' ), 'lifterlms' ),
					$order->get( 'billing_frequency' ),
					$order->get( 'billing_period' )
				);
				//phpcs:enable WordPress.WP.I18n.MissingSingularPlaceholder
				?>
				<?php if ( $order->get( 'billing_length' ) > 0 ) : ?>
					<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'billing_length' ), 'lifterlms' ), $order->get( 'billing_length' ), $order->get( 'billing_period' ) ); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php _e( 'One-time', 'lifterlms' ); ?>
			<?php endif; ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_payment_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_customer_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php _e( 'Customer Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Name:', 'lifterlms' ); ?></label>
			<?php if ( llms_parse_bool( $order->get( 'anonymized' ) ) ) : ?>
				<?php echo $order->get_customer_name(); ?>
			<?php else : ?>
				<a href="<?php echo get_edit_user_link( $order->get( 'user_id' ) ); ?>"><?php echo $order->get_customer_name(); ?></a>
			<?php endif; ?>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Email:', 'lifterlms' ); ?></label>
			<a href="mailto:<?php echo $order->get( 'billing_email' ); ?>"><?php echo $order->get( 'billing_email' ); ?></a>
		</div>

		<?php if ( $order->get( 'billing_address_1' ) ) : ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'Buyer Address:', 'lifterlms' ); ?></label>
				<?php echo $order->get( 'billing_address_1' ); ?><br>
				<?php if ( isset( $order->billing_address_2 ) ) : ?>
					<?php echo $order->get( 'billing_address_2' ); ?><br>
				<?php endif; ?>
				<?php echo $order->get( 'billing_city' ); ?>,
				<?php echo $order->get( 'billing_state' ); ?>,
				<?php echo $order->get( 'billing_zip' ); ?><br>
				<?php echo llms_get_country_name( $order->get( 'billing_country' ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $order->get( 'billing_phone' ) ) : ?>
			<div class="llms-metabox-field">
			<label><?php _e( 'Buyer Phone:', 'lifterlms' ); ?></label>
				<?php echo $order->get( 'billing_phone' ); ?>
			</div>
		<?php endif; ?>


		<div class="llms-metabox-field">
			<label><?php _e( 'Buyer IP Address:', 'lifterlms' ); ?></label>
			<?php echo $order->get( 'user_ip_address' ); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_customer_information', $order ); ?>

	</div>

	<div class="clear"></div>


	<?php do_action( 'lifterlms_order_meta_box_before_gateway_information', $order ); ?>

	<?php if ( $gateway ) : ?>

		<div class="llms-metabox-section d-all">

			<h4><?php _e( 'Gateway Information', 'lifterlms' ); ?><a class="llms-editable" href="#"><span class="dashicons dashicons-edit"></span></a></h4>

			<div class="llms-metabox-field d-1of4" data-gateway-fields='<?php echo json_encode( $switchable_gateway_fields ); ?>' data-llms-editable="payment_gateway" data-llms-editable-options='<?php echo json_encode( $switchable_gateways ); ?>' data-llms-editable-type="select" data-llms-editable-value="<?php echo $order->get( 'payment_gateway' ); ?>">
				<label><?php _e( 'Name:', 'lifterlms' ); ?></label>
				<?php echo is_wp_error( $gateway ) ? $order->get( 'payment_gateway' ) : $gateway->get_admin_title(); ?>
			</div>

			<?php if ( ! is_wp_error( $gateway ) ) : ?>

				<?php foreach ( $gateway->get_admin_order_fields() as $field => $data ) : ?>

					<div class="llms-metabox-field d-1of4"<?php echo ! $data['enabled'] ? ' style="display:none;"' : ' '; ?>data-llms-editable="<?php echo $data['name']; ?>" data-llms-editable-required="yes" data-llms-editable-type="text" data-llms-editable-value="<?php echo $order->get( $data['name'] ); ?>">
						<label><?php echo $data['label']; ?></label>
						<?php echo $gateway->get_item_link( $field, $order->get( $data['name'] ), $order->get( 'gateway_api_mode' ) ); ?>
					</div>

				<?php endforeach; ?>

			<?php endif; ?>

			<?php do_action( 'lifterlms_order_meta_box_after_gateway_information', $order ); ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_order_meta_box', $order ); ?>

</div>
