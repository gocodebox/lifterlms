<?php
/**
 * Test Order Functions
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_3390
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_3390 extends LLMS_UnitTestCase {

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
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-3390.php';
	}

	/**
	 * Test llms_update_3390_remove_session_options()
	 *
	 * @since [version]
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

		llms_update_3390_remove_session_options();

		$this->assertEquals( 0, $wpdb->get_var( $sql ) );

	}

	/**
	 * Test llms_update_3390_clear_session_cron()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_clear_session_cron() {

		wp_schedule_event( time(), 'daily', 'wp_session_garbage_collection' );

		llms_update_3390_clear_session_cron();

		$this->assertFalse( wp_next_scheduled( 'wp_session_garbage_collection' ) );

	}

	/**
	 * Test llms_update_3390_update_db_version()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		llms_update_3390_update_db_version();

		$this->assertEquals( '3.39.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}


}
