<?php
/**
 * View an Order
 * @since    3.0.0
 * @version  3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$gateway = $order->get_gateway();
?>

<div class="llms-sd-section llms-view-order">

	<?php if ( ! $order ) : ?>
		<p><?php _e( 'Invalid Order', 'lifterlms' ); ?></p>
	<?php else : ?>

		<h5><?php printf( __( 'Order #%d', 'lifterlms' ), $order->get( 'id' ) ); ?></h5>

		<?php do_action( 'lifterlms_before_view_order_table' ); ?>

		<table class="orders-table">
			<tbody>
				<tr>
					<td><strong><?php _e( 'Status', 'lifterlms' ); ?></strong></td>
					<td><?php echo $order->get_status_name(); ?></td>
				</tr>

				<tr>
					<td><strong><?php _e( 'Access Plan', 'lifterlms' ); ?></strong></td>
					<td><?php echo $order->get( 'plan_title' ); ?></td>
				</tr>

				<tr>
					<td><strong><?php _e( 'Product', 'lifterlms' ); ?></strong></td>
					<td><a href="<?php echo get_permalink( $order->get( 'product_id' ) ); ?>"><?php echo $order->get( 'product_title' ); ?></a></td>
				</tr>

				<?php if ( $order->has_trial() ) : ?>
					<?php if ( $order->has_coupon() && $order->get( 'coupon_amount_trial' ) ) : ?>
						<tr>
							<td><strong><?php _e( 'Original Total', 'lifterlms' ) ?></strong></td>
							<td><?php echo $order->get_price( 'trial_original_total' ); ?></td>
						</tr>

						<tr>
							<td><strong><?php _e( 'Coupon Discount', 'lifterlms' ) ?></strong></td>
							<td>
								<?php echo $order->get_coupon_amount( 'trial' ); ?>
								(<?php echo llms_price( $order->get_price( 'coupon_value_trial', array(), 'float' ) * - 1 ); ?>)
								[<a href="<?php echo get_edit_post_link( $order->get( 'coupon_id' ) ); ?>"><?php echo $order->get( 'coupon_code' ); ?></a>]
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<td><strong><?php _e( 'Trial Total', 'lifterlms' ); ?></strong></td>
						<td>
							<?php echo $order->get_price( 'trial_total' ); ?>
							<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'trial_length' ), 'lifterlms' ), $order->get( 'trial_length' ), $order->get( 'trial_period' ) ); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( $order->has_discount() ) : ?>
					<tr>
						<td><strong><?php _e( 'Original Total', 'lifterlms' ) ?></strong></td>
						<td><?php echo $order->get_price( 'original_total' ); ?></td>
					</tr>

					<?php if ( $order->has_sale() ) : ?>
						<tr>
							<td><strong><?php _e( 'Sale Discount', 'lifterlms' ) ?></strong></td>
							<td>
								<?php echo $order->get_price( 'sale_price' ); ?>
								(<?php echo llms_price( $order->get_price( 'sale_value', array(), 'float' ) * -1 ); ?>)
							</td>
						</tr>
					<?php endif; ?>

					<?php if ( $order->has_coupon() ) : ?>
						<tr>
							<td><strong><?php _e( 'Coupon Discount', 'lifterlms' ) ?></strong></td>
							<td>
								<?php echo $order->get_coupon_amount( 'regular' ); ?>
								(<?php echo llms_price( $order->get_price( 'coupon_value', array(), 'float' ) * - 1 ); ?>)
								[<a href="<?php echo get_edit_post_link( $order->get( 'coupon_id' ) ); ?>"><?php echo $order->get( 'coupon_code' ); ?></a>]
							</td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<tr>
					<td><strong><?php _e( 'Total', 'lifterlms' ); ?></strong></td>
					<td>
						<?php echo $order->get_price( 'total' ); ?>
						<?php if ( $order->is_recurring() ) : ?>
							<?php printf( _n( 'Every %2$s', 'Every %1$d %2$ss', $order->get( 'billing_frequency' ), 'lifterlms' ), $order->get( 'billing_frequency' ), $order->get( 'billing_period' ) ); ?>
							<?php if ( $order->get( 'billing_cycle' ) > 0 ) : ?>
								<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'billing_cycle' ), 'lifterlms' ), $order->get( 'billing_cycle' ), $order->get( 'billing_period' ) ); ?>
							<?php endif; ?>
						<?php else : ?>
							<?php _e( 'One-time', 'lifterlms' ); ?>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<td><strong><?php _e( 'Payment Method', 'lifterlms' ); ?></strong></td>
					<td>
						<?php if ( is_wp_error( $gateway ) ) : ?>
							<?php echo $order->get( 'payment_gateway' ); ?>
						<?php else : ?>
							<?php echo $gateway->get_title(); ?>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<td><strong><?php _e( 'Start Date', 'lifterlms' ); ?></strong></td>
					<td><?php echo $order->get_date( 'date', 'F j, Y' );; ?></td>
				</tr>
				<?php if ( $order->is_recurring() ) : ?>
					<tr>
						<td><strong><?php _e( 'Last Payment Date', 'lifterlms' ); ?></strong></td>
						<td><?php echo $order->get_last_transaction_date( 'llms-txn-succeeded', 'any', 'F j, Y' ); ?></td>
					</tr>
					<tr>
						<td><strong><?php _e( 'Next Payment Date', 'lifterlms' ); ?></strong></td>
						<td>
							<?php if ( $order->has_scheduled_payment() ) : ?>
								<?php echo $order->get_next_payment_due_date( 'F j, Y' ); ?>
							<?php else : ?>
								&ndash;
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td><strong><?php _e( 'Expiration Date', 'lifterlms' ); ?></strong></td>
					<td><?php echo $order->get_access_expiration_date( 'F j, Y' ); ?></td>
				</tr>

			</tbody>
		</table>

		<?php do_action( 'lifterlms_after_view_order_table' ); ?>

	<?php endif; ?>
</div>
