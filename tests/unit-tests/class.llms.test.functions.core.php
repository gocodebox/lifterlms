<?php
/**
 * Tests for LifterLMS Core Functions
 * @since    3.3.1
 * @version  3.3.1
 */
class LLMS_Test_Functions_Core extends LLMS_UnitTestCase {

	/**
	 * Test llms_are_terms_and_conditions_required()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_are_terms_and_conditions_required() {

		// terms true & page id numeric
		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', '1' );
		$this->assertTrue( llms_are_terms_and_conditions_required() );

		// terms true & page id non-numeric
		update_option( 'lifterlms_registration_require_agree_to_terms', 'yes' );
		update_option( 'lifterlms_terms_page_id', 'brick' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		// terms true & no page id
		update_option( 'lifterlms_terms_page_id', '' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		// terms true & page id 0
		update_option( 'lifterlms_terms_page_id', '0' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		// terms false and page id good
		update_option( 'lifterlms_registration_require_agree_to_terms', 'no' );
		update_option( 'lifterlms_terms_page_id', '1' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

		update_option( 'lifterlms_registration_require_agree_to_terms', 'no' );
		update_option( 'lifterlms_terms_page_id', 'brick' );
		$this->assertFalse( llms_are_terms_and_conditions_required() );

	}

	/**
	 * Test llms_get_core_supported_themes()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_core_supported_themes() {

		$this->assertFalse( empty( llms_get_core_supported_themes() ) );
		$this->assertTrue( is_array( llms_get_core_supported_themes() ) );

	}

	/**
	 * test llms_get_date_diff()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_date_diff() {

		$this->assertEquals( '18 days', llms_get_date_diff( '2016-05-12', '2016-05-30' ) );
		$this->assertEquals( '1 year, 2 months', llms_get_date_diff( '2016-01-01', '2017-03-25 23:32:32' ) );
		$this->assertEquals( '10 months, 14 days', llms_get_date_diff( '2016-01-01', '2016-11-15' ) );
		$this->assertEquals( '4 years, 24 days', llms_get_date_diff( '2013-03-01', '2017-03-25' ) );
		$this->assertEquals( '3 years, 10 months', llms_get_date_diff( '2013-03-01', '2017-01-25' ) );
		$this->assertEquals( '24 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:01:25' ) );
		$this->assertEquals( '1 second', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:01:02' ) );
		$this->assertEquals( '59 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:02:00' ) );
		$this->assertEquals( '1 minute, 44 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:02:45' ) );
		$this->assertEquals( '1 minute, 14 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:02:15' ) );
		$this->assertEquals( '3 minutes, 59 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:05:00' ) );
		$this->assertEquals( '44 minutes, 33 seconds', llms_get_date_diff( '2016-05-12 01:01:01', '2016-05-12 01:45:34' ) );
		$this->assertEquals( '44 minutes, 33 seconds', llms_get_date_diff( '2016-05-12 01:45:34', '2016-05-12 01:01:01' ) );

	}

	/**
	 * test llms_get_engagement_triggers()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_engagement_triggers() {
		$this->assertFalse( empty( llms_get_engagement_triggers() ) );
		$this->assertTrue( is_array( llms_get_engagement_triggers() ) );
	}

	/**
	 * test llms_get_engagement_types()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_engagement_types() {
		$this->assertFalse( empty( llms_get_engagement_types() ) );
		$this->assertTrue( is_array( llms_get_engagement_types() ) );
	}

	/**
	 * Test llms_find_coupon()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_find_coupon() {

		// create a coupon
		$id = $this->factory->post->create( array(
			'post_title' => 'coopond',
			'post_type' => 'llms_coupon',
		) );
		$this->assertEquals( $id, llms_find_coupon( 'coopond' ) );

		// create a dup
		$dup = $this->factory->post->create( array(
			'post_title' => 'coopond',
			'post_type' => 'llms_coupon',
		) );
		$this->assertEquals( $dup, llms_find_coupon( 'coopond' ) );

		// test dupcheck
		$this->assertEquals( $id, llms_find_coupon( 'coopond', $dup ) );

		// delete the coupon
		wp_delete_post( $id );
		wp_delete_post( $dup );
		$this->assertEmpty( llms_find_coupon( 'coopond' ) );

	}

	/**
	 * test llms_get_enrollment_statuses()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_enrollment_statuses() {
		$this->assertFalse( empty( llms_get_enrollment_statuses() ) );
		$this->assertTrue( is_array( llms_get_enrollment_statuses() ) );
	}

	/**
	 * Test llms_get_enrollment_status_name()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_enrollment_status_name() {
		$this->assertNotEquals( 'asrt', llms_get_enrollment_status_name( 'cancelled' ) );
		$this->assertNotEquals( 'cancelled', llms_get_enrollment_status_name( 'Cancelled' ) );
		$this->assertEquals( 'Cancelled', llms_get_enrollment_status_name( 'cancelled' ) );
		$this->assertEquals( 'Cancelled', llms_get_enrollment_status_name( 'Cancelled' ) );
		$this->assertEquals( 'wut', llms_get_enrollment_status_name( 'wut' ) );
	}

	/**
	 * Test llms_get_order_status_name()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_order_status_name() {
		$this->assertNotEmpty( llms_get_order_status_name( 'llms-active' ) );
		$this->assertEquals( 'Active', llms_get_order_status_name( 'llms-active' ) );
		$this->assertEquals( 'wut', llms_get_order_status_name( 'wut' ) );
	}

	/**
	 * test llms_get_order_statuses()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_order_statuses() {

		$this->assertTrue( is_array( llms_get_order_statuses() ) );
		$this->assertFalse( empty( llms_get_order_statuses() ) );
		$this->assertEquals( array(
			'llms-active',
			'llms-cancelled',
			'llms-completed',
			'llms-expired',
			'llms-failed',
			'llms-pending',
			'llms-refunded',
		), array_keys( llms_get_order_statuses() ) );

		$this->assertTrue( is_array( llms_get_order_statuses( 'recurring' ) ) );
		$this->assertFalse( empty( llms_get_order_statuses( 'recurring' ) ) );
		$this->assertEquals( array(
			'llms-active',
			'llms-cancelled',
			'llms-expired',
			'llms-failed',
			'llms-pending',
			'llms-refunded',
		), array_keys( llms_get_order_statuses( 'recurring' ) ) );

		$this->assertTrue( is_array( llms_get_order_statuses( 'single' ) ) );
		$this->assertFalse( empty( llms_get_order_statuses( 'single' ) ) );
		$this->assertEquals( array(
			'llms-cancelled',
			'llms-completed',
			'llms-failed',
			'llms-pending',
			'llms-refunded',
		), array_keys( llms_get_order_statuses( 'single' ) ) );

	}

	/**
	 * Test llms_get_post()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_post() {

		$types = array(
			'llms_access_plan',
			'llms_coupon',
			'course',
			'lesson',
			'llms_membership',
			'llms_order',
			'llms_quiz',
			'llms_question',
			'llms_section',
			'llms_transaction',
		);

		foreach ( $types as $type ) {

			$id = $this->factory->post->create( array(
				'post_type' => 'course',
			) );
			$this->assertInstanceOf( 'LLMS_Course', llms_get_post( $id ) );

		}

		$this->assertInstanceOf( 'WP_Post', llms_get_post( $this->factory->post->create() ) );
		$this->assertFalse( llms_get_post( 'fail' ) );
		$this->assertFalse( llms_get_post( 0 ) );

	}


	/**
	 * test llms_get_transaction_statuses()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_transaction_statuses() {
		$this->assertFalse( empty( llms_get_transaction_statuses() ) );
		$this->assertTrue( is_array( llms_get_transaction_statuses() ) );
	}

	/**
	 * Test llms_is_site_https()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_is_site_https() {
		update_option( 'home', 'https://is.ssl' );
		$this->assertTrue( llms_is_site_https() );

		update_option( 'home', 'http://is.ssl' );
		$this->assertFalse( llms_is_site_https() );
	}

	/**
	 * Test llms_trim_string()
	 * @return   [type]     [description]
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function llms_trim_string() {

		$this->assertEquals( 'yasssss', wc_trim_string( 'yasssss' ) );
		$this->assertEquals( 'yass',    wc_trim_string( 'yasssss', 4 ) );
		$this->assertEquals( 'yas.',    wc_trim_string( 'yasssss', 3, '.' ) );
		$this->assertEquals( 'yassss$', wc_trim_string( 'yassss$', 7, '' ) );

	}

}
