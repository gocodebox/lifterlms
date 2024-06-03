<?php
/**
 * Test AJAX Handler
 *
 * @package LifterLMS/Tests
 *
 * @group AJAX
 *
 * @since 3.32.0
 * @since 3.37.2 Added tests on querying courses/memberships filtererd by instructors.
 * @since 3.37.14 Added tests on persisting tracking events.
 * @since 3.37.15 Added tests for admin table methods.
 * @since 5.5.0 Added tests on select2_query_posts when searching terms with quotes.
 */
class LLMS_Test_AJAX_Handler extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/class.llms.admin.reporting.php';
	}

	/**
	 * Setup the test
	 *
	 * @since 3.32.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		add_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
	}

	/**
	 * Teardown the test
	 *
	 * @since 3.32.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		remove_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
	}

	/**
	 * Call a method for the LLMS_AJAX_Handler class that calls wp_die()
	 *
	 * @since 3.32.0
	 *
	 * @param string $function Method name.
	 * @param array  $args     $_REQUEST args.
	 * @return array
	 */
	protected function do_ajax( $function, $args = array() ) {

		ob_start();
		$this->mockPostRequest( $args );
		try {
			call_user_func( array( 'LLMS_AJAX_Handler', $function ) );
		} catch ( WPAjaxDieContinueException $e ) {}
		return json_decode( $this->last_response, true );

	}

	/**
	 * Test export_admin_table()
	 *
	 * @since 3.37.15
	 *
	 * @return void
	 */
	public function test_export_admin_table() {

		$expected_keys = array( 'filename', 'progress', 'url' );
		foreach( array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ) as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$res = LLMS_AJAX_Handler::export_admin_table( array( 'handler' => 'Students' ) );
			$this->assertEquals( $expected_keys, array_keys( $res ) );
		}

	}

	/**
	 * Test export_admin_table() with invalid handlers
	 *
	 * @since 3.37.15
	 *
	 * @return void
	 */
	public function test_export_admin_table_invalid_handler() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// No handler.
		$this->assertFalse( LLMS_AJAX_Handler::export_admin_table( array() ) );

		// Invalid handler.
		$this->assertFalse( LLMS_AJAX_Handler::export_admin_table( array( 'handler' => 'fake' ) ) );

	}

	/**
	 * Test export_admin_table() ensuring only users with proper permissions can access.
	 *
	 * @since  3.37.15
	 *
	 * @return void
	 */
	public function test_export_admin_table_invalid_permissions() {

		// No user.
		$this->assertFalse( LLMS_AJAX_Handler::export_admin_table( array( 'handler' => 'Students' ) ) );

		// Student.
		wp_set_current_user( $this->factory->student->create() );
		$this->assertFalse( LLMS_AJAX_Handler::export_admin_table( array( 'handler' => 'Students' ) ) );

	}

	/**
	 * Test get_admin_table_data()
	 *
	 * @since 3.37.15
	 *
	 * @return void
	 */
	public function test_get_admin_table_data() {

		$expected_keys = array( 'args', 'thead', 'tbody', 'tfoot' );

		foreach( array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ) as $role ) {

			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$res = LLMS_AJAX_Handler::get_admin_table_data( array( 'handler' => 'Students' ) );
			$this->assertEquals( $expected_keys, array_keys( $res ) );

		}

	}

	/**
	 * Test get_admin_table_data() when invalid handlers are submitted.
	 *
	 * @since  3.37.15
	 *
	 * @return void
	 */
	public function test_get_admin_table_data_invalid_handler() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// No handler.
		$this->assertFalse( LLMS_AJAX_Handler::get_admin_table_data( array() ) );

		// Invalid handler.
		$this->assertFalse( LLMS_AJAX_Handler::get_admin_table_data( array( 'handler' => 'fake' ) ) );

	}

	/**
	 * Test get_admin_table_data() ensuring only users with proper permissions can access.
	 *
	 * @since  3.37.15
	 *
	 * @return void
	 */
	public function test_get_admin_table_data_invalid_permissions() {

		// No user.
		$this->assertFalse( LLMS_AJAX_Handler::get_admin_table_data( array( 'handler' => 'Students' ) ) );

		// Student.
		wp_set_current_user( $this->factory->student->create() );
		$this->assertFalse( LLMS_AJAX_Handler::get_admin_table_data( array( 'handler' => 'Students' ) ) );

	}

	/**
	 * Test the select2_query_posts() ajax method.
	 *
	 * @since 3.32.0
	 * @since 3.37.2 Added tests on querying courses/memberships filtererd by instructors.
	 *
	 * @return void
	 */
	public function test_select2_query_posts() {

		$args = array(
			'post_type' => 'course',
		);

		// No results.
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		$this->factory->post->create_many( 50, array(
			'post_type' => 'course',
		) );

		// Full result list.
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 30, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertTrue( $res['more'] );

		// Second page.
		$args['page'] = 1;
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 20, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// Term not found.
		unset( $args['page'] );
		$args['term'] = 'arstarstarst';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );

		// Term found.
		$args['term'] = 'title';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( count( $res['items'] ) >= 1 );

		$this->factory->post->create_many( 5, array(
			'post_title' => 'search title',
		) );
		$this->factory->post->create_many( 5, array(
			'post_type'  => 'course',
			'post_title' => 'search title',
		) );

		// multiple post types.
		$args['post_type'] .= ',post';
		$args['term'] = 'search title';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( array_key_exists( 'post', $res['items'] ) );
		$this->assertSame( 'Posts', $res['items']['post']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['post'] ) );
		$this->assertTrue( array_key_exists( 'course', $res['items'] ) );
		$this->assertSame( 'Courses', $res['items']['course']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['course'] ) );

		// No results, when querying for 'future' posts.
		$args = array(
			'post_type'     => 'course',
			'post_statuses' => 'future',
		);
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// create 4 courses in draft.
		$this->factory->post->create_many( 4, array(
			'post_type'   => 'course',
			'post_status' => 'draft',
		));

		// 4 results when querying for Courses in 'draft'.
		$args['post_statuses'] = 'draft';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 4, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// Full result list querying for 'draft' and 'publish' Course statuses.
		$args['post_statuses'] .= ',publish';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 30, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertTrue( $res['more'] );

		// Second page querying for 'draft' and 'publish' Course statuses.
		$args['page'] = 1;
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 29, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		$this->factory->post->create_many( 1, array(
			'post_title'  => 'search title again',
			'post_status' => 'draft',
		));
		$this->factory->post->create_many( 5, array(
			'post_type'  => 'course',
			'post_title' => 'search title again',
		));

		// Search for multiple post types and multiple status.
		// Only 1 post in 'draft' and 5 courses 'publish' must be found matching the 'term'.
		unset( $args['page'] );

		$args['post_type'] .= ',post';
		$args['term']       = 'search title again';

		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( array_key_exists( 'post', $res['items'] ) );
		$this->assertSame( 'Posts', $res['items']['post']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['post'] ) );
		$this->assertSame( 1, count( $res['items']['post']['items'] ) );
		$this->assertTrue( array_key_exists( 'course', $res['items'] ) );
		$this->assertSame( 'Courses', $res['items']['course']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['course'] ) );
		$this->assertSame( 5, count( $res['items']['course']['items'] ) );

		// Search for multiple post types only for the 'draft' status.
		// Only 1 post in 'draft' and no courses must be found matching the 'term'.
		$args['post_statuses'] = 'draft';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( array_key_exists( 'post', $res['items'] ) );
		$this->assertSame( 'Posts', $res['items']['post']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['post'] ) );
		$this->assertSame( 1, count( $res['items']['post']['items'] ) );

		// 2 Courses and 2 Memberships when querying for multiple post types limited to a specific instructor id.
		// create and setup an instructor for the just created 2 Courses and 2 Memberships.
		$instructor_id = $this->factory->instructor->create();
		foreach ( array( 'course', 'llms_membership' ) as $post_type ) {
			$ids = $this->factory->post->create_many( 2, array(
				'post_type' => $post_type,
			));
			foreach ( $ids as $id ) {
				llms_get_post( $id )->instructors()->set_instructors( array(
					array(
						'id' => $instructor_id,
					),
				));
			}
		}

		$args = array(
			'post_type'     => 'course,llms_membership',
			'post_statuses' => 'publish',
			'instructor_id' => $instructor_id,
		);
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( array_key_exists( 'course', $res['items'] ) );
		$this->assertSame( 'Courses', $res['items']['course']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['course'] ) );
		$this->assertSame( 2, count( $res['items']['course']['items'] ) );
		$this->assertTrue( array_key_exists( 'llms_membership', $res['items'] ) );
		$this->assertSame( 'Memberships', $res['items']['llms_membership']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['llms_membership'] ) );
		$this->assertSame( 2, count( $res['items']['llms_membership']['items'] ) );

	}

	/**
	 * Test the select2_query_posts() ajax method with search term and quotes.
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_select2_query_posts_search_term_quote() {

		$course = $this->factory->post->create( array(
			'post_title'  => 'search title with this quotes:\'" - :)',
			'post_type'   => 'course',
			'post_stauts' => 'publish',
		));

		$args = array(
			'post_type'   => 'course',
			'term'        => 'search title with this quotes:\'',
		);

		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 1, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertSame( $course, (int) $res['items'][0]['id'] );
		$this->assertSame( 'search title with this quotes:\'" - :)' .  " (ID# $course)", $res['items'][0]['name'] );

	}

	/**
	 * Test the errors returned by the LLMS_AJAX_Handler::update_student_enrollment() method.
	 *
	 * @since 3.33.0
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_errors() {

		$request = array(
			'post_id' => 1,
			'status'  => 'add',
		);
		// Missing student_id.
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'student_id' => 1,
			'status'     => 'add',
		);
		// Missing post_id.
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'student_id' => 1,
			'post_id'    => 1,
		);
		// Missing status.
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'status'     => 'add',
			'student_id' => 1,
			'post_id'    => '',
		);
		// Empty post_id ( or student_id, or status) value.
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'status'     => 'enjoy',
			'student_id' => 1,
			'post_id'    => 2,
		);
		// status not in ('add', 'remove', 'delete').
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Invalid status', $res->get_error_message() );

		// create a student.
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		// create a course.
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		$request = array(
			'status'     => 'add',
			'student_id' => $student_id,
			'post_id'    => $course_id + 1,
		);
		// 'add' failure: no course.
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Action "add" failed. Please try again', $res->get_error_message() );

		// 'remove' failure: student not enrolled in a Course with ID as $course_id.
		$request['status']  = 'remove';
		$request['post_id'] = $course_id;
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Action "remove" failed. Please try again', $res->get_error_message() );

		// 'delete' failure: student not enrolled in a Course with ID as $course_id.
		$request['status']  = 'delete';
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Action "delete" failed. Please try again', $res->get_error_message() );

	}

	/**
	 * Test the update_student_enrollment() method can perform user's enrollment
	 *
	 * @since 3.33.0
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_enroll() {

		// create a student.
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		// create a course.
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		$request = array(
			'status'     => 'add',
			'student_id' => $student_id,
			'post_id'    => $course_id,
		);

		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertTrue( $res['success'] );
		$this->assertTrue( $student->is_enrolled( $course_id ) );

	}

	/**
	 * Test the update_student_enrollment() method can perform user's unenrollment
	 *
	 * @since 3.33.0
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_unenroll() {

		// create a student.
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		// create a course.
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		// enroll the student in the course.
		$student->enroll( $course_id );

		$request = array(
			'status'     => 'remove',
			'student_id' => $student_id,
			'post_id'    => $course_id,
		);

		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $student->is_enrolled( $course_id ) );

	}

	/**
	 * Test the update_student_enrollment() method can perform user's enrollment deletion
	 *
	 * @since 3.33.0
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_delete() {

		// create a student.
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		// create a course.
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		// enroll the student in the course.
		$student->enroll( $course_id );

		$request = array(
			'status'     => 'delete',
			'student_id' => $student_id,
			'post_id'    => $course_id,
		);

		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertTrue( $res['success'] );
		$this->assertEquals( array(), llms_get_user_postmeta( $student_id, $course_id ) );

	}

	/**
	 * Test `persist_tracking_events()` ajax callback.
	 *
	 * @since 3.37.14
	 *
	 * @return void
	 */
	public function test_persist_tracking_events() {

		$request = array(
			'something' => 'what'
		);

		// missing tracking data.
		$res = LLMS_AJAX_Handler::persist_tracking_events( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'error', $res );
		$this->assertSame( 'Missing tracking data.', $res->get_error_message() );


		// unauthorized, missing tracking nonce or user not logged in.

		// create nonce.
		// check user not logged in.
		$request = array(
			'llms-tracking' => json_encode(
				array(
					'events' => array(),
					'nonce'  => wp_create_nonce( 'llms-tracking' ),
				)
			),
		);

		$res = LLMS_AJAX_Handler::persist_tracking_events( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_events_tracking_unauthorized', $res );
		$this->assertSame( 'You\'re not allowed to store tracking events', $res->get_error_message() );

		// log-in. check missing nonce.
		wp_set_current_user(1);
		$request = array(
			'llms-tracking' => json_encode(
				array(
					'events' => array(),
				)
			),
		);

		$res = LLMS_AJAX_Handler::persist_tracking_events( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_events_tracking_unauthorized', $res );
		$this->assertSame( 'You\'re not allowed to store tracking events', $res->get_error_message() );

		// persist events.
		$request = array(
			'llms-tracking' => json_encode(
				array(
					'events' => array(
						array(
							'object_type' => 'user',
							'object_id' => 1,
							'event' => 'account.signon',
						),
						array(
							'object_type' => 'user',
							'object_id' => 1,
							'event' => 'account.signoff',
						),
					),
					'nonce'  => wp_create_nonce( 'llms-tracking' ),
				)
			),
		);

		$res = LLMS_AJAX_Handler::persist_tracking_events( $request );
		$this->assertTrue( $res['success'] );
		$events = ( new LLMS_Events_Query( array(
			'actor' => array(1)
		) ) )->get_events();

		$this->assertEquals( 2, count( $events ) );

	}

	/**
	 * Catch wp_die() called by ajax methods & store the output buffer contents for use later.
	 *
	 * @since 3.32.0
	 *
	 * @param string $msg Die msg.
	 * @return void
	 */
	public function _wp_die_handler( $msg ) {
		$this->last_response = ob_get_clean();
		throw new WPAjaxDieContinueException( $msg );
	}

}
