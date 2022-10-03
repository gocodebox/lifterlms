<?php
/**
 * Tests for LifterLMS Coupon Model
 *
 * @package  LifterLMS_Tests/Models
 *
 * @group access_plan
 *
 * @since 3.23.0
 */
class LLMS_Test_LLMS_Access_Plan extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var string
	 */
	protected $class_name = 'LLMS_Access_Plan';

	/**
	 * DB post type of the model being tested
	 *
	 * @var string
	 */
	protected $post_type = 'llms_access_plan';

	/**
	 * Get properties, used by test_getters_setters
	 *
	 * This should match, exactly, the object's $properties array
	 *
	 * @since 3.23.0
	 *
	 * @return array
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
	 *
	 * This is used by test_getters_setters
	 *
	 * @since 3.23.0
	 *
	 * @return array
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

	/**
	 * Setup plan product association.
	 *
	 * @since 3.23.0
	 *
	 * @param string $type Associated post type.
	 *
	 * @return void
	 */
	protected function set_obj_product( $type = 'course' ) {
		$this->obj->set( 'product_id', $this->factory->post->create( array(
			'post_type' => $type,
		) ) );
	}

	/**
	 * Setup the test case
	 *
	 * @since 3.23.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->create();
	}

	/**
	 * Test can_expire()
	 *
	 * @since 3.23.0
	 *
	 * @return void
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

	/**
	 * Override to prevent output of skipped test since the test doesn't matter for this class.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_edit_date() {
		$this->assertTrue( true );
	}

	/**
	 * Test get_access_period_name()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_access_period_name() {

		//  Use values from the plan.
		$this->obj->set( 'access_period', 'week' );
		$this->obj->set( 'access_length', 2 );
		$this->assertEquals( 'weeks', $this->obj->get_access_period_name() );

		// Pass in values.
		$this->assertEquals( 'day', $this->obj->get_access_period_name( 'day', 1 ) );
		$this->assertEquals( 'month', $this->obj->get_access_period_name( 'month', 1 ) );
		$this->assertEquals( 'years', $this->obj->get_access_period_name( 'years', 25 ) );

	}

	/**
	 * Test get_checkout_url()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test get_checkout_url() with redirection.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_get_checkout_url_with_redirection() {

		$this->set_obj_product();
		LLMS_Install::create_pages();

		// No restrictions.
		$url = add_query_arg( 'plan', $this->obj->get( 'id' ), get_permalink( get_option( 'lifterlms_checkout_page_id' ) ) );
		$this->assertEquals( $url, $this->obj->get_checkout_url() );

		// Add redirect.
		$this->obj->set( 'checkout_redirect_type', 'url' );
		$this->obj->set( 'checkout_redirect_url', 'https://example.com' );
		// No redirect query arg added to the checkout url, the redirect will be added to the checkout form as hidden input field.
		$this->assertEquals( $url, $this->obj->get_checkout_url() );

		// 1 restriction returns link to that membership.
		$membership_id = $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) );
		$this->obj->set( 'availability', 'members' );
		$this->obj->set( 'availability_restrictions', array( $membership_id ) );
		// No redirect query arg added to the checkout url, the redirect will be added to the checkout form as hidden input field.
		$this->assertEquals( get_permalink( $membership_id ), $this->obj->get_checkout_url() );

		// Force the redirect via INPUT_GET
		$this->mockGetRequest(
			array(
				'redirect' => 'https://example-redirect-get.com',
			)
		);
		// Expect the redirect URL via INPUT_GET to be added to the membership's permalink.
		$this->assertEquals(
			add_query_arg(
				'redirect',
				urlencode( 'https://example-redirect-get.com' ),
				get_permalink( $membership_id )
			),
			$this->obj->get_checkout_url()
		);

		// Enable the option that forces the access plan redirection settings to take over the membership redirections.
		$this->obj->set( 'checkout_redirect_forced', 'yes' );
		// The INPUT_GET will win.
		// Expect the redirect URL to be added to the membership's permalink.
		$this->assertEquals(
			add_query_arg(
				'redirect',
				urlencode( 'https://example-redirect-get.com' ),
				get_permalink( $membership_id )
			),
			$this->obj->get_checkout_url()
		);

		// Reset the INPUT_GET
		$this->mockGetRequest( array() );
		// Expect the redirect URL to be added to the membership's permalink.
		$this->assertEquals(
			add_query_arg(
				'redirect',
				urlencode( $this->obj->get( 'checkout_redirect_url' ) ),
				get_permalink( $membership_id )
			),
			$this->obj->get_checkout_url()
		);

		// Multiple returns the hash for popover display.
		$this->obj->set( 'availability_restrictions', array( $membership_id, 1234 ) );
		$this->assertEquals( '#llms-plan-locked', $this->obj->get_checkout_url() );

		// Bypass availability checks.
		$this->assertEquals( $url, $this->obj->get_checkout_url( false ) );

	}

	/**
	 * Test get_free_pricing_text()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_get_free_pricing_text() {

		$text = '<span class="lifterlms-price">FREE</span>';
		$this->assertEquals( $text, $this->obj->get_free_pricing_text() );
		$this->assertEquals( $text, $this->obj->get_free_pricing_text( 'html' ) );
		$this->assertEquals( 0.00, $this->obj->get_free_pricing_text( 'float' ) );

	}

	/**
	 * Test the get_initial_price() method.
	 *
	 * @since 3.30.1
	 *
	 * @return void
	 */
	public function test_get_initial_price() {

		// trial w/ no price
		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'trial_offer', 'yes' );
		$this->assertSame( 0.00, $this->obj->get_initial_price() );

		// free trial.
		$this->obj->set( 'trial_price', 0 );
		$this->assertSame( 0.00, $this->obj->get_initial_price() );

		// paid trial.
		$this->obj->set( 'trial_price', 1 );
		$this->assertSame( 1.00, $this->obj->get_initial_price() );


		// disable the trial.
		$this->obj->set( 'trial_offer', 'no' );


		// No sale price set.
		$this->obj->set( 'on_sale', 'yes' );
		$this->assertSame( 0.00, $this->obj->get_initial_price() );

		// on sale for free.
		$this->obj->set( 'sale_price', 0 );
		$this->assertSame( 0.00, $this->obj->get_initial_price() );

		// paid sale.
		$this->obj->set( 'sale_price', 1 );
		$this->assertSame( 1.00, $this->obj->get_initial_price() );


		// disable the sale.
		$this->obj->set( 'on_sale', 'no' );


		// free.
		$this->obj->set( 'price', 0 );
		$this->assertSame( 0.00, $this->obj->get_initial_price() );

		$this->obj->set( 'price', 2 );
		$this->assertSame( 2.00, $this->obj->get_initial_price() );

	}

	/**
	 * Test the get_initial_price() method when using coupons.
	 *
	 * @since 3.30.1
	 *
	 * @return void
	 */
	public function test_get_initial_price_with_coupon() {

		$coupon_id = $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) );
		$coupon = llms_get_post( $coupon_id );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );
		$coupon->set( 'enable_trial_discount', 'yes' );
		$coupon->set( 'trial_amount', 100 );


		// Trial 100% discount.
		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'trial_offer', 'yes' );
		$this->obj->set( 'trial_price', 1 );
		$this->assertSame( 0.00, $this->obj->get_initial_price( array(), $coupon_id ) );

		// Trial 50% discount.
		$coupon->set( 'trial_amount', 50 );
		$this->assertSame( 0.50, $this->obj->get_initial_price( array(), $coupon_id ) );

		// No trial offer.
		$this->obj->set( 'trial_offer', 'no' );

		// Free with coupon.
		$this->obj->set( 'price', 10 );
		$this->assertSame( 0.00, $this->obj->get_initial_price( array(), $coupon_id ) );

		// 50% off coupon.
		$coupon->set( 'coupon_amount', 50 );
		$this->assertSame( 5.00, $this->obj->get_initial_price( array(), $coupon_id ) );

		// free with coupon.
		$this->obj->set( 'is_free', 'yes' );
		$this->assertSame( 0.00, $this->obj->get_initial_price( array(), $coupon_id) );

	}

	/**
	 * Test get_price()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test get_product()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_get_product() {

		$this->set_obj_product();
		$this->assertTrue( is_a( $this->obj->get_product(), 'LLMS_Product' ) );

	}

	/**
	 * Test get_product_type()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_get_product_type() {

		$this->set_obj_product();
		$this->assertEquals( 'course', $this->obj->get_product_type() );

		$this->set_obj_product( 'llms_membership' );
		$this->assertEquals( 'membership', $this->obj->get_product_type() );

	}

	/**
	 * Test get_enroll_text()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test get_expiration_details()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_get_expiration_details() {

		$this->assertEquals( '', $this->obj->get_expiration_details() );

		$this->obj->set( 'access_expiration', 'limited-date' );
		$this->assertTrue( 0 === strpos( $this->obj->get_expiration_details(), 'access until' ) );

		$this->obj->set( 'access_expiration', 'limited-period' );
		$this->assertTrue( false !== strpos( $this->obj->get_expiration_details(), 'of access' ) );

	}

	/**
	 * Test get_schedule_details()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test get_trial_details()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_get_trial_details() {

		$this->assertEquals( '', $this->obj->get_trial_details() );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'trial_offer', 'yes' );
		$this->obj->set( 'trial_length', 1 );
		$this->obj->set( 'trial_period', 'year' );

		$this->assertEquals( 'for 1 year', $this->obj->get_trial_details() );

	}

	/**
	 * Test visibility getters / setters
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test has_availability_restrictions()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test has_free_checkout()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test has_trial()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test is_available_to_user()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test is_free()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

	/**
	 * Test is_on_sale()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
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

		// test on sale end at 00:00 of $future day plus 1
		$this->obj->set( 'on_sale', 'yes' );
		$this->obj->set( 'sale_end', $future );

		// set current current time as last second of $future day
		llms_tests_mock_current_time( strtotime( $future . ' 23:59:59' ) );
		$this->assertTrue( $this->obj->is_on_sale() );

		// set current current time as first second of $future day plus 1
		llms_tests_mock_current_time( strtotime( '+1 day', strtotime( $future . ' 00:00:00' ) ) );
		$this->assertFalse( $this->obj->is_on_sale() );

	}

	/**
	 * Test is_recurring()
	 *
	 * @since 3.23.0
	 *
	 * @return void
	 */
	public function test_is_recurring() {

		$this->assertFalse( $this->obj->is_recurring() );

		$this->obj->set( 'frequency', 0 );
		$this->assertFalse( $this->obj->is_recurring() );

		$this->obj->set( 'frequency', 1 );
		$this->assertTrue( $this->obj->is_recurring() );

		$this->obj->set( 'frequency', 3 );
		$this->assertTrue( $this->obj->is_recurring() );

	}

	/**
	 * Test requires_payment(): free plan
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_free() {

		$this->obj->set( 'is_free', 'yes' );
		$this->assertFalse( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): one-time payment
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time() {

		$this->obj->set( 'price', 1 );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): one-time payment with a paid sale
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time_sale() {

		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): one-time payment with a free sale
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time_sale_free() {

		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 0 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertFalse( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): one-time payment with a sale and a coupon
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time_sale_coupon() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 50 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): one-time payment with a sale and a coupon that discounts price to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time_sale_coupon_free() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertFalse( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): one-time payment with a coupon
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time_coupon() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 50 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'price', 2 );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): one-time payment with a coupon that discounts the price to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_one_time_coupon_free() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'price', 2 );

		$this->assertFalse( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 1 );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with sale
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_sale() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with sale reducing price to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_sale_free() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 0 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertFalse( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with sale and coupon
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_sale_coupon() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 50 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with sale and coupon reducing price to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_sale_coupon_free() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );

		$this->assertFalse( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with paid trial
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_free() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 0 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and a coupon
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_coupon() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 50 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and a coupon discounting recurring price to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_coupon_free() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and a coupon that discounts both recurring and trial payments
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_coupon_trial_coupon_discount() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 50 );
		$coupon->set( 'discount_type', 'percent' );
		$coupon->set( 'enable_trial_discount', 'yes' );
		$coupon->set( 'trial_amount', 50 );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and a coupon that discounts both recurring and trial payments
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_coupon_free_trial_coupon_discount() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );
		$coupon->set( 'enable_trial_discount', 'yes' );
		$coupon->set( 'trial_amount', 50 );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and a coupon that discounts both recurring and trial payments
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_coupon_trial_coupon_free() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 50 );
		$coupon->set( 'discount_type', 'percent' );
		$coupon->set( 'enable_trial_discount', 'yes' );
		$coupon->set( 'trial_amount', 100 );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and a coupon that discounts both recurring and trial payments to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_coupon_free_trial_coupon_free() {

		$coupon = llms_get_post( $this->factory->post->create( array( 'post_type' => 'llms_coupon' ) ) );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );
		$coupon->set( 'enable_trial_discount', 'yes' );
		$coupon->set( 'trial_amount', 100 );

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertFalse( $this->obj->requires_payment( $coupon ) );

	}

	/**
	 * Test requires_payment(): recurring payment with paid trial and sale
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_sale() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 1 );
		$this->obj->set( 'on_sale', 'yes' );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with paid trial and sale reducing recurring payment to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_sale_free() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 0 );
		$this->obj->set( 'on_sale', 'yes' );
		$this->obj->set( 'trial_price', 1 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertTrue( $this->obj->requires_payment() );

	}

	/**
	 * Test requires_payment(): recurring payment with free trial and sale reducing recurring payment to free
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_requires_payment_recurring_trial_free_sale_free() {

		$this->obj->set( 'frequency', 1 );
		$this->obj->set( 'price', 2 );
		$this->obj->set( 'sale_price', 0 );
		$this->obj->set( 'on_sale', 'yes' );
		$this->obj->set( 'trial_price', 0 );
		$this->obj->set( 'trial_offer', 'yes' );

		$this->assertFalse( $this->obj->requires_payment() );

	}

	/**
	 * Test method `get_redirection_url()` when there's no 'redirect' $_GET variable.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_get_redirection_url_no_input_get() {

		$this->set_obj_product( 'course' );
		$this->obj->set( 'checkout_redirect_type', 'url' );
		$this->obj->set( 'checkout_redirect_url', 'https://example.com' );

		// Expect the encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url(), urlencode( $this->obj->get( 'checkout_redirect_url' ) ) );
		// Require only querystring, expect empty string.
		$this->assertEmpty( $this->obj->get_redirection_url( true, true ) );

		// Expect the not-encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url( false ), $this->obj->get( 'checkout_redirect_url' ) );
		// Require only querystring, expect empty string.
		$this->assertEmpty( $this->obj->get_redirection_url( false, true ) );


		$this->obj->set( 'checkout_redirect_type', 'self' ); // Default.
		$this->obj->set( 'checkout_redirect_url', '' );

		// Expect empty string.
		$this->assertEmpty( $this->obj->get_redirection_url() );
		$this->assertEmpty( $this->obj->get_redirection_url( true, false ) );
	}

	/**
	 * Test method `get_redirection_url()` when there's a 'redirect' $_GET variable.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_get_redirection_url_with_input_get() {

		$this->set_obj_product( 'course' );

		$this->mockGetRequest(
			array(
				'redirect' => '',
			)
		);
		// Expect empty string.
		$this->assertEmpty( $this->obj->get_redirection_url() );

		$this->mockGetRequest(
			array(
				'redirect' => 'https://example-redirect-get.com',
			)
		);
		// Expect the encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url(), urlencode( 'https://example-redirect-get.com' ) );
		// Require only querystring, expect the encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url( true, true ), urlencode( 'https://example-redirect-get.com' ) );

		// Expect the not-encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url( false ), 'https://example-redirect-get.com' );
		// Require only querystring, expect the not-encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url( false, true ), 'https://example-redirect-get.com' );

		// Set access plan redirect options, still the $_GET variable will win.
		$this->obj->set( 'checkout_redirect_type', 'url' );
		$this->obj->set( 'checkout_redirect_url', 'https://example.com' );

		// Expect the encoded url.
		$this->assertEquals( $this->obj->get_redirection_url(), urlencode( 'https://example-redirect-get.com' ) );
		// Require only querystring, expect the encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url( true, true ), urlencode( 'https://example-redirect-get.com' ) );

		// Expect the not-encoded url.
		$this->assertEquals( $this->obj->get_redirection_url( false ), 'https://example-redirect-get.com' );
		// Require only querystring, expect the not-encoded URL.
		$this->assertEquals( $this->obj->get_redirection_url( false, true ), 'https://example-redirect-get.com' );

		$this->obj->set( 'checkout_redirect_type', 'self' ); // Default.
		$this->obj->set( 'checkout_redirect_url', '' );

		$this->mockGetRequest(
			array(
				'redirect' => '',
			)
		);

		// Expect empty string.
		$this->assertEmpty( $this->obj->get_redirection_url() );
		$this->assertEmpty( $this->obj->get_redirection_url( true, false ) );

	}

}
