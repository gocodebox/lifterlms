<?php
/**
 * Tests for the LLMS_Controller_Account class
 *
 * @group controllers
 * @group controller_account
 *
 * @since 3.19.0
 * @since 3.34.0 Use `LLMS_Unit_Test_Exception_Exit` from tests lib.
 * @since [version] Create forms before executing tests account update tests.
 *               Split account update assertions into multiple tests.
 */
class LLMS_Test_Controller_Account extends LLMS_UnitTestCase {

	// consider dates equal within 60 seconds
	private $date_delta = 60;

	/**
	 * Test order completion actions
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_cancel_subscription() {

		// form not submitted
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );

		// form submitted but missing required fields
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// form submitted but invalid order id or the order id is invalid
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
			'order_id' => 123,
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();

		// create a real order
		$order = $this->get_mock_order();

		// form submitted but invalid order id or the order doesn't belong to the current user
		$this->setup_post( array(
			'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
			'order_id' => $order->get( 'id' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_subscription_cancelled_by_student' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		llms_clear_notices();
		wp_set_current_user( $order->get( 'user_id' ) );

		foreach ( array_keys( llms_get_order_statuses( 'recurring' ) ) as $status ) {

			// active order moves to pending cancel
			$order->set_status( $status );

			$this->setup_post( array(
				'_cancel_sub_nonce' => wp_create_nonce( 'llms_cancel_subscription' ),
				'order_id' => $order->get( 'id' ),
			) );
			do_action( 'init' );

			$expected = 'llms-active' === $status ? 'llms-pending-cancel' : 'llms-cancelled';
			$this->assertEquals( $expected, get_post_status( $order->get( 'id' ) ) );

		}

	}

	/**
	 * Test account update form submission handler when form is not submitted
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_not_submitted() {

		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );

	}

	/**
	 * Test account update form submission handler when user is not logged in
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_no_user() {

		// form submitted but user isn't logged in
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler when missing required fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_missing_fields() {

		// create a user
		$uid = $this->factory->user->create();
		// sign the user in
		wp_set_current_user( $uid );

		// form submitted but missing fields
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
		$this->assertTrue( ( llms_notice_count( 'error' ) >= 1 ) );
		$this->assertEquals( 0, did_action( 'lifterlms_user_updated' ) );
		llms_clear_notices();

	}

	/**
	 * Test account update form submission handler
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_success() {

		LLMS_Install::create_pages();
		LLMS_Forms::instance()->install();

		// I can't figure out why the action in the constructor isn't added when this test is run.
		LLMS_Unit_Test_Util::call_method( LLMS_Form_Handler::instance(), '__construct' );

		// create a user
		$uid = $this->factory->user->create();
		// sign the user in
		wp_set_current_user( $uid );

		// update something
		$this->setup_post( array(
			'_llms_update_person_nonce' => wp_create_nonce( 'llms_update_person' ),
			'email_address' => 'help+23568@lifterlms.com',
			'email_address_confirm' => 'help+23568@lifterlms.com',
			'first_name' => 'Marshall',
			'last_name' => 'Pate',
			'llms_billing_address_1' => 'Voluptatem',
			'llms_billing_address_2' => '#12345',
			'llms_billing_city' => 'Harum est dolorum sed vel perspiciatis consequatur dignissimos possimus delectus quos optio omnis error quas rem dicta et consectetur odio',
			'llms_billing_state' => 'Esse ea est dolore sed sunt ipsum a ut nemo dolorem aut aliquam cillum asperiores minim culpa',
			'llms_billing_zip' => '72995',
			'llms_billing_country' => 'US',
		) );

		// exceptions thrown in testing env instead of exit()
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );

		// run these assertions within actions because the exit() at the end of the redirect will halt program execution
		// and then we'll never get to these assertions!
		add_action( 'llms_before_user_account_update_submit', function() {
			$this->assertEquals( 1, did_action( 'llms_before_user_account_update_submit' ) );
			$this->assertEquals( 0, llms_notice_count( 'error' ) );
		} );
		add_action( 'lifterlms_user_updated', function() {
			$this->assertEquals( 1, did_action( 'lifterlms_user_updated' ) );
		} );

		do_action( 'init' );

	}

}
