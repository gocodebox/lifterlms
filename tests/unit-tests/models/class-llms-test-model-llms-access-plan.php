<?php
/**
 * Tests for LifterLMS Coupon Model
 * @group    access_plan
 * @since    3.23.0
 * @version  3.23.0
 */
class LLMS_Test_LLMS_Access_Plan extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Access_Plan';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'llms_access_plan';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.23.0
	 * @version  3.23.0
	 */
	protected function get_properties() {
		return array(
			'access_expiration' => 'string',
			'access_expires' => 'string',
			'access_length' => 'absint',
			'access_period' => 'string',
			'availability' => 'string',
			'availability_restrictions' => 'array',
			'enroll_text' => 'string',
			'frequency' => 'absint',
			'is_free' => 'yesno',
			'length' => 'absint',
			'menu_order' => 'absint',
			'on_sale' => 'yesno',
			'period' => 'string',
			'price' => 'float',
			'product_id' => 'absint',
			'sale_end' => 'string',
			'sale_start' => 'string',
			'sale_price' => 'float',
			'sku' => 'string',
			'title' => 'string',
			'trial_length' => 'absint',
			'trial_offer' => 'yesno',
			'trial_period' => 'string',
			'trial_price' => 'float',
		);
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.23.0
	 * @version  3.23.0
	 */
	protected function get_data() {
		return array(
			'access_expiration' => 'lifetime',
			'access_expires' => '01/01/2018',
			'access_length' => 2,
			'access_period' => 'year',
			'availability' => 'open', // members
			'availability_restrictions' => array(),
			'enroll_text' => 'Enroll Now',
			'frequency' => 0,
			'is_free' => 'no',
			'length' => 0,
			'menu_order' => 0,
			'on_sale' => 'no',
			'period' => 'year',
			'price' => 25.99,
			'product_id' => $this->factory->post->create( array(
				'post_type' => 'course',
			) ),
			'sale_end' => '2018-02-03',
			'sale_start' => '2018-01-15',
			'sale_price' => 5.99,
			'sku' => 'testsku',
			'title' => 'Access Plan Title',
			'trial_length' => 1,
			'trial_offer' => 'no',
			'trial_period' => 'week',
			'trial_price' => 1.99,
		);
	}

	protected function set_obj_product( $type = 'course' ) {
		$this->obj->set( 'product_id', $this->factory->post->create( array(
			'post_type' => $type,
		) ) );
	}

	public function setUp() {
		parent::setUp();
		$this->create();
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

	public function test_can_expire() {

		$opts = array(
			'' => true, // @todo empty values should return false
			'fake' => true, // @todo fake values should return false
			'lifetime' => false,
			'limited-period' => true,
			'limited-date' => true,
		);

		foreach ( $opts as $val => $expect ) {
			$this->obj->set( 'access_expiration', $val );
			$this->assertEquals( $expect, $this->obj->can_expire() );
		}

	}

	// public function test_get_access_period_name() {}

	public function test_get_checkout_url() {

		$this->set_obj_product();
		LLMS_Install::create_pages();

		// no restrictions
		$url = add_query_arg( 'plan', $this->obj->get( 'id' ), get_permalink( get_option( 'lifterlms_checkout_page_id' ) ) );
		$this->assertEquals( $url, $this->obj->get_checkout_url() );

		// 1 restriction returns link to that membership
		$membership_id = $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) );
		$this->obj->set( 'availability', 'members' );
		$this->obj->set( 'availability_restrictions', array( $membership_id ) );
		$this->assertEquals( get_permalink( $membership_id ), $this->obj->get_checkout_url() );

		// multiple returns the hash for popover display
		$this->obj->set( 'availability_restrictions', array( $membership_id, 1234 ) );
		$this->assertEquals( '#llms-plan-locked', $this->obj->get_checkout_url() );

		// bypass availability checks
		$this->assertEquals( $url, $this->obj->get_checkout_url( false ) );

	}

	public function test_get_free_pricing_text() {

		$text = '<span class="lifterlms-price">FREE</span>';
		$this->assertEquals( $text, $this->obj->get_free_pricing_text() );
		$this->assertEquals( $text, $this->obj->get_free_pricing_text( 'html' ) );
		$this->assertEquals( 0.00, $this->obj->get_free_pricing_text( 'float' ) );

	}

	public function test_get_price() {

		$prices = array(
			'price',
			'trial_price',
			'sale_price',
		);

		foreach ( $prices as $key ) {

			$this->obj->set( $key, 1.00 );
			$this->assertEquals( llms_price( 1.00 ), $this->obj->get_price( $key ) );
			$this->assertEquals( 1.00, $this->obj->get_price( $key, array(), 'float' ) );

			$this->obj->set( $key, 0.00 );
			$this->assertEquals( $this->obj->get_free_pricing_text(), $this->obj->get_price( $key ) );
			$this->assertEquals( 0.00, $this->obj->get_price( $key, array(), 'float' ) );

		}

	}

	// public function test_get_price_with_coupon() {}

	public function test_get_product() {

		$this->set_obj_product();
		$this->assertTrue( is_a( $this->obj->get_product(), 'LLMS_Product' ) );

	}

	public function test_get_product_type() {

		$this->set_obj_product();
		$this->assertEquals( 'course', $this->obj->get_product_type() );

		$this->set_obj_product( 'llms_membership' );
		$this->assertEquals( 'membership', $this->obj->get_product_type() );

	}

	public function test_get_enroll_text() {

		// course
		$this->set_obj_product();
		$this->assertEquals( 'Enroll', $this->obj->get_enroll_text() );

		// membership
		$this->set_obj_product( 'llms_membership' );
		$this->assertEquals( 'Join', $this->obj->get_enroll_text() );

		// custom
		$this->obj->set( 'enroll_text', 'DO SOMETHING!' );
		$this->assertEquals( 'DO SOMETHING!', $this->obj->get_enroll_text() );

	}

	public function test_get_expiration_details() {

		$this->assertEquals( '', $this->obj->get_expiration_details() );

		$this->obj->set( 'access_expiration', 'limited-date' );
		$this->assertTrue( 0 === strpos( $this->obj->get_expiration_details(), 'access until' ) );

		$this->obj->set( 'access_expiration', 'limited-period' );
		$this->assertTrue( false !== strpos( $this->obj->get_expiration_details(), 'of access' ) );

	}

	public function test_get_schedule_details() {

		$this->assertEquals( '', $this->obj->get_schedule_details() );

 		$this->obj->set( 'period', 'week' );
		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'length', 0 );

		$this->assertEquals( 'per week', $this->obj->get_schedule_details() );
		$this->assertTrue( false === strpos( $this->obj->get_schedule_details(), 'total payments' ) );

		$this->obj->set( 'frequency', 2 );
		$this->assertEquals( 'every 2 weeks', $this->obj->get_schedule_details() );


		$this->obj->set( 'length', 3 );
		$this->assertEquals( 'every 2 weeks for 3 total payments', $this->obj->get_schedule_details() );

	}

	public function test_get_trial_details() {

		$this->assertEquals( '', $this->obj->get_trial_details() );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'trial_offer', 'yes' );
		$this->obj->set( 'trial_length', 1 );
		$this->obj->set( 'trial_period', 'year' );

		$this->assertEquals( 'for 1 year', $this->obj->get_trial_details() );

	}

	public function test_visibility() {

		$this->assertEquals( 'visible', $this->obj->get_visibility() );

		$opts = array(
			'visible' => array(
				'is_featured' => false,
				'is_visible' => true,
			),
			'hidden'  => array(
				'is_featured' => false,
				'is_visible' => false,
			),
			'featured' => array(
				'is_featured' => true,
				'is_visible' => true,
			),
		);

		foreach ( $opts as $opt => $tests ) {
			$this->obj->set_visibility( $opt );
			$this->assertEquals( $opt, $this->obj->get_visibility() );
			foreach ( $tests as $func => $expect ) {
				$this->assertEquals( $expect, call_user_func( array( $this->obj, $func ) ) );
			}
		}

	}

	public function test_has_availability_restrictions() {

		$this->set_obj_product( 'llms_membership' );
		$this->assertFalse( $this->obj->has_availability_restrictions() );

		$this->set_obj_product();
		$this->assertFalse( $this->obj->has_availability_restrictions() );

		$this->obj->set( 'availability', 'members' );
		$this->assertFalse( $this->obj->has_availability_restrictions() );

		$this->obj->set( 'availability_restrictions', array( 12345 ) );
		$this->assertTrue( $this->obj->has_availability_restrictions() );

	}

	public function test_has_free_checkout() {

		$this->assertFalse( $this->obj->has_free_checkout() );

		$this->obj->set( 'is_free', 'no' );
		$this->assertFalse( $this->obj->has_free_checkout() );

		$this->obj->set( 'is_free', 'fake' );
		$this->assertFalse( $this->obj->has_free_checkout() );

		$this->obj->set( 'is_free', '' );
		$this->assertFalse( $this->obj->has_free_checkout() );

		$this->obj->set( 'is_free', 'yes' );
		$this->assertTrue( $this->obj->has_free_checkout() );

	}

	public function test_has_trial() {

		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'frequency', 0 );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'frequency', 1 );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'frequency', 1 );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'trial_offer', 'no' );
		$this->assertFalse( $this->obj->has_trial() );

		$this->obj->set( 'trial_offer', 'yes' );
		$this->assertTrue( $this->obj->has_trial() );

	}

	public function test_is_available_to_user() {

		$this->set_obj_product();
		$this->assertTrue( $this->obj->is_available_to_user() );

		$mid = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );

		$this->obj->set( 'availability', 'members' );
		$this->obj->set( 'availability_restrictions', array( $mid ) );

		$this->assertFalse( $this->obj->is_available_to_user() );

		// enroll the student
		$uid = $this->factory->user->create();
		llms_enroll_student( $uid, $mid );
		$this->assertTrue( $this->obj->is_available_to_user( $uid ) );

	}

	public function test_is_free() {

		$this->assertFalse( $this->obj->is_free() );

		$this->obj->set( 'is_free', 'no' );
		$this->assertFalse( $this->obj->is_free() );

		$this->obj->set( 'is_free', 'fake' );
		$this->assertFalse( $this->obj->is_free() );

		$this->obj->set( 'is_free', '' );
		$this->assertFalse( $this->obj->is_free() );

		$this->obj->set( 'is_free', 'yes' );
		$this->assertTrue( $this->obj->is_free() );

	}

	public function test_is_on_sale() {

		// no vals, not on sale
		$this->assertFalse( $this->obj->is_on_sale() );

		$now = current_time( 'timestamp' );
		$future = date( 'Y-m-d', strtotime( '+1 year', $now ) );
		$past = date( 'Y-m-d', strtotime( '-1 year', $now ) );
		$now = date( 'Y-m-d', $now );

		// on sale, no dates
		$this->obj->set( 'on_sale', 'yes' );
		$this->assertTrue( $this->obj->is_on_sale() );

		// start & end
		$this->obj->set( 'sale_start', $past );
		$this->obj->set( 'sale_end', $future );
		$this->assertTrue( $this->obj->is_on_sale() );

		// no start & has end
		$this->obj->set( 'sale_start', '' );
		$this->assertTrue( $this->obj->is_on_sale() );

		// has start & no end
		$this->obj->set( 'sale_start', $past );
		$this->obj->set( 'sale_end', '' );
		$this->assertTrue( $this->obj->is_on_sale() );

		// not on sale
		$this->obj->set( 'on_sale', 'no' );
		$this->assertFalse( $this->obj->is_on_sale() );

		// start in future
		$this->obj->set( 'on_sale', 'yes' );
		$this->obj->set( 'sale_start', $future );
		$this->obj->set( 'sale_end', '' );
		$this->assertFalse( $this->obj->is_on_sale() );

		// end in past
		$this->obj->set( 'on_sale', 'yes' );
		$this->obj->set( 'sale_start', '' );
		$this->obj->set( 'sale_end', $past );
		$this->assertFalse( $this->obj->is_on_sale() );

	}

	public function test_is_recurring() {

		$this->assertFalse( $this->obj->is_recurring() );

		$this->obj->set( 'frequency', 0 );
		$this->assertFalse( $this->obj->is_recurring() );

		$this->obj->set( 'frequency', 1 );
		$this->assertTrue( $this->obj->is_recurring() );

		$this->obj->set( 'frequency', 3 );
		$this->assertTrue( $this->obj->is_recurring() );

	}

	public function test_requires_payment() {

		// trial
		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'trial_offer', 'yes' );
		$this->assertFalse( $this->obj->requires_payment() );

		$this->obj->set( 'trial_price', 0 );
		$this->assertFalse( $this->obj->requires_payment() );

		$this->obj->set( 'trial_price', 1 );
		$this->assertTrue( $this->obj->requires_payment() );

		$this->obj->set( 'trial_offer', 'no' );

		// sale
		$this->obj->set( 'on_sale', 'yes' );
		$this->assertFalse( $this->obj->requires_payment() );

		$this->obj->set( 'sale_price', 0 );
		$this->assertFalse( $this->obj->requires_payment() );

		$this->obj->set( 'sale_price', 1 );
		$this->assertTrue( $this->obj->requires_payment() );

		$this->obj->set( 'on_sale', 'no' );

		// price
		$this->obj->set( 'price', 0 );
		$this->assertFalse( $this->obj->requires_payment() );

		$this->obj->set( 'price', 1 );
		$this->assertTrue( $this->obj->requires_payment() );

		// do it with a coupon
		$coupon_id = $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) );
		$coupon = llms_get_post( $coupon_id );
		$coupon_props = array(
			'coupon_amount' => 100,
			'discount_type' => 'percent',
			'enable_trial_discount' => 'yes',
			'trial_amount' => 100,
		);
		foreach ( $coupon_props as $key => $val ) {
			$coupon->set( $key, $val );
		}

		// trial
		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'trial_offer', 'yes' );
		$this->assertFalse( $this->obj->requires_payment( $coupon_id ) );

		$this->obj->set( 'trial_price', 1 );
		$this->assertFalse( $this->obj->requires_payment( $coupon_id ) );

		$coupon->set( 'trial_amount', 50 );
		$this->assertTrue( $this->obj->requires_payment( $coupon_id ) );

		$this->obj->set( 'trial_offer', 'no' );

		$this->obj->set( 'price', 1 );
		$this->assertFalse( $this->obj->requires_payment( $coupon_id ) );

		$coupon->set( 'coupon_amount', 50 );
		$this->assertTrue( $this->obj->requires_payment( $coupon_id ) );

		// free
		$this->obj->set( 'is_free', 'yes' );
		$this->assertFalse( $this->obj->requires_payment() );

	}

}
