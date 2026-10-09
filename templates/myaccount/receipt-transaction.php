<?php
/**
 * Single Transaction Receipt Template (HTML, printable) for students.
 *
 * Used as a fallback when the LifterLMS PDFs add-on is not active.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 *
 * @var LLMS_Transaction $transaction The transaction object.
 * @var LLMS_Order       $order       The parent order object.
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>
	<?php
		/* translators: %d: Transaction ID */
		printf( esc_html__( 'Receipt - Transaction #%d', 'lifterlms' ), (int) $transaction->get( 'id' ) );
	?>
	</title>
	<style>
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 40px; color: #333; font-size: 14px; line-height: 1.6; }
		h1 { font-size: 24px; margin-bottom: 5px; }
		h2 { font-size: 18px; margin-top: 30px; border-bottom: 1px solid #ddd; padding-bottom: 8px; }
		table { width: 100%; border-collapse: collapse; margin-top: 10px; }
		th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #eee; }
		th { width: 200px; font-weight: 600; color: #555; }
		.receipt-header { display: flex; justify-content: space-between; align-items: flex-start; }
		.receipt-header .site-name { font-size: 12px; color: #666; }
		.status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
		.status.llms-txn-succeeded { background: #d4edda; color: #155724; }
		.status.llms-txn-failed { background: #f8d7da; color: #721c24; }
		.status.llms-txn-pending { background: #fff3cd; color: #856404; }
		.status.llms-txn-refunded { background: #d1ecf1; color: #0c5460; }
		@media print { body { margin: 20px; } .no-print { display: none; } }
	</style>
</head>
<body>
	<div class="receipt-header">
		<div>
			<h1>
			<?php
				/* translators: %d: Transaction ID */
				printf( esc_html__( 'Transaction Receipt #%d', 'lifterlms' ), (int) $transaction->get( 'id' ) );
			?>
			</h1>
			<p class="site-name"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		</div>
		<div class="no-print">
			<button onclick="window.print()"><?php esc_html_e( 'Print', 'lifterlms' ); ?></button>
		</div>
	</div>

	<h2><?php esc_html_e( 'Transaction Details', 'lifterlms' ); ?></h2>
	<table>
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Transaction ID', 'lifterlms' ); ?></th>
				<td>#<?php echo esc_html( $transaction->get( 'id' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Date', 'lifterlms' ); ?></th>
				<td><?php echo esc_html( $transaction->get_date( 'date', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Amount', 'lifterlms' ); ?></th>
				<td><?php echo wp_kses( llms_price( $transaction->get( 'amount' ) ), LLMS_ALLOWED_HTML_PRICES ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Status', 'lifterlms' ); ?></th>
				<td>
					<?php
					$status     = $transaction->get( 'status' );
					$status_obj = get_post_status_object( $status );
					?>
					<span class="status <?php echo esc_attr( $status ); ?>">
						<?php echo esc_html( $status_obj ? $status_obj->label : $status ); ?>
					</span>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Payment Method', 'lifterlms' ); ?></th>
				<td><?php echo esc_html( $transaction->get( 'gateway_source_description' ) ); ?></td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Order Details', 'lifterlms' ); ?></h2>
	<table>
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Order', 'lifterlms' ); ?></th>
				<td>#<?php echo esc_html( $order->get( 'id' ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Product', 'lifterlms' ); ?></th>
				<td><?php echo esc_html( $order->get( 'product_title' ) ); ?></td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Customer Information', 'lifterlms' ); ?></h2>
	<table>
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Name', 'lifterlms' ); ?></th>
				<td><?php echo esc_html( $order->get_customer_name() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Email', 'lifterlms' ); ?></th>
				<td><?php echo esc_html( $order->get( 'billing_email' ) ); ?></td>
			</tr>
			<?php if ( $order->get( 'billing_address_1' ) ) : ?>
			<tr>
				<th><?php esc_html_e( 'Address', 'lifterlms' ); ?></th>
				<td>
					<?php echo esc_html( $order->get( 'billing_address_1' ) ); ?><br>
					<?php if ( $order->get( 'billing_address_2' ) ) : ?>
						<?php echo esc_html( $order->get( 'billing_address_2' ) ); ?><br>
					<?php endif; ?>
					<?php echo esc_html( $order->get( 'billing_city' ) ); ?>,
					<?php echo esc_html( $order->get( 'billing_state' ) ); ?>
					<?php echo esc_html( $order->get( 'billing_zip' ) ); ?><br>
					<?php echo esc_html( llms_get_country_name( $order->get( 'billing_country' ) ) ); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
</body>
</html>
