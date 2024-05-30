<?php
/**
 * Tests for LifterLMS Order Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group metaboxes
 * @group LLMS_Meta_Box_Order_Enrollment
 * @group metaboxes_post_type
 *
 * @since 3.33.0
 * @since 4.18.0 Added some tests on the output method.
 */
class LLMS_Test_Meta_Box_Order_Enrollment extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test
	 *
	 * @since 3.33.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Order_Enrollment();

	}

	/**
	 * Test the LLMS_Meta_Box_Order_Enrollment save method
	 *
	 * @since 3.33.0
	 * @since 6.0.0 Replaced use of deprecated items.
	 *              - `LLMS_UnitTestCase::setup_post()` method with `LLMS_Unit_Test_Mock_Requests::mockPostRequest()`
	 *
	 * @return void
	 */
	public function test_save() {

		// Create a real order.
		$order = $this->get_mock_order();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$student_id = $order->get( 'user_id' );

		// Check enroll.
		$this->mockPostRequest( array(
			'llms_update_enrollment_status'      => 'Update',
			'llms_student_old_enrollment_status' => '',
			'llms_student_new_enrollment_status' => 'enrolled',
		) );

		$this->metabox->save( $order_id );
		$this->assertTrue( llms_is_user_enrolled( $student_id, $product_id ) );

		// Check unenroll.
		$this->mockPostRequest( array(
			'llms_update_enrollment_status'      => 'Update',
			'llms_student_old_enrollment_status' => 'enrolled',
			'llms_student_new_enrollment_status' => 'expired',
		) );

		$this->metabox->save( $order_id );
		$this->assertFalse( llms_is_user_enrolled( $student_id, $product_id ) );

		// Check enrollment deleted => no enrollment records + order status set to cancelled.
		$this->mockPostRequest( array(
			'llms_delete_enrollment_status'      => 'Delete',
			'llms_student_old_enrollment_status' => 'expired',
			'llms_student_new_enrollment_status' => 'deleted',
		) );

		$this->metabox->save( $order_id );
		$this->assertFalse( llms_is_user_enrolled( $student_id, $product_id ) );
		$this->assertEquals( array(), llms_get_user_postmeta( $student_id, $product_id ) );
		$this->assertSame( 'llms-cancelled', llms_get_post( $order_id )->get( 'status' ) );

	}


	/**
	 * Test the LLMS_Meta_Box_Order_Enrollment output method for anonymized orders
	 *
	 * @since 4.18.0
	 *
	 * @return void
	 */
	public function test_output_anonymized_order() {

		// Create a real order.
		$order = $this->get_mock_order();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$student_id = $order->get( 'user_id' );

		$order->set( 'anonymized', 'yes' );

		$this->assertOutputEquals( 'Cannot manage enrollment status for anonymized orders.', array( $this->metabox, 'output' ) );

	}

	/**
	 * Test the LLMS_Meta_Box_Order_Enrollment output method for orders with no user
	 *
	 * @since 4.18.0
	 *
	 * @return void
	 */
	public function test_output_order_with_no_user() {

		// Create a real order.
		$order = $this->get_mock_order();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );

		$order->set( 'user_id', '' );
		$this->assertOutputEmpty( array( $this->metabox, 'output' ) );

	}


	/**
	 * Test the LLMS_Meta_Box_Order_Enrollment output method for orders of deleted students
	 *
	 * @since 4.18.0
	 *
	 * @return void
	 */
	public function test_output_order_with_deleted_student() {

		// Create a real order.
		$order = $this->get_mock_order();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$student_id = $order->get( 'user_id' );

		wp_delete_user( $student_id );

		$this->assertOutputEquals( "The student who placed the order doesn&#039;t exist anymore.", array( $this->metabox, 'output' ) );

	}

	/**
	 * Test the LLMS_Meta_Box_Order_Enrollment output method for orders with student
	 *
	 * @since 4.18.0
	 *
	 * @return void
	 */
	public function test_output_order_with_student() {

		// Create a real order.
		$order = $this->get_mock_order();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$student_id = $order->get( 'user_id' );

		$output = $this->get_output( array( $this->metabox, 'output' ) );

		// There's a status selecter.
		$this->assertStringContainsString( '<select name="llms_student_new_enrollment_status">', $output );

		// The student is not enrolled yet.
		// No selected option, as well as the old (current) enrollment status.
		$this->assertStringNotContainsString( "selected='selected'>", $output );
		$this->assertStringContainsString( '<input name="llms_student_old_enrollment_status" type="hidden" value="">', $output );
		// The delete enrollment button doesn't exist.
		$this->assertStringNotContainsString( '<input name="llms_delete_enrollment_status" ', $output );

		// Enroll the student.
		llms_enroll_student( $student_id, $product_id );

		$output = $this->get_output( array( $this->metabox, 'output' ) );

		// The selected option is 'enrolled', as well as the old (current) enrollment status.
		$this->assertStringContainsString( "<option value=\"enrolled\" selected='selected'>", $output );
		$this->assertStringContainsString( '<input name="llms_student_old_enrollment_status" type="hidden" value="enrolled">', $output );
		// The delete enrollment button does not exist.
		$this->assertStringNotContainsString( '<input name="llms_delete_enrollment_status" ', $output );

		// Unenroll the student (cancelled status).
		llms_unenroll_student( $student_id, $product_id, 'cancelled', 'any' );

		$output = $this->get_output( array( $this->metabox, 'output' ) );

		// The selected option is 'cancelled', as well as the old (current) enrollment status.
		$this->assertStringContainsString( "<option value=\"cancelled\" selected='selected'>", $output );
		$this->assertStringContainsString( '<input name="llms_student_old_enrollment_status" type="hidden" value="cancelled">', $output );
		// The delete enrollment button exists.
		$this->assertStringContainsString( '<input name="llms_delete_enrollment_status" ', $output );

		// Unenroll the student (expired status).
		llms_enroll_student( $student_id, $product_id );
		llms_unenroll_student( $student_id, $product_id, 'expired', 'any' );

		$output = $this->get_output( array( $this->metabox, 'output' ) );

		// The selected option is 'expired', as well as the old (current) enrollment status.
		$this->assertStringContainsString( "<option value=\"expired\" selected='selected'>", $output );
		$this->assertStringContainsString( '<input name="llms_student_old_enrollment_status" type="hidden" value="expired">', $output );
		// The delete enrollment button exists.
		$this->assertStringContainsString( '<input name="llms_delete_enrollment_status" ', $output );

		// Delete enrollment.
		llms_delete_student_enrollment( $student_id, $product_id, 'any' );

		$output = $this->get_output( array( $this->metabox, 'output' ) );

		// No selected option, as well as the old (current) enrollment status.
		$this->assertStringNotContainsString( "selected='selected'>", $output );
		$this->assertStringContainsString( '<input name="llms_student_old_enrollment_status" type="hidden" value="">', $output );
		// The delete enrollment button doesn't exist.
		$this->assertStringNotContainsString( '<input name="llms_delete_enrollment_status" ', $output );
	}

}
