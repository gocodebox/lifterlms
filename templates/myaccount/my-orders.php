<?php
/**
 * Order History List
 *
 * @package LifterLMS/Templates
 *
 * @since    3.0.0
 * @version  7.6.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-sd-section llms-my-orders">

	<?php if ( ! $orders || ! $orders['orders'] ) : ?>
		<p><?php esc_html_e( 'No orders found.', 'lifterlms' ); ?></p>
	<?php else : ?>

		<table class="orders-table">
			<thead>
				<tr>
					<td><?php esc_html_e( 'Order', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Date', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Expires', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Next Payment', 'lifterlms' ); ?></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $orders['orders'] as $order ) : ?>
				<tr class="llms-order-item <?php echo esc_attr( $order->get( 'status' ) ); ?>" id="llms-order-<?php esc_attr( $order->get( 'id' ) ); ?>">
					<td data-label="<?php esc_attr_e( 'Order', 'lifterlms' ); ?>: ">
						<a href="<?php echo esc_url( $order->get_view_link() ); ?>">#<?php echo esc_html( $order->get( 'id' ) ); ?></a>
						<span class="llms-status <?php echo esc_attr( $order->get( 'status' ) ); ?>"><?php echo esc_html( $order->get_status_name() ); ?></span>
					</td>
					<td data-label="<?php esc_attr_e( 'Date', 'lifterlms' ); ?>: "><?php echo esc_html( $order->get_date( 'date', 'F j, Y' ) ); ?></td>
					<td data-label="<?php esc_attr_e( 'Expires', 'lifterlms' ); ?>: ">
						<?php if ( $order->is_recurring() && 'lifetime' === $order->get( 'access_expiration' ) ) : ?>
							&ndash;
						<?php else : ?>
							<?php echo esc_html( $order->get_access_expiration_date( 'F j, Y' ) ); ?>
						<?php endif; ?>
					</td>
					<td data-label="<?php esc_attr_e( 'Next Payment', 'lifterlms' ); ?>: ">
						<?php if ( $order->has_scheduled_payment() ) : ?>
							<?php echo esc_html( $order->get_next_payment_due_date( 'F j, Y' ) ); ?>
						<?php else : ?>
							&ndash;
						<?php endif; ?>
					</td>
					<td>
						<a class="llms-button-primary small" href="<?php echo esc_url( $order->get_view_link() ); ?>"><?php esc_html_e( 'View', 'lifterlms' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $orders['orders'] ) : ?>
			<footer class="llms-sd-pagination llms-my-orders-pagination">
				<?php if ( $orders['page'] > 1 ) : ?>
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'opage' => $orders['page'] - 1,
							)
						)
					);
					?>
					"><?php esc_html_e( 'Back', 'lifterlms' ); ?></a>
				<?php endif; ?>

				<?php if ( $orders['page'] < $orders['pages'] ) : ?>
					<a href="
					<?php
					echo esc_url(
						add_query_arg(
							array(
								'opage' => $orders['page'] + 1,
							)
						)
					);
					?>
					"><?php esc_html_e( 'Next', 'lifterlms' ); ?></a>
				<?php endif; ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>
</div>
