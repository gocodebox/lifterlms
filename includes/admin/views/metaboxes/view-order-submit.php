<?php
/**
 * View for the LLMS_Meta_Box_Order_Submit metabox
 * @since     3.19.0
 * @version   3.19.0
 *
 * @property  obj  $this   LLMS_Meta_Box_Order_Submit instance
 * @property  obj  $order  LLMS_Order instance
 */
defined( 'ABSPATH' ) || exit;

$current_status = $order->get( 'status' );
$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$statuses = llms_get_order_statuses( $order->is_recurring() ? 'recurring' : 'single' );
?>

<div class="llms-metabox">

	<div class="llms-metabox-section d-all no-top-margin">

		<div class="llms-metabox-field">
			<label for="_llms_order_status"><?php _e( 'Update Order Status:', 'lifterlms' ) ?></label>
			<select id="_llms_order_status" name="_llms_order_status">
				<?php foreach ( $statuses as $key => $val ) : ?>
					<option value="<?php echo $key; ?>"<?php selected( $key, $current_status ); ?>><?php echo $val; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="llms-metabox-field">
			<label><?php _e( 'Order Date', 'lifterlms' ) ?>:</label>
			<?php echo $order->get_date( 'date', $date_format ); ?>
		</div>

		<?php if ( $order->is_recurring() ) : ?>

			<?php $next_time = $order->get_next_payment_due_date( 'U' ); ?>

			<?php if ( $order->has_trial() ) : ?>
				<div class="llms-metabox-field">
					<label><?php _e( 'Trial End Date', 'lifterlms' ) ?>:</label>
					<span
						id="llms-editable-trial-end-date"
						data-llms-editable="_llms_date_trial_end"
						data-llms-editable-date-format="yy-mm-dd"
						data-llms-editable-date-min="<?php echo $order->get_date( 'date', 'Y-m-d' ); ?>"
						data-llms-editable-type="datetime"
						data-llms-editable-value='<?php echo $this->get_editable_date_json( $order->get_trial_end_date( 'U' ) ); ?>'><?php echo $order->get_trial_end_date( $date_format ); ?></span>
					<?php if ( ! $order->has_trial_ended() ) : ?>
						<a class="llms-editable" data-fields="#llms-editable-trial-end-date" href="#"><span class="dashicons dashicons-edit"></span></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $order->is_recurring() && 'llms-pending-cancel' !== $current_status ) : ?>
			<div class="llms-metabox-field">
				<label><?php _e( 'Next Payment Date', 'lifterlms' ) ?>:</label>
				<?php if ( is_wp_error( $next_time ) ) : ?>
					<?php echo $next_time->get_error_message(); ?>
				<?php else : ?>
					<span
						id="llms-editable-next-payment-date"
						data-llms-editable="_llms_date_next_payment"
						data-llms-editable-date-format="yy-mm-dd"
						data-llms-editable-date-min="<?php echo current_time( 'Y-m-d' ); ?>"
						data-llms-editable-type="datetime"
						data-llms-editable-value='<?php echo $this->get_editable_date_json( $next_time ); ?>'><?php echo date_i18n( $date_format, $next_time ); ?></span>
					<a class="llms-editable" data-fields="#llms-editable-next-payment-date" href="#"><span class="dashicons dashicons-edit"></span></a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( llms_is_user_enrolled( $order->get( 'user_id' ), $order->get( 'product_id' ) ) ) : ?>

			<?php $expire_time = $order->get_access_expiration_date( 'U' ); ?>

			<div class="llms-metabox-field">
				<label><?php _e( 'Access Expiration', 'lifterlms' ); ?>:</label>
				<?php if ( ! is_numeric( $expire_time ) ) : ?>
					<?php echo $expire_time; ?>
				<?php else : ?>
					<span
						id="llms-editable-access-expires-date"
						data-llms-editable="_llms_date_access_expires"
						data-llms-editable-date-format="yy-mm-dd"
						data-llms-editable-date-min="<?php echo current_time( 'Y-m-d' ); ?>"
						data-llms-editable-type="datetime"
						data-llms-editable-value='<?php echo $this->get_editable_date_json( $expire_time ); ?>'><?php echo date_i18n( $date_format, $expire_time ); ?></span>
					<a class="llms-editable" data-fields="#llms-editable-access-expires-date" href="#"><span class="dashicons dashicons-edit"></span></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="clear"></div>

		<div class="llms-metabox-field" style="text-align: right;">
			<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php _e( 'Update Order', 'lifterlms' ); ?>">
		</div>

	</div>

</div>
