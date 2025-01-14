<?php
/**
 * Single order transactions table.
 *
 * @package LifterLMS/Templates
 *
 * @since 3.10.0
 * @since 6.0.0 Logic to return empty when no transactions present has been moved to the template function.
 * @version 6.0.0
 *
 * @var array $transactions Result array from {@see LLMS_Order::get_transactions()}.
 */

defined( 'ABSPATH' ) || exit;
?>

<table class="orders-table transactions" id="llms-txns">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Transaction', 'lifterlms' ); ?></th>
			<th><?php esc_html_e( 'Date', 'lifterlms' ); ?></th>
			<th><?php esc_html_e( 'Amount', 'lifterlms' ); ?></th>
			<th><?php esc_html_e( 'Method', 'lifterlms' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $transactions['transactions'] as $txn ) : ?>
		<tr>
			<td>
				#<?php echo esc_html( $txn->get( 'id' ) ); ?>
				<span class="llms-status <?php echo esc_attr( $txn->get( 'status' ) ); ?>"><?php echo esc_html( $txn->get_status_name() ); ?></span>
			</td>
			<td><?php echo esc_html( $txn->get_date( 'date' ) ); ?></td>
			<td>
				<?php $refund_amount = $txn->get_price( 'refund_amount', array(), 'float' ); ?>
				<?php if ( $refund_amount ) : ?>
					<del><?php echo wp_kses( $txn->get_price( 'amount' ), LLMS_ALLOWED_HTML_PRICES ); ?></del>
					<?php echo wp_kses( $txn->get_net_amount(), LLMS_ALLOWED_HTML_PRICES ); ?>
				<?php else : ?>
					<?php echo wp_kses( $txn->get_price( 'amount' ), LLMS_ALLOWED_HTML_PRICES ); ?>
				<?php endif; ?>
			</td>
			<td><?php echo wp_kses_post( $txn->get( 'gateway_source_description' ) ); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
	<?php if ( $transactions['pages'] > 1 ) : ?>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php if ( $transactions['page'] > 1 ) : ?>
						<a class="llms-button-secondary small" href="<?php echo esc_url( add_query_arg( 'txnpage', $transactions['page'] - 1 ) ); ?>#llms-txns"><?php esc_html_e( 'Back', 'lifterlms' ); ?></a>
					<?php endif; ?>
					<?php if ( $transactions['page'] < $transactions['pages'] ) : ?>
						<a class="llms-button-secondary small" href="<?php echo esc_url( add_query_arg( 'txnpage', $transactions['page'] + 1 ) ); ?>#llms-txns"><?php esc_html_e( 'Next', 'lifterlms' ); ?></a>
					<?php endif; ?>
				</td>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>
