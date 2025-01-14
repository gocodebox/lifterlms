<?php
/**
 * Meta box for Student Enrollment Information via the Order interface
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 3.0.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Meta_Box_Order_Enrollment class
 *
 * @since 3.0.0
 * @since 3.33.0 Added the logic to handle the Enrollment 'deleted' status on save.
 * @since 4.2.0 In ` save_delete_enrollment()` removed order cancellation instruction, moved elsewhere as reaction to the enrollment deletion.
 *              @see `LLMS_Controller_Orders->on_deleted_enrollment()` in `includes\controllers\class.llms.controller.orders.php`.
 *              Also, add order note about the enrollment deletion only if it actually occurred.
 */
class LLMS_Meta_Box_Order_Enrollment extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 *
	 * @since 3.0.0
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
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_fields() {
		return array();
	}

	/**
	 * Function to field WP::output() method call
	 *
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
			esc_html_e( 'Cannot manage enrollment status for anonymized orders.', 'lifterlms' );
			return;
		}

		$student_id = $order->get( 'user_id' );
		if ( ! $student_id ) {// No user id, nothing to show.
			return;
		}

		$student = llms_get_student( $student_id );

		// No student, show a message.
		if ( empty( $student ) ) {
			esc_html_e( "The student who placed the order doesn't exist anymore.", 'lifterlms' );
			return;
		}

		$current_status = $student->get_enrollment_status( $order->get( 'product_id' ) );

		$select  = '<select name="llms_student_new_enrollment_status">';
		$select .= '<option value="">-- ' . esc_html__( 'Select', 'lifterlms' ) . ' --</option>';

		foreach ( llms_get_enrollment_statuses() as $val => $name ) {
			$select .= '<option value="' . esc_attr( $val ) . '"' . selected( $val, strtolower( $current_status ), false ) . '>' . esc_html( $name ) . '</option>';
		}
		$select .= '</select>';

		echo '<p>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
		printf( esc_html_x( 'Status: %s', 'enrollment status', 'lifterlms' ), $select );
		echo '</p>';

		echo '<p>';
		printf( esc_html_x( 'Enrolled: %s', 'enrollment trigger', 'lifterlms' ), esc_html( $student->get_enrollment_date( $order->get( 'product_id' ), 'enrolled', 'm/d/Y h:i:s A' ) ) );
		echo '</p>';
		echo '<p>';
		printf( esc_html_x( 'Updated: %s', 'enrollment trigger', 'lifterlms' ), esc_html( $student->get_enrollment_date( $order->get( 'product_id' ), 'updated', 'm/d/Y h:i:s A' ) ) );
		echo '</p>';
		echo '<p>';
		printf( esc_html_x( 'Trigger: %s', 'enrollment trigger', 'lifterlms' ), esc_html( $student->get_enrollment_trigger( $order->get( 'product_id' ) ) ) );
		echo '</p>';

		echo '<input name="llms_student_old_enrollment_status" type="hidden" value="' . esc_attr( $current_status ) . '">';

		echo '<input name="llms_update_enrollment_status" type="submit" class="llms-button-secondary small" value="' . esc_html__( 'Update Status', 'lifterlms' ) . '"> ';
		if ( $current_status && 'enrolled' !== $current_status ) {
			echo '<input name="llms_delete_enrollment_status" type="submit" class="llms-button-danger small" value="' . esc_html__( 'Delete Enrollment', 'lifterlms' ) . '">';
		}
	}

	/**
	 * Save method
	 *
	 * @since 3.0.0
	 * @since 3.33.0 Added the logic to handle the Enrollment 'deleted' status.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param int $post_id Post ID of the Order.
	 * @return void
	 */
	public function save( $post_id ) {

		$update = llms_filter_input( INPUT_POST, 'llms_update_enrollment_status' );
		if ( ! empty( $update ) ) {
			$this->save_update_enrollment( $post_id );
		}

		$delete = llms_filter_input( INPUT_POST, 'llms_delete_enrollment_status' );
		if ( ! empty( $delete ) ) {
			$this->save_delete_enrollment( $post_id );
		}
	}

	/**
	 * Delete enrollment data based on posted values.
	 *
	 * @since 3.33.0
	 * @since 4.2.0 Removed order cancellation instruction, moved elsewhere as reaction to the enrollment deletion.
	 *              @see `LLMS_Controller_Orders->on_deleted_enrollment()` in `includes\controllers\class.llms.controller.orders.php`.
	 *              Also, add order note about the enrollment deletion only if it actually occurred.
	 *
	 * @param int $post_id WP_Post ID of the order.
	 * @return void
	 */
	private function save_delete_enrollment( $post_id ) {

		$order = llms_get_post( $post_id );

		/**
		 * Completely remove any enrollment records related to the given product & order.
		 * Also note that, by design, at this stage the student has already been unenrolled,
		 * as the delete button is only available when the enrollment status is NOT 'enrolled'.
		 */
		if ( llms_delete_student_enrollment( $order->get( 'user_id' ), $order->get( 'product_id' ), 'order_' . $order->get( 'id' ) ) ) {

			$order->add_note( __( 'Student enrollment records have been deleted.', 'lifterlms' ), true );

		}
	}

	/**
	 * Update enrollment data based on posted values.
	 *
	 * @since 3.33.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @param int $post_id WP_Post ID of the order.
	 * @return void
	 */
	private function save_update_enrollment( $post_id ) {

		$old_status = llms_filter_input_sanitize_string( INPUT_POST, 'llms_student_old_enrollment_status' );
		$new_status = llms_filter_input_sanitize_string( INPUT_POST, 'llms_student_new_enrollment_status' );

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
