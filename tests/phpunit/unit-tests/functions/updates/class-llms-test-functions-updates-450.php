<?php
/**
* Test updates functions when updating to 4.5.0
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_450
 *
 * @since 4.5.0
 * @version 4.15.0
 */
class LLMS_Test_Functions_Updates_450 extends LLMS_UnitTestCase {

	private $sessions;

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since 4.5.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-450.php';
	}

	/**
	 * Setup the test case
	 *
	 * @since 4.5.0
	 * @since 5.3.3 Renamed setUp() to set_up() and moved teardown functions into here.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->sessions = LLMS_Sessions::instance();

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
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_migrate_events_open_sessions() {
		// Create open session events.
		$open_session_ids = $this->create_open_session_events( 10, 3 );

		// Fire the update.
		llms_update_450_migrate_events_open_sessions();

		// Get the migrated open sessions.
		$open_sessions = LLMS_Unit_Test_Util::call_method( $this->sessions, 'get_open_sessions' );

		// Expect 7 open sessions.
		$this->assertEquals( 7, count( $open_sessions ) );
		$this->assertEquals( count( $open_session_ids ), count( $open_sessions ) );

		// Expect their ids match the not closed ones.
		foreach ( $open_sessions as $os ) {
			$this->assertContains( $os->get('id'), $open_session_ids );
		}

	}

	/**
	 * Test "pagination" in llms_update_450_migrate_events_open_sessions()
	 *
	 * @since 4.5.0
	 *
	 * @return void
	 */
	public function test_migrate_events_open_sessions_pagination() {

		// Create open session events.
		$num_open_sessions = 250;
		$open_session_ids  = $this->create_open_session_events( $num_open_sessions );

		$loops = 1;
		// Check how many times the update function needs to run.
		// Internally we fetch 200 sessions at time, we expect it to run the following number of times:
		$expected_loops = 3;
		while ( llms_update_450_migrate_events_open_sessions() ) {
			$loops++;
		}
		$this->assertEquals( $expected_loops, $loops );
		$this->assertEquals( get_transient( 'llms_450_skipper_events_open_sessions' ), $expected_loops * 200 );
		$this->assertEquals( get_transient( 'llms_update_450_migrate_events_open_sessions' ), 'complete' );

		// Get the migrated open sessions.
		global $wpdb;
		$open_sessions = $wpdb->get_col( // db call ok; no-cache ok.
			$wpdb->prepare(
				"
			   SELECT event_id
			   FROM {$wpdb->prefix}lifterlms_events_open_sessions
			   ORDER BY event_id ASC
			   LIMIT %d, %d
		",
				0,
				300
			)
		);

		// Expect all of them have been correctly migrated.
		$this->assertEquals( $num_open_sessions, count( $open_sessions ) );

	}

	/**
	 * Test llms_update_450_update_db_version()
	 *
	 * @since 4.5.0
	 * @since 4.15.0 Get original db_version before removing it.
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		llms_update_450_update_db_version();

		$this->assertNotEquals( '4.5.0', get_option( 'lifterlms_db_version' ) );

		// Unlock the db version update.
		set_transient( 'llms_update_450_migrate_events_open_sessions', 'complete', DAY_IN_SECONDS );

		llms_update_450_update_db_version();

		$this->assertEquals( '4.5.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

	/**
	 * Util to create open sessions in the lifterlms_events table
	 *
	 * @since 4.5.0
	 *
	 * @param int $num_open_sessions   Number of sessions to open.
	 * @param int $num_closed_sessions Optional. Number of sessions to close. Default 0.
	 * @return int[] An array with the events ids of the still open sessions.
	 */
	private function create_open_session_events( $num_open_sessions, $num_closed_sessions = 0 ) {
		$time = time();

		$i = 1;
		$open_session_ids = array();

		while ( $i <= $num_open_sessions ) {
			$user = $this->factory->user->create();
			wp_set_current_user( $user );

			$time += MINUTE_IN_SECONDS;
			llms_tests_mock_current_time( $time );

			$object_id = LLMS_Unit_Test_Util::call_method( $this->sessions, 'get_new_id', array( $user ) );
			// Record session start.
			$session_start = llms()->events()->record(
				array(
					'actor_id'     => $user,
					'object_type'  => 'session',
					'object_id'    => $object_id,
					'event_type'   => 'session',
					'event_action' => 'start',
				)
			);
			$open_session_ids[] = $session_start->get( 'id' );

			// Close N sessions.
			if ( $num_closed_sessions
					&& $num_closed_sessions <= $num_open_sessions
					&& $i > ( $num_open_sessions - $num_closed_sessions ) ) {

				$time += MINUTE_IN_SECONDS;
				llms_tests_mock_current_time( $time );
				// Record session end.
				llms()->events()->record(
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

		return $open_session_ids;
	}

}
