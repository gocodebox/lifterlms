<?php
/**
 * Transactions Table Metabox for Orders
 * @since    3.5.0
 * @version  3.26.1
 */

defined( 'ABSPATH' ) || exit;
if ( ! is_admin() ) { exit; }

// create a "step" attribute for price fields according to LLMS settings
$price_step = number_format( 0.01, get_lifterlms_decimals(), get_lifterlms_decimal_separator(), get_lifterlms_thousand_separator() );

?>
<table class="llms-table">
	<thead>
		<tr>
			<th><?php _e( 'ID', 'lifterlms' ); ?></th>
			<th><?php _e( 'Status', 'lifterlms' ); ?></th>
			<th><?php _e( 'Date', 'lifterlms' ); ?></th>
			<th><?php _e( 'Amount', 'lifterlms' ); ?></th>
			<th><?php _e( 'Refunded', 'lifterlms' ); ?></th>
			<th class="expandable closed"><?php _e( 'Type', 'lifterlms' ); ?></th>
			<th class="expandable closed"><?php _e( 'Gateway', 'lifterlms' ); ?></th>
			<th class="expandable closed"><?php _e( 'Source', 'lifterlms' ); ?></th>
			<th class="expandable closed"><?php _e( 'Transaction ID', 'lifterlms' ); ?></th>
			<th class="expandable"><?php _e( 'Actions', 'lifterlms' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( $transactions['transactions'] ) : ?>
			<?php foreach ( $transactions['transactions'] as $txn ) : ?>
				<?php $gateway = $txn->get_gateway(); ?>
				<?php $refund_amount = $txn->get_price( 'refund_amount', array(), 'float' ); ?>
				<tr class="<?php echo $txn->get( 'status' ); ?>" data-transaction-id="<?php echo $txn->get( 'id' ); ?>">
					<td><?php echo $txn->get( 'id' ); ?></td>
					<td><?php echo $txn->get_status_name(); ?></td>
					<td><?php echo $txn->get_date( 'date', 'm/d/Y h:ia' ); ?></td>
					<td>

						<?php if ( $refund_amount ) : ?>
							<del><?php echo $txn->get_price( 'amount' ); ?></del>
							<?php echo $txn->get_net_amount(); ?>
						<?php else : ?>
							<?php echo $txn->get_price( 'amount' ); ?>
						<?php endif; ?>

					</td>
					<td><?php echo llms_price( $refund_amount * -1 ); ?></td>
					<td class="expandable closed"><?php echo $txn->translate( 'payment_type' ); ?></td>
					<td class="expandable closed"><?php echo $gateway ? $gateway->get_admin_title() : $txn->get( 'payment_gateway' ); ?></td>
					<td class="expandable closed">
						<?php echo $txn->get( 'gateway_source_description' ); ?>
						<?php $source_id = $txn->get( 'gateway_source_id' );
						if ( $source_id ) : ?>
							<?php $source = $gateway ? $gateway->get_source_url( $source_id ) : false; ?>
							<?php if ( false === filter_var( $source, FILTER_VALIDATE_URL ) ) : ?>
								(<?php echo $source; ?>)
							<?php else : ?>
								(<a href="<?php echo $source; ?>" target="_blank"><?php echo $source_id; ?></a>)
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<td class="expandable closed">
						<?php $txn_id = $txn->get( 'gateway_transaction_id' );
						if ( $txn_id ) : ?>
							<?php $txn_url = $gateway ? $gateway->get_transaction_url( $txn_id, $txn->get( 'api_mode' ) ) : false; ?>
							<?php if ( false === filter_var( $txn_url, FILTER_VALIDATE_URL ) ) : ?>
								<?php echo $txn_id; ?>
							<?php else : ?>
								<a href="<?php echo $txn_url; ?>" target="_blank"><?php echo $txn_id; ?></a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<td class="expandable">
						<?php if ( $txn->can_be_refunded() ) : ?>
							<button class="button" data-gateway="<?php echo $gateway ? $gateway->get_admin_title() : ''; ?>" data-gateway-supports="<?php echo ( $gateway && $gateway->supports( 'refunds' ) ); ?>" data-refundable="<?php echo $txn->get_refundable_amount(); ?>" name="llms-refund-toggle" type="button"><?php _e( 'Refund', 'lifterlms' ); ?></button>
						<?php endif; ?>
						<button class="button" name="llms_resend_receipt" type="submit" value="<?php echo $txn->get( 'id' ); ?>"><?php _e( 'Resend Receipt', 'lifterlms' ); ?></button>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="10">

				<?php if ( ! empty( $prev_url ) ) : ?>
					<a class="button" href="<?php echo $prev_url; ?>"><?php printf( __( '%s Newer', 'lifterlms' ), '&laquo;' ); ?></a>
				<?php endif; ?>

				<?php if ( ! empty( $next_url ) ) : ?>
					<a class="button" href="<?php echo $next_url; ?>"><?php printf( __( 'Older %s', 'lifterlms' ), '&raquo;' ); ?></a>
				<?php endif; ?>

				<?php if ( ! empty( $all_url ) ) : ?>
					<a class="button" href="<?php echo $all_url; ?>"><?php printf( __( 'View all', 'lifterlms' ), '&raquo;' ); ?></a>
				<?php endif; ?>

				<button class="button" name="llms-manual-txn-toggle" type="button"><?php _e( 'Record a Manual Payment', 'lifterlms' ); ?></button>
				<button class="button" data-text="<?php _e( 'Show Less Info', 'lifterlms' ); ?>" name="llms-expand-table" type="button"><?php _e( 'Show More Info', 'lifterlms' ); ?></button>
			</th>
		</tr>
	</tfoot>
</table>

<table id="llms-txn-refund-model" style="display:none;">
	<tr class="llms-txn-refund-form"><td colspan="10">
	<div class="llms-metabox">

		<div class="llms-metabox-section">

			<div class="llms-metabox-field">
				<label><?php _e( 'Refund Amount:', 'lifterlms' ) ?></label>
				<input disabled="disabled" name="llms_refund_amount" min="0" step="<?php echo $price_step; ?>" type="number">
			</div>

			<div class="llms-metabox-field">
				<label><?php _e( 'Refund Note (optional):', 'lifterlms' ) ?></label>
				<input disabled="disabled" name="llms_refund_note" type="text">
			</div>

			<div class="llms-metabox-field">
				<button class="button button-primary tooltip" data-gateway="manual" name="llms_process_refund" title="<?php _e( 'The refund will be recorded and you will need to manually issue a refund', 'lifterlms' ); ?>" value="manual"><?php _e( 'Refund Manually', 'lifterlms' ); ?></button>
				<button class="button button-primary gateway-btn" data-gateway="0" name="llms_process_refund" style="display:none;" value="gateway"><?php printf( _x( 'Refund via %s', 'refund via payment gateway', 'lifterlms' ), '<span class="llms-gateway-title"></span>' ); ?></button>
			</div>

			<input disabled="disabled" type="hidden" name="llms_refund_txn_id">

			<div class="clear"></div>

		</div>

	</div>
	</td></tr>
</table>


<table id="llms-manual-txn-model" style="display:none;">
	<tr class="llms-manual-txn-form"><td colspan="10">
	<div class="llms-metabox">

		<div class="llms-metabox-section">

			<div class="llms-metabox-field">
				<label><?php _e( 'Payment Amount:', 'lifterlms' ) ?></label>
				<input disabled="disabled" name="llms_txn_amount" min="0" step="<?php echo $price_step; ?>" type="number">
			</div>

			<div class="llms-metabox-field">
				<label><?php _e( 'Payment Source (optional):', 'lifterlms' ) ?></label>
				<input disabled="disabled" name="llms_txn_source" type="text">
			</div>

			<div class="llms-metabox-field">
				<label><?php _e( 'Payment Transaction ID (optional):', 'lifterlms' ) ?></label>
				<input disabled="disabled" name="llms_txn_id" type="text">
			</div>

			<div class="llms-metabox-field">
				<label><?php _e( 'Payment Note (optional):', 'lifterlms' ) ?></label>
				<input disabled="disabled" name="llms_txn_note" type="text">
			</div>

			<div class="llms-metabox-field">
				<button class="button button-primary" name="llms_record_txn" value="llms_record_txn"><?php _e( 'Record Payment', 'lifterlms' ); ?></button>
			</div>

			<div class="clear"></div>

		</div>

	</div>
	</td></tr>
</table>
