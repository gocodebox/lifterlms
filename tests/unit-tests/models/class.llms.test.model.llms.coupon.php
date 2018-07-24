<?php
/**
 * Tests for LifterLMS Coupon Model
 * @group    coupons
 * @since    3.4.0
 * @version  3.19.0
 */
class LLMS_Test_LLMS_Coupon extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Coupon';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'llms_coupon';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function get_properties() {
		return array(
			'coupon_amount' => 'float',
			'coupon_courses' => 'array',
			'coupon_membership' => 'array',
			'description' => 'string',
			'discount_type' => 'string',
			'enable_trial_discount' => 'yesno',
			'expiration_date' => 'string',
			'plan_type' => 'string',
			'trial_amount' => 'float',
			'usage_limit' => 'absint',
		);
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function get_data() {
		return array(
			'coupon_amount' => 50,
			'coupon_courses' => array(),
			'coupon_membership' => array(),
			'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
			'discount_type' => 'percent',
			'enable_trial_discount' => 'no',
			'expiration_date' => '02/17/2017',
			'plan_type' => 'any',
			'trial_amount' => 5,
			'usage_limit' => 25,
		);
	}


	/*
		   /$$                           /$$
		  | $$                          | $$
		 /$$$$$$    /$$$$$$   /$$$$$$$ /$$$$$$   /$$$$$$$
		|_  $$_/   /$$__  $$ /$$_____/|_  $$_/  /$$_____/
		  | $$    | $$$$$$$$|  $$$$$$   | $$   |  $$$$$$
		  | $$ /$$| $$_____/ \____  $$  | $$ /$$\____  $$
		  |  $$$$/|  $$$$$$$ /$$$$$$$/  |  $$$$//$$$$$$$/
		   \___/   \_______/|_______/    \___/ |_______/
	*/

	/**
	 * Test the get expiration time function
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_get_expiration_time() {

		$this->create();

		// no expiration date
		$this->obj->set( 'expiration_date', '' );
		$this->assertFalse( $this->obj->get_expiration_time() );

		$dates = array(
			'02/28/2018',
			'01/31/2015',
			'12/31/2016',
			'05/05/2015',
		);

		foreach ( $dates as $date ) {
			$this->obj->set( 'expiration_date', $date );
			$this->assertEquals( ( strtotime( $date ) + DAY_IN_SECONDS - 1 ), $this->obj->get_expiration_time() );
		}


	}

	/**
	 * Test get_products() function
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function test_get_products() {
		$this->create();
		$this->obj->set( 'coupon_courses', array( 1, 2, 3 ) );
		$this->obj->set( 'coupon_membership', array( 4, 5, 6 ) );
		$this->assertEquals( array( 1, 2, 3, 4, 5, 6 ), $this->obj->get_products() );
	}

	/**
	 * test the has_main_discount() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_has_main_discount() {

		$this->create();

		// not set
		$this->assertFalse( $this->obj->has_main_discount() );

		// set to various positive numbers
		$amounts = array(
			'1', 1, '1.00', 1.00, 200, 2934234, 234.32, 0.50, '0.99'
		);
		foreach ( $amounts as $amount ) {
			$this->obj->set( 'coupon_amount', $amount );
			$this->assertTrue( $this->obj->has_main_discount() );
		}

		// 0 amounts
		$amounts = array(
			0, false, '', '0', '0.00', null, 0.00, '.00', 'no', 'arst',
		);
		foreach ( $amounts as $amount ) {
			$this->obj->set( 'coupon_amount', $amount );
			$this->assertFalse( $this->obj->has_main_discount() );
		}

	}

	/**
	 * Test has_trial_discount() function
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	public function test_has_trial_discount() {

		$this->create();

		// trial discount enabled
		$this->obj->set( 'enable_trial_discount', 'yes' );
		$this->assertTrue( $this->obj->has_trial_discount() );

		// trial discount not enabled
		$this->obj->set( 'enable_trial_discount', 'no' );
		$this->assertFalse( $this->obj->has_trial_discount() );
		$this->obj->set( 'enable_trial_discount', '' );
		$this->assertFalse( $this->obj->has_trial_discount() );
		$this->obj->set( 'enable_trial_discount', 'string' );
		$this->assertFalse( $this->obj->has_trial_discount() );

	}

	/**
	 * Test is_expired function
	 * @return   void
	 * @since    3.2.2
	 * @version  3.19.0
	 */
	public function test_is_expired() {

		$this->create();

		// no date set so it's not expired
		$this->assertFalse( $this->obj->is_expired() );

		// date empty, not expired
		$this->obj->set( 'expiration_date', '' );
		$this->assertFalse( $this->obj->is_expired() );

		// should be expired
		llms_mock_current_time( '2016-01-02' );
		$this->obj->set( 'expiration_date', '01/01/2016' );
		$this->assertTrue( $this->obj->is_expired() );

		// should not be expired
		llms_mock_current_time( '2015-01-01' );
		$this->obj->set( 'expiration_date', '01/01/2016' );
		$this->assertFalse( $this->obj->is_expired() );

		// should expire end of day on expiration date
		llms_mock_current_time( '2016-01-01 12:00pm' );
		$this->obj->set( 'expiration_date', '01/01/2016' );
		$this->assertFalse( $this->obj->is_expired() );

	}

}
