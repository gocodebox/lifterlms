<?php
/**
 * Tests for LifterLMS Course Model
 *
 * @package LifterLMS/Tests
 *
 * @group orders
 * @group LLMS_Order
 * @group LLMS_Post_Model
 *
 * @since 3.10.0
 * @since 3.32.0 Update to use latest action-scheduler functions.
 * @since 3.37.2 Add additional recurring payment tests.
 * @since 3.37.6 Adjusted date delta for recurring payment next date assertions.
 *               Added default test override for test_edit_date() test to prevent output
 *               of skipped test that doesn't apply to the order model.
 * @since 4.6.0 Add coverage for `get_next_scheduled_action_time()`, `unschedule_expiration()`, and `unschedule_recurring_payment()`.
 * @since 4.7.0 Update tests to handle new meta property `plan_ended`.
 */
class LLMS_Test_LLMS_Order extends LLMS_PostModelUnitTestCase {

	/**
	 * Consider dates equal for +/- 2 mins
	 * @var  integer
	 */
	private $date_delta = 120;

	/**
	 * Setup the test case.
	 *
	 * @since Unknown
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->create();
	}

	/**
	 * Add support for a payment gateway feature.
	 *
	 * @since Unknown
	 *
	 * @param string $feature Feature name
	 * @return void
	 */
	private function mock_gateway_support( $feature ) {

		global $llms_mock_gateway_feature;
		$llms_mock_gateway_feature = $feature;

		add_filter( 'llms_get_gateway_supported_features', function( $features ) {
			global $llms_mock_gateway_feature;
			$features[ $llms_mock_gateway_feature ] = true;
			return $features;
		} );

	}

	private function get_plan( $price = 25.99, $frequency = 1, $expiration = 'lifetime', $on_sale = false, $trial = false ) {

		return $this->get_mock_plan( $price, $frequency, $expiration, $on_sale, $trial );

	}

	private function get_order( $plan = null, $coupon = false ) {

		return $this->get_mock_order( $plan, $coupon );

	}

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var  string
	 */
	protected $class_name = 'LLMS_Order';

	/**
	 * Db post type of the model being tested
	 *
	 * @var  string
	 */
	protected $post_type = 'llms_order';

	/**
	 * Get properties, used by test_getters_setters
	 *
	 * This should match, exactly, the object's $properties array
	 *
	 * @since 3.10.0
	 *
	 * @return string[]
	 */
	protected function get_properties() {
		return array(

			'coupon_amount' => 'float',
			'coupon_amout_trial' => 'float',
			'coupon_value' => 'float',
			'coupon_value_trial' => 'float',
			'original_total' => 'float',
			'sale_price' => 'float',
			'sale_value' => 'float',
			'total' => 'float',
			'trial_original_total' => 'float',
			'trial_total' => 'float',

			'access_length' => 'absint',
			'billing_frequency' => 'absint',
			'billing_length' => 'absint',
			'coupon_id' => 'absint',
			'plan_id' => 'absint',
			'product_id' => 'absint',
			'trial_length' => 'absint',
			'user_id' => 'absint',

			'access_expiration' => 'text',
			'access_expires' => 'text',
			'access_period' => 'text',
			'billing_address_1' => 'text',
			'billing_address_2' => 'text',
			'billing_city' => 'text',
			'billing_country' => 'text',
			'billing_email' => 'text',
			'billing_first_name' => 'text',
			'billing_last_name' => 'text',
			'billing_state' => 'text',
			'billing_zip' => 'text',
			'billing_period' => 'text',
			'coupon_code' => 'text',
			'coupon_type' => 'text',
			'coupon_used' => 'text',
			'currency' => 'text',
			'on_sale' => 'text',
			'order_key' => 'text',
			'order_type' => 'text',
			'payment_gateway' => 'text',
			'plan_sku' => 'text',
			'plan_title' => 'text',
			'product_sku' => 'text',
			'product_type' => 'text',
			'title' => 'text',
			'gateway_api_mode' => 'text',
			'gateway_customer_id' => 'text',
			'trial_offer' => 'text',
			'trial_period' => 'text',
			'user_ip_address' => 'text',

		);
	}

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters.
	 *
	 * @since 3.10.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(

			'coupon_amount' => 1.00,
			'coupon_amout_trial' => 0.50,
			'coupon_value' => 1.00,
			'coupon_value_trial' => 1234234.00,
			'original_total' => 25.93,
			'sale_price' => 25.23,
			'sale_value' => 2325.00,
			'total' => 12325.00,
			'trial_original_total' => 25.00,
			'trial_total' => 123.43,

			'access_length' => 1,
			'billing_frequency' => 1,
			'billing_length' => 1,
			'coupon_id' => 1,
			'plan_id' => 1,
			'product_id' => 1,
			'trial_length' => 1,
			'user_id' => 1,

			'access_expiration' => 'text',
			'access_expires' => 'text',
			'access_period' => 'text',
			'billing_address_1' => 'text',
			'billing_address_2' => 'text',
			'billing_city' => 'text',
			'billing_country' => 'text',
			'billing_email' => 'text',
			'billing_first_name' => 'text',
			'billing_last_name' => 'text',
			'billing_state' => 'text',
			'billing_zip' => 'text',
			'billing_period' => 'text',
			'coupon_code' => 'text',
			'coupon_type' => 'text',
			'coupon_used' => 'text',
			'currency' => 'text',
			'on_sale' => 'text',
			'order_key' => 'text',
			'order_type' => 'single',
			'payment_gateway' => 'text',
			'plan_sku' => 'text',
			'plan_title' => 'text',
			'product_sku' => 'text',
			'product_type' => 'text',
			'title' => 'test title',
			'gateway_api_mode' => 'text',
			'gateway_customer_id' => 'text',
			'trial_offer' => 'text',
			'trial_period' => 'text',
			'user_ip_address' => 'text',

		);
	}

	/**
	 * Test the add_note() method
	 *
	 * @since 3.10.0
	 *
	 * @return void
	 */
	public function test_add_note() {

		// Don't create empty notes.
		$this->assertNull( $this->obj->add_note( '' ) );

		$note_text = 'This is an order note';
		$id = $this->obj->add_note( $note_text );

		// Should return the comment id.
		$this->assertTrue( is_numeric( $id ) );

		$note = get_comment( $id );

		// Should be a comment.
		$this->assertTrue( is_a( $note, 'WP_Comment' ) );

		// Comment content should be our original note.
		$this->assertEquals( $note->comment_content, $note_text );
		// Author should be the system (LifterLMS).
		$this->assertEquals( $note->comment_author, 'LifterLMS' );

		// Create a new note by a user.
		$id = $this->obj->add_note( $note_text, true );
		$note = get_comment( $id );
		$this->assertEquals( get_current_user_id(), $note->user_id );

		// 1 for original creation note, 2 for our test notes.
		$this->assertEquals( 3, did_action( 'llms_new_order_note_added' ) );

	}

	/**
	 * Test can_be_confirmed().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_can_be_confirmed() {

		$statuses = array(
			// Can be confirmed.
			'llms-pending'        => true,

			// Cannot be confirmed.
			'llms-completed'      => false,
			'llms-active'         => false,
			'llms-expired'        => false,
			'llms-on-hold'        => false,
			'llms-pending-cancel' => false,
			'llms-cancelled'      => false,
			'llms-refunded'       => false,
			'llms-failed'         => false,
		);

		foreach ( $statuses as $status => $expected ) {
			$this->obj->set_status( $status );
			$this->assertEquals( $expected, $this->obj->can_be_confirmed() );
		}

	}

	/**
	 * Test the can_be_retried() method.
	 *
	 * @since Unknown.
	 * @since 5.2.1 Add assertions for checking against single payment orders and
	 *        			when the recurring retry feature option is disabled.
	 *
	 * @return void
	 */
	public function test_can_be_retried() {

		$order = $this->get_order();

		// Pending order can't be retried.
		$this->assertFalse( $order->can_be_retried() );

		// Active can be retried.
		$order->set_status( 'llms-active' );

		// Gateway doesn't support retries.
		$this->assertFalse( $order->can_be_retried() );

		// Allow the gateway to support retries.
		$this->mock_gateway_support( 'recurring_retry' );

		// Can be retried now.
		$this->assertTrue( $order->can_be_retried() );

		// On hold can be retried.
		$order->set_status( 'llms-on-hold' );
		$this->assertTrue( $order->can_be_retried() );

		// Retry disabled.
		update_option( 'lifterlms_recurring_payment_retry', 'no' );
		$this->assertFalse( $order->can_be_retried() );
		update_option( 'lifterlms_recurring_payment_retry', 'yes' );

		// Single payment cannot be retried.
		$order->set( 'order_type', 'single' );
		$this->assertFalse( $order->can_be_retried() );

	}

	/**
	 * Test the can_resubscribe() method
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_can_resubscribe() {

		$statuses = array(
			'llms-completed' => false,
			'llms-active' => false,
			'llms-expired' => false,
			'llms-on-hold' => true,
			'llms-pending-cancel' => true,
			'llms-pending' => true,
			'llms-cancelled' => false,
			'llms-refunded' => false,
			'llms-failed' => false,
		);

		foreach ( $statuses as $status => $expect ) {

			$this->obj->set( 'order_type', 'single' );
			$this->obj->set_status( $status );
			$this->assertEquals( false, $this->obj->can_resubscribe() );

			$this->obj->set( 'order_type', 'recurring' );
			$this->obj->set_status( $status );
			$this->assertEquals( $expect, $this->obj->can_resubscribe() );
		}

	}

	/**
	 * Test creation of the model.
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_create_model() {

		$date = '2021-04-22 14:34:00';
		llms_tests_mock_current_time( $date );

		$this->create( '' );

		$id = $this->obj->get( 'id' );

		$test = llms_get_post( $id );

		$this->assertEquals( $id, $test->get( 'id' ) );
		$this->assertEquals( 'llms_order', $test->get( 'type' ) );
		$this->assertEquals( 'Order &ndash; Apr 22, 2021 @ 02:34 PM', $test->get( 'title' ) );

		$this->assertEquals( $date, $test->get( 'date' ) );
		$this->assertEquals( 'llms-pending', $test->get( 'status' ) );

		$this->assertTrue( post_password_required( $id ) );

	}


	/**
	 * Overrides test from the abstract
	 *
	 * Since this test isn't essential for this class we'll skip the test with a fake assertion
	 * in order to reduce the number of skipped tests warnings which are output.
	 *
	 * @since 3.37.6
	 *
	 * @return void
	 */
	public function test_edit_date() {
		$this->assertTrue( true );
	}

	/**
	 * Test the generate_order_key() method
	 *
	 * @since 3.10.0
	 *
	 * @return void
	 */
	public function test_generate_order_key() {

		$this->assertTrue( is_string( $this->obj->generate_order_key() ) );
		$this->assertEquals( 0, strpos( $this->obj->generate_order_key(), 'order-' ) );

	}

	/**
	 * Test the get_access_expiration_date() method
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 *
	 * @return void
	 */
	public function test_get_access_expiration_date() {

		// Lifetime responds with a string not a date.
		$this->obj->set( 'access_expiration', 'lifetime' );
		$this->assertEquals( 'Lifetime Access', $this->obj->get_access_expiration_date() );

		// Expires on a specific date.
		$this->obj->set( 'access_expiration', 'limited-date' );
		$this->obj->set( 'access_expires', '12/01/2020' ); // m/d/Y format (from datepicker).
		$this->assertEquals( '2020-12-01', $this->obj->get_access_expiration_date() );

		// Expires after a period of time.
		$this->obj->set( 'access_expiration', 'limited-period' );

		$tests = array(
			array(
				'start' => '05/25/2015',
				'length' => '1',
				'period' => 'week',
				'expect' => '06/01/2015',
			),
			array(
				'start' => '12/21/2017',
				'length' => '1',
				'period' => 'day',
				'expect' => '12/22/2017',
			),
			array(
				'start' => '02/05/2017',
				'length' => '1',
				'period' => 'year',
				'expect' => '02/05/2018',
			),
			array(
				'start' => '12/31/2017',
				'length' => '1',
				'period' => 'day',
				'expect' => '01/01/2018',
			),
			array(
				'start' => '05/01/2017',
				'length' => '2',
				'period' => 'month',
				'expect' => '07/01/2017',
			),
		);

		foreach ( $tests as $data ) {

			$this->obj->set( 'start_date', $data['start'] );
			$this->obj->set( 'access_length', $data['length'] );
			$this->obj->set( 'access_period', $data['period'] );
			$this->assertEquals( date_i18n( 'Y-m-d', strtotime( $data['expect'] ) ), $this->obj->get_access_expiration_date() );

		}

		// Recurring pending cancel has access until the next payment due date.
		$this->obj->set( 'order_type', 'recurring' );
		$this->obj->set( 'status', 'llms-pending-cancel' );
		$this->assertEquals( $this->obj->get_next_payment_due_date( 'U' ), $this->obj->get_access_expiration_date( 'U' ) );

	}

	/**
	 * Test get access status function
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 * @since 6.0.0 Replaced use of the deprecated `llms_mock_current_time()` function
	 *              with `llms_tests_mock_current_time()` from the `lifterlms-tests` project.
	 *
	 * @return void
	 */
	public function test_get_access_status() {

		$this->assertEquals( 'inactive', $this->obj->get_access_status() );

		$this->obj->set( 'order_type', 'single' );
		$this->obj->set( 'status', 'llms-active' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		$this->obj->set( 'status', 'llms-completed' );
		$this->obj->set( 'access_expiration', 'lifetime' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		// Past should still grant access.
		llms_tests_mock_current_time( '2010-05-05' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		// Future should still grant access.
		llms_tests_mock_current_time( '2525-05-05' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		// Check limited access by date.
		$this->obj->set( 'access_expiration', 'limited-date' );
		$tests = array(
			array(
				'now' => '2010-05-05',
				'expires' => '05/06/2010', // m/d/Y from datepicker.
				'expect' => 'active',
			),
			array(
				'now' => '2015-05-05',
				'expires' => '05/06/2010', // m/d/Y from datepicker.
				'expect' => 'expired',
			),
			array(
				'now' => '2010-05-05',
				'expires' => '05/05/2010', // m/d/Y from datepicker.
				'expect' => 'active',
			),
		array(
				'now' => '2010-05-06',
				'expires' => '05/05/2010', // m/d/Y from datepicker.
				'expect' => 'expired',
			),
		);

		foreach ( $tests as $data ) {
			llms_tests_mock_current_time( $data['now'] );
			$this->obj->set( 'access_expires', $data['expires'] );
			$this->assertEquals( $data['expect'], $this->obj->get_access_status() );
			if ( 'active' === $data['expect'] ) {
				$this->assertTrue( $this->obj->has_access() );
			} else {
				$this->assertFalse( $this->obj->has_access() );
			}
		}

		$this->obj->set( 'order_type', 'recurring' );
		$this->obj->set( 'status', 'llms-pending-cancel' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

	}

	/**
	 * Test the get_customer_name() method.
	 *
	 * @since Unknown
	 * @since 5.2.1 Add assertion for anonymized order.
	 *
	 * @return void
	 */
	public function test_get_customer_name() {
		$first = 'Jeffrey';
		$last = 'Lebowski';
		$this->obj->set( 'billing_first_name', $first );
		$this->obj->set( 'billing_last_name', $last );
		$this->assertEquals( $first . ' ' . $last,  $this->obj->get_customer_name() );

		$this->obj->set( 'anonymized', 'yes' );
		$this->assertEquals( 'Anonymous', $this->obj->get_customer_name() );

	}

	/**
	 * Test the get_gateway() method.
	 *
	 * @return void
	 */
	public function test_get_gateway() {

		// Gateway doesn't exist.
		$this->obj->set( 'payment_gateway', 'garbage' );
		$this->assertTrue( is_a( $this->obj->get_gateway(), 'WP_Error' ) );

		$manual = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
		$this->obj->set( 'payment_gateway', 'manual' );

		// Real gateway that's not enabled.
		update_option( $manual->get_option_name( 'enabled' ), 'no' );
		$this->assertTrue( is_a( $this->obj->get_gateway(), 'WP_Error' ) );

		// Enabled gateway responds with the gateway instance.
		update_option( $manual->get_option_name( 'enabled' ), 'yes' );
		$this->assertTrue( is_a( $this->obj->get_gateway(), 'LLMS_Payment_Gateway_Manual' ) );

	}

	/**
	 * Test get_initial_price() method
	 *
	 * @return void
	 */
	public function test_get_initial_price() {

		// No trial.
		$order = $this->get_order();
		$this->assertEquals( 25.99, $order->get_initial_price( array(), 'float' ) );

		// With trial.
		$trial_plan = $this->get_plan( 25.99, 1, 'lifetime', false, true );
		$order = $this->get_order( $trial_plan );
		$this->assertEquals( 1.00, $order->get_initial_price( array(), 'float' ) );

	}

	/**
	 * Test get_notes() method
	 *
	 * @return void
	 */
	public function test_get_notes() {

		$i = 1;
		while( $i <= 10 ) {

			$this->obj->add_note( sprintf( 'note %d', $i ) );
			$i++;

		}

		// Remove filter so we can test order notes.
		remove_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );

		$notes = $this->obj->get_notes( 1, 1 );

		$this->assertCount( 1, $notes );
		$this->assertTrue( is_a( $notes[0], 'WP_Comment' ) );

		$notes_p_1 = $this->obj->get_notes( 5, 1 );
		$notes_p_2 = $this->obj->get_notes( 5, 2 );
		$this->assertCount( 5, $notes_p_1 );
		$this->assertCount( 5, $notes_p_2 );
		$this->assertTrue( $notes_p_2 !== $notes_p_1 );

		add_filter( 'comments_clauses', array( 'LLMS_Comments', 'exclude_order_comments' ) );

	}

	/**
	 * Test get_product() method
	 *
	 * @return void
	 */
	public function test_get_product() {

		$course = new LLMS_Course( 'new', 'test' );
		$this->obj->set( 'product_id', $course->get( 'id' ) );
		$this->assertTrue( is_a( $this->obj->get_product(), 'LLMS_Course' ) );

	}

	// public function test_get_last_transaction() {}

	// public function test_get_last_transaction_date() {}

	/**
	 * Test get_next_payment_due_date() for a one-time payment
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_next_payment_due_date_single() {

		$plan = $this->get_plan( 25.99, 0 );
		$order = $this->get_order( $plan );
		$this->assertTrue( is_a( $order->get_next_payment_due_date(), 'WP_Error' ) );

	}

	/**
	 * Test get_next_payment_due_date() for recurring payments
	 *
	 * @since Unknown.
	 * @since 3.37.6 Adjusted delta on date comparison to allow 2 hours difference when calculating recurring payment dates.
	 * @since 5.3.0 Don't rely on the date_billing_end property for ending a payment plan.
	 * @since 5.3.3 Use `assertEqualsWithDelta()` in favor of 4th parameter provided to `assertEquals()`.
	 * @since 6.0.0 Replaced use of the deprecated `llms_mock_current_time()` function
	 *              with `llms_tests_mock_current_time()` from the `lifterlms-tests` project.
	 *
	 * @return void
	 */
	public function test_get_next_payment_due_date_recurring() {

		$original_time = current_time( 'Y-m-d H:i:s' );

		$plan = $this->get_plan();
		foreach ( array( 'day', 'week', 'month', 'year' ) as $period ) {

			llms_tests_mock_current_time( $original_time );

			$plan->set( 'period', $period );

			// Test due date with a trial.
			$plan->set( 'trial_offer', 'yes' );
			$order = $this->get_order( $plan );
			$this->assertEqualsWithDelta( strtotime( $order->get_trial_end_date() ), strtotime( $order->get_next_payment_due_date() ), $this->date_delta );
			$plan->set( 'trial_offer', 'no' );

			// Perform calculation tests against different frequencies.
			$i = 1;
			while ( $i <= 3 ) {

				$plan->set( 'frequency', $i );

				$order = $this->get_order( $plan );

				$expect = strtotime( "+{$i} {$period}", $order->get_date( 'date', 'U' ) );
				$this->assertEquals( $expect, $order->get_next_payment_due_date( 'U') );

				// Time travel a bit and recalculate the time.
				llms_tests_mock_current_time( date( 'Y-m-d H:i:s', $expect + HOUR_IN_SECONDS * 2 ) );
				$future_expect = strtotime( "+{$i} {$period}", $expect );

				// This will calculate the next payment date based off of the saved next payment date (which is now in the past).
				$order->maybe_schedule_payment( true );
				$this->assertEquals( $future_expect, $order->get_next_payment_due_date( 'U' ) );

				// Recalculate off a transaction -- this is the fallback for pre 3.10 orders.
				// Occurs only when no date_next_payment is set.
				$order->set( 'date_next_payment', '' );
				$order->record_transaction( array(
					'amount' => 25.99,
					'completed_date' => $original_time,
					'status' => 'llms-txn-succeeded',
					'payment_type' => 'recurring',
				) );
				$order->maybe_schedule_payment( true );

				$this->assertEqualsWithDelta( strtotime( date( 'Y-m-d H:i:s', $future_expect ) ), strtotime( $order->get_next_payment_due_date( 'Y-m-d H:i:s' ) ), HOUR_IN_SECONDS * 2 );

				// Plan ended so func should return a WP_Error.
				$order->set( 'billing_length', 1 );
				$order->maybe_schedule_payment( true );
				$date = $order->get_next_payment_due_date();
				$this->assertIsWPError( $date );
				$this->assertWPErrorCodeEquals( 'plan-ended', $date );
				$this->assertEquals( 'yes', $order->get( 'plan_ended' ) );
				$order->set( 'billing_length', 0 );

				$i++;

			}

		}

	}

	/**
	 * Test get_next_payment_due_date() method for a payment plan
	 *
	 * Additionally tests calculate_next_payment_date() via action hooks.
	 *
	 * @since Unknown
	 * @since 5.3.0 Updated to rely on number of successful transactions in favor of the current date.
	 * @since 6.0.0 Replaced use of the deprecated `llms_mock_current_time()` function
	 *              with `llms_tests_mock_current_time()` from the `lifterlms-tests` project.
	 *
	 * @return void
	 */
	public function test_get_next_payment_due_date_payment_plan() {

		$original_time = current_time( 'Y-m-d H:i:s' );

		llms_tests_mock_current_time( $original_time );

		// This should run 3 total payments over the course of 9 weeks.
		$plan = $this->get_plan();
		$plan->set( 'frequency', 3 ); // Every 3rd.
		$plan->set( 'period', 'week' ); // Week.
		$plan->set( 'length', 3 ); // For 3 payments.

		// Create the order.
		$order = $this->get_order( $plan );

		// 3 total payments due.
		$this->assertEquals( 3, $order->get_remaining_payments() );

		// Make the initial payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// Two payments remaining.
		$this->assertEquals( 2, $order->get_remaining_payments() );

		// Payment two is scheduled properly.
		$expect = strtotime( "+3 weeks", $order->get_date( 'date', 'U' ) );
		$this->assertEquals( $expect, $order->get_next_payment_due_date( 'U' ) );

		// Time travel to when the second payment is due.
		llms_tests_mock_current_time( date( 'Y-m-d H:i:s', $expect ) );

		// Record the second payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// Only one payment remaining.
		$this->assertEquals( 1, $order->get_remaining_payments() );

		// Payment 3 is scheduled properly.
		$expect += WEEK_IN_SECONDS * 3;
		$this->assertEquals( $expect, $order->get_next_payment_due_date( 'U' ) );

		// Time travel to when the 3rd payment is due.
		llms_tests_mock_current_time( date( 'Y-m-d H:i:s', $expect ) );

		// Make the 3rd payment.
		$order->record_transaction( array(
			'payment_type' => 'recurring',
			'status'       => 'llms-txn-succeeded',
		) );

		// No more payments due.
		$this->assertTrue( is_a( $order->get_next_payment_due_date( 'U' ), 'WP_Error' ) );
		$this->assertEquals( 0, $order->get_remaining_payments() );

	}

	/**
	 * Test get_remaining_payments()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_remaining_payments() {

		// Not recurring.
		$this->assertFalse( $this->obj->get_remaining_payments() );

		// No length.
		$this->obj->set( 'order_type', 'recurring' );
		$this->assertFalse( $this->obj->get_remaining_payments() );

		// Has length.
		$this->obj->set( 'billing_length', 5 );
		$this->assertEquals( 5, $this->obj->get_remaining_payments() );

		// These statuses don't count.
		foreach ( array( 'failed', 'pending' ) as $status ) {
			$this->obj->record_transaction( array(
				'status'       => "llms-txn-{$status}",
				'payment_type' => 'recurring',
			) );
			$this->assertEquals( 5, $this->obj->get_remaining_payments() );
		}

		// Record a few successes.
		$i = 1;
		while ( $i <= 4 ) {
			$this->obj->record_transaction( array(
				'payment_type' => 'recurring',
			) );
			$this->assertEquals( 5 - $i, $this->obj->get_remaining_payments(), $i );
			++$i;
		}

		// Refunds count?
		$this->obj->record_transaction( array(
			'status'       => 'llms-txn-refunded',
			'payment_type' => 'recurring',
		) );
		$this->assertEquals( 0, $this->obj->get_remaining_payments() );

	}

	// public function test_get_transaction_total() {}

	// public function test_get_start_date() {}

	// public function test_get_transactions() {}

	/**
	 * Test the get_trial_end_date() method.
	 *
	 * @since 3.10.0
	 * @since 6.0.0 Replaced use of the deprecated `llms_mock_current_time()` function
	 *              with `llms_tests_mock_current_time()` from the `lifterlms-tests` project.
	 *
	 * @return void
	 */
	public function test_get_trial_end_date() {

		$this->obj->set( 'order_type', 'recurring' );

		// No trial so false for end date.
		$this->assertEmpty( $this->obj->get_trial_end_date() );

		// Enable trial.
		$this->obj->set( 'trial_offer', 'yes' );
		$start = $this->obj->get_start_date( 'U' );

		// When the date is saved the getter shouldn't calculate a new date and should return the saved date.
		$set = '2017-05-05 13:42:19';
		$this->obj->set( 'date_trial_end', $set );
		$this->assertEquals( $set, $this->obj->get_trial_end_date() );
		$this->obj->set( 'date_trial_end', '' );

		// Run a bunch of tests testing the dynamic calculations for various periods and whatever.
		foreach ( array( 'day', 'week', 'month', 'year' ) as $period ) {

			$this->obj->set( 'trial_period', $period );
			$i = 1;
			while ( $i <= 3 ) {

				llms_tests_mock_current_time( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );

				$this->obj->set( 'trial_length', $i );
				$expect = strtotime( '+' . $i . ' ' . $period, $start );
				$this->assertEquals( $expect, $this->obj->get_trial_end_date( 'U' ) );

				// Trial is not over.
				$this->assertFalse( $this->obj->has_trial_ended() );

				// Change date to future.
				llms_tests_mock_current_time( date( 'Y-m-d H:i:s', $this->obj->get_trial_end_date( 'U' ) + HOUR_IN_SECONDS ) );
				$this->assertTrue( $this->obj->has_trial_ended() );

				$i++;

			}

		}

	}

	// public function test_get_revenue() {}

	/**
	 * Test has_coupon() method
	 *
	 * @return void
	 */
	public function test_has_coupon() {

		$this->obj->set( 'coupon_used', 'whatarst' );
		$this->assertFalse( $this->obj->has_coupon() );

		$this->obj->set( 'coupon_used', 'no' );
		$this->assertFalse( $this->obj->has_coupon() );

		$this->obj->set( 'coupon_used', '' );
		$this->assertFalse( $this->obj->has_coupon() );

		$this->obj->set( 'coupon_used', 'yes' );
		$this->assertTrue( $this->obj->has_coupon() );

	}

	/**
	 * Test had_discount() method
	 *
	 * @return void
	 */
	public function test_has_discount() {

		$this->obj->set( 'coupon_used', 'yes' );
		$this->assertTrue( $this->obj->has_discount() );

		$this->obj->set( 'coupon_used', 'no' );
		$this->assertFalse( $this->obj->has_discount() );

		$this->obj->set( 'on_sale', 'yes' );
		$this->assertTrue( $this->obj->has_discount() );

		$this->obj->set( 'on_sale', 'no' );
		$this->assertFalse( $this->obj->has_discount() );

	}

	/**
	 * Test has_plan_expiration()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_has_plan_expiration() {

		// Single payment.
		$this->assertFalse( $this->obj->has_plan_expiration() );

		// Recurring with no length.
		$this->obj->set( 'order_type', 'recurring' );
		$this->assertFalse( $this->obj->has_plan_expiration() );

		// Has length.
		$this->obj->set( 'billing_length', 1 );
		$this->assertTrue( $this->obj->has_plan_expiration() );

	}

	/**
	 * Test has_sale() method
	 *
	 * @return void
	 */
	public function test_has_sale() {

		$this->obj->set( 'on_sale', 'whatarst' );
		$this->assertFalse( $this->obj->has_sale() );

		$this->obj->set( 'on_sale', 'no' );
		$this->assertFalse( $this->obj->has_sale() );

		$this->obj->set( 'on_sale', '' );
		$this->assertFalse( $this->obj->has_sale() );

		$this->obj->set( 'on_sale', 'yes' );
		$this->assertTrue( $this->obj->has_sale() );

	}

	// public function test_has_scheduled_payment() {}

	/**
	 * Test has_trial() method
	 *
	 * @return void
	 */
	public function test_has_trial() {

		$this->obj->set( 'order_type', 'recurring' );

		$this->obj->set( 'trial_offer', 'whatarst' );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'trial_offer', 'no' );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'trial_offer', '' );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'trial_offer', 'yes' );
		$this->assertTrue( $this->obj->has_trial() );

	}

	/**
	 * Test init().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init() {

		$user_data = array(
			'user_email' => 'orderinit@mock.tld',
			'first_name' => 'Adam',
			'last_name'  => 'Phillips',
		);

		$user_extra = array(
			'billing_address_1'    => 'add1',
			'billing_address_2'    => 'add2',
			'billing_city'         => 'City',
			'billing_state'        => 'AB',
			'billing_zip'          => '12345',
			'billing_country'      => 'ZZ',
		);

		$student = $this->factory->student->create_and_get( $user_data );
		$plan    = $this->get_mock_plan();

		$expected_data = array(
			'billing_email'        => $user_data['user_email'],
			'billing_first_name'   => $user_data['first_name'],
			'billing_last_name'    => $user_data['last_name'],
			'billing_phone'        => '(123) 456-7890',
			'user_ip_address'      => '127.0.0.1',
			'plan_id'              => $plan->get( 'id' ),
			'plan_title'           => $plan->get( 'title' ),
			'plan_sku'             => $plan->get( 'sku' ),
			'product_id'           => $plan->get( 'product_id' ),
			'product_title'        => get_the_title( $plan->get( 'product_id' ) ),
			'product_sku'          => '',
			'product_type'         => 'course',
			'payment_gateway'      => 'manual',
			'gateway_api_mode'     => 'live',
			'trial_offer'          => 'no',
			'trial_length'         => 0,
			'trial_period'         => '',
			'trial_original_total' => 0.0,
			'trial_total'          => 0.0,
			'date_trial_end'       => '',
			'currency'             => 'USD',
			'on_sale'              => 'no',
			'sale_price'           => 0.0,
			'sale_value'           => 0,
			'original_total'       => 25.99,
			'total'                => 25.99,
			'coupon_id'            => 0,
			'coupon_amount'        => 0,
			'coupon_code'          => '',
			'coupon_type'          => '',
			'coupon_used'          => 'no',
			'coupon_value'         => 0.0,
			'coupon_amount_trial'  => '',
			'coupon_value_trial'   => 0,
			'billing_frequency'    => 1,
			'billing_length'       => 0,
			'billing_period'       => 'day',
			'order_type'           => 'recurring',
			'date_next_payment'    => '2022-03-03 12:22:19',
			'access_expiration'    => 'lifetime',
			'access_expires'       => '',
			'access_length'        => 0,
			'access_period'        => '',
		);

		foreach ( $user_extra as $key => $val ) {
			$student->set( $key, $val );
			$expected_data[ $key ] = $val;
		}
		$student->set( 'phone', '(123) 456-7890' );

		$mock_next_payment_date = function() use ( $expected_data ) {
			return $expected_data['date_next_payment'];
		};
		add_filter( 'llms_order_calculate_next_payment_date', $mock_next_payment_date );

		$order = $this->get_mock_order( $plan, null, $student );

		remove_filter( 'llms_order_calculate_next_payment_date', $mock_next_payment_date );

		foreach ( $expected_data as $key => $expected ) {
			$this->assertEquals( $expected, $order->get( $key ), $key );
		}

	}

	/**
	 * Test init() with a user data array.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_with_user_data_array() {

		// Should pass an empty student object to the hook.
		$handler = function( $order, $student, $user_data ) {
			$this->assertFalse( $student->exists() );
		};
		add_action( 'lifterlms_new_pending_order', $handler, 10, 3 );

		$order = new LLMS_Order( 'new' );

		// Use a user data array.
		$order->init(
			array(
				'billing_email' => 'email@email.tld',
			),
			$this->get_mock_plan(),
			llms()->payment_gateways()->get_gateway_by_id( 'manual' )
		);

		$this->assertEquals( 'email@email.tld', $order->get( 'billing_email' ) );
		$this->assertEquals( 0, $order->get( 'user_id' ) );

		remove_action( 'lifterlms_new_pending_order', $handler, 10 );

	}

	/**
	 * Test init() with a plan that has a trial.
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function test_init_with_trial() {

		// Test initialization of a trial.
		$plan = $this->get_plan( 25.99, 1, 'lifetime', false, true );
		$order = $this->get_order( $plan );

		$this->assertTrue( $order->has_trial() );
		$this->assertNotEmpty( $order->get( 'date_trial_end' ) );

	}

	/**
	 * Test the is_recurring() method.
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function test_is_recurring() {

		$this->assertFalse( $this->obj->is_recurring() );
		$this->obj->set( 'order_type', 'recurring' );
		$this->assertTrue( $this->obj->is_recurring() );

	}

	/**
	 * Test the schedule expiration function
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 4.6.0 Add coverage for `get_next_scheduled_action_time()`.
	 *
	 * @return void
	 */
	public function test_maybe_schedule_expiration() {

		// Recurring order with lifetime access won't schedule expiration.
		$order = $this->get_mock_order();

		$order->set_status( 'llms-active' );
		$order->maybe_schedule_expiration();

		$this->assertFalse( as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) ) );
		$this->assertFalse( $order->get_next_scheduled_action_time( 'llms_access_plan_expiration' ) );

		// Limited access will schedule expiration.
		$plan = $this->get_mock_plan( '25.99', 0, 'limited-date' );
		$order = $this->get_mock_order( $plan );

		$order->set_status( 'llms-active' );
		$order->maybe_schedule_expiration();

		$action_time = as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) );
		$this->assertEquals( $order->get_access_expiration_date( 'U' ), $action_time );
		$this->assertEquals( $action_time, $order->get_next_scheduled_action_time( 'llms_access_plan_expiration' ) );

	}

	/**
	 * Test recurring payment scheduling for a one-time order
	 *
	 * @since 4.7.0 Split from test_maybe_schedule_payment_recurring()
	 *
	 * @return void
	 */
	public function test_maybe_schedule_payment_one_time() {

		// Does nothing for a one-time order.
		$plan = $this->get_mock_plan( '25.99', 0 );
		$order = $this->get_mock_order( $plan );
		$order->maybe_schedule_payment();
		$this->assertEmpty( $order->get( 'date_next_payment' ) );

	}

	/**
	 * Test recurring payment scheduling for a recurring order
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 4.6.0 Add coverage for `get_next_scheduled_action_time()`.
	 * @since 4.7.0 Split into its own method to prevent variable clashes.
	 *
	 * @return void
	 */
	public function test_maybe_schedule_payment_recurring() {

		$order = $this->get_mock_order();

		$this->assertFalse( as_next_scheduled_action( 'llms_charge_recurring_payment', array(
			'order_id' => $order->get( 'id' ),
		) ) );
		$this->assertFalse( $order->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) );

		$order->maybe_schedule_payment();
		$this->assertTrue( ! empty( $order->get( 'date_next_payment' ) ) );

		$action_time = as_next_scheduled_action( 'llms_charge_recurring_payment', array( 'order_id' => $order->get( 'id' ) ) );
		$this->assertEquals( $order->get_next_payment_due_date( 'U' ), $action_time );
		$this->assertEquals( $action_time, $order->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) );

	}

	/**
	 * Test maybe_schedule_retry() method.
	 *
	 * @return void
	 */
	public function test_maybe_schedule_retry() {

		$this->mock_gateway_support( 'recurring_retry' );

		$order = $this->get_order();
		$order->set_status( 'on-hold' );

		$i = 1;
		while ( $i <= 5 ) {

			$original_next_date = $order->get_next_payment_due_date( 'U' );

			$txn = $order->record_transaction( array(
				'amount' => 25.99,
				'status' => 'llms-txn-pending',
				'payment_type' => 'recurring',
			) );
			$txn->set( 'status', 'llms-txn-failed' );

			$order = llms_get_post( $order->get( 'id' ) );

			if ( $i <= 4 ) {

				$this->assertEquals( $i, did_action( 'llms_automatic_payment_retry_scheduled' ) );
				$this->assertEquals( $i - 1, $order->get( 'last_retry_rule' ) );
				$this->assertNotEquals( $original_next_date, $order->get_next_payment_due_date( 'U' ) );

			} else {

				$this->assertEquals( 1, did_action( 'llms_automatic_payment_maximum_retries_reached' ) );
				$this->assertEquals( '', $order->get( 'last_retry_rule' ) );
				$this->assertEquals( 'llms-failed', $order->get( 'status' ) );

			}


			$i++;

		}

	}

	/**
	 * Test record_transaction() method
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function test_record_transaction() {

		$order = $this->get_order();
		$txn = $order->record_transaction( array(
			'amount' => 25.99,
			'status' => 'llms-txn-succeeded',
			'payment_type' => 'recurring',
		) );
		$this->assertTrue( is_a( $txn, 'LLMS_Transaction' ) );
		$order = llms_get_post( $order->get( 'id' ) );
		$this->assertEquals( 'llms-active', $order->get( 'status' ) );
		$this->assertEquals( 1, did_action( 'lifterlms_transaction_status_succeeded' ) );
		$this->assertEquals( 1, did_action( 'lifterlms_order_status_active' ) );

	}

	/**
	 * Test the set_date() method
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_set_date() {

		$dates = array(
			'next_payment',
			'trial_end',
			'access_expires',
		);

		foreach ( $dates as $key ) {

			// Set via date string.
			$date = current_time( 'mysql' );
			$this->obj->set_date( $key, $date );
			$this->assertEquals( $date, $this->obj->get( 'date_' . $key ) );

			// Set via timestamp.
			$timestamp = current_time( 'timestamp' );
			$this->obj->set_date( $key, $timestamp );
			$this->assertEquals( date_i18n( 'Y-m-d H:i:s', $timestamp ), $this->obj->get( 'date_' . $key ) );

		}

	}

	/**
	 * Test set_status() method
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function test_set_status() {

		$this->obj->set_status( 'fakestatus' );
		$this->assertNotEquals( 'fakestatus', $this->obj->get( 'status' ) );

		$this->obj->set( 'order_type', 'single' );
		foreach ( array_keys( llms_get_order_statuses( 'single' ) ) as $status ) {

			$this->obj->set_status( $status );
			$this->assertEquals( $status, $this->obj->get( 'status' ) );

			$unprefixed = str_replace( 'llms-', '', $status );
			$this->obj->set_status( $unprefixed );
			$this->assertEquals( $status, $this->obj->get( 'status' ) );

		}

	}

	/**
	 * Test set_user_data().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_set_user_data() {

		$expected = array(
			'billing_email'      => 'maude@thelittlelebowskiurbanachievers.org',
			'billing_first_name' => 'Maude',
			'billing_last_name'  => 'Lebowski',
			'billing_address_1'  => '123 Ant Street',
			'billing_address_2'  => 'Suite Z',
			'billing_city'       => 'Someplace',
			'billing_state'      => 'OK',
			'billing_zip'        => '32921-2342',
			'billing_country'    => 'US',
			'billing_phone'      => '(123) 456-7890',
			'user_ip_address'    => '127.0.0.1',
		);

		$user_id = $this->factory->student->create( array(
			'user_email' => $expected['billing_email'],
			'first_name' => $expected['billing_first_name'],
			'last_name'  => $expected['billing_last_name'],
		) );

		$student = llms_get_student( $user_id );
		$student->set( 'billing_address_1', $expected['billing_address_1'] );
		$student->set( 'billing_address_2', $expected['billing_address_2'] );
		$student->set( 'billing_city', $expected['billing_city'] );
		$student->set( 'billing_state', $expected['billing_state'] );
		$student->set( 'billing_zip', $expected['billing_zip'] );
		$student->set( 'billing_country', $expected['billing_country'] );
		$student->set( 'phone', $expected['billing_phone'] );

		$expected['user_id'] = $user_id;

		$tests = array(
			'User ID'      => $user_id,
			'LLMS_Student' => $student,
			'WP_User'      => get_user_by( 'id', $user_id ),
			'Raw Array'    => $expected,
		);
		foreach ( $tests as $msg => $input ) {

			$this->create(); // Reset the data from the previous run.
 			$this->assertUserDataSet( $expected, $this->obj->set_user_data( $input ), "From {$msg}" );

		}

		$this->create();

		// Test raw array with a "forced" ip address.
		$expected['user_ip_address'] = '192.168.1.45';
		$this->assertUserDataSet( $expected, $this->obj->set_user_data( $expected ), "From raw array with forced IP address" );

		// Extra fields are excluded.
		$this->assertUserDataSet( $expected, $this->obj->set_user_data( array_merge( $expected, array( 'extra_field' => 'excluded' ) ) ), "From raw array with extra data" );

	}

	private function assertUserDataSet( $expected, $received, $message = '' ) {

		// Response is correct.
		$this->assertEquals( $expected, $received, $message );

		// Persisted to the order.
		foreach ( $expected as $key => $val ) {
			$this->assertEquals( $val, $this->obj->get( $key ), "{$message}: {$key}" );
		}

	}


	/**
	 * Test the start access method
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 * @since 6.0.0 Replaced use of the deprecated `llms_mock_current_time()` function
	 *              with `llms_tests_mock_current_time()` from the `lifterlms-tests` project.
	 *
	 * @return void
	 */
	public function test_start_access() {

		$plan = $this->get_mock_plan( '25.99', 0, 'limited-date' );
		$order = $this->get_mock_order( $plan );

		// Freeze time.
		$time = current_time( 'mysql' );
		llms_tests_mock_current_time( $time );

		// Prior to starting access there should be no access start date.
		$this->assertEmpty( $order->get( 'start_date' ) );

		// Start the access.
		$order->start_access();

		// Time should be our mocked time.
		$this->assertEquals( $time, $order->get( 'start_date' ) );

		// An expiration event should be scheduled to match the expiration date.
		$event_time = as_next_scheduled_action( 'llms_access_plan_expiration', array(
			'order_id' => $order->get( 'id' ),
		) );
		$this->assertEquals( $order->get_access_expiration_date( 'U' ), $event_time );

	}

	/**
	 * Test unschedule_expiration() method
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function test_unschedule_expiration() {

		$plan = $this->get_mock_plan( '25.99', 0, 'limited-date' );
		$order = $this->get_mock_order( $plan );

		$order->set_status( 'llms-active' );
		$order->maybe_schedule_expiration();

		$order->unschedule_expiration();

		$this->assertFalse( $order->get_next_scheduled_action_time( 'llms_access_plan_expiration' ) );

	}

	/**
	 * Test unschedule_recurring_payment() method
	 *
	 * @since 4.6.0
	 *
	 * @return void
	 */
	public function test_unschedule_recurring_payment() {

		$order = $this->get_mock_order();
		$order->maybe_schedule_payment();

		$order->unschedule_recurring_payment();

		$this->assertFalse( $order->get_next_scheduled_action_time( 'llms_charge_recurring_payment' ) );

	}

	/**
	 * Test get_customer_full_address() method
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_get_customer_full_address() {

		$customer_details = array(
			'billing_address_1' => 'Rue Jennifer 7',
			'billing_address_2' => 'c/o Juniper',
			'billing_city'      => 'Pasadena',
			'billing_state'     => 'CA',
			'billing_zip'       => '28282',
			'billing_country'   => 'US'
		);

		$this->obj->set_bulk( $customer_details );

		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena CA, 28282, United States', $this->obj->get_customer_full_address() );

		// Remove city.
		$this->obj->set( 'billing_city', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, CA, 28282, United States', $this->obj->get_customer_full_address() );

		// Remove state.
		$this->obj->set( 'billing_state', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, 28282, United States', $this->obj->get_customer_full_address() );

		// Add back city.
		$this->obj->set( 'billing_city', $customer_details['billing_city'] );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena, 28282, United States', $this->obj->get_customer_full_address() );

		// Remove zip code.
		$this->obj->set( 'billing_zip', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena, United States', $this->obj->get_customer_full_address() );

		// Remove country.
		$this->obj->set( 'billing_country', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena', $this->obj->get_customer_full_address() );

		// Remove secondary address.
		$this->obj->set( 'billing_address_2', '' );
		$this->assertEquals( 'Rue Jennifer 7, Pasadena', $this->obj->get_customer_full_address() );

		// Remove main billing address. We expect that nothing is returned.
		$this->obj->set( 'billing_address_1', '' );
		$this->assertEquals( '', $this->obj->get_customer_full_address() );

	}


	/**
	 * Test get_recurring_payment_due_date_for_scheduler() method
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_get_recurring_payment_due_date_for_scheduler() {

		$order = $this->get_mock_order();

		$now = current_time( 'timestamp' );
		llms_tests_mock_current_time( $now );

		// One time payment plan.
		$plan = $this->get_plan( '25.99', 0 );

		$order = $this->get_mock_order( $plan );

		$this->assertWPErrorCodeEquals( 'not-recurring', $order->get_recurring_payment_due_date_for_scheduler() );

		// Check order with invalid status.
		$plan = $this->get_plan();

		$plan->set( 'frequency', 1 ); // Every.
		$plan->set( 'period', 'month' ); // Month.
		$plan->set( 'length', 3 ); // for 3 total payments.
		$order = $this->get_mock_order( $plan );

		$original_status = $order->get( 'status' );
		$order->set( 'status', 'some-invalid' );
		$this->assertWPErrorCodeEquals( 'invalid-status', $order->get_recurring_payment_due_date_for_scheduler() );
		$order->set( 'status', $original_status);

		// Check providing a (boolean) false date.
		$this->assertWPErrorCodeEquals( 'invalid-recurring-payment-date', $order->get_recurring_payment_due_date_for_scheduler( 0 ) );

		// Check the returning timestamp is the order next payment due date converted to UTC.
		$this->assertEquals(
			get_gmt_from_date( $order->get_next_payment_due_date(), 'U' ),
			$order->get_recurring_payment_due_date_for_scheduler()
		);

		// Pretend we the next payment due date was UTC.
		$this->assertEquals(
			date_format( date_create( $order->get_next_payment_due_date() ), 'U' ),
			$order->get_recurring_payment_due_date_for_scheduler( false, true )
		);

	}

}
