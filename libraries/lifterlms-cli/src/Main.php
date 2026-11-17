<?php
/**
 * LifterLMS CLI Main Class file
 *
 * @package LifterLMS_CLI/Classes
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI;

use WP_CLI\Dispatcher\CommandAddition;

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Assignments Main Class
 *
 * @since 0.0.1
 */
final class Main {

	/**
	 * Current version of the plugin
	 *
	 * @var string
	 */
	public $version = '0.0.1';

	/**
	 * Singleton instance of the class
	 *
	 * @var LifterLMS_CLI
	 */
	private static $instance = null;

	/**
	 * Singleton Instance of the LifterLMS_CLI class
	 *
	 * @since 0.0.1
	 *
	 * @return LifterLMS_CLI
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_CLI_VERSION' ) ) {
			define( 'LLMS_CLI_VERSION', $this->version );
		}

		// Get started (after REST).
		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Add all LifterLMS CLI commands
	 *
	 * This includes a separate file so that commands can be included on their own
	 * when generating documentation.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function commands() {
		require_once LLMS_CLI_PLUGIN_DIR . 'src/commands.php';
	}

	/**
	 * Register WP_CLI hooks
	 *
	 * Loads all commands and sets up license and addon commands to be aborted
	 * if the LifterLMS Helper is not present.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	private function hooks() {

		\WP_CLI::add_hook( 'after_wp_load', array( $this, 'commands' ) );

		// If the Helper doesn't exist abort command addition.
		if ( ! class_exists( 'LifterLMS_Helper' ) ) {
			$helper_commands = array(
				'license',
				'addon install',
				'addon uninstall',
				'addon activate',
				'addon deactivate',
				'addon update',
			);
			foreach ( $helper_commands as $command ) {
				\WP_CLI::add_hook(
					"before_add_command:llms {$command}",
					function( CommandAddition $command_addition ) {
						$command_addition->abort( 'The LifterLMS Helper is required to use this command.' );
					}
				);
			}
		}

	}
	/**
	 * Include all required files and classes
	 *
	 * @since [version
	 *
	 * @return void
	 */
	public function init() {

		// Only load if we have the minimum LifterLMS version installed & activated.
		if ( function_exists( 'llms' ) && version_compare( '5.0.0', llms()->version, '<=' ) ) {

			$this->hooks();

		}

	}

}
