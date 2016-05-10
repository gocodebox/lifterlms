<?php do_action( 'lifterlms_before_order_meta_box' ); ?>

<table class="form-table">
	<tbody>

		<tr><th><label><?php _e( 'Buyer Details', 'lifterlms' ); ?></label></th></tr>

		<tr><td><table class="form-table">

			<tr>
				<td><label><?php _e( 'Buyer Name', 'lifterlms' ) ?></label></td>
				<td><a href="<?php echo get_edit_user_link( $order->get_user_id() ); ?>"><?php echo $order->get_billing_name(); ?></a></td>
			</tr>
			<tr>
				<td><label><?php _e( 'Buyer Email', 'lifterlms' ) ?></label></td>
				<td><a href="mailto:<?php echo $order->get_billing_email(); ?>"><?php echo $order->get_billing_email(); ?></td>
			</tr>

		</table></td></tr>


		<tr><th><label><?php _e( 'General Details', 'lifterlms' ); ?></label></th></tr>

		<tr><td><table class="form-table">

			<tr>
				<td><label><?php _e( 'Order Date', 'lifterlms' ) ?></label></td>
				<td><?php echo $order->get_date(); ?></td>
			</tr>
			<tr>
				<td><label><?php _e( 'Payment Method', 'lifterlms' ) ?></label></td>
				<td><?php echo $order->get_payment_method(); ?></td>
			</tr>
			<tr>
				<td><label><?php _e( 'Product Name', 'lifterlms' ) ?></label></td>
				<td><a href="<?php echo get_edit_post_link( $order->get_product_id() ); ?>"><?php echo $order->get_product_title(); ?></a></td>
			</tr>
			<tr>
				<td><label><?php _e( 'Product Type', 'lifterlms' ) ?></label></td>
				<td><?php echo $order->get_product_type(); ?></td>
			</tr>
			<tr>
				<td><label><?php _e( 'Purchase Total', 'lifterlms' ) ?></label></td>
				<td><?php echo get_lifterlms_currency_symbol() . $order->get_total(); ?></td>
			</tr>

			<tr>
				<td><label><?php _e( 'Order Type', 'lifterlms' ) ?></label></td>
				<td><?php echo $order->get_type(); ?></td>
			</tr>


			<?php if ( 'recurring' === $order->get_type() ) : ?>

				<tr>
					<td><label><?php _e( 'First Payment (paid)', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_first_payment(); ?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Billing Amount', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_recurring_price(); ?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Billing Period', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_billing_period(); ?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Billing Frequency', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_billing_frequency(); ?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Billing Start Date', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_billing_start_date(); ?></td>
				</tr>

			<?php endif; ?>



			<?php if ( ! empty( $order->get_coupon_id() ) ) : ?>

				<tr>
					<td><label><?php _e( 'Coupon Used?', 'lifterlms' ) ?></label></td>
					<td><?php echo _e( 'Yes' ); ?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Coupon Code', 'lifterlms' ) ?></label></td>
					<td><a href="<?php echo get_edit_post_link( $order->get_coupon_id() ); ?>"><?php echo $order->get_coupon_code(); ?></a></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Coupon Amount', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_formatted_coupon_amount(); ?></td>
				</tr>
				<tr>
					<td><label><?php _e( 'Remaining coupon uses', 'lifterlms' ) ?></label></td>
					<td><?php echo $order->get_coupon_limit(); ?></td>
				</tr>

			<?php else : ?>

				<tr>
					<td><label><?php _e( 'Coupon Used?', 'lifterlms' ) ?></label></td>
					<td><?php echo _e( 'No' ); ?></td>
				</tr>

			<?php endif; ?>

		</table></td></tr>
	</tbody>
</table>
<?php
do_action( 'lifterlms_after_order_meta_box' );

wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );
