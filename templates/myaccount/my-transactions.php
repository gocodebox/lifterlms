<?php
/**
 * Student Dashboard: Order History (transactions + orders) list.
 *
 * Lists each of the student's transactions plus any transaction-less orders (free
 * enrollments, trials, pending payment orders). Each row links to the parent order
 * where the student can download a per-transaction receipt.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 *
 * @var array $transactions Result array from {@see LLMS_Student_Dashboard::get_transactions_list()}.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="llms-sd-section llms-my-transactions">

	<?php if ( ! $transactions || ! $transactions['rows'] ) : ?>
		<p><?php esc_html_e( 'No orders found.', 'lifterlms' ); ?></p>
	<?php else : ?>

		<table class="orders-table transactions-table">
			<thead>
				<tr>
					<td><?php esc_html_e( 'Transaction', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Date', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Product', 'lifterlms' ); ?></td>
					<td><?php esc_html_e( 'Amount', 'lifterlms' ); ?></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ( $transactions['rows'] as $row ) :
				if ( $row instanceof LLMS_Transaction ) {
					$order  = llms_get_post( $row->get( 'order_id' ) );
					$row_id = $row->get( 'id' );
					/* translators: %d: transaction ID */
					$row_label  = sprintf( __( 'Transaction #%d', 'lifterlms' ), $row_id );
					$status     = $row->get( 'status' );
					$status_obj = get_post_status_object( $status );
					$status_lbl = $status_obj ? $status_obj->label : $status;
					$amount     = $row->get_price( 'amount' );
					$date       = $row->get_date( 'date', 'F j, Y' );
				} else {
					$order  = $row;
					$row_id = $row->get( 'id' );
					/* translators: %d: order ID */
					$row_label  = sprintf( __( 'Order #%d', 'lifterlms' ), $row_id );
					$status     = $row->get( 'status' );
					$status_lbl = $row->get_status_name();
					$amount     = $row->get_price( 'total' );
					$date       = $row->get_date( 'date', 'F j, Y' );
				}

				if ( ! $order instanceof LLMS_Order ) {
					continue;
				}
				?>
				<tr class="llms-order-item <?php echo esc_attr( $status ); ?>">
					<td data-label="<?php esc_attr_e( 'Transaction', 'lifterlms' ); ?>: ">
						<?php echo esc_html( $row_label ); ?>
						<span class="llms-status <?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_lbl ); ?></span>
						<?php if ( $row instanceof LLMS_Transaction ) : ?>
						<br><small><a href="<?php echo esc_url( $order->get_view_link() ); ?>">
							<?php
							/* translators: %d: order ID */
							printf( esc_html__( 'Order #%d', 'lifterlms' ), (int) $order->get( 'id' ) );
							?>
						</a></small>
						<?php endif; ?>
					</td>
					<td data-label="<?php esc_attr_e( 'Date', 'lifterlms' ); ?>: "><?php echo esc_html( $date ); ?></td>
					<td data-label="<?php esc_attr_e( 'Product', 'lifterlms' ); ?>: "><?php echo esc_html( $order->get( 'product_title' ) ); ?></td>
					<td data-label="<?php esc_attr_e( 'Amount', 'lifterlms' ); ?>: "><?php echo wp_kses( $amount, LLMS_ALLOWED_HTML_PRICES ); ?></td>
					<td>
						<a class="llms-button-primary small" href="<?php echo esc_url( $order->get_view_link() ); ?>"><?php esc_html_e( 'View', 'lifterlms' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $transactions['pages'] > 1 ) : ?>
			<footer class="llms-sd-pagination llms-my-transactions-pagination">
				<?php if ( $transactions['page'] > 1 ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'txlpage' => $transactions['page'] - 1 ) ) ); ?>"><?php esc_html_e( 'Back', 'lifterlms' ); ?></a>
				<?php endif; ?>

				<?php if ( $transactions['page'] < $transactions['pages'] ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'txlpage' => $transactions['page'] + 1 ) ) ); ?>"><?php esc_html_e( 'Next', 'lifterlms' ); ?></a>
				<?php endif; ?>
			</footer>
		<?php endif; ?>

	<?php endif; ?>
</div>
