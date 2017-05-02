<?php
/**
 * View an Order
 * @since    3.0.0
 * @version  3.8.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $order ) {
	return _e( 'Invalid Order.', 'lifterlms' );
}

$gateway = $order->get_gateway();
?>

<div class="llms-sd-section llms-view-order">

	<?php if ( ! $order ) : ?>
		<p><?php _e( 'Invalid Order', 'lifterlms' ); ?></p>
	<?php else : ?>

		<h3><?php printf( __( 'Order #%d', 'lifterlms' ), $order->get( 'id' ) ); ?></h3>

		<?php do_action( 'lifterlms_before_view_order_table' ); ?>

		<table class="orders-table">
			<tbody>
				<tr>
					<th><?php _e( 'Status', 'lifterlms' ); ?></th>
					<td><?php echo $order->get_status_name(); ?></td>
				</tr>

				<tr>
					<th><?php _e( 'Access Plan', 'lifterlms' ); ?></th>
					<td><?php echo $order->get( 'plan_title' ); ?></td>
				</tr>

				<tr>
					<th><?php _e( 'Product', 'lifterlms' ); ?></th>
					<td><a href="<?php echo get_permalink( $order->get( 'product_id' ) ); ?>"><?php echo $order->get( 'product_title' ); ?></a></td>
				</tr>

				<?php if ( $order->has_trial() ) : ?>
					<?php if ( $order->has_coupon() && $order->get( 'coupon_amount_trial' ) ) : ?>
						<tr>
							<th><?php _e( 'Original Total', 'lifterlms' ) ?></th>
							<td><?php echo $order->get_price( 'trial_original_total' ); ?></td>
						</tr>

						<tr>
							<th><?php _e( 'Coupon Discount', 'lifterlms' ) ?></th>
							<td>
								<?php echo $order->get_coupon_amount( 'trial' ); ?>
								(<?php echo llms_price( $order->get_price( 'coupon_value_trial', array(), 'float' ) * - 1 ); ?>)
								[<a href="<?php echo get_edit_post_link( $order->get( 'coupon_id' ) ); ?>"><?php echo $order->get( 'coupon_code' ); ?></a>]
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<th><?php _e( 'Trial Total', 'lifterlms' ); ?></th>
						<td>
							<?php echo $order->get_price( 'trial_total' ); ?>
							<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'trial_length' ), 'lifterlms' ), $order->get( 'trial_length' ), $order->get( 'trial_period' ) ); ?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( $order->has_discount() ) : ?>
					<tr>
						<th><?php _e( 'Original Total', 'lifterlms' ) ?></th>
						<td><?php echo $order->get_price( 'original_total' ); ?></td>
					</tr>

					<?php if ( $order->has_sale() ) : ?>
						<tr>
							<th><?php _e( 'Sale Discount', 'lifterlms' ) ?></th>
							<td>
								<?php echo $order->get_price( 'sale_price' ); ?>
								(<?php echo llms_price( $order->get_price( 'sale_value', array(), 'float' ) * -1 ); ?>)
							</td>
						</tr>
					<?php endif; ?>

					<?php if ( $order->has_coupon() ) : ?>
						<tr>
							<th><?php _e( 'Coupon Discount', 'lifterlms' ) ?></th>
							<td>
								<?php echo $order->get_coupon_amount( 'regular' ); ?>
								(<?php echo llms_price( $order->get_price( 'coupon_value', array(), 'float' ) * - 1 ); ?>)
								[<a href="<?php echo get_edit_post_link( $order->get( 'coupon_id' ) ); ?>"><?php echo $order->get( 'coupon_code' ); ?></a>]
							</td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<tr>
					<th><?php _e( 'Total', 'lifterlms' ); ?></th>
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
					<th><?php _e( 'Payment Method', 'lifterlms' ); ?></th>
					<td>
						<?php if ( is_wp_error( $gateway ) ) : ?>
							<?php echo $order->get( 'payment_gateway' ); ?>
						<?php else : ?>
							<?php echo $gateway->get_title(); ?>
						<?php endif; ?>
					</td>
				</tr>

				<tr>
					<th><?php _e( 'Start Date', 'lifterlms' ); ?></th>
					<td><?php echo $order->get_date( 'date', 'F j, Y' ); ?></td>
				</tr>
				<?php if ( $order->is_recurring() ) : ?>
					<tr>
						<th><?php _e( 'Last Payment Date', 'lifterlms' ); ?></th>
						<td><?php echo $order->get_last_transaction_date( 'llms-txn-succeeded', 'any', 'F j, Y' ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Next Payment Date', 'lifterlms' ); ?></th>
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
					<th><?php _e( 'Expiration Date', 'lifterlms' ); ?></th>
					<td><?php echo $order->get_access_expiration_date( 'F j, Y' ); ?></td>
				</tr>

				<?php do_action( 'lifterlms_view_order_table_body' ); ?>
			</tbody>
		</table>

		<?php if ( $transactions['transactions'] ) : ?>

			<h3><?php _e( 'Order Transactions', 'lifterlms' ); ?></h3>
			<table class="orders-table transactions">
				<thead>
					<tr>
						<th><?php _e( 'ID', 'lifterlms' ); ?></th>
						<th><?php _e( 'Status', 'lifterlms' ); ?></th>
						<th><?php _e( 'Date', 'lifterlms' ); ?></th>
						<th><?php _e( 'Amount', 'lifterlms' ); ?></th>
						<th><?php _e( 'Method', 'lifterlms' ); ?></th>
					<tr>
				</thead>
				<tbody>
				<?php foreach ( $transactions['transactions'] as $txn ) : ?>
					<tr>
						<th><?php echo $txn->get( 'id' ); ?></th>
						<th><?php echo $txn->get_status_name(); ?></th>
						<th><?php echo $txn->get_date( 'date' ); ?></th>
						<th>
							<?php $refund_amount = $txn->get_price( 'refund_amount', array(), 'float' ); ?>
							<?php if ( $refund_amount ) : ?>
								<del><?php echo $txn->get_price( 'amount' ); ?></del>
								<?php echo $txn->get_net_amount(); ?>
							<?php else : ?>
								<?php echo $txn->get_price( 'amount' ); ?>
							<?php endif; ?>
						</th>
						<th><?php echo $txn->get( 'gateway_source_description' ); ?></th>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<?php if ( $transactions['pages'] > 1 ) : ?>
					<tfoot>
						<tr>
							<td colspan="5">
								<?php if ( $transactions['page'] > 1 ) : ?>
									<a class="llms-button-secondary small" href="<?php echo esc_url( add_query_arg( 'txnpage', $transactions['page'] - 1 ) ); ?>"><?php _e( 'Back', 'lifterlms' ); ?></a>
								<?php endif; ?>
								<?php if ( $transactions['page'] < $transactions['pages'] ) : ?>
									<a class="llms-button-secondary small" href="<?php echo esc_url( add_query_arg( 'txnpage', $transactions['page'] + 1 ) ); ?>"><?php _e( 'Next', 'lifterlms' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					</tfoot>
				<?php endif; ?>
			</table>

		<?php endif; ?>

		<?php do_action( 'lifterlms_after_view_order_table' ); ?>

	<?php endif; ?>
</div>
