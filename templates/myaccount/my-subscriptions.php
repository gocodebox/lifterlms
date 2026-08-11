<?php
/**
 * Student Dashboard: My Subscriptions list.
 *
 * Lists the student's recurring orders (subscriptions). Each subscription links to the
 * single order view where the student can update the payment method, cancel the
 * subscription, and download the order receipt.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 *
 * @var array $subscriptions Result array from {@see LLMS_Student::get_subscriptions()}.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-sd-section llms-my-subscriptions">

	<?php if ( ! $subscriptions || ! $subscriptions['orders'] ) : ?>
		<p><?php esc_html_e( 'No subscriptions found.', 'lifterlms' ); ?></p>
	<?php else : ?>

		<table class="orders-table subscriptions-table">
			<thead>
				<tr>
					<td><?php esc_html_e( 'Subscription', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Product', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Status', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Next Payment', 'lifterlms' ); ?></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $subscriptions['orders'] as $order ) : ?>
				<tr class="llms-order-item <?php echo esc_attr( $order->get( 'status' ) ); ?>" id="llms-order-<?php echo esc_attr( $order->get( 'id' ) ); ?>">
					<td data-label="<?php esc_attr_e( 'Subscription', 'lifterlms' ); ?>: ">
						<a href="<?php echo esc_url( $order->get_view_link() ); ?>">#<?php echo esc_html( $order->get( 'id' ) ); ?></a>
					</td>
					<td data-label="<?php esc_attr_e( 'Product', 'lifterlms' ); ?>: "><?php echo esc_html( $order->get( 'product_title' ) ); ?></td>
					<td data-label="<?php esc_attr_e( 'Status', 'lifterlms' ); ?>: ">
						<span class="llms-status <?php echo esc_attr( $order->get( 'status' ) ); ?>"><?php echo esc_html( $order->get_status_name() ); ?></span>
					</td>
					<td data-label="<?php esc_attr_e( 'Next Payment', 'lifterlms' ); ?>: ">
						<?php if ( $order->has_scheduled_payment() ) : ?>
							<?php echo esc_html( $order->get_next_payment_due_date( 'F j, Y' ) ); ?>
						<?php else : ?>
							&ndash;
						<?php endif; ?>
					</td>
					<td>
						<a class="llms-button-primary small" href="<?php echo esc_url( $order->get_view_link() ); ?>"><?php esc_html_e( 'Manage', 'lifterlms' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<footer class="llms-sd-pagination llms-my-subscriptions-pagination">
			<?php if ( $subscriptions['page'] > 1 ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'subspage' => $subscriptions['page'] - 1 ) ) ); ?>"><?php esc_html_e( 'Back', 'lifterlms' ); ?></a>
			<?php endif; ?>

			<?php if ( $subscriptions['page'] < $subscriptions['pages'] ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'subspage' => $subscriptions['page'] + 1 ) ) ); ?>"><?php esc_html_e( 'Next', 'lifterlms' ); ?></a>
			<?php endif; ?>
		</footer>

	<?php endif; ?>
</div>
