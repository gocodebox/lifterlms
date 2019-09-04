<?php
/**
 * Order update/submit box.
 *
 * @since 1.0.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Meta_Box_Order_Submit
 *
 * @since 1.0.0
 * @since [version] Verify nonces and sanitize `$_POST` data.
 */
class LLMS_Meta_Box_Order_Submit extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-order-submit';
		$this->title    = __( 'Order Information', 'lifterlms' );
		$this->screens  = array(
			'llms_order',
		);
		$this->context  = 'side';
		$this->priority = 'high';

	}

	/**
	 * Retrieve json to be used by the llms-editable date fields
	 *
	 * @param    int $time  timestamp
	 * @return   string
	 * @since    3.10.0
	 * @version  3.19.0
	 */
	public function get_editable_date_json( $time ) {

		return json_encode(
			array(
				'date'   => date_i18n( 'Y-m-d', $time ),
				'hour'   => date_i18n( 'H', $time ),
				'minute' => date_i18n( 'i', $time ),
			)
		);

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 *
	 * @return string|null
	 */
	public function output() {

		$order = new LLMS_Order( $this->post );

		if ( $order->is_legacy() ) {
			return _e( 'The status of a Legacy order cannot be changed.', 'lifterlms' );
		}

		include LLMS_PLUGIN_DIR . 'includes/admin/views/metaboxes/view-order-submit.php';

		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

	}

	/**
	 * Save action, update order status
	 *
	 * @since 3.0.0
	 * @since 3.19.0 Unknown.
	 * @since [version] Verify nonces and sanitize `$_POST` data.
	 *
	 * @param int $post_id  WP Post ID of the Order
	 * @return null
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		$order = llms_get_post( $post_id );

		if ( isset( $_POST['_llms_order_status'] ) ) {

			$new_status = llms_filter_input( INPUT_POST, '_llms_order_status', FILTER_SANITIZE_STRING );
			$old_status = $order->get( 'status' );

			if ( $old_status !== $new_status ) {

				// update the status
				$order->set( 'status', $new_status );

			}
		}

		// order is important -- if both trial and next payment are updated
		// they should be saved in that order since next payment date
		// is automatically recalculated by trial end date update
		$editable_dates = array(
			'_llms_date_trial_end',
			'_llms_date_next_payment',
			'_llms_date_access_expires',
		);

		foreach ( $editable_dates as $id => $key ) {

			if ( isset( $_POST[ $key ] ) ) {

				// the array of date, hour, minute that was submitted
				$dates = llms_filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );

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
