<?php
defined( 'ABSPATH' ) || exit;

/**
 * Metaboxes for Student Enrollment Information
 * Associated with a specific order
 *
 * @since    3.0.0
 * @version  3.18.0
 */
class LLMS_Meta_Box_Order_Enrollment extends LLMS_Admin_Metabox {

	/**
	 * Configure the metabox settings
	 * @return void
	 * @since  3.0.0
	 * @version 3.0.0
	 */
	public function configure() {

		$this->id = 'lifterlms-order-enrollment-status';
		$this->title = __( 'Student Enrollment', 'lifterlms' );
		$this->screens = array(
			'llms_order',
		);
		$this->context = 'side';
		$this->priority = 'default';

	}

	/**
	 * Not used because our metabox doesn't use the standard fields api
	 * @return array
	 * @since  3.0.0
	 */
	public function get_fields() {}

	/**
	 * Function to field WP::output() method call
	 * Passes output instruction to parent
	 * @param    object  $post  WP global post object
	 * @return   void
	 * @since    3.0.0
	 * @version  3.18.0
	 */
	public function output() {

		$order = llms_get_post( $this->post );

		if ( llms_parse_bool( $order->get( 'anonymized' ) ) ) {
			_e( 'Cannot manage enrollment status for anonymized orders.', 'lifterlms' );
			return '';
		}

		if ( $order->get( 'user_id' ) ) {
			$student = llms_get_student( $order->get( 'user_id' ) );
			$current_status = $student->get_enrollment_status( $order->get( 'product_id' ) );
		} else {
			$current_status = '';
		}

		$select = '<select name="llms_student_new_enrollment_status">';
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

		echo '<input name="llms_update_enrollment_status" type="submit" class="button" value="' . __( 'Update Enrollment Status', 'lifterlms' ) . '">';

	}

	/**
	 * Save method
	 * Does nothing because there's no editable data in this metabox
	 * @param    int     $post_id  Post ID of the Order
	 * @return   void
	 * @since    3.0.0
	 * @version  3.18.0
	 */
	public function save( $post_id ) {
		if ( isset( $_POST['llms_update_enrollment_status'] ) && isset( $_POST['llms_student_old_enrollment_status'] ) && isset( $_POST['llms_student_new_enrollment_status'] ) ) {

			$old_status = $_POST['llms_student_old_enrollment_status'];
			$new_status = $_POST['llms_student_new_enrollment_status'];

			if ( $new_status && $old_status !== $new_status ) {

				$order = new LLMS_Order( $post_id );

				$order->add_note( sprintf( __( 'Student enrollment status changed from %1$s to %2$s', 'lifterlms' ), llms_get_enrollment_status_name( $old_status ), llms_get_enrollment_status_name( $new_status ) ), true );

				if ( 'enrolled' === $new_status ) {

					llms_enroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), 'order_' . $order->get( 'id' ) );

				} else {

					llms_unenroll_student( $order->get( 'user_id' ), $order->get( 'product_id' ), $new_status, 'any' );

				}
			}
		}
		return;
	}

}
