<?php
/**
 * Test the main plugin file.
 *
 * @package Banner_Notifications/Tests
 *
 * @group plugin
 *
 * @since 1.0.0
 */
class Banner_Notifications_Test_Plugin extends Banner_Notifications_Unit_Test_Case_Base {

	/**
	 * Test that the plugin is loaded and initialized.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_plugin_initialized() {
		// Verify that the notifications object is initialized.
		$this->assertInstanceOf( 'Gocodebox_Banner_Notifier', $this->notifications );
	}

	/**
	 * Test that the notification test filters are registered.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_test_filters_registered() {
		// Get all filters for the notification tests
		global $wp_filter;

		// Check that the notification test filters are registered
		$this->assertTrue( isset( $wp_filter["{$this->prefix}_notification_test_plugins_active"] ) );
		$this->assertTrue( isset( $wp_filter["{$this->prefix}_notification_test_check_plugin_version"] ) );
		$this->assertTrue( isset( $wp_filter["{$this->prefix}_notification_test_site_url_match"] ) );
		$this->assertTrue( isset( $wp_filter["{$this->prefix}_notification_test_check_option"] ) );
	}

	/**
	 * Test that the get_all_notifications method returns an array.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_all_notifications_returns_array() {
		// Get all notifications
		$notifications = $this->notifications->get_all_notifications();

		// Verify that we got an array
		$this->assertIsArray( $notifications );
	}

	/**
	 * Test that the get_next_notification method returns false when there are no notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_next_notification_no_notifications() {
		// Clear any existing notifications
		$this->clear_mock_notifications();

		// Get the next notification
		$notification = $this->notifications->get_next_notification();

		// Verify that we got false
		$this->assertFalse( $notification );
	}

	/**
	 * Test that the get_next_notification method returns a notification when there are notifications.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_next_notification_with_notifications() {
		$user = $this->factory->user->create();
		wp_set_current_user( $user );

		$this->set_mock_notifications();

		$notification = $this->notifications->get_next_notification();

		$this->assertIsObject( $notification );
		$this->assertEquals( 1, $notification->id );

		$this->clear_mock_notifications();
	}

}
