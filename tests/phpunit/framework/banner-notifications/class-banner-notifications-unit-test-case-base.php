<?php
/**
 * Banner Notifications Unit Test Case Bootstrap.
 *
 * @package Banner_Notifications/Tests
 *
 * @since 1.0.0
 * @version 1.0.0
 */

class Banner_Notifications_Unit_Test_Case_Base extends LLMS_Unit_Test_Case {

	/**
	 * The notifications class instance.
	 *
	 * @var object
	 */
	protected $notifications;

	protected $prefix;

	protected $version;

	function filter_active_plugins( $active_plugins ) {
		global $mock_active_plugins;

		if ( isset( $mock_active_plugins ) ) {
			return $mock_active_plugins;
		}

		return $active_plugins;
	}

	/**
	 * Set up the test case.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_active_plugins', array( $this, 'filter_active_plugins' ), 10, 1 );

		global $mock_active_plugins;

		$mock_active_plugins = array();

		// Create a random version number.
		$this->version = sanitize_key( '1.0.' . rand( 0, 999 ) );

		// Create random prefix.
		$this->prefix = 'test_prefix_' . rand( 0, 999 );

		// Initialize the test notifications class with required arguments
		$this->notifications = new Gocodebox_Banner_Notifier( array(
			'prefix' => $this->prefix,
			// Set a random version for testing
			'version' => $this->version,
			'notifications_url' => 'https://example.com/notifications.json',
		) );
	}

	/**
	 * Tear down the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		remove_filter( 'pre_option_active_plugins', array( $this, 'filter_active_plugins' ), 10 );

		global $mock_active_plugins;
		unset( $mock_active_plugins );

		parent::tear_down();

	}

	/**
	 * Create a mock notification object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Notification properties to override defaults.
	 * @return object
	 */
	protected function create_mock_notification( $args = array() ) {
		$defaults = array(
			'id' => 1,
			'name' => 'test-notification',
			'title' => 'Test Notification',
			'content' => '<p>This is a test notification.</p>',
			'status' => 'publish',
			'starts' => date( 'Y-m-d', strtotime( '-2 day' ) ),
			'ends' => date( 'Y-m-d', strtotime( '+2 day' ) ),
			'type' => 'info',
			'dashicon' => 'info',
			'priority' => 4,
			'dismissible' => 1,
			'show_if' => array(),
			'hide_if' => array(),
		);

		return (object) wp_parse_args( $args, $defaults );
	}

	/**
	 * Set a transient with mock notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notifications Array of notification objects.
	 * @return void
	 */
	protected function set_mock_notifications( $notifications = array() ) {
		if ( empty( $notifications ) ) {
			$notifications = array(
				$this->create_mock_notification(),
			);
		}

		set_transient( "{$this->prefix}_notifications_{$this->version}", $notifications );
	}

	/**
	 * Clear the notifications transient.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function clear_mock_notifications() {
		delete_transient( "{$this->prefix}_notifications_{$this->version}" );
	}

	/**
	 * Mock a plugin as active or inactive.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Plugin path.
	 * @param bool   $active Whether the plugin should be active.
	 * @return void
	 */
	protected function mock_plugin_active( $plugin, $active = true ) {
		global $mock_active_plugins;

		if ( ! isset( $mock_active_plugins ) ) {
			$mock_active_plugins = array();

			// Add a filter to is_plugin_active to use our mock active plugins
//			add_filter( 'pre_option_active_plugins', function( $pre ) {
//				global $mock_active_plugins;
//				return $mock_active_plugins;
//			} );
		}

		if ( $active ) {
			$mock_active_plugins[] = $plugin;
		} else {
			$mock_active_plugins = array_diff( $mock_active_plugins, array( $plugin ) );
		}
	}

}
