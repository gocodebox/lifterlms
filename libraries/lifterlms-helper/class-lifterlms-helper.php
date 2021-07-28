<?php
/**
 * LifterLMS Helper main class
 *
 * @package LifterLMS_Helper/Main
 *
 * @since 3.2.0
 * @version 3.3.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS_Helper class
 *
 * @since 1.0.0
 * @since 3.2.0 Moved class to its own file from `lifterlms-helper.php`.
 *              Replaced class variable `$_instance` with `$instance`.
 */
final class LifterLMS_Helper {

	/**
	 * Current Plugin Version
	 *
	 * @var string
	 */
	public $version = '3.3.1';

	/**
	 * Singleton instance reference
	 *
	 * @var null
	 */
	protected static $instance = null;

	/**
	 * Instance of the LLMS_Helper_Upgrader class
	 *
	 * Use/retrieve via llms_helper()->upgrader().
	 *
	 * @var null|LLMS_Helper_Upgrader
	 */
	private $upgrader = null;

	/**
	 * Retrieve the main Instance of LifterLMS_Helper
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Use `self::$instance` in favor of `self::$_instance`.
	 *
	 * @return LifterLMS_Helper
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor, get things started!
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function __construct() {

		// Define class constants.
		$this->define_constants();

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Inititalize the Plugin
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Unknown.
	 * @since 3.2.0 Use `llms()` in favor of deprecated `LLMS()`.
	 * @since 3.3.1 Load the upgrader instance in WP_CLI context.
	 *
	 * @return void
	 */
	public function init() {

		// Only load if we have the minimum LifterLMS version installed & activated.
		if ( function_exists( 'llms' ) && version_compare( '3.22.0', llms()->version, '<=' ) ) {

			$this->includes();
			$this->crons();

			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				$this->upgrader = LLMS_Helper_Upgrader::instance();
			}
		}

	}

	/**
	 * Schedule and handle cron functions
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	private function crons() {

		add_action( 'llms_helper_check_license_keys', array( 'LLMS_Helper_Keys', 'check_keys' ) );

		if ( ! wp_next_scheduled( 'llms_helper_check_license_keys' ) ) {
			wp_schedule_event( time(), 'daily', 'llms_helper_check_license_keys' );
		}

	}

	/**
	 * Define constants for plugin
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function define_constants() {

		if ( ! defined( 'LLMS_HELPER_VERSION' ) ) {
			define( 'LLMS_HELPER_VERSION', $this->version );
		}

	}

	/**
	 * Include all clasess required by the plugin
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Include new files.
	 *
	 * @return void
	 */
	private function includes() {

		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-admin-add-ons.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-assets.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-betas.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-cloned.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-install.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-keys.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-options.php';
		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/class-llms-helper-upgrader.php';

		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/models/class-llms-helper-add-on.php';

		require_once LLMS_HELPER_PLUGIN_DIR . 'includes/functions-llms-helper.php';

	}

	/**
	 * Load l10n files
	 *
	 * The first loaded file takes priority
	 *
	 * Files can be found in the following order:
	 *
	 *      WP_LANG_DIR/lifterlms/lifterlms-helper-LOCALE.mo (safe directory will never be automatically overwritten).
	 *      WP_LANG_DIR/plugins/lifterlms-helper-LOCALE.mo (unsafe directory, may be automatically updated).
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Load locale.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms-helper' );

		// Load a lifterlms specific locale file if one exists.
		load_textdomain( 'lifterlms-helper', WP_LANG_DIR . '/lifterlms/lifterlms-helper-' . $locale . '.mo' );

		// Load localization files.
		load_plugin_textdomain( 'lifterlms-helper', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n' );

	}

	/**
	 * Return the singleton instance of the LLMS_Helper_Upgader
	 *
	 * @since 3.0.0
	 *
	 * @return LLMS_Helper_Upgrader
	 */
	public function upgrader() {
		return $this->upgrader;
	}

}
