<?php
/**
 * Test Coupon-related methods in the LLMS_AJAX_Handler class.
 *
 * @package LifterLMS/Tests/AJAX
 *
 * @group ajax_coupons
 * @group ajax
 * @group coupons
 *
 * @since 3.39.0
 */
class LLMS_Test_AJAX_Handler_Coupons extends LLMS_UnitTestCase {

	/**
	 * Test remove_coupon_code()
	 *
	 * @since 3.39.0
	 *
	 * @return void
	 */
	public function test_remove_coupon_code() {

		llms()->session->set( 'llms_coupon', 'this-will-be-cleared' );

		$res = LLMS_AJAX_Handler::remove_coupon_code( array(
			'plan_id' => $this->get_mock_plan(),
		) );

		// HTML returned.
		$this->assertEquals( array( 'coupon_html', 'gateways_html', 'summary_html' ), array_keys( $res ) );

		$this->assertFalse( llms()->session->get( 'llms_coupon' ) );

	}

	/**
	 * Test validate_coupon_code(): no coupon data supplied
	 *
	 * @since 3.39.0
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_none_supplied() {

		$request = array();
		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertWPErrorMessageEquals( 'Please enter a coupon code.', $res );

	}

	/**
	 * Test validate_coupon_code(): no access plan supplied
	 *
	 * @since 3.39.0
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_no_plan() {

		$request = array(
			'code' => 123,
		);
		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertWPErrorMessageEquals( 'Please enter a plan ID.', $res );

	}

	/**
	 * Test validate_coupon_code(): coupon not found
	 *
	 * @since 3.39.0
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_not_found() {

		$request = array(
			'code' => 123,
			'plan_id' => 456,
		);
		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertWPErrorMessageEquals( 'Coupon code "123" not found.', $res );

	}

	/**
	 * Test validate_coupon_code(): coupon invalid for the given plan
	 *
	 * @since 3.39.0
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_invalid() {

		$coupon = new LLMS_Coupon( 'new', 'couponname' );
		$coupon->set( 'status', 'publish' );
		$coupon->set( 'coupon_courses', array( $this->factory->post->create() ) );

		$request = array(
			'code' => 'couponname',
			'plan_id' => $this->get_mock_plan(),
		);
		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertStringContains( 'This coupon cannot be used to purchase', $res->get_error_message() );

	}

	/**
	 * Test validate_coupon_code(): coupon code is valid
	 *
	 * @since 3.39.0
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_valid() {

		$coupon = new LLMS_Coupon( 'new', 'couponname' );
		$coupon->set( 'status', 'publish' );

		$request = array(
			'code' => 'couponname',
			'plan_id' => $this->get_mock_plan(),
		);

		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );

		// HTML returned.
		$this->assertEquals( array( 'code', 'coupon_html', 'gateways_html', 'summary_html' ), array_keys( $res ) );

		// Session data set.
		$expect = array(
			'plan_id'   => $request['plan_id'],
			'coupon_id' => $coupon->get( 'id' ),
		);
		$this->assertEquals( $expect, llms()->session->get( 'llms_coupon' ) );

	}

	/**
	 * Test validate_coupon_code(): prevent reflected xss
	 *
	 * Input is only a tag that will be stripped resulting in an empty response.
	 *
	 * @since 4.21.1
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_sanitization_empty_result() {

		$request = array(
			'code' => '<img src="#">',
		);
		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertWPErrorMessageEquals( 'Please enter a coupon code.', $res );

	}

	/**
	 * Test validate_coupon_code(): prevent reflected xss
	 *
	 * Input is text mixed with a a tag that will be stripped resulting in a not found error.
	 *
	 * @since 4.21.1
	 *
	 * @return void
	 */
	public function test_validate_coupon_code_sanitization_mixed_result() {

		$request = array(
			'code'    => 'FAKE_CODE<script>alert(1);</script>_WITH_TAGS',
			'plan_id' => 123,
		);
		$res = LLMS_AJAX_Handler::validate_coupon_code( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertWPErrorMessageEquals( 'Coupon code "FAKE_CODE_WITH_TAGS" not found.', $res );

	}


}
