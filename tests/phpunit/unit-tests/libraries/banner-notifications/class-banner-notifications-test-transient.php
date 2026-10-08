<?php
/**
 * Test notifications transient.
 *
 * @package Banner_Notifications/Tests
 *
 * @group transient
 *
 * @since 1.0.0
 */
class Banner_Notifications_Test_Transient extends Banner_Notifications_Unit_Test_Case_Base {

	/**
	 * Test get_all_notifications() with the example notification and convertkit active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_all_notifications_with_example_convertkit_active() {
		// Create the example notification from the issue description
		$example_notifications = json_decode( '[
			{
				"id": 67,
				"name": "kit-extension-for-lifterlms",
				"title": "Kit Extension for LifterLMS",
				"content": " <p>Integrate your LifterLMS website with Kit for seamless email marketing and automation.<\/p> <div class=\"wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex\"> <div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button llms-button-primary\" href=\"https:\/\/lifterlms.com\/product\/convertkit\/\">Learn More<\/a><\/div> <\/div> ",
				"status": "publish",
				"starts": "2024-04-01",
				"ends": "2030-04-08",
				"type": "info",
				"dashicon": "email-alt",
				"priority": 4,
				"dismissible": 1,
				"show_if": {
					"plugins_active": [
						"convertkit\/wp-convertkit.php"
					]
				},
				"hide_if": {
					"plugins_active": [
						"lifterlms-convertkit\/lifterlms-convertkit.php"
					]
				}
			}
		]' );

		// Set the transient with the example notification
		$this->set_mock_notifications( $example_notifications, 86400 );

		// Mock that convertkit is active
		$this->mock_plugin_active( 'convertkit/wp-convertkit.php', true );

		// Get all notifications
		$notifications = $this->notifications->get_all_notifications();

		// Verify that we got the expected notification
		$this->assertCount( 1, $notifications );
		$this->assertEquals( 67, $notifications[0]->id );

		// Clean up
		$this->clear_mock_notifications();
		$this->mock_plugin_active( 'convertkit/wp-convertkit.php', false );
	}

	/**
	 * Test get_all_notifications() with the example notification and lifterlms-convertkit active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_get_all_notifications_with_example_lifterlms_convertkit_active() {
		// Create the example notification from the issue description
		$example_notifications = json_decode( '[
			{
				"id": 67,
				"name": "kit-extension-for-lifterlms",
				"title": "Kit Extension for LifterLMS",
				"content": " <p>Integrate your LifterLMS website with Kit for seamless email marketing and automation.<\/p> <div class=\"wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex\"> <div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button llms-button-primary\" href=\"https:\/\/lifterlms.com\/product\/convertkit\/\">Learn More<\/a><\/div> <\/div> ",
				"status": "publish",
				"starts": "2024-04-01",
				"ends": "2030-04-08",
				"type": "info",
				"dashicon": "email-alt",
				"priority": 4,
				"dismissible": 1,
				"show_if": {
					"plugins_active": [
						"convertkit\/wp-convertkit.php"
					]
				},
				"hide_if": {
					"plugins_active": [
						"lifterlms-convertkit\/lifterlms-convertkit.php"
					]
				}
			}
		]' );

		// Set the transient with the example notification
		$this->set_mock_notifications( $example_notifications );

		// Mock that lifterlms-convertkit is active
		$this->mock_plugin_active( 'lifterlms-convertkit/lifterlms-convertkit.php', true );

		// Get all notifications
		$notifications = $this->notifications->get_all_notifications();

		// Verify that we got no notifications because the hide_if condition is met
		$this->assertCount( 0, $notifications );

		// Clean up
		$this->clear_mock_notifications();
		$this->mock_plugin_active( 'lifterlms-convertkit/lifterlms-convertkit.php', false );
	}

}
