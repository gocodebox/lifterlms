<?php
/**
 * View an Order
 * @since    3.10.0
 * @version  3.10.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! $transactions || ! $transactions['transactions'] ) {
	return;
}
?>

<table class="orders-table transactions" id="llms-txns">
	<thead>
		<tr>
			<th><?php _e( 'Transaction', 'lifterlms' ); ?></th>
			<th><?php _e( 'Date', 'lifterlms' ); ?></th>
			<th><?php _e( 'Amount', 'lifterlms' ); ?></th>
			<th><?php _e( 'Method', 'lifterlms' ); ?></th>
		<tr>
	</thead>
	<tbody>
	<?php foreach ( $transactions['transactions'] as $txn ) : ?>
		<tr>
			<th>
				#<?php echo $txn->get( 'id' ); ?>
				<span class="llms-status <?php echo $txn->get( 'status' ); ?>"><?php echo $txn->get_status_name(); ?></span>
			</th>
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
						<a class="llms-button-secondary small" href="<?php echo esc_url( add_query_arg( 'txnpage', $transactions['page'] - 1 ) ); ?>#llms-txns"><?php _e( 'Back', 'lifterlms' ); ?></a>
					<?php endif; ?>
					<?php if ( $transactions['page'] < $transactions['pages'] ) : ?>
						<a class="llms-button-secondary small" href="<?php echo esc_url( add_query_arg( 'txnpage', $transactions['page'] + 1 ) ); ?>#llms-txns"><?php _e( 'Next', 'lifterlms' ); ?></a>
					<?php endif; ?>
				</td>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>
