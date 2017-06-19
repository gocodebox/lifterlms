<?php
/**
 * Tests for LifterLMS Course Model
 * @group    LLMS_Order
 * @group    LLMS_Post_Model
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_LLMS_Order extends LLMS_PostModelUnitTestCase {

	public function setUp() {

		parent::setUp();
		$this->create();

	}

	private function get_plan( $price = 25.99, $frequency = 1, $expiration = 'lifetime', $on_sale = false, $trial = false ) {

		$course = $this->generate_mock_courses( 1 );
		$course_id = $course[0];

		$plan = new LLMS_Access_Plan( 'new', 'Test Access Plan' );
		$plan_data = array(
			'access_expiration' => $expiration,
			'access_expires' => ( 'limited-date' === $expiration ) ? date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) : '',
			'access_length' => '1',
			'access_period' => 'year',
			'frequency' => $frequency,
			'is_free' => 'no',
			'length' => 0,
			'on_sale' => $on_sale ? 'yes' : 'no',
			'period' => 'day',
			'price' => $price,
			'product_id' => $course_id,
			'sale_price' => round( $price - ( $price * .1 ), 2 ),
			'sku' => 'accessplansku',
			'trial_length' => 1,
			'trial_offer' => $trial ? 'yes' : 'no',
			'trial_period' => 'week',
			'trial_price' => 1.00,
		);

		foreach ( $plan_data as $key => $val ) {
			$plan->set( $key, $val );
		}

		return $plan;

	}

	private function get_order( $plan = null, $coupon = false ) {

		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $gateway->get_option_name( 'enabled' ), 'yes' );

		if ( ! $plan ) {
			$plan = $this->get_plan();
		}

		if ( $coupon ) {
			$coupon = new LLMS_Coupon( 'new', 'couponcode' );
			$coupon_data = array(
				'coupon_amount' => 10,
				'discount_type' => 'percent',
				'plan_type' => 'any',
			);
			foreach ( $coupon_data as $key => $val ) {
				$coupon->set( $key, $val );
			}
		}

		$order = new LLMS_Order( 'new' );
		return $order->init( $this->get_mock_student(), $plan, $gateway, $coupon );

	}

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Order';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'llms_order';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    [version]
	 * @version  [version]
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
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    [version]
	 * @version  [version]
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
			'order_type' => 'text',
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
	 * @return   [type]     [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_add_note() {

		// don't create empty notes
		$this->assertNull( $this->obj->add_note( '' ) );

		$note_text = 'This is an order note';
		$id = $this->obj->add_note( $note_text );

		// should return the comment id
		$this->assertTrue( is_numeric( $id ) );

		$note = get_comment( $id );

		// should be a comment
		$this->assertTrue( is_a( $note, 'WP_Comment' ) );

		// comment content should be our orignal note
		$this->assertEquals( $note->comment_content, $note_text );
		// author should be the system (LifterLMS)
		$this->assertEquals( $note->comment_author, 'LifterLMS' );

		// create a new note by a user
		$id = $this->obj->add_note( $note_text, true );
		$note = get_comment( $id );
		$this->assertEquals( get_current_user_id(), $note->user_id );

		// 1 for original creation note, 2 for our test notes
		$this->assertEquals( 3, did_action( 'llms_new_order_note_added' ) );

	}

	/**
	 * Test the generate_order_key() method
	 * @return   [type]     [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_generate_order_key() {

		$this->assertTrue( is_string( $this->obj->generate_order_key() ) );
		$this->assertEquals( 0, strpos( $this->obj->generate_order_key(), 'order-' ) );

	}

	/**
	 * Test the get_access_expiration_date() method
	 * @return   [type]     [description]
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_access_expiration_date() {

		// lifetime responds with a string not a date
		$this->obj->set( 'access_expiration', 'lifetime' );
		$this->assertEquals( 'Lifetime Access', $this->obj->get_access_expiration_date() );

		// expires on a specific datae
		$this->obj->set( 'access_expiration', 'limited-date' );
		$this->obj->set( 'access_expires', '12/01/2020' ); // m/d/Y format (from datepicker)
		$this->assertEquals( '2020-12-01', $this->obj->get_access_expiration_date() );


		// expires after a period of time
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

	}

	public function test_get_access_status() {

		$this->assertEquals( 'inactive', $this->obj->get_access_status() );

		$this->obj->set( 'status', 'llms-completed' );
		$this->obj->set( 'access_expiration', 'lifetime' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		// past should still grant access
		llms_mock_current_time( '2010-05-05' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );

		// future should still grant access
		llms_mock_current_time( '2525-05-05' );
		$this->assertEquals( 'active', $this->obj->get_access_status() );


		// check limited access by date
		$this->obj->set( 'access_expiration', 'limited-date' );
		$tests = array(
			array(
				'now' => '2010-05-05',
				'expires' => '05/06/2010', // m/d/Y from datepicker
				'expect' => 'active',
			),
			array(
				'now' => '2015-05-05',
				'expires' => '05/06/2010', // m/d/Y from datepicker
				'expect' => 'expired',
			),
			array(
				'now' => '2010-05-05',
				'expires' => '05/05/2010', // m/d/Y from datepicker
				'expect' => 'active',
			),
		array(
				'now' => '2010-05-06',
				'expires' => '05/05/2010', // m/d/Y from datepicker
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

	}

	// public function test_get_coupon_amount() {}

	public function test_get_customer_name() {
		$first = 'Jeffrey';
		$last = 'Lebowski';
		$this->obj->set( 'billing_first_name', $first );
		$this->obj->set( 'billing_last_name', $last );
		$this->assertEquals( $first . ' ' . $last,  $this->obj->get_customer_name() );
	}

	public function test_get_gateway() {

		// gateway doesn't exist
		$this->obj->set( 'payment_gateway', 'garbage' );
		$this->assertTrue( is_a( $this->obj->get_gateway(), 'WP_Error' ) );

		// real gateway that's not enabled
		$this->obj->set( 'payment_gateway', 'manual' );
		$this->assertTrue( is_a( $this->obj->get_gateway(), 'WP_Error' ) );

		// enabled gateway responds with the gateway instance
		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $manual->get_option_name( 'enabled' ), 'yes' );
		$this->assertTrue( is_a( $this->obj->get_gateway(), 'LLMS_Payment_Gateway_Manual' ) );

	}

	public function test_get_initial_price() {

		// no trial
		$order = $this->get_order();
		$this->assertEquals( 25.99, $order->get_initial_price( array(), 'float' ) );

		// with trial
		$trial_plan = $this->get_plan( 25.99, 1, 'lifetime', false, true );
		$order = $this->get_order( $trial_plan );
		$this->assertEquals( 1.00, $order->get_initial_price( array(), 'float' ) );

	}

	public function test_get_notes() {

		$i = 1;
		while( $i <= 10 ) {

			$this->obj->add_note( sprintf( 'note %d', $i ) );
			$i++;

		}

		// remove filter so we can test order notes
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

	public function test_get_product() {

		$course = new LLMS_Course( 'new' );

		$this->obj->set( 'product_id', $course->get( 'id' ) );
		$this->assertTrue( is_a( $this->obj->get_product(), 'LLMS_Course' ) );

	}

	// public function test_get_last_transaction() {}

	// public function test_get_last_transaction_date() {}

	public function test_get_next_payment_due_date() {

		// one-time payments
		$plan = $this->get_plan( 25.99, 0 );
		$order = $this->get_order( $plan );
		$this->assertTrue( is_a( $order->get_next_payment_due_date(), 'WP_Error' ) );

		// recurring
		// $order = $this->get_order();
		// var_dump( $order->get_next_payment_due_date() );


	}

	// public function test_get_transaction_total() {}

	// public function test_get_start_date() {}

	// public function test_get_transactions() {}

	public function test_get_trial_end_date() {

		$this->obj->set( 'order_type', 'recurring' );

		// no trial so false for end date
		$this->assertFalse( $this->obj->get_trial_end_date() );

		// enable trial
		$this->obj->set( 'trial_offer', 'yes' );
		$start = $this->obj->get_start_date( 'U' );

		// run a bunch of tests
		foreach ( array( 'day', 'week', 'month', 'year' ) as $period ) {

			$this->obj->set( 'trial_period', $period );
			$i = 1;
			while ( $i <= 5 ) {

				$this->obj->set( 'trial_length', $i );
				$expect = strtotime( '+' . $i . ' ' . $period, $start );
				$this->assertEquals( $expect, $this->obj->get_trial_end_date( 'U' ) );
				$i++;

				// trial is not over
				$this->assertFalse( $this->obj->has_trial_ended() );

				// change date to future
				llms_mock_current_time( date( 'Y-m-d H:i:s', $this->obj->get_trial_end_date( 'U' ) + HOUR_IN_SECONDS ) );
				$this->assertTrue( $this->obj->has_trial_ended() );

				// return to real date
				llms_mock_current_time( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );

			}

		}

	}

	// public function test_get_revenue() {}

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

	// public function test_init() {}

	public function test_is_recurring() {

		$this->assertFalse( $this->obj->is_recurring() );
		$this->obj->set( 'order_type', 'recurring' );
		$this->assertTrue( $this->obj->is_recurring() );

	}

	// public function test_maybe_schedule_payment() {}

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

	// public function test_start_access() {}

	// public function test_unschedule_recurring_payment() {}

}
