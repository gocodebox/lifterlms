<?php
/**
 * Order History List
 * @since    3.0.0
 * @version  3.17.6
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<div class="llms-sd-section llms-my-orders">

	<?php if ( ! $orders || ! $orders['orders'] ) : ?>
		<p><?php _e( 'No orders found.', 'lifterlms' ); ?></p>
	<?php else : ?>

		<table class="orders-table">
			<thead>
				<tr>
					<td><?php _e( 'Order', 'lifterlms' ); ?></td>
					<td><?php _e( 'Date', 'lifterlms' ); ?></td>
					<td><?php _e( 'Expires', 'lifterlms' ); ?></td>
					<td><?php _e( 'Next Payment', 'lifterlms' ); ?></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $orders['orders'] as $order ) : ?>
				<tr class="llms-order-item <?php echo $order->get( 'status' ); ?>" id="llms-order-<?php $order->get( 'id' ); ?>">
					<td data-label="<?php _e( 'Order', 'lifterlms' ); ?>: ">
						<a href="<?php echo $order->get_view_link(); ?>">#<?php echo $order->get( 'id' ); ?></a>
						<span class="llms-status <?php echo $order->get( 'status' ); ?>"><?php echo $order->get_status_name(); ?></span>
					</td>
					<td data-label="<?php _e( 'Date', 'lifterlms' ); ?>: "><?php echo $order->get_date( 'date', 'F j, Y' ); ?></td>
					<td data-label="<?php _e( 'Expires', 'lifterlms' ); ?>: ">
						<?php if ( $order->is_recurring() && 'lifetime' === $order->get( 'access_expiration' ) ) : ?>
							&ndash;
						<?php else : ?>
							<?php echo $order->get_access_expiration_date( 'F j, Y' ); ?>
						<?php endif; ?>
					</td>
					<td data-label="<?php _e( 'Next Payment', 'lifterlms' ); ?>: ">
						<?php if ( $order->has_scheduled_payment() ) : ?>
							<?php echo $order->get_next_payment_due_date( 'F j, Y' ); ?>
						<?php else : ?>
							&ndash;
						<?php endif; ?>
					</td>
					<td>
						<a class="llms-button-primary small" href="<?php echo $order->get_view_link(); ?>"><?php _e( 'View', 'lifterlms' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $orders['orders'] ) : ?>
			<footer class="llms-sd-pagination llms-my-orders-pagination">
				<?php if ( $orders['page'] > 1 ) : ?>
					<a href="<?php echo add_query_arg( array(
						'opage' => $orders['page'] - 1,
					) ); ?>"><?php _e( 'Back', 'lifterlms' ); ?></a>
				<?php endif; ?>

				<?php if ( $orders['page'] < $orders['pages'] ) : ?>
					<a href="<?php echo add_query_arg( array(
						'opage' => $orders['page'] + 1,
					) ); ?>"><?php _e( 'Next', 'lifterlms' ); ?></a>
				<?php endif; ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>
</div>
