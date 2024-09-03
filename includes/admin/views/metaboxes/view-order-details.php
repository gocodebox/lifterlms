<?php
/**
 * Order Details metabox for Order on Admin Panel
 *
 * @package LifterLMS/Admin/Views
 *
 * @since 5.3.0
 * @since 5.4.0 Inform about deleted products.
 * @since 6.1.0 Add validation to the remaining payments input.
 *              Allow the number of remaining payments to be `0` for already-completed payment plans.
 * @version 7.0.0
 *
 * @property LLMS_Order           $order                     Order object.
 * @property LLMS_Payment_Gateway $gateway                   Instance of the order's payment gateway.
 * @property array                $switchable_gateways       List of gateways that the order can be switched to.
 * @property array                $switchable_gateway_fields List of admin fields for the available switchable gateways.
 */

defined( 'ABSPATH' ) || exit;

$supports_modify_recurring_payments = $order->supports_modify_recurring_payments();
?>

<div class="llms-metabox">

	<?php if ( 'test' === $order->get( 'gateway_api_mode' ) ) : ?>
		<h6 class="llms-transaction-test-mode"><?php esc_html_e( 'This order was processed in the gateway\'s testing mode', 'lifterlms' ); ?></h6>
	<?php endif; ?>

	<?php do_action( 'lifterlms_before_order_meta_box', $order ); ?>


	<h2><?php echo esc_html( sprintf( __( 'Order #%s', 'lifterlms' ), $order->get( 'id' ) ) ); ?></h2>
	<h3><?php echo esc_html( sprintf( __( 'Processed by %s', 'lifterlms' ), is_wp_error( $gateway ) ? $order->get( 'payment_gateway' ) : $gateway->get_admin_title() ) ); ?></h3>


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

			<h4><?php esc_html_e( 'Access Plan Information', 'lifterlms' ); ?></h4>

			<div class="llms-metabox-field">
				<label><?php esc_html_e( 'Name:', 'lifterlms' ); ?></label>
				<?php echo esc_html( $order->get( 'plan_title' ) ); ?>
				<small>(#<?php echo esc_html( $order->get( 'plan_id' ) ); ?>)</small>
			</div>

			<?php if ( llms_parse_bool( get_option( 'llms_access_plans_allow_skus', 'no' ) ) ) : ?>
				<div class="llms-metabox-field">
					<label><?php esc_html_e( 'SKU:', 'lifterlms' ); ?></label>
					<?php echo esc_html( $order->get( 'plan_sku' ) ); ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'lifterlms_order_meta_box_after_plan_information', $order ); ?>

		<?php do_action( 'lifterlms_order_meta_box_before_product_information', $order ); ?>

		<h4><?php esc_html_e( 'Product Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php esc_html_e( 'Name:', 'lifterlms' ); ?></label>
			<?php if ( llms_get_post( $order->get( 'product_id' ) ) ) : ?>
				<a href="<?php echo esc_url( get_edit_post_link( $order->get( 'product_id' ) ) ); ?>"><?php echo esc_html( $order->get( 'product_title' ) ); ?></a>
			<?php else : ?>
				<?php echo esc_html__( '[DELETED]', 'lifterlms' ) . ' ' . esc_html( $order->get( 'product_title' ) ); ?>
			<?php endif; ?>
			<small>(<?php echo esc_html( ucfirst( $order->get( 'product_type' ) ) ); ?>)</small>
		</div>

		<div class="llms-metabox-field">
			<label><?php esc_html_e( 'SKU:', 'lifterlms' ); ?></label>
			<?php echo esc_html( $order->get( 'product_sku' ) ); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_product_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_payment_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<?php if ( $order->has_trial() ) : ?>

			<h4><?php esc_html_e( 'Trial Information', 'lifterlms' ); ?></h4>

			<?php if ( $order->has_coupon() && $order->get( 'coupon_amount_trial' ) ) : ?>
				<div class="llms-metabox-field">
					<label><?php esc_html_e( 'Original Total:', 'lifterlms' ); ?></label>
					<?php echo wp_kses( $order->get_price( 'trial_original_total' ), LLMS_ALLOWED_HTML_PRICES ); ?>
				</div>

				<div class="llms-metabox-field">
					<label><?php esc_html_e( 'Coupon Discount:', 'lifterlms' ); ?></label>
					<?php echo wp_kses( $order->get_coupon_amount( 'trial' ), LLMS_ALLOWED_HTML_PRICES ); ?>
					(<?php echo wp_kses( llms_price( $order->get_price( 'coupon_value_trial', array(), 'float' ) * - 1 ), LLMS_ALLOWED_HTML_PRICES ); ?>)
					[<a href="<?php echo esc_url( get_edit_post_link( $order->get( 'coupon_id' ) ) ); ?>"><?php echo esc_html( $order->get( 'coupon_code' ) ); ?></a>]
				</div>
			<?php endif; ?>

			<div class="llms-metabox-field">
				<label><?php esc_html_e( 'Total:', 'lifterlms' ); ?></label>
				<?php echo wp_kses( $order->get_price( 'trial_total' ), LLMS_ALLOWED_HTML_PRICES ); ?>
				<?php echo esc_html( sprintf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'trial_length' ), 'lifterlms' ), $order->get( 'trial_length' ), $order->get( 'trial_period' ) ) ); ?>
			</div>

		<?php endif; ?>

		<h4><?php esc_html_e( 'Payment Information', 'lifterlms' ); ?></h4>

		<?php if ( $order->has_discount() ) : ?>
			<div class="llms-metabox-field">
				<label><?php esc_html_e( 'Original Total:', 'lifterlms' ); ?></label>
				<?php echo wp_kses( $order->get_price( 'original_total' ), LLMS_ALLOWED_HTML_PRICES ); ?>
			</div>

			<?php if ( $order->has_sale() ) : ?>
				<div class="llms-metabox-field">
					<label><?php esc_html_e( 'Sale Discount:', 'lifterlms' ); ?></label>
					<?php echo wp_kses( $order->get_price( 'sale_price' ), LLMS_ALLOWED_HTML_PRICES ); ?>
					(<?php echo wp_kses( llms_price( $order->get_price( 'sale_value', array(), 'float' ) * -1 ), LLMS_ALLOWED_HTML_PRICES ); ?>)
				</div>
			<?php endif; ?>

			<?php if ( $order->has_coupon() ) : ?>
				<div class="llms-metabox-field">
					<label><?php esc_html_e( 'Coupon Discount:', 'lifterlms' ); ?></label>
					<?php echo wp_kses( $order->get_coupon_amount( 'regular' ), LLMS_ALLOWED_HTML_PRICES ); ?>
					(<?php echo wp_kses( llms_price( $order->get_price( 'coupon_value', array(), 'float' ) * - 1 ), LLMS_ALLOWED_HTML_PRICES ); ?>)
					[<a href="<?php echo esc_url( get_edit_post_link( $order->get( 'coupon_id' ) ) ); ?>"><?php echo esc_html( $order->get( 'coupon_code' ) ); ?></a>]
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="llms-metabox-field">
			<label><?php esc_html_e( 'Total:', 'lifterlms' ); ?></label>
			<?php echo wp_kses( $order->get_price( 'total' ), LLMS_ALLOWED_HTML_PRICES ); ?>
			<?php if ( $order->is_recurring() ) : ?>
				<?php
				//phpcs:disable WordPress.WP.I18n.MissingSingularPlaceholder -- We don't output the number so it's throwing an error but it's not broken.
				echo esc_html(
					sprintf(
					// Translators: %1$d = The order billing period; %2$s = The order billing frequency.
						_n( 'Every %2$s', 'Every %1$d %2$ss', $order->get( 'billing_frequency' ), 'lifterlms' ), // phpcs:ignore: WordPress.WP.I18n.MismatchedPlaceholders
						$order->get( 'billing_frequency' ),
						$order->get( 'billing_period' )
					)
				);
				//phpcs:enable WordPress.WP.I18n.MissingSingularPlaceholder
				?>
				<?php if ( $order->get( 'billing_length' ) > 0 ) : ?>
					<?php echo esc_html( sprintf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'billing_length' ), 'lifterlms' ), $order->get( 'billing_length' ), $order->get( 'billing_period' ) ) ); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php esc_html_e( 'One-time', 'lifterlms' ); ?>
			<?php endif; ?>
		</div>

		<?php
		if ( $order->has_plan_expiration() ) :
			$remaining               = $order->get_remaining_payments();
			$remaining_input_min_val = 0 === $remaining ? 0 : 1;
			?>
			<div class="llms-metabox-field">
				<label><?php esc_html_e( 'Remaining Payments:', 'lifterlms' ); ?></label>
				<span id="llms-remaining-payments-view"><?php echo esc_html( $remaining ); ?></span>
				<?php if ( $supports_modify_recurring_payments ) : ?>
					<?php add_thickbox(); ?>
					<div id="llms-remaining-edit">
						<div class="llms-remaining-edit--content">
							<h4><?php esc_html_e( 'Modify Remaining Payments', 'lifterlms' ); ?></h4>

							<label>
								<span><?php esc_html_e( 'Remaining payments', 'lifterlms' ); ?></span>
								<input type="number" id="llms-num-remaining-payments" value="<?php echo esc_attr( $remaining ); ?>" min="<?php echo esc_attr( $remaining_input_min_val ); ?>" step="1">
							</label>

							<label>
								<span><?php esc_html_e( 'Order Note', 'lifterlms' ); ?></span>
								<textarea id="llms-remaining-payments-note" rows="3"></textarea>
								<em><?php esc_html_e( 'For internal use only, not visible to the customer.', 'lifterlms' ); ?></em>
							</label>

							<button id="llms-save-remaining-payments" class="button button-primary button-large"><?php esc_html_e( 'Save', 'lifterlms' ); ?></button>

							<script>
								(function(){
									document.getElementById( 'llms-save-remaining-payments' ).addEventListener( 'click', function() {
										var remainingEl = document.getElementById( 'llms-num-remaining-payments' ),
											errEl       = document.getElementById( 'llms-remaining-payments-err' ),
											remaining   = remainingEl.value,
											note        = document.getElementById( 'llms-remaining-payments-note' ).value;

										if ( errEl ) {
											errEl.remove();
										}

										if ( ! remainingEl.checkValidity() ) {
											remainingEl.insertAdjacentHTML( 'afterend', '<em id="llms-remaining-payments-err" class="llms-error">' + remainingEl.validationMessage + '</em>' );
											return;
										}

										tb_remove();

										document.querySelector( 'input[name="_llms_remaining_payments"]' ).value = remaining;
										document.querySelector( 'input[name="_llms_remaining_note"]' ).value = note;
										document.getElementById( 'llms-remaining-payments-view' ).innerHTML = remaining;
									} );
								})();
							</script>

						</div>
					</div>

					<a href="#TB_inline?&width=300&height=400&inlineId=llms-remaining-edit" class="thickbox llms-metabox-icon">
						<span class="dashicons dashicons-edit" role="img" aria-label="<?php esc_attr_e( 'Add additional payments', 'lifterlms' ); ?>"></span>
					</a>

					<input type="hidden" name="_llms_remaining_payments" value="<?php echo esc_attr( $remaining ); ?>">
					<input type="hidden" name="_llms_remaining_note">
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'lifterlms_order_meta_box_after_payment_information', $order ); ?>

	</div>

	<?php do_action( 'lifterlms_order_meta_box_before_customer_information', $order ); ?>

	<div class="llms-metabox-section d-1of3">

		<h4><?php esc_html_e( 'Customer Information', 'lifterlms' ); ?></h4>

		<div class="llms-metabox-field">
			<label><?php esc_html_e( 'Buyer Name:', 'lifterlms' ); ?></label>
			<?php if ( llms_parse_bool( $order->get( 'anonymized' ) ) || empty( llms_get_student( $order->get( 'user_id' ) ) ) ) : ?>
				<?php echo esc_html( $order->get_customer_name() ); ?>
			<?php else : ?>
				<?php
				$edit_user_link = $order->get( 'user_id' ) ? get_edit_user_link( $order->get( 'user_id' ) ) : '';
				echo ! $edit_user_link ? esc_html( $order->get_customer_name() ) . '<br>' : '<a href="' . esc_url( $edit_user_link ) . '">' . esc_html( $order->get_customer_name() ) . '</a>';
				?>
			<?php endif; ?>
		</div>

		<div class="llms-metabox-field">
			<label><?php esc_html_e( 'Buyer Email:', 'lifterlms' ); ?></label>
			<a href="<?php echo esc_url( 'mailto:' . $order->get( 'billing_email' ) ); ?>"><?php echo esc_html( $order->get( 'billing_email' ) ); ?></a>
		</div>

		<?php if ( $order->get( 'billing_address_1' ) ) : ?>
			<div class="llms-metabox-field">
				<label><?php esc_html_e( 'Buyer Address:', 'lifterlms' ); ?></label>
				<?php echo esc_html( $order->get( 'billing_address_1' ) ); ?><br>
				<?php if ( isset( $order->billing_address_2 ) ) : ?>
					<?php echo esc_html( $order->get( 'billing_address_2' ) ); ?><br>
				<?php endif; ?>
				<?php echo esc_html( $order->get( 'billing_city' ) ); ?>,
				<?php echo esc_html( $order->get( 'billing_state' ) ); ?>,
				<?php echo esc_html( $order->get( 'billing_zip' ) ); ?><br>
				<?php echo esc_html( llms_get_country_name( $order->get( 'billing_country' ) ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $order->get( 'billing_phone' ) ) : ?>
			<div class="llms-metabox-field">
			<label><?php esc_html_e( 'Buyer Phone:', 'lifterlms' ); ?></label>
				<?php echo esc_html( $order->get( 'billing_phone' ) ); ?>
			</div>
		<?php endif; ?>


		<div class="llms-metabox-field">
			<label><?php esc_html_e( 'Buyer IP Address:', 'lifterlms' ); ?></label>
			<?php echo esc_html( $order->get( 'user_ip_address' ) ); ?>
		</div>

		<?php do_action( 'lifterlms_order_meta_box_after_customer_information', $order ); ?>

	</div>

	<div class="clear"></div>


	<?php do_action( 'lifterlms_order_meta_box_before_gateway_information', $order ); ?>

	<?php if ( $gateway ) : ?>

		<div class="llms-metabox-section d-all">

			<h4>
				<?php esc_html_e( 'Gateway Information', 'lifterlms' ); ?>
				<button class="llms-editable" title="<?php esc_attr_e( 'Edit gateway information', 'lifterlms' ); ?>">
					<span class="dashicons dashicons-edit"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Edit gateway information', 'lifterlms' ); ?></span>
				</button>
			</h4>

			<div class="llms-metabox-field d-1of4" data-gateway-fields='<?php echo wp_json_encode( $switchable_gateway_fields ); ?>' data-llms-editable="payment_gateway" data-llms-editable-options='<?php echo wp_json_encode( $switchable_gateways ); ?>' data-llms-editable-type="select" data-llms-editable-value="<?php echo esc_attr( $order->get( 'payment_gateway' ) ); ?>">
				<label><?php esc_html_e( 'Name:', 'lifterlms' ); ?></label>
				<?php echo is_wp_error( $gateway ) ? esc_html( $order->get( 'payment_gateway' ) ) : esc_html( $gateway->get_admin_title() ); ?>
			</div>

			<?php if ( ! is_wp_error( $gateway ) ) : ?>

				<?php foreach ( $gateway->get_admin_order_fields() as $field => $data ) : ?>

					<div class="llms-metabox-field d-1of4"<?php echo ! $data['enabled'] ? ' style="display:none;"' : ' '; ?>data-llms-editable="<?php echo esc_attr( $data['name'] ); ?>" data-llms-editable-required="yes" data-llms-editable-type="text" data-llms-editable-value="<?php echo esc_attr( $order->get( $data['name'] ) ); ?>">
						<label><?php echo esc_html( $data['label'] ); ?></label>
						<?php echo wp_kses_post( $gateway->get_item_link( $field, $order->get( $data['name'] ), $order->get( 'gateway_api_mode' ) ) ); ?>
					</div>

				<?php endforeach; ?>

			<?php endif; ?>

			<?php do_action( 'lifterlms_order_meta_box_after_gateway_information', $order ); ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'lifterlms_after_order_meta_box', $order ); ?>

</div>
