<?php
/**
 * Test notification functions.
 *
 * @package Banner_Notifications/Tests
 *
 * @group functions
 *
 * @since 1.0.0
 */
class Banner_Notifications_Test_Functions extends Banner_Notifications_Unit_Test_Case_Base {

	/**
	 * Test notification_test_plugins_active() when no plugins are active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_test_plugins_active_none_active() {
		// Test with a non-existent plugin
		$this->assertFalse( $this->notifications->notification_test_plugins_active( false, 'non-existent-plugin/non-existent-plugin.php' ) );

		// Test with an array of plugins
		$this->assertFalse( $this->notifications->notification_test_plugins_active( false, array(
			'non-existent-plugin-1/non-existent-plugin-1.php',
			'non-existent-plugin-2/non-existent-plugin-2.php',
		) ) );
	}

	/**
	 * Test notification_test_check_plugin_version() with invalid data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_test_check_plugin_version_invalid_data() {
		// Test with non-array data
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, 'not-an-array' ) );

		// Test with incomplete array data
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, array() ) );
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, array( 'plugin.php' ) ) );
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, array( 'plugin.php', '>' ) ) );

		// Test with empty values
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, array( '', '>', '1.0.0' ) ) );
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, array( 'plugin.php', '', '1.0.0' ) ) );
	}

	/**
	 * Test notification_test_check_plugin_version() with non-existent plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_test_check_plugin_version_non_existent_plugin() {
		$this->assertFalse( $this->notifications->notification_test_check_plugin_version( false, array( 'non-existent-plugin/non-existent-plugin.php', '>', '1.0.0' ) ) );
	}

	/**
	 * Test is_notification_applicable() with date conditions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_is_notification_applicable_dates() {
		// Test with a notification that starts in the future
		$notification = $this->create_mock_notification( array(
			'starts' => date( 'Y-m-d', strtotime( '+1 day' ) ),
		) );
		$this->assertFalse( $this->notifications->is_notification_applicable( $notification ) );

		// Test with a notification that ended in the past
		$notification = $this->create_mock_notification( array(
			'ends' => date( 'Y-m-d', strtotime( '-1 day' ) ),
		) );
		$this->assertFalse( $this->notifications->is_notification_applicable( $notification ) );

		// Test with a notification that is currently active
		$notification = $this->create_mock_notification( array(
			'starts' => date( 'Y-m-d', strtotime( '-1 day' ) ),
			'ends' => date( 'Y-m-d', strtotime( '+1 day' ) ),
		) );
		$this->assertTrue( $this->notifications->is_notification_applicable( $notification ) );
	}

	/**
	 * Test should_show_notification() and should_hide_notification() with plugin conditions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_show_hide_conditions() {
		// Create a notification that should show if a plugin is active
		$notification = $this->create_mock_notification( array(
			'show_if' => array(
				'plugins_active' => array( 'non-existent-plugin/non-existent-plugin.php' ),
			),
		) );

		// The plugin is not active, so the notification should not be shown
		$this->assertFalse( $this->notifications->should_show_notification( $notification ) );

		// Create a notification that should hide if a plugin is active
		$notification = $this->create_mock_notification( array(
			'hide_if' => array(
				'plugins_active' => array( 'non-existent-plugin/non-existent-plugin.php' ),
			),
		) );

		// The plugin is not active, so the notification should not be hidden
		$this->assertFalse( $this->notifications->should_hide_notification( $notification ) );
	}

	/**
	 * Test get_all_notifications() with mock notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_all_notifications() {
		// Set up mock notifications
		$mock_notifications = array(
			$this->create_mock_notification( array(
				'id' => 1,
				'priority' => 2,
			) ),
			$this->create_mock_notification( array(
				'id' => 2,
				'priority' => 1,
			) ),
		);
		$this->set_mock_notifications( $mock_notifications );

		// Get all notifications
		$notifications = $this->notifications->get_all_notifications();

		// Verify that we got the expected notifications, sorted by priority
		$this->assertCount( 2, $notifications );
		$this->assertEquals( 2, $notifications[0]->id ); // Priority 1 should be first
		$this->assertEquals( 1, $notifications[1]->id ); // Priority 2 should be second

		// Clean up
		$this->clear_mock_notifications();
	}

	/**
	 * Test notification_test_site_url_match().
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_test_site_url_match() {
		// Get the site URL
		$site_url = get_site_url();

		// Test with the actual site URL
		$this->assertTrue( $this->notifications->notification_test_site_url_match( false, $site_url ) );

		// Test with a different URL
		$this->assertFalse( $this->notifications->notification_test_site_url_match( false, 'https://example.com' ) );
	}

	/**
	 * Test notification_test_check_option().
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_test_check_option() {
		// Set a test option
		update_option( 'test_option', 'test_value' );

		// Test with the correct option and value
		$this->assertTrue( $this->notifications->notification_test_check_option( false, array( 'test_option', '=', 'test_value' ) ) );

		// Test with the correct option but wrong value
		$this->assertFalse( $this->notifications->notification_test_check_option( false, array( 'test_option', '=', 'wrong_value' ) ) );

		// Test with a non-existent option
		$this->assertFalse( $this->notifications->notification_test_check_option( false, array( 'non_existent_option', '=', 'test_value' ) ) );

		// Clean up
		delete_option( 'test_option' );
	}

}
