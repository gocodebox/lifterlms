<?php
/**
 * Test AJAX Handler
 *
 * @package LifterLMS/Tests
 *
 * @group AJAX
 *
 * @since 3.32.0
 * @version 3.32.0
 */
class LLMS_Test_AJAX_Handler extends LLMS_UnitTestCase {

	public function setUp() {
		parent::setUp();
		add_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
	}

	public function tearDown() {
		parent::tearDown();
		remove_filter( 'wp_die_handler', array( $this, '_wp_die_handler' ), 1 );
	}

	/**
	 * Call a method for the LLMS_AJAX_Handler class that calls wp_die()
	 *
	 * @since 3.32.0
	 *
	 * @param string $function Method name.
	 * @param array $args $_REQUEST args.
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
	 * Test the select2_query_posts() ajax method.
	 *
	 * @since 3.32.0
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

		$this->factory->post->create_many( 50, array( 'post_type' => 'course' ) );

		// Full result list.
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 30, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertTrue( $res['more'] );

		// Second page
		$args['page'] = 1;
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 20, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// Term not found
		unset( $args['page'] );
		$args['term'] = 'arstarstarst';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );

		// Term found.
		$args['term'] = 'title';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( count( $res['items'] ) >= 1 );

		$this->factory->post->create_many( 5, array( 'post_title' => 'search title' ) );
		$this->factory->post->create_many( 5, array( 'post_type' => 'course', 'post_title' => 'search title' ) );

		// multiple post types
		$args['post_type'] .= ',post';
		$args['term'] = 'search title';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertTrue( array_key_exists( 'post', $res['items'] ) );
		$this->assertSame( 'Posts', $res['items']['post']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['post'] ) );
		$this->assertTrue( array_key_exists( 'course', $res['items'] ) );
		$this->assertSame( 'Courses', $res['items']['course']['label'] );
		$this->assertTrue( array_key_exists( 'items', $res['items']['course'] ) );

		// No results, when querying for 'future' posts
		$args = array(
			'post_type'     => 'course',
			'post_statuses' => 'future',
		);
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 0, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// create 4 courses in draft
		$this->factory->post->create_many( 4, array( 'post_type' => 'course', 'post_status' => 'draft' ) );

		// 4 results when querying for Courses in 'draft'.
		$args['post_statuses'] = 'draft';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 4, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		// Full result list querying for 'draft' and 'publish' Course statuses
		$args['post_statuses'] .= ',publish';
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 30, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertTrue( $res['more'] );

		// Second page querying for 'draft' and 'publish' Course statuses
		$args['page'] = 1;
		$res = $this->do_ajax( 'select2_query_posts', $args );
		$this->assertSame( 29, count( $res['items'] ) );
		$this->assertTrue( $res['success'] );
		$this->assertFalse( $res['more'] );

		$this->factory->post->create_many( 1, array( 'post_title' => 'search title again', 'post_status' => 'draft' ) );
		$this->factory->post->create_many( 5, array( 'post_type' => 'course', 'post_title' => 'search title again' ) );

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
	}

	/**
	 * Test the errors returned by the LLMS_AJAX_Handler::update_student_enrollment() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_errors() {

		$request = array(
			'post_id' => 1,
			'status'  => 'add',
		);
		// Missing student_id
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'student_id' => 1,
			'status'     => 'add',
		);
		// Missing post_id
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'student_id' => 1,
			'post_id'    => 1,
		);
		// Missing status
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'status'     => 'add',
			'student_id' => 1,
			'post_id'    => '',
		);
		// Empty post_id ( or student_id, or status) value
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Missing required parameters', $res->get_error_message() );

		$request = array(
			'status'     => 'enjoy',
			'student_id' => 1,
			'post_id'    => 2,
		);
		// status not in ('add', 'remove', 'delete')
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Invalid status', $res->get_error_message() );

		//create a student
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		//create a course
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		$request = array(
			'status'     => 'add',
			'student_id' => $student_id,
			'post_id'    => $course_id + 1,
		);
		// 'add' failure: no course
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Action "add" failed. Please try again', $res->get_error_message() );

		// 'remove' failure: student not enrolled to a Course with ID as $course_id
		$request['status']  = 'remove';
		$request['post_id'] = $course_id;
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Action "remove" failed. Please try again', $res->get_error_message() );

		// 'delete' failure: student not enrolled to a Course with ID as $course_id
		$request['status']  = 'delete';
		$res = LLMS_AJAX_Handler::update_student_enrollment( $request );
		$this->assertWPError( $res );
		$this->assertWPErrorCodeEquals( '400', $res );
		$this->assertSame( 'Action "delete" failed. Please try again', $res->get_error_message() );

	}

	/**
	 * Test the update_student_enrollment() method can perform user's enrollment
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_enroll() {

		//create a student
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		//create a course
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_unenroll() {

		// create a student
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		// create a course
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		// enroll the student to the course
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_student_enrollment_delete() {

		// create a student
		$student    = $this->get_mock_student();
		$student_id = $student->get( 'id' );

		// create a course
		$course_id  = $this->generate_mock_courses( 1, 1, 3 )[0];

		// enroll the student to the course
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
