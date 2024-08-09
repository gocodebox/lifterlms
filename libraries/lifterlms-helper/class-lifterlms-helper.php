<?php
/**
 * LifterLMS Helper main class
 *
 * @package LifterLMS_Helper/Main
 *
 * @since 3.2.0
 * @version 3.4.0
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
	public $version = '3.5.4';

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
	 * @since 3.4.0 Only localize when loaded as an independent plugin.
	 *
	 * @return void
	 */
	private function __construct() {

		// Define class constants.
		$this->define_constants();

		/**
		 * When loaded as a library included by the LifterLMS core localization is handled by the LifterLMS core.
		 *
		 * When the plugin is loaded by itself as a plugin, we must localize it independently.
		 */
		if ( ! defined( 'LLMS_HELPER_LIB' ) || ! LLMS_HELPER_LIB ) {
			add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		}

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

		require_once 'vendor-prefixed/autoload.php';

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
	 * Load l10n files.
	 *
	 * This method is only used when the plugin is loaded as a standalone plugin (for development purposes),
	 * otherwise (when loaded as a library from within the LifterLMS core plugin) the localization
	 * strings are included into the LifterLMS Core plugin's po/mo files and are localized by the LifterLMS
	 * core plugin.
	 *
	 * Files can be found in the following order (The first loaded file takes priority):
	 *   1. WP_LANG_DIR/lifterlms/lifterlms-rest-LOCALE.mo
	 *   2. WP_LANG_DIR/plugins/lifterlms-rest-LOCALE.mo
	 *   3. WP_CONTENT_DIR/plugins/lifterlms-rest/i18n/lifterlms-rest-LOCALE.mo
	 *
	 * Note: The function `load_plugin_textdomain()` is not used because the same textdomain as the LifterLMS core
	 * is used for this plugin but the file is named `lifterlms-rest` in order to allow using a separate language
	 * file for each codebase.
	 *
	 * @since 2.5.0
	 * @since 3.4.0 Updated to the core textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Load locale.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms' );

		// Load from the LifterLMS "safe" directory if it exists.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/lifterlms/lifterlms-helper-' . $locale . '.mo' );

		// Load from the default plugins language file directory.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/plugins/lifterlms-helper-' . $locale . '.mo' );

		// Load from the plugin's language file directory.
		load_textdomain( 'lifterlms', LLMS_HELPER_PLUGIN_DIR . '/i18n/lifterlms-helper-' . $locale . '.mo' );
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
