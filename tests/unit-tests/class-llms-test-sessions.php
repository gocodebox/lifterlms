<?php
/**
 * Test sessions class
 *
 * @package LifterLMS/Tests
 *
 * @group sessions
 *
 * @since 3.36.0
 * @version 3.36.0
 */
class LLMS_Test_Sessions extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->sessions = LLMS_Sessions::instance();


	}

	/**
	 * Test get_idle_sessions()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_open_sessions() {

		$time = time();

		$i = 0;
		while ( $i < 5 ) {

			wp_set_current_user( $this->factory->user->create() );

			$time += MINUTE_IN_SECONDS;
			llms_tests_mock_current_time( $time );
			$this->sessions->start();
			$time += MINUTE_IN_SECONDS;
			llms_tests_mock_current_time( $time );
			$this->sessions->end_current();

			$time += MINUTE_IN_SECONDS;
			llms_tests_mock_current_time( $time );

			$this->sessions->start();

			$i++;

		}

		$sessions = LLMS_Unit_Test_Util::call_method( $this->sessions, 'get_open_sessions' );
		$this->assertEquals( 5, count( $sessions ) );

		foreach ( $sessions as $session ) {
			$this->assertEquals( 2, $session->get( 'object_id' ) );
			$this->assertTrue( $this->sessions->is_session_open( $session ) );
		}

	}

	public function test_end_idle_sessions() {

		$time = time();

		$started = array();

		$i = 0;
		while ( $i < 5 ) {

			wp_set_current_user( $this->factory->user->create() );

			$time += MINUTE_IN_SECONDS;
			llms_tests_mock_current_time( $time );
			$started[] = $this->sessions->start();

			$i++;

		}

		// It hasn't been long enough.
		$this->sessions->end_idle_sessions();
		foreach ( $started as $i => $session ) {
			$this->assertTrue( $this->sessions->is_session_open( $session ) );
		}

		$this->assertEquals( 4, $i );

		$time += HOUR_IN_SECONDS;
		llms_tests_mock_current_time( $time );
		$this->sessions->end_idle_sessions();
		foreach ( $started as $i => $session ) {
			$this->assertFalse( $this->sessions->is_session_open( $session ) );
		}

		$this->assertEquals( 4, $i );

	}

	/**
	 * Test end_current()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_end_current() {

		wp_set_current_user( $this->factory->user->create() );
		$start = $this->sessions->start();

		$end = $this->sessions->end_current();

		$this->assertTrue( is_a( $end, 'LLMS_Event' ) );
		$this->assertEquals( $start->get( 'actor_id' ), $end->get( 'actor_id' ) );
		$this->assertEquals( 'session', $end->get( 'event_type' ) );
		$this->assertEquals( 'end', $end->get( 'event_action' ) );
		$this->assertEquals( 'session', $end->get( 'object_type' ) );
		$this->assertEquals( $start->get( 'object_id' ), $end->get( 'object_id' ) );

	}

	/**
	 * Test get_new_session_id()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_new_session_id() {

		wp_set_current_user( $this->factory->user->create() );

		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->sessions, 'get_new_id' ) );
		$this->sessions->start();

		$this->assertEquals( 2, LLMS_Unit_Test_Util::call_method( $this->sessions, 'get_new_id' ) );

	}

	/**
	 * Test get_current() when there's no logged in user
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_current_no_user() {

		$this->assertFalse( $this->sessions->get_current() );

	}

	/**
	 * Test get_current() when user has no previous sessions
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_current_no_previous_sessions() {

		wp_set_current_user( $this->factory->user->create() );
		$this->assertFalse( $this->sessions->get_current() );

	}

	/**
	 * Test get_current() when there's an open session
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_current_is_open() {

		wp_set_current_user( $this->factory->user->create() );
		$event = $this->sessions->start();

		$current = $this->sessions->get_current();
		$this->assertEquals( $event->get( 'id' ), $current->get( 'id' ) );

	}

	/**
	 * Test get_current() when the most recent session is closed
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_current_last_is_closed() {

		wp_set_current_user( $this->factory->user->create() );
		$this->sessions->start();
		$this->sessions->end_current();

		$this->assertFalse( $this->sessions->get_current() );

	}

	/**
	 * Test get_session_end() when there's no end event for the session
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_session_end_no_end() {

		wp_set_current_user( $this->factory->user->create() );
		$start = $this->sessions->start();

		$this->assertNull( $this->sessions->get_session_end( $start ) );

	}

	/**
	 * Test get_session_end()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_session_end() {

		wp_set_current_user( $this->factory->user->create() );
		$start = $this->sessions->start();
		$end = $this->sessions->end_current();

		$test_end = $this->sessions->get_session_end( $start );

		$this->assertTrue( is_a( $test_end, 'LLMS_Event' ) );
		$this->assertEquals( $end->get( 'id' ), $test_end->get( 'id' ) );

	}

	/**
	 * Test get_session_events()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_get_session_events() {

		$start_time = time() - HOUR_IN_SECONDS;
		llms_tests_mock_current_time( $start_time );

		$user = $this->factory->user->create();
		wp_set_current_user( $user );

		// Start session.
		$start = $this->sessions->start();

		llms_tests_mock_current_time( $start_time + MINUTE_IN_SECONDS );

		// Create events.
		LLMS()->events()->record( array(
			'actor_id' => $user,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'load',
		) );

		llms_tests_mock_current_time( $start_time + ( MINUTE_IN_SECONDS * 2 ) );

		LLMS()->events()->record( array(
			'actor_id' => $user,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'exit',
		) );

		// Return those events during an open session.
		$sessions = $this->sessions->get_session_events( $start );
		$this->assertEquals( 2, count( $sessions ) );

		foreach ( $sessions as $event ) {
			$this->assertTrue( is_a( $event, 'LLMS_Event' ) );
			$this->assertEquals( $user, $event->get( 'actor_id' ) );
			$this->assertEquals( 1, $event->get( 'object_id' ) );
			$this->assertEquals( 'page', $event->get( 'event_type' ) );
			$this->assertEquals( 'post', $event->get( 'object_type' ) );
		}

		llms_tests_mock_current_time( $start_time + ( MINUTE_IN_SECONDS * 3 ) );

		// End the session.
		$this->sessions->end_current();

		// Add a new event (new session)
		LLMS()->events()->record( array(
			'actor_id' => $user,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'exit',
		) );

		// Original session should still only return 2 events.
		$sessions = $this->sessions->get_session_events( $start );
		$this->assertEquals( 2, count( $sessions ) );

	}

	/**
	 * Test is_session_idle() on an already closed session
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_is_session_idle_already_closed() {

		wp_set_current_user( $this->factory->user->create() );

		// Start session.
		$start = $this->sessions->start();
		$this->sessions->end_current();

		// This session has already ended.
		$this->assertFalse( $this->sessions->is_session_idle( $start ) );

	}

	/**
	 * Test is_session_idle() on a session that started less than 30 minutes ago
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_is_session_idle_started_within_window() {

		wp_set_current_user( $this->factory->user->create() );

		// Start session.
		$start = $this->sessions->start();

		$this->assertFalse( $this->sessions->is_session_idle( $start ) );

		llms_tests_mock_current_time( time() + ( 29 * MINUTE_IN_SECONDS ) );
		$this->assertFalse( $this->sessions->is_session_idle( $start ) );

	}

	/**
	 * Test is_session_idle() for a session that started more than 30 minutes ago and has no events
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_is_session_idle_old_with_no_events() {

		wp_set_current_user( $this->factory->user->create() );

		// Start session.
		$start = $this->sessions->start();

		// Session is older than 30 minutes or older & has no events.
		llms_tests_mock_current_time( time() + ( 31 * MINUTE_IN_SECONDS ) );
		$this->assertTrue( $this->sessions->is_session_idle( $start ) );

	}


	/**
	 * Test is_session_idle() for a session that started more than 30 minutes ago
	 * and has at least one active event that's less than 30 minutes old
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_is_session_idle_old_with_events_within_window() {

		$user = $this->factory->user->create();
		wp_set_current_user( $user );

		// Start session.
		$start = $this->sessions->start();

		llms_tests_mock_current_time( time() + ( 10 * MINUTE_IN_SECONDS ) );

		// Add a new
		LLMS()->events()->record( array(
			'actor_id' => $user,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'exit',
		) );

		llms_tests_mock_current_time( time() + ( 31 * MINUTE_IN_SECONDS ) );
		$this->assertFalse( $this->sessions->is_session_idle( $start ) );

	}


	/**
	 * Test is_session_idle() for a session that started more than 30 minutes ago with it's most recent event more than 30 minutes old.
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_is_session_idle_old_with_events_outside_window() {

		$user = $this->factory->user->create();
		wp_set_current_user( $user );

		// Start session.
		$start = $this->sessions->start();

		llms_tests_mock_current_time( time() + ( 15 * MINUTE_IN_SECONDS ) );

		// Add a new
		LLMS()->events()->record( array(
			'actor_id' => $user,
			'object_type' => 'post',
			'object_id' => 1,
			'event_type' => 'page',
			'event_action' => 'exit',
		) );

		// Session is older than 30 minutes and last event within the session is older than 30 mins.
		llms_tests_mock_current_time( time() + ( 46 * MINUTE_IN_SECONDS ) );
		$this->assertTrue( $this->sessions->is_session_idle( $start ) );

	}

	/**
	 * Test start() when no user
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_start_no_user() {

		$this->assertFalse( $this->sessions->start() );

	}

	/**
	 * Test is_session_open()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_is_session_open() {

		wp_set_current_user( $this->factory->user->create() );
		$start = $this->sessions->start();

		$this->assertTrue( $this->sessions->is_session_open( $start ) );
		$this->sessions->end_current();

		$this->assertFalse( $this->sessions->is_session_open( $start ) );

	}

	/**
	 * Test start()
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	public function test_start() {

		$user =  $this->factory->user->create();
		wp_set_current_user( $user );

		$event = $this->sessions->start();

		$this->assertTrue( is_a( $event, 'LLMS_Event' ) );
		$this->assertEquals( $user, $event->get( 'actor_id' ) );
		$this->assertEquals( 'session', $event->get( 'event_type' ) );
		$this->assertEquals( 'start', $event->get( 'event_action' ) );
		$this->assertEquals( 'session', $event->get( 'object_type' ) );
		$this->assertTrue( is_numeric( $event->get( 'object_id' ) ) );

	}

}
