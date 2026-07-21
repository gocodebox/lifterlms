<?php
/**
 * Test example notification from issue description.
 *
 * @package Banner_Notifications/Tests
 *
 * @group example
 *
 * @since 1.0.0
 */
class Banner_Notifications_Test_Example extends Banner_Notifications_Unit_Test_Case_Base {

	/**
	 * The example notification from the issue description.
	 *
	 * @var object
	 */
	protected $example_notification;

	/**
	 * Set up the test case.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Create the example notification from the issue description
		$this->example_notification = $this->create_mock_notification( array(
			'id' => 67,
			'name' => 'kit-extension-for-lifterlms',
			'title' => 'Kit Extension for LifterLMS',
			'content' => ' <p>Integrate your LifterLMS website with Kit for seamless email marketing and automation.</p> <div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex"> <div class="wp-block-button"><a class="wp-block-button__link wp-element-button llms-button-primary" href="https://lifterlms.com/product/convertkit/">Learn More</a></div> </div> ',
			'status' => 'publish',
			'starts' => '2024-04-01',
			'ends' => '2030-04-08',
			'type' => 'info',
			'dashicon' => 'email-alt',
			'priority' => 4,
			'dismissible' => 1,
			'show_if' => array(
				'plugins_active' => array(
					'convertkit/wp-convertkit.php'
				)
			),
			'hide_if' => array(
				'plugins_active' => array(
					'lifterlms-convertkit/lifterlms-convertkit.php'
				)
			)
		) );
	}

	/**
	 * Test that the notification is not shown when neither plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_neither_plugin_active() {
		// Neither plugin is active, so should_show_notification should return false
		$this->assertFalse( $this->notifications->should_show_notification( $this->example_notification ) );

		// Neither plugin is active, so should_hide_notification should return false
		$this->assertFalse( $this->notifications->should_hide_notification( $this->example_notification ) );

		// Overall, the notification should not be applicable
		$this->assertFalse( $this->notifications->is_notification_applicable( $this->example_notification ) );
	}

	/**
	 * Test that the notification is shown when convertkit is active but lifterlms-convertkit is not.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_convertkit_active() {
		// Mock that convertkit is active
		$this->mock_plugin_active( 'convertkit/wp-convertkit.php', true );

		// convertkit is active, so should_show_notification should return true
		$this->assertTrue( $this->notifications->should_show_notification( $this->example_notification ) );

		// lifterlms-convertkit is not active, so should_hide_notification should return false
		$this->assertFalse( $this->notifications->should_hide_notification( $this->example_notification ) );

		// Overall, the notification should be applicable
		$this->assertTrue( $this->notifications->is_notification_applicable( $this->example_notification ) );

		// Clean up
		$this->mock_plugin_active( 'convertkit/wp-convertkit.php', false );
	}

	/**
	 * Test that the notification is not shown when lifterlms-convertkit is active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_lifterlms_convertkit_active() {
		// Mock that lifterlms-convertkit is active
		$this->mock_plugin_active( 'lifterlms-convertkit/lifterlms-convertkit.php', true );

		// lifterlms-convertkit is active, so should_hide_notification should return true
		$this->assertTrue( $this->notifications->should_hide_notification( $this->example_notification ) );

		// Overall, the notification should not be applicable
		$this->assertFalse( $this->notifications->is_notification_applicable( $this->example_notification ) );

		// Clean up
		$this->mock_plugin_active( 'lifterlms-convertkit/lifterlms-convertkit.php', false );
	}

	/**
	 * Test that the notification is not shown when both plugins are active.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_notification_both_plugins_active() {
		// Mock that both plugins are active.
		$this->mock_plugin_active( 'convertkit/wp-convertkit.php', true );
		$this->mock_plugin_active( 'lifterlms-convertkit/lifterlms-convertkit.php', true );

		// convertkit is active, so should_show_notification should return true.
		$this->assertTrue( $this->notifications->should_show_notification( $this->example_notification ) );

		// lifterlms-convertkit is active, so should_hide_notification should return true.
		$this->assertTrue( $this->notifications->should_hide_notification( $this->example_notification ) );

		// Overall, the notification should not be applicable.
		$this->assertFalse( $this->notifications->is_notification_applicable( $this->example_notification ) );

		// Clean up.
		$this->mock_plugin_active( 'convertkit/wp-convertkit.php', false );
		$this->mock_plugin_active( 'lifterlms-convertkit/lifterlms-convertkit.php', false );
	}

}
