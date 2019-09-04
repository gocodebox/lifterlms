<?php
/**
 * Metabox for Student Enrollment Information via the Order interface
 *
 * @since 3.0.0
 * @version 3.33.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Metabox for Student Enrollment Information via the Order interface
 *
 * @since 3.0.0
 * @since 3.33.0 Added the logic to handle the Enrollment 'deleted' status on save.
 */
class LLMS_Meta_Box_Order_Enrollment extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @since  3.0.0
	 *
	 * @return void
	 */
	public function configure() {

		$this->id       = 'lifterlms-order-enrollment-status';
		$this->title    = __( 'Student Enrollment', 'lifterlms' );
		$this->screens  = array(
			'llms_order',
		);
		$this->context  = 'side';
		$this->priority = 'default';

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
	 * Function to field WP::output() method call.
	 * Passes output instruction to parent.
	 *
	 * @since 3.0.0
	 * @since 3.33.0 Added 'Delete Enrollment' button.
	 *
	 * @return null
	 */
	public function output() {

		$order = llms_get_post( $this->post );

		if ( llms_parse_bool( $order->get( 'anonymized' ) ) ) {
			_e( 'Cannot manage enrollment status for anonymized orders.', 'lifterlms' );
			return '';
		}

		if ( $order->get( 'user_id' ) ) {
			$student        = llms_get_student( $order->get( 'user_id' ) );
			$current_status = $student->get_enrollment_status( $order->get( 'product_id' ) );
		} else {
			$current_status = '';
		}

		$select  = '<select name="llms_student_new_enrollment_status">';
		$select .= '<option value="">-- ' . esc_html__( 'Select', 'lifterlms' ) . ' --</option>';

		foreach ( llms_get_enrollment_statuses() as $val => $name ) {
			$select .= '<option value="' . $val . '"' . selected( $val, strtolower( $current_status ), false ) . '>' . $name . '</option>';
		}
		$select .= '</select>';

		echo '<p>';
		printf( _x( 'Status: %s', 'enrollment status', 'lifterlms' ), $select );
		echo '</p>';

		if ( $student ) {
			echo '<p>';
			printf( _x( 'Enrolled: %s', 'enrollment trigger', 'lifterlms' ), $student->get_enrollment_date( $order->get( 'product_id' ), 'enrolled', 'm/d/Y h:i:s A' ) );
			echo '</p>';
			echo '<p>';
			printf( _x( 'Updated: %s', 'enrollment trigger', 'lifterlms' ), $student->get_enrollment_date( $order->get( 'product_id' ), 'updated', 'm/d/Y h:i:s A' ) );
			echo '</p>';
			echo '<p>';
			printf( _x( 'Trigger: %s', 'enrollment trigger', 'lifterlms' ), $student->get_enrollment_trigger( $order->get( 'product_id' ) ) );
			echo '</p>';
		}

		echo '<input name="llms_student_old_enrollment_status" type="hidden" value="' . $current_status . '">';

		echo '<input name="llms_update_enrollment_status" type="submit" class="llms-button-secondary small" value="' . __( 'Update Status', 'lifterlms' ) . '"> ';
		if ( $current_status && 'enrolled' !== $current_status ) {
			echo '<input name="llms_delete_enrollment_status" type="submit" class="llms-button-danger small" value="' . __( 'Delete Enrollment', 'lifterlms' ) . '">';
		}

	}

	/**
	 * Save method.
	 *
	 * @since 3.0.0
	 * @since 3.33.0 Added the logic to handle the Enrollment 'deleted' status.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return void
	 */
	public function save( $post_id ) {

		$update = llms_filter_input( INPUT_POST, 'llms_update_enrollment_status', FILTER_SANITIZE_STRING );
		if ( ! empty( $update ) ) {
			$this->save_update_enrollment( $post_id );
		}

		$delete = llms_filter_input( INPUT_POST, 'llms_delete_enrollment_status', FILTER_SANITIZE_STRING );
		if ( ! empty( $delete ) ) {
			$this->save_delete_enrollment( $post_id );
		}

	}

	/**
	 * Delete enrollment data based on posted values.
	 *
	 * @since 3.33.0
	 *
	 * @param int $post_id WP_Post ID of the order.
	 * @return void
	 */
	private function save_delete_enrollment( $post_id ) {

		$order = llms_get_post( $post_id );

		// Switch the order status to Cancelled: it will also unenroll the student setting the enrollment status to 'cancelled' as well
		// @see `LLMS_Controller_Orders->error_order()`
		$order->set_status( 'cancelled' );

		// Completely remove any enrollment records related to the given product & order.
		llms_delete_student_enrollment( $order->get( 'user_id' ), $order->get( 'product_id' ), 'order_' . $order->get( 'id' ) );

		$order->add_note( __( 'Student enrollment records have been deleted.', 'lifterlms' ), true );

	}

	/**
	 * Update enrollment data based on posted values.
	 *
	 * @since 3.33.0
	 *
	 * @param int $post_id WP_Post ID of the order.
	 * @return void
	 */
	private function save_update_enrollment( $post_id ) {

		$old_status = llms_filter_input( INPUT_POST, 'llms_student_old_enrollment_status', FILTER_SANITIZE_STRING );
		$new_status = llms_filter_input( INPUT_POST, 'llms_student_new_enrollment_status', FILTER_SANITIZE_STRING );

		if ( ! $new_status || $old_status === $new_status ) {
			return;
		}

		$order = llms_get_post( $post_id );

		if ( 'enrolled' === $new_status ) {
			llms_enroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), 'order_' . $order->get( 'id' ) );
		} else {
			llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), $new_status, 'any' );
		}

		$new_status_name = llms_get_enrollment_status_name( $new_status );

		if ( ! $old_status ) {

			$note = sprintf( __( 'Student enrollment status changed to %s.', 'lifterlms' ), $new_status_name );

		} else {

			// Translators: %1$s = old enrollment status; %2$s = new enrollment status.
			$note = sprintf( __( 'Student enrollment status changed from %1$s to %2$s', 'lifterlms' ), llms_get_enrollment_status_name( $old_status ), $new_status_name );

		}

		$order->add_note( $note, true );

	}

}
