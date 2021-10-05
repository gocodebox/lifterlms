<?php
/**
* Test updates functions when updating to 5.2.0
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_520
 *
 * @since 5.2.0
 */
class LLMS_Test_Functions_Updates_520 extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since 5.2.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-520.php';
	}

	/**
	 * Test llms_update_520_upcoming_reminder_notification_backward_compat() method
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_llms_update_520_upcoming_reminder_notification_backward_compat() {

		$subscribers_for_type = array(
			'email' => array(
				'student',
			),
			'basic' => array(
				'student',
				'author',
				'custom',
			),
		);

		foreach ( $subscribers_for_type as $type => $subscribers ) {
			$this->assertEquals(
				array(),
				get_option( "llms_notification_upcoming_payment_reminder_{$type}_subscribers", array() )
			);
		}

		// Run the update.
		llms_update_520_upcoming_reminder_notification_backward_compat();

		foreach ( $subscribers_for_type as $type => $subscribers ) {
			$this->assertEquals(
				array_fill_keys( $subscribers, 'no' ),
				get_option( "llms_notification_upcoming_payment_reminder_{$type}_subscribers", array() )
			);
		}

		// Create the option and check it's not overridden.
		foreach ( $subscribers_for_type as $type => $subscribers ) {
			update_option( "llms_notification_upcoming_payment_reminder_{$type}_subscribers", array_fill_keys( $subscribers, 'yes' ) );

			$this->assertNotEquals(
				array_fill_keys( $subscribers, 'no' ),
				get_option( "llms_notification_upcoming_payment_reminder_{$type}_subscribers", array() )
			);
		}

	}

	/**
	 * Test llms_update_520_update_db_version()
	 *
	 * @since 5.2.0
	 *
	 * @return void
	 */
	public function test_update_520_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		llms_update_520_update_db_version();

		$this->assertEquals( '5.2.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

}
