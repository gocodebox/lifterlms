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
	 *
	 * @return void.
	 */
	public function setUp() {
		parent::setUp();
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
	 * Test private calculate_next_payment_date() for a plan with an end date.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_calculate_next_payment_date_with_end_date() {

		$now = current_time( 'timestamp' );
		llms_mock_current_time( $now );

		$plan = $this->get_plan();

		$plan->set( 'frequency', 1 ); // Every.
		$plan->set( 'period', 'month' ); // Month.
		$plan->set( 'length', 3 ); // for 3 total payments.
		$order = $this->get_mock_order( $plan );

		// Delete the end date to simulate pre 3.10 behavior.
		// $order->set( 'date_billing_end', '' );

		$first_recurring = LLMS_Unit_Test_Util::call_method( $order, 'calculate_next_payment_date' );

		// End date is calculated and stored for future use.
		$this->assertTrue( ! empty( $order->get( 'date_billing_end' ) ) );

		// First recurring payment (the second payment) is 1 month from the order start date.
		$this->assertEquals( strtotime( '+1 month', $order->get_date( 'date', 'U' ) ), strtotime( $first_recurring ) );

		// Time travel to simulate the completion of the previous payment.
		$now = strtotime( $first_recurring ) + 1;
		llms_mock_current_time( $now );

		// Second recurring payment (the final payment) is 1 month from the first recurring payment date.
		$second_recurring = LLMS_Unit_Test_Util::call_method( $order, 'calculate_next_payment_date' );
		$this->assertEquals( strtotime( '+1 month', strtotime( $first_recurring ) ), strtotime( $second_recurring ) );

		// Time travel to simulate the completion of the previous payment.
		$now = strtotime( $second_recurring );
		llms_mock_current_time( $now );

		// There is no 3rd recurring payment because it's after the end date.
		$third_recurring = LLMS_Unit_Test_Util::call_method( $order, 'calculate_next_payment_date' );
		$this->assertEquals( '', $third_recurring );

	}

	/**
	 * Test the can_be_retried() method.
	 *
	 * @since Unknown.
	 *
	 * @return void
	 */
	public function test_can_be_retried() {

		$order = $this->get_order();

		// pending order can't be retried
		$this->assertFalse( $order->can_be_retried() );

		// active can be retried
		$order->set_status( 'llms-active' );

		// gateway doesn't support retries
		$this->assertFalse( $order->can_be_retried() );

		// allow the gateway to support retries
		$this->mock_gateway_support( 'recurring_retry' );

		// can be retried now
		$this->assertTrue( $order->can_be_retried() );

		// on hold can be retried
		$order->set_status( 'llms-on-hold' );
		$this->assertTrue( $order->can_be_retried() );

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
		llms_mock_current_time( '2010-05-05' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		// Future should still grant access.
		llms_mock_current_time( '2525-05-05' );
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
			llms_mock_current_time( $data['now'] );
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
	 *
	 * @return void
	 */
	public function test_get_customer_name() {
		$first = 'Jeffrey';
		$last = 'Lebowski';
		$this->obj->set( 'billing_first_name', $first );
		$this->obj->set( 'billing_last_name', $last );
		$this->assertEquals( $first . ' ' . $last,  $this->obj->get_customer_name() );
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

		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
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
	 *
	 * @return void
	 */
	public function test_get_next_payment_due_date_recurring() {

		$original_time = current_time( 'Y-m-d H:i:s' );

		$plan = $this->get_plan();
		foreach ( array( 'day', 'week', 'month', 'year' ) as $period ) {

			llms_mock_current_time( $original_time );

			$plan->set( 'period', $period );

			// Test due date with a trial.
			$plan->set( 'trial_offer', 'yes' );
			$order = $this->get_order( $plan );
			$this->assertEquals( strtotime( $order->get_trial_end_date() ), strtotime( $order->get_next_payment_due_date() ), '', $this->date_delta );
			$plan->set( 'trial_offer', 'no' );

			// Perform calculation tests against different frequencies.
			$i = 1;
			while ( $i <= 3 ) {

				$plan->set( 'frequency', $i );

				$order = $this->get_order( $plan );

				$expect = strtotime( "+{$i} {$period}", $order->get_date( 'date', 'U' ) );
				$this->assertEquals( $expect, $order->get_next_payment_due_date( 'U') );

				// Time travel a bit and recalculate the time.
				llms_mock_current_time( date( 'Y-m-d H:i:s', $expect + HOUR_IN_SECONDS * 2 ) );
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

				$this->assertEquals( strtotime( date( 'Y-m-d H:i:s', $future_expect ) ), strtotime( $order->get_next_payment_due_date( 'Y-m-d H:i:s' ) ), '', HOUR_IN_SECONDS * 2 );

				// Plan ends so func should return a WP_Error.
				$order->set( 'date_billing_end', date( 'Y-m-d H:i:s', $future_expect - DAY_IN_SECONDS ) );
				$order->maybe_schedule_payment( true );
				$date = $order->get_next_payment_due_date();
				$this->assertIsWPError( $date );
				$this->assertWPErrorCodeEquals( 'plan-ended', $date );
				$this->assertEquals( 'yes', $order->get( 'plan_ended' ) );
				$order->set( 'date_billing_end', 0 );

				$i++;

			}

		}

	}

	/**
	 * Undocumented get_next_payment_due_date() method
	 *
	 * @return void
	 */
	public function test_get_next_payment_due_date_payment_plan() {

		$original_time = current_time( 'Y-m-d H:i:s' );

		llms_mock_current_time( $original_time );

		// This should run 3 total payments over the course of 9 weeks.
		$plan = $this->get_plan();
		$plan->set( 'frequency', 3 ); // Every 3rd.
		$plan->set( 'period', 'week' ); // Week.
		$plan->set( 'length', 3 ); // For 3 payments.

		// Payment one is order date.
		$order = $this->get_order( $plan );

		// Payment two.
		$expect = strtotime( "+3 weeks", $order->get_date( 'date', 'U' ) );
		$this->assertEquals( $expect, $order->get_next_payment_due_date( 'U' ) );

		// Time travel.
		llms_mock_current_time( date( 'Y-m-d H:i:s', $expect ) );
		// Reschedule next date.
		$order->maybe_schedule_payment( true );
		$expect += WEEK_IN_SECONDS * 3;

		// Payment three.
		$this->assertEquals( $expect, $order->get_next_payment_due_date( 'U' ) );

		// Time travel.
		llms_mock_current_time( date( 'Y-m-d H:i:s', $expect ) );
		// Reschedule next date.
		$order->maybe_schedule_payment( true );

		// No more payments.
		$this->assertTrue( is_a( $order->get_next_payment_due_date( 'U' ), 'WP_Error' ) );

	}

	// public function test_get_transaction_total() {}

	// public function test_get_start_date() {}

	// public function test_get_transactions() {}

	/**
	 * Test get_trial_end_deate() method
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

				llms_mock_current_time( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );

				$this->obj->set( 'trial_length', $i );
				$expect = strtotime( '+' . $i . ' ' . $period, $start );
				$this->assertEquals( $expect, $this->obj->get_trial_end_date( 'U' ) );

				// Trial is not over.
				$this->assertFalse( $this->obj->has_trial_ended() );

				// Change date to future.
				llms_mock_current_time( date( 'Y-m-d H:i:s', $this->obj->get_trial_end_date( 'U' ) + HOUR_IN_SECONDS ) );
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
	 * Test init() with a plan that has limited number of payments
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function test_init_with_limited_plan() {

		// Test initialization of an order with a plan that ends.
		$plan = $this->get_plan();
		$plan->set( 'length', 5 );
		$order = $this->get_order( $plan );
		$this->assertNotEmpty( $order->get( 'date_billing_end' ) );

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
	 * @since 4.7.0 Split into it's own method to prevent variable clashes.
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
	 * Test the start access method
	 *
	 * @since 3.19.0
	 * @since 3.32.0 Update to use latest action-scheduler functions.
	 *
	 * @return void
	 */
	public function test_start_access() {

		$plan = $this->get_mock_plan( '25.99', 0, 'limited-date' );
		$order = $this->get_mock_order( $plan );

		// Freeze time.
		$time = current_time( 'mysql' );
		llms_mock_current_time( $time );

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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_cutomer_full_address() {

		$customer_details = array(
			'billing_address_1' => 'Rue Jennifer 7',
			'billing_address_2' => 'c/o Juniper',
			'billing_city'      => 'Pasadena',
			'billing_state'     => 'CA',
			'billing_zip'       => '28282',
			'billing_country'   => 'US'
		);

		$this->obj->set_bulk( $customer_details );

		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena CA, 28282, United States (US)', $this->obj->get_customer_full_address() );

		// Remove city.
		$this->obj->set( 'billing_city', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, CA, 28282, United States (US)', $this->obj->get_customer_full_address() );

		// Remove state.
		$this->obj->set( 'billing_state', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, 28282, United States (US)', $this->obj->get_customer_full_address() );

		// Add back city.
		$this->obj->set( 'billing_city', $customer_details['billing_city'] );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena, 28282, United States (US)', $this->obj->get_customer_full_address() );

		// Remove zip code.
		$this->obj->set( 'billing_zip', '' );
		$this->assertEquals( 'Rue Jennifer 7 c/o Juniper, Pasadena, United States (US)', $this->obj->get_customer_full_address() );

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

}
