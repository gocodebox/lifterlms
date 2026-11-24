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
	 * @since    3.21.1
	 * @version  3.21.1
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
	 * Test the is_expired() function.
	 *
	 * @since    3.2.2
	 * @since 6.0.0 Replaced use of the deprecated `llms_mock_current_time()` function
	 *              with `llms_tests_mock_current_time()` from the `lifterlms-tests` project.
	 *
	 * @return   void
	 */
	public function test_is_expired() {

		$this->create();

		// no date set so it's not expired
		$this->assertFalse( $this->obj->is_expired() );

		// date empty, not expired
		$this->obj->set( 'expiration_date', '' );
		$this->assertFalse( $this->obj->is_expired() );

		// should be expired
		llms_tests_mock_current_time( '2016-01-02' );
		$this->obj->set( 'expiration_date', '01/01/2016' );
		$this->assertTrue( $this->obj->is_expired() );

		// should not be expired
		llms_tests_mock_current_time( '2015-01-01' );
		$this->obj->set( 'expiration_date', '01/01/2016' );
		$this->assertFalse( $this->obj->is_expired() );

		// should expire end of day on expiration date
		llms_tests_mock_current_time( '2016-01-01 12:00pm' );
		$this->obj->set( 'expiration_date', '01/01/2016' );
		$this->assertFalse( $this->obj->is_expired() );

	}

	/**
	 * Creates a published 100%-off coupon that can be used once.
	 *
	 * @since 11.1.1
	 *
	 * @param string $code Coupon code.
	 * @return LLMS_Coupon
	 */
	private function create_single_use_coupon( $code ) {

		$coupon = new LLMS_Coupon( 'new', $code );
		$coupon->set( 'status', 'publish' );
		$coupon->set( 'coupon_amount', 100 );
		$coupon->set( 'discount_type', 'percent' );
		$coupon->set( 'plan_type', 'any' );
		$coupon->set( 'usage_limit', 1 );

		return $coupon;
	}

	/**
	 * Saves an order that used the coupon, as a concurrent checkout would.
	 *
	 * @since 11.1.1
	 *
	 * @param LLMS_Coupon $coupon Coupon.
	 * @return LLMS_Order
	 */
	private function create_order_with_coupon( $coupon ) {

		$order = new LLMS_Order( 'new' );
		$order->set( 'coupon_code', $coupon->get( 'title' ) );

		return $order;
	}

	/**
	 * Test with_usage_lock() runs the callback and releases the lock.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_with_usage_lock_runs_callback_and_releases_lock() {

		global $wpdb;

		$coupon = $this->create_single_use_coupon( 'lockrelease' );

		$lock = null;
		$spy  = function ( $query ) use ( &$lock ) {
			if ( preg_match( "/GET_LOCK\\( '([^']+)'/", $query, $m ) ) {
				$lock = $m[1];
			}
			return $query;
		};
		add_filter( 'query', $spy );

		$this->assertSame( 'done', $coupon->with_usage_lock( function () { return 'done'; } ) );

		try {
			$coupon->with_usage_lock(
				function () {
					throw new Exception( 'callback failed' );
				}
			);
			$this->fail( 'The exception was not rethrown.' );
		} catch ( Exception $e ) {
			$this->assertSame( 'callback failed', $e->getMessage() );
		}

		remove_filter( 'query', $spy );

		$this->assertNotNull( $lock );
		// IS_FREE_LOCK() returns 0 while any session, including this one, holds the lock.
		$this->assertSame( '1', $wpdb->get_var( $wpdb->prepare( 'SELECT IS_FREE_LOCK( %s )', $lock ) ) );
	}

	/**
	 * Test with_usage_lock() re-counts uses after the lock is taken.
	 *
	 * The order saved in the `query` filter stands in for a concurrent checkout that
	 * finishes after this request validated the coupon and before it saved its order.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_with_usage_lock_rechecks_remaining_uses() {

		$coupon = $this->create_single_use_coupon( 'lockrecheck' );
		$this->assertTrue( $coupon->has_remaining_uses() );

		$race = function ( $query ) use ( $coupon, &$race ) {
			if ( false !== strpos( $query, 'GET_LOCK' ) ) {
				remove_filter( 'query', $race );
				$this->create_order_with_coupon( $coupon );
			}
			return $query;
		};
		add_filter( 'query', $race );

		$ran = false;
		$res = $coupon->with_usage_lock(
			function () use ( &$ran ) {
				$ran = true;
			}
		);

		remove_filter( 'query', $race );

		$this->assertFalse( $ran );
		$this->assertWPErrorCodeEquals( 'llms-coupon-usage-limit', $res );
		$this->assertSame( 1, $coupon->get_uses() );
	}

	/**
	 * Databases that can't provide the usage lock.
	 *
	 * @since 11.1.1
	 *
	 * @return array[]
	 */
	public function provider_lock_unavailable() {
		return array(
			'not supported, the query fails' => array( 'SELECT GET_LOCK_UNSUPPORTED()' ),
			'refused, the query returns NULL' => array( 'SELECT NULL' ),
			'held elsewhere until the timeout' => array( 'SELECT 0' ),
		);
	}

	/**
	 * Test with_usage_lock() re-checks the remaining uses without the lock when the lock can't be acquired.
	 *
	 * @since 11.1.1
	 *
	 * @dataProvider provider_lock_unavailable
	 *
	 * @param string $replacement SQL run in place of the lock query.
	 * @return void
	 */
	public function test_with_usage_lock_falls_back_when_lock_not_acquired( $replacement ) {

		global $wpdb;

		$coupon = $this->create_single_use_coupon( 'locktimeout' );

		$queries = array();
		$fail    = function ( $query ) use ( &$queries, $replacement ) {
			$queries[] = $query;
			return false !== strpos( $query, 'GET_LOCK' ) ? $replacement : $query;
		};
		add_filter( 'query', $fail );

		// A database error that isn't suppressed would be printed into the checkout response.
		$show = $wpdb->show_errors();
		ob_start();

		// The coupon has a use left: the callback runs.
		$first = $coupon->with_usage_lock( function () { return 'done'; } );

		// The coupon is used up: the re-check still blocks it.
		$this->create_order_with_coupon( $coupon );
		$ran = false;
		$res = $coupon->with_usage_lock(
			function () use ( &$ran ) {
				$ran = true;
			}
		);

		$output = ob_get_clean();
		$wpdb->show_errors( $show );
		remove_filter( 'query', $fail );

		$this->assertSame( 'done', $first );
		$this->assertFalse( $ran );
		$this->assertWPErrorCodeEquals( 'llms-coupon-usage-limit', $res );
		$this->assertSame( '', $output );
		$this->assertCount( 2, preg_grep( '/GET_LOCK/', $queries ) );
		$this->assertEmpty( preg_grep( '/RELEASE_LOCK/', $queries ) );
	}

	/**
	 * Test with_usage_lock() runs the callback when the lock is turned off.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_with_usage_lock_runs_callback_when_lock_is_off() {

		$coupon = $this->create_single_use_coupon( 'lockoff' );

		$queries = array();
		$spy     = function ( $query ) use ( &$queries ) {
			$queries[] = $query;
			return $query;
		};
		add_filter( 'query', $spy );
		add_filter( 'llms_lock_timeout', '__return_zero' );

		$first = $coupon->with_usage_lock( function () { return 'done'; } );
		$this->create_order_with_coupon( $coupon );
		$second = $coupon->with_usage_lock( function () { return 'done'; } );

		remove_filter( 'llms_lock_timeout', '__return_zero' );
		remove_filter( 'query', $spy );

		$this->assertSame( 'done', $first );
		$this->assertWPErrorCodeEquals( 'llms-coupon-usage-limit', $second );
		$this->assertEmpty( preg_grep( '/(GET|RELEASE)_LOCK/', $queries ) );
	}

	/**
	 * Test with_usage_lock() runs the callback without a lock for coupons without a usage limit.
	 *
	 * @since 11.1.1
	 *
	 * @return void
	 */
	public function test_with_usage_lock_skips_lock_without_usage_limit() {

		$coupon = $this->create_single_use_coupon( 'lockunlimited' );
		$coupon->set( 'usage_limit', '' );
		$this->create_order_with_coupon( $coupon );

		$queries = array();
		$spy     = function ( $query ) use ( &$queries ) {
			$queries[] = $query;
			return $query;
		};
		add_filter( 'query', $spy );

		$res = $coupon->with_usage_lock( function () { return 'done'; } );

		remove_filter( 'query', $spy );

		$this->assertSame( 'done', $res );
		$this->assertEmpty( preg_grep( '/GET_LOCK/', $queries ) );
	}

}
