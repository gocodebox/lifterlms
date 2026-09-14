<?php
/**
 * Customer orders table partial
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @property array $orders_result Orders result array from LLMS_Student::get_orders().
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$orders = ! empty( $orders_result['orders'] ) ? $orders_result['orders'] : array();
?>
<?php if ( empty( $orders ) ) : ?>
	<p><?php esc_html_e( 'No orders found for this customer.', 'lifterlms' ); ?></p>
<?php else : ?>
	<table class="llms-table zebra llms-customer-orders-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Product', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Status', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Revenue', 'lifterlms' ); ?></th>
				<th><?php esc_html_e( 'Date', 'lifterlms' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $orders as $customer_order ) : ?>
				<tr>
					<td>
						<a href="<?php echo esc_url( get_edit_post_link( $customer_order->get( 'id' ) ) ); ?>">
							#<?php echo esc_html( $customer_order->get( 'id' ) ); ?>
						</a>
					</td>
					<td><?php echo esc_html( $customer_order->get( 'product_title' ) ); ?></td>
					<td><?php echo esc_html( llms_get_order_status_name( $customer_order->get( 'status' ) ) ); ?></td>
					<td><?php echo wp_kses_post( llms_price( $customer_order->get_revenue( 'net' ) ) ); ?></td>
					<td><?php echo esc_html( $customer_order->get_date( 'date', get_option( 'date_format' ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
