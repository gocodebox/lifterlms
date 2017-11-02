<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Metaboxes for Orders
 *
 * @version  3.0.0
 */
class LLMS_Meta_Box_Order_Submit extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-order-submit';
		$this->title = __( 'Order Information', 'lifterlms' );
		$this->screens = array(
			'llms_order',
		);
		$this->context = 'side';
		$this->priority = 'high';

	}

	/**
	 * Retrieve json to be used by the llms-editable date fields
	 * @param    int     $time  timestamp
	 * @return   string
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	private function get_editable_date_json( $time ) {

		return json_encode( array(
			'date' => date_i18n( 'Y-m-d', $time ),
			'hour' => date_i18n( 'H', $time ),
			'minute' => date_i18n( 'i', $time ),
		) );

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 * @return array
	 *
	 * @since  3.0.0
	 */
	public function get_fields() {}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @param object $post WP global post object
	 * @return void
	 *
	 * @version  3.10.0
	 */
	public function output() {

		$order = new LLMS_Order( $this->post );
		$current_status = $order->get( 'status' );

		if ( $order->is_legacy() ) {
			return _e( 'The status of a Legacy order cannot be changed.', 'lifterlms' );
		}

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

				<div class="clear"></div>

				<div class="llms-metabox-field" style="text-align: right;">
					<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php _e( 'Update Order', 'lifterlms' ); ?>">
				</div>

			</div>

		</div>
		<?php
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

	}

	/**
	 * Save action, update order status
	 * @param    int     $post_id  WP Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.10.0
	 */
	public function save( $post_id ) {

		$order = llms_get_post( $post_id );

		if ( isset( $_POST['_llms_order_status'] ) ) {

			$new_status = $_POST['_llms_order_status'];
			$old_status = $order->get( 'status' );

			if ( $old_status !== $new_status ) {

				// update the status
				$order->set( 'status', $new_status );

			}
		}

		// order is important -- if both trial and next payment are updated
		// they should be saved in that order since next payment date
		// is automatically recalced by trial end date update
		$editable_dates = array(
			'_llms_date_trial_end',
			'_llms_date_next_payment',
		);

		foreach ( $editable_dates as $id => $key ) {

			if ( isset( $_POST[ $key ] ) ) {

				// the array of date, hour, minute that was submitted
				$dates = $_POST[ $key ];

				// format the array of data as a datetime string
				$new_date = $dates['date'] . ' ' . sprintf( '%02d', $dates['hour'] ) . ':' . sprintf( '%02d', $dates['minute'] );

				// get the existing saved date without seconds (in the same format as $new_date)
				$saved_date = date_i18n( 'Y-m-d H:i', strtotime( get_post_meta( $post_id, $key, true ) ) );

				// if the dates are not equal, update the date
				if ( $new_date !== $saved_date ) {
					$order->set_date( str_replace( '_llms_date_', '', $key ), $new_date . ':00' );
				}
			}
		}

	}

}
