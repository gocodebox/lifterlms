<?php
/**
 * Test Lesson Time Session model and related functionality
 *
 * @package LifterLMS/Tests
 *
 * @group lesson_time
 *
 * @since 10.1.0
 */
class LLMS_Test_Lesson_Time_Session extends LLMS_UnitTestCase {

	/**
	 * Student user ID.
	 *
	 * @var int
	 */
	private $student_id;

	/**
	 * Lesson post ID.
	 *
	 * @var int
	 */
	private $lesson_id;

	/**
	 * Course post ID.
	 *
	 * @var int
	 */
	private $course_id;

	/**
	 * Setup the test.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->student_id = $this->factory->user->create( array( 'role' => 'student' ) );

		$this->course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$this->lesson_id = $this->factory->post->create( array(
			'post_type'  => 'lesson',
			'meta_input' => array(
				'_llms_parent_course'    => $this->course_id,
				'_llms_has_minimum_time' => 'yes',
				'_llms_minimum_time'     => 600,
			),
		) );
	}

	/**
	 * Test start_session creates a new session.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_start_session() {
		$session = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );

		$this->assertInstanceOf( 'LLMS_Lesson_Time_Session', $session );
		$this->assertNotEmpty( $session->get( 'session_token' ) );
		$this->assertEquals( $this->student_id, absint( $session->get( 'user_id' ) ) );
		$this->assertEquals( $this->lesson_id, absint( $session->get( 'lesson_id' ) ) );
		$this->assertEquals( 0, absint( $session->get( 'accumulated_seconds' ) ) );
		$this->assertNull( $session->get( 'session_end' ) );
	}

	/**
	 * Test that starting a new session expires old ones.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_start_session_expires_prior() {
		$session1 = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$token1   = $session1->get( 'session_token' );

		$session2 = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );

		$old_session = LLMS_Lesson_Time_Tracking::instance()->find_by_token( $token1 );
		$this->assertNotNull( $old_session->get( 'session_end' ) );
	}

	/**
	 * Test find_by_token returns the correct session.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_find_by_token() {
		$session = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$token   = $session->get( 'session_token' );

		$found = LLMS_Lesson_Time_Tracking::instance()->find_by_token( $token );
		$this->assertInstanceOf( 'LLMS_Lesson_Time_Session', $found );
		$this->assertEquals( $session->get_id(), $found->get_id() );
	}

	/**
	 * Test find_by_token returns false for invalid token.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_find_by_token_invalid() {
		$this->assertFalse( LLMS_Lesson_Time_Tracking::instance()->find_by_token( 'nonexistent_token' ) );
	}

	/**
	 * Test get_total_seconds returns 0 when no sessions exist.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_get_total_seconds_no_sessions() {
		$total = LLMS_Lesson_Time_Tracking::instance()->get_total_seconds( $this->student_id, $this->lesson_id, false );
		$this->assertEquals( 0, $total );
	}

	/**
	 * Test format_time outputs correct H:MM:SS.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_format_time() {
		$this->assertEquals( '0:00:00', LLMS_Lesson_Time_Tracking::instance()->format_time( 0 ) );
		$this->assertEquals( '0:01:00', LLMS_Lesson_Time_Tracking::instance()->format_time( 60 ) );
		$this->assertEquals( '0:10:30', LLMS_Lesson_Time_Tracking::instance()->format_time( 630 ) );
		$this->assertEquals( '1:00:00', LLMS_Lesson_Time_Tracking::instance()->format_time( 3600 ) );
		$this->assertEquals( '2:30:45', LLMS_Lesson_Time_Tracking::instance()->format_time( 9045 ) );
	}

	/**
	 * Test record and get admin override.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_admin_override() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		LLMS_Lesson_Time_Tracking::instance()->record_admin_override( $this->student_id, $this->lesson_id, 'admin_' . $admin_id );

		$override = LLMS_Lesson_Time_Tracking::instance()->get_admin_override( $this->student_id, $this->lesson_id );
		$this->assertIsArray( $override );
		$this->assertEquals( $admin_id, $override['admin_id'] );
		$this->assertEquals( $this->student_id, $override['student_id'] );
		$this->assertEquals( $this->lesson_id, $override['lesson_id'] );
	}

	/**
	 * Test has_minimum_time returns false for free lessons.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_has_minimum_time_free_lesson() {
		$lesson = llms_get_post( $this->lesson_id );
		$this->assertTrue( $lesson->has_minimum_time() );

		update_post_meta( $this->lesson_id, '_llms_free_lesson', 'yes' );
		$lesson = llms_get_post( $this->lesson_id );
		$this->assertFalse( $lesson->has_minimum_time() );
	}

	/**
	 * Test has_minimum_time returns false when not enabled.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_has_minimum_time_not_enabled() {
		update_post_meta( $this->lesson_id, '_llms_has_minimum_time', 'no' );
		$lesson = llms_get_post( $this->lesson_id );
		$this->assertFalse( $lesson->has_minimum_time() );
	}

	/**
	 * Test has_minimum_time returns false when time is 0.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_has_minimum_time_zero_time() {
		update_post_meta( $this->lesson_id, '_llms_minimum_time', 0 );
		$lesson = llms_get_post( $this->lesson_id );
		$this->assertFalse( $lesson->has_minimum_time() );
	}

	/**
	 * Test heartbeat AJAX handler with valid session.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_heartbeat_valid() {
		wp_set_current_user( $this->student_id );
		$session = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );

		sleep( 1 );

		$result = LLMS_AJAX_Handler::lesson_time_heartbeat( array(
			'session_token' => $session->get( 'session_token' ),
		) );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'total', $result );
		$this->assertArrayHasKey( 'remaining', $result );
		$this->assertArrayHasKey( 'met', $result );
		$this->assertArrayHasKey( 'credited', $result );
		$this->assertGreaterThan( 0, $result['credited'] );
	}

	/**
	 * Test heartbeat with expired/superseded session.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_heartbeat_superseded() {
		wp_set_current_user( $this->student_id );
		$session1 = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$token1   = $session1->get( 'session_token' );

		LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );

		$result = LLMS_AJAX_Handler::lesson_time_heartbeat( array(
			'session_token' => $token1,
		) );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'session_superseded', $result->get_error_code() );
	}

	/**
	 * Test heartbeat with missing token.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_heartbeat_missing_token() {
		wp_set_current_user( $this->student_id );

		$result = LLMS_AJAX_Handler::lesson_time_heartbeat( array() );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'missing_token', $result->get_error_code() );
	}

	/**
	 * Test heartbeat with invalid token.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_heartbeat_invalid_token() {
		wp_set_current_user( $this->student_id );

		$result = LLMS_AJAX_Handler::lesson_time_heartbeat( array(
			'session_token' => 'bogus_token',
		) );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'invalid_session', $result->get_error_code() );
	}

	/**
	 * Test lesson_time_end handler.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_lesson_time_end() {
		wp_set_current_user( $this->student_id );
		$session = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$token   = $session->get( 'session_token' );

		$result = LLMS_AJAX_Handler::lesson_time_end( array(
			'session_token' => $token,
		) );

		$this->assertIsArray( $result );
		$this->assertTrue( $result['ended'] );

		$ended = LLMS_Lesson_Time_Tracking::instance()->find_by_token( $token );
		$this->assertNotNull( $ended->get( 'session_end' ) );
	}

	/**
	 * Test minimum_time_maybe_prevent_lesson_completion blocks when time not met.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_completion_blocked_when_time_not_met() {
		$controller = new LLMS_Controller_Lesson_Progression();

		$result = $controller->minimum_time_maybe_prevent_lesson_completion(
			true,
			$this->student_id,
			$this->lesson_id,
			'lesson_' . $this->lesson_id,
			array()
		);

		$this->assertFalse( $result );
	}

	/**
	 * Test admin override allows completion.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_completion_allowed_admin_override() {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$controller = new LLMS_Controller_Lesson_Progression();

		$result = $controller->minimum_time_maybe_prevent_lesson_completion(
			true,
			$this->student_id,
			$this->lesson_id,
			'admin_' . $admin_id,
			array()
		);

		$this->assertTrue( $result );

		$override = LLMS_Lesson_Time_Tracking::instance()->get_admin_override( $this->student_id, $this->lesson_id );
		$this->assertIsArray( $override );
	}

	/**
	 * Test completion allowed for lessons without minimum time.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_completion_allowed_no_minimum() {
		update_post_meta( $this->lesson_id, '_llms_has_minimum_time', 'no' );

		$controller = new LLMS_Controller_Lesson_Progression();

		$result = $controller->minimum_time_maybe_prevent_lesson_completion(
			true,
			$this->student_id,
			$this->lesson_id,
			'lesson_' . $this->lesson_id,
			array()
		);

		$this->assertTrue( $result );
	}

	/**
	 * Test one active session per student globally.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_one_session_per_student_globally() {
		$lesson_id_2 = $this->factory->post->create( array(
			'post_type'  => 'lesson',
			'meta_input' => array(
				'_llms_parent_course'    => $this->course_id,
				'_llms_has_minimum_time' => 'yes',
				'_llms_minimum_time'     => 300,
			),
		) );

		$session1 = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$token1   = $session1->get( 'session_token' );

		$session2 = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $lesson_id_2 );

		$old_session = LLMS_Lesson_Time_Tracking::instance()->find_by_token( $token1 );
		$this->assertNotNull( $old_session->get( 'session_end' ) );

		$this->assertNull( $session2->get( 'session_end' ) );
	}

	/**
	 * Test cached totals are updated.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_cached_totals() {
		$session = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$session->set( 'accumulated_seconds', 120 );
		$session->set( 'session_end', current_time( 'mysql' ) );
		$session->save();

		LLMS_Lesson_Time_Tracking::instance()->update_cached_time( $this->student_id, $this->lesson_id );

		$total = LLMS_Lesson_Time_Tracking::instance()->get_total_seconds( $this->student_id, $this->lesson_id );
		$this->assertGreaterThanOrEqual( 120, $total );
	}

	/**
	 * Test llms_has_met_lesson_minimum_time().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_has_met_lesson_minimum_time() {

		$lesson = llms_get_post( $this->lesson_id );

		$this->assertFalse( llms_has_met_lesson_minimum_time( $this->student_id, $lesson ) );
		$this->assertFalse( llms_has_met_lesson_minimum_time( $this->student_id, $this->lesson_id ) );

		$session = LLMS_Lesson_Time_Tracking::instance()->start_session( $this->student_id, $this->lesson_id );
		$session->set( 'accumulated_seconds', 600 );
		$session->set( 'session_end', current_time( 'mysql' ) );
		$session->save();
		LLMS_Lesson_Time_Tracking::instance()->update_cached_time( $this->student_id, $this->lesson_id );

		$this->assertTrue( llms_has_met_lesson_minimum_time( $this->student_id, $lesson ) );

		update_post_meta( $this->lesson_id, '_llms_has_minimum_time', 'no' );
		$lesson = llms_get_post( $this->lesson_id );
		$this->assertTrue( llms_has_met_lesson_minimum_time( $this->student_id, $lesson ) );
	}
}
