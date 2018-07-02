<?php
/**
 * Tests for LifterLMS Core Functions
 * @group    functions
 * @group    functions_core
 * @since    3.3.1
 * @version  [version]
 */
class LLMS_Test_Functions_Core extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_core_supported_themes()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_core_supported_themes() {

		$this->assertFalse( empty( llms_get_core_supported_themes() ) );
		$this->assertTrue( is_array( llms_get_core_supported_themes() ) );

	}

	/**
	 * test llms_get_date_diff()
	 * @return   void
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
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_engagement_triggers() {
		$this->assertFalse( empty( llms_get_engagement_triggers() ) );
		$this->assertTrue( is_array( llms_get_engagement_triggers() ) );
	}

	/**
	 * test llms_get_engagement_types()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_engagement_types() {
		$this->assertFalse( empty( llms_get_engagement_types() ) );
		$this->assertTrue( is_array( llms_get_engagement_types() ) );
	}

	/**
	 * Test the llms_get_option_page_anchor() function
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_llms_get_option_page_anchor() {

		$id = $this->factory->post->create( array(
			'post_title' => 'The Page Title',
			'post_type' => 'page',
		) );

		$option_name = 'llms_test_page_anchor';

		// returns empty if option isn't set
		$this->assertEmpty( llms_get_option_page_anchor( $option_name ) );

		update_option( $option_name, $id );

		// title found in string
		$this->assertTrue( false !== strpos( llms_get_option_page_anchor( $option_name ), get_the_title( $id ) ) );

		// URL found
		$this->assertTrue( false !== strpos( llms_get_option_page_anchor( $option_name ), get_the_permalink( $id ) ) );

		// target found
		$this->assertTrue( false !== strpos( llms_get_option_page_anchor( $option_name ), 'target="_blank"' ) );

		// no target found
		$this->assertTrue( false === strpos( llms_get_option_page_anchor( $option_name, false ), 'target="_blank"' ) );

	}

	/**
	 * Test llms_get_product_visibility_options()
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function test_llms_get_product_visibility_options() {
		$this->assertFalse( empty( llms_get_product_visibility_options() ) );
		$this->assertTrue( is_array( llms_get_product_visibility_options() ) );
	}

	/**
	 * Test llms_find_coupon()
	 * @return   void
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
	 * Test llms_get_enrolled_students()
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	function test_llms_get_enrolled_students() {

		$course_id = $this->factory->post->create( array(
			'post_type' => 'course',
		) );

		$students = $this->factory->user->create_many( 25, array( 'role' => 'student' ) );
		$students_copy = $students;
		foreach ( $students as $student_id ) {
			$student = new LLMS_Student( $student_id );
			$student->enroll( $course_id );
		}

		// test basic enrollment query passing in a string
		$this->assertEquals( $students, llms_get_enrolled_students( $course_id, 'enrolled', 50, 0 ) );
		// test basic enrollment query passing in an array
		$this->assertEquals( $students, llms_get_enrolled_students( $course_id, array( 'enrolled' ), 50, 0 ) );

		// test pagination
		$this->assertEquals( array_splice( $students, 0, 10 ), llms_get_enrolled_students( $course_id, 'enrolled', 10, 0 ) );
		$this->assertEquals( array_splice( $students, 0, 10 ), llms_get_enrolled_students( $course_id, 'enrolled', 10, 10 ) );
		$this->assertEquals( $students, llms_get_enrolled_students( $course_id, 'enrolled', 10, 20 ) );

		// should be no one expired
		$this->assertEquals( array(), llms_get_enrolled_students( $course_id, 'expired', 10, 0 ) );

		// sleeping makes unerollment tests work
		sleep( 1 );

		$i = 0;
		$expired = array();
		while ( $i < 5 ) {
			$student = new LLMS_Student( $students_copy[ $i ] );
			$student->unenroll( $course_id, 'any', 'expired' );
			$expired[] = $students_copy[ $i ];
			$i++;
		}

		// test expired alone
		$this->assertEquals( $expired, llms_get_enrolled_students( $course_id, 'expired', 10, 0 ) );

		// test multiple statuses
		$this->assertEquals( $students_copy, llms_get_enrolled_students( $course_id, array( 'enrolled', 'expired' ), 50, 0 ) );

	}

	/**
	 * test llms_get_enrollment_statuses()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_enrollment_statuses() {
		$this->assertFalse( empty( llms_get_enrollment_statuses() ) );
		$this->assertTrue( is_array( llms_get_enrollment_statuses() ) );
	}

	/**
	 * Test llms_get_enrollment_status_name()
	 * @return   void
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
	 * Test llms_get_ip_address()
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function test_llms_get_ip_address() {

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

		$_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1, 192.168.1.1, 192.168.1.5';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

		$_SERVER['X-Real-IP'] = '127.0.0.1';
		$this->assertEquals( '127.0.0.1', llms_get_ip_address() );

	}

	/**
	 * Test llms_get_order_status_name()
	 * @return   void
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
	 * @return   void
	 * @since    3.3.1
	 * @version  3.19.0
	 */
	public function test_llms_get_order_statuses() {

		$this->assertTrue( is_array( llms_get_order_statuses() ) );
		$this->assertFalse( empty( llms_get_order_statuses() ) );
		$this->assertEquals( array(
			'llms-completed',
			'llms-active',
			'llms-expired',
			'llms-on-hold',
			'llms-pending-cancel',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
		), array_keys( llms_get_order_statuses() ) );

		$this->assertTrue( is_array( llms_get_order_statuses( 'recurring' ) ) );
		$this->assertFalse( empty( llms_get_order_statuses( 'recurring' ) ) );
		$this->assertEquals( array(
			'llms-active',
			'llms-expired',
			'llms-on-hold',
			'llms-pending-cancel',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
		), array_keys( llms_get_order_statuses( 'recurring' ) ) );

		$this->assertTrue( is_array( llms_get_order_statuses( 'single' ) ) );
		$this->assertFalse( empty( llms_get_order_statuses( 'single' ) ) );
		$this->assertEquals( array(
			'llms-completed',
			'llms-pending',
			'llms-cancelled',
			'llms-refunded',
			'llms-failed',
		), array_keys( llms_get_order_statuses( 'single' ) ) );

	}

	/**
	 * Test llms_get_post()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.16.11
	 */
	public function test_llms_get_post() {

		$types = array(
			'LLMS_Access_Plan' => 'llms_access_plan',
			'LLMS_Coupon' => 'llms_coupon',
			'LLMS_Course' => 'course',
			'LLMS_Lesson' => 'lesson',
			'LLMS_Membership' => 'llms_membership',
			'LLMS_Order' => 'llms_order',
			'LLMS_Quiz' => 'llms_quiz',
			'LLMS_Question' => 'llms_question',
			'LLMS_Section' => 'llms_section',
			'LLMS_Transaction' => 'llms_transaction',
		);

		foreach ( $types as $class => $type ) {

			$id = $this->factory->post->create( array(
				'post_type' => $type,
			) );
			$this->assertInstanceOf( $class, llms_get_post( $id ) );

		}

		$this->assertInstanceOf( 'WP_Post', llms_get_post( $this->factory->post->create(), 'post' ) );
		$this->assertNull( llms_get_post( 'fail' ) );
		$this->assertNull( llms_get_post( 0 ) );

	}

	/**
	 * Test llms_get_post_parent_course()
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	public function test_llms_get_post_parent_course() {

		$course = new LLMS_Course( 'new', 'title' );
		$section = new LLMS_Section( 'new', array(
			'post_title' => 'section',
			'meta_input' => array(
				'_llms_parent_course' => $course->get( 'id' )
			),
		) );
		$lesson = new LLMS_Lesson( 'new', array(
			'post_title' => 'lesson',
			'meta_input' => array(
				'_llms_parent_course' => $course->get( 'id' ),
				'_llms_parent_section' => $section->get( 'id' ),
			),
		) );

		foreach ( array( $section, $lesson ) as $obj ) {

			$post = get_post( $obj->get( 'id' ) );

			// pass in post id
			$this->assertEquals( $course, llms_get_post_parent_course( $post->ID ) );

			// pass in an object
			$this->assertEquals( $course, llms_get_post_parent_course( $post ) );

		}

		// other post types don't have a parent course
		$reg_post = $this->factory->post->create();
		$this->assertNull( llms_get_post_parent_course( $reg_post ) );

	}


	/**
	 * test llms_get_transaction_statuses()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_get_transaction_statuses() {
		$this->assertFalse( empty( llms_get_transaction_statuses() ) );
		$this->assertTrue( is_array( llms_get_transaction_statuses() ) );
	}

	/**
	 * Test llms_is_site_https()
	 * @return   void
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
	 * Test the llms_parse_bool function
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_llms_parse_bool() {

		$true = array( 'yes', 'on', true, 1, 'true', '1' );

		foreach ( $true as $val ) {
			$this->assertTrue( llms_parse_bool( $val ) );
		}

		$false = array( 'no', 'off', false, 0, 'false', 'something', '', null, '0', array(), array( 'ast' ), array( true ) );

		foreach ( $false as $val ) {
			$this->assertFalse( llms_parse_bool( $val ) );
		}

	}

	/**
	 * Test llms_redirect_and_exit() func with safe on
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_redirect_and_exit_safe_on() {

		$this->expectException( LLMS_Testing_Exception_Redirect::class );
		$this->expectExceptionMessage( 'https://lifterlms.com [302] YES' );
		llms_redirect_and_exit( 'https://lifterlms.com' );

	}

	/**
	 * Test llms_redirect_and_exit() func with safe on
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_redirect_and_exit_safe_off() {

		$this->expectException( LLMS_Testing_Exception_Redirect::class );
		$this->expectExceptionMessage( 'https://lifterlms.com [302] NO' );
		llms_redirect_and_exit( 'https://lifterlms.com', array( 'safe' => false ) );

	}

	/**
	 * Test llms_redirect_and_exit() func with safe custom status
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_llms_redirect_and_exit_safe_status() {

		$this->expectException( LLMS_Testing_Exception_Redirect::class );
		$this->expectExceptionMessage( 'https://lifterlms.com [301] YES' );
		llms_redirect_and_exit( 'https://lifterlms.com', array( 'status' => 301 ) );

	}

	/**
	 * Test llms_trim_string()
	 * @return   void
	 * @since    3.3.1
	 * @version  3.6.0
	 */
	public function test_llms_trim_string() {

		$this->assertEquals( 'yasssss', llms_trim_string( 'yasssss' ) );
		$this->assertEquals( 'y...',    llms_trim_string( 'yasssss', 4 ) );
		$this->assertEquals( 'ya.',     llms_trim_string( 'yasssss', 3, '.' ) );
		$this->assertEquals( 'yassss$', llms_trim_string( 'yassss$s', 7, '' ) );

	}

}
