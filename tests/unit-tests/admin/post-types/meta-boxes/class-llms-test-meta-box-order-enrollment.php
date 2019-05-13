<?php
/**
 * Tests for LifterLMS Order Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group metabox
 * @group admin
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Order_Enrollment extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->metabox = new LLMS_Meta_Box_Order_Enrollment();

	}

	/**
	 * test the LLMS_Meta_Box_Order_Enrollment save method
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save() {

		// create a real order
		$order = $this->get_mock_order();

		$order_id   = $order->get( 'id' );
		$product_id = $order->get( 'product_id' );
		$student_id = $order->get( 'user_id' );

		// check enroll
		$this->setup_post( array(
			'llms_update_enrollment_status'      => '',
			'llms_student_old_enrollment_status' => '',
			'llms_student_new_enrollment_status' => 'enrolled',
		) );

		$this->metabox->save( $order_id );
		$this->assertTrue( llms_is_user_enrolled( $student_id, $product_id ) );

		// check unenroll
		$this->setup_post( array(
			'llms_update_enrollment_status'      => '',
			'llms_student_old_enrollment_status' => 'enrolled',
			'llms_student_new_enrollment_status' => 'expired',
		) );

		$this->metabox->save( $order_id );
		$this->assertFalse( llms_is_user_enrolled( $student_id, $product_id ) );

		// check enrollment deleted => no enrollment records + order status set to cancelled
		$this->setup_post( array(
			'llms_update_enrollment_status'      => '',
			'llms_student_old_enrollment_status' => 'expired',
			'llms_student_new_enrollment_status' => 'deleted',
		) );

		$this->metabox->save( $order_id );
		$this->assertFalse( llms_is_user_enrolled( $student_id, $product_id ) );
		$this->assertEquals( array(), llms_get_user_postmeta( $student_id, $product_id ) );
		$this->assertSame( 'llms-cancelled', llms_get_post( $order_id )->get( 'status' ) );

	}

}
