<?php
/**
 * Test update to 4.0.0 functions
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_400
 *
 * @since 4.0.0
 */
class LLMS_Test_Functions_Updates_400 extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since 4.0.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-400.php';
	}

	/**
	 * Test llms_update_400_remove_session_options()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_remove_session_options() {

		$i = 0;
		while ( $i < 20 ) {
			$string = uniqid();
			add_option( '_wp_session_' . $string, array( 'data' ) );
			add_option( '_wp_session_expires' . $string, time() + HOUR_IN_SECONDS );
			++$i;
		}

		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_wp_session_%';";

		$this->assertEquals( 40, $wpdb->get_var( $sql ) );

		llms_update_400_remove_session_options();

		$this->assertEquals( 0, $wpdb->get_var( $sql ) );

	}

	/**
	 * Test llms_update_400_clear_session_cron()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_clear_session_cron() {

		wp_schedule_event( time(), 'daily', 'wp_session_garbage_collection' );

		llms_update_400_clear_session_cron();

		$this->assertFalse( wp_next_scheduled( 'wp_session_garbage_collection' ) );

	}

	/**
	 * Test llms_update_400_update_db_version()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		llms_update_400_update_db_version();

		$this->assertEquals( '4.0.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}


}
