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
		$this->create();
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
		}

	}

}
