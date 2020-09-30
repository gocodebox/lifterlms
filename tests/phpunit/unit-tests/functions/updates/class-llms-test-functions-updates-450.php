<?php
/**
 * Test Order Functions
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_450
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_450 extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {
		parent::setupBeforeClass();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-450.php';
	}

	/**
	 * Teardown the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		// Clean open sessions table.
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events_open_sessions" );
		// Delete transients.
		delete_transient( 'llms_update_450_migrate_events_open_sessions' );
		delete_transient( 'llms_450_skipper_events_open_sessions' );
	}

	/**
	 * Test llms_update_450_migrate_events_open_sessions()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_migrate_events_open_sessions() {

		$sessions = LLMS_Sessions::instance();
		$time = time();

		$i = 1;
		$open_session_ids = array();

		while ( $i <= 10 ) {
			$user = $this->factory->user->create();
			wp_set_current_user( $user );

			$time += MINUTE_IN_SECONDS;
			llms_tests_mock_current_time( $time );

			$object_id = LLMS_Unit_Test_Util::call_method( $sessions, 'get_new_id', array( $user ) );
			// Record session start.
			$session_start = LLMS()->events()->record(
				array(
					'actor_id'     => $user,
					'object_type'  => 'session',
					'object_id'    => $object_id,
					'event_type'   => 'session',
					'event_action' => 'start',
				)
			);
			$open_session_ids[] = $session_start->get( 'id' );

			// Close 3 sessions in the middle.
			if ( $i > 4 && $i < 8 ) {
				$time += MINUTE_IN_SECONDS;
				llms_tests_mock_current_time( $time );
				// Record session end.
				LLMS()->events()->record(
					array(
						'actor_id'     => $user,
						'object_type'  => 'session',
						'object_id'    => $object_id,
						'event_type'   => 'session',
						'event_action' => 'end',
					)
				);
				array_pop( $open_session_ids );
			}

			$i++;
		}

		// Fire the session update
		llms_update_450_migrate_events_open_sessions();

		$open_sessions = LLMS_Unit_Test_Util::call_method( $sessions, 'get_open_sessions' );

		// Expect 7 open sessions.
		$this->assertEquals( 7, count( $open_sessions ) );
		$this->assertEquals( count( $open_session_ids ), count( $open_sessions ) );

		// Expect their ids are not the closed ones.
		foreach ( $open_sessions as $os ) {
			$this->assertContains( $os->get('id'), $open_session_ids );
		}

	}

	// TODO: test query pagination

	/**
	 * Test llms_update_450_update_db_version()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		llms_update_450_update_db_version();

		$this->assertNotEquals( '4.5.0', get_option( 'lifterlms_db_version' ) );

		// Unlock the db version update.
		set_transient( 'llms_update_450_migrate_events_open_sessions', 'complete', DAY_IN_SECONDS );

		llms_update_450_update_db_version();

		$this->assertEquals( '4.5.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

}
