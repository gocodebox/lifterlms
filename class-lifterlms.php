<?php
/**
 * Main LifterLMS class
 *
 * @package LifterLMS/Main
 *
 * @since 1.0.0
 * @version 7.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main LifterLMS Class
 *
 * @since 1.0.0
 * @since 3.32.0 Update action-scheduler to latest version; load staging class on the admin panel.
 * @since 3.34.0 Include the LLMS_Admin_Users_Table class.
 * @since 3.36.0 Added events classes and methods.
 * @since 3.36.1 Include SendWP Connector.
 * @since 3.37.0 Move theme support methods to LLMS_Theme_Support.
 * @since 3.38.1 Include LLMS_Mime_Type_Extractor class.
 * @since 4.0.0 Update session management.
 *              Remove deprecated class files and variables.
 *              Move includes (file loading) into the LLMS_Loader class.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 */
final class LifterLMS {

	use LLMS_Trait_Singleton;

	/**
	 * LifterLMS Plugin Version.
	 *
	 * @var string
	 */
	public $version = '7.5.3';

	/**
	 * LLMS_Assets instance
	 *
	 * @var LLMS_Assets
	 */
	public $assets = null;

	/**
	 * LLMS_Query instance
	 *
	 * @var LLMS_Query
	 */
	public $query = null;

	/**
	 * Session instance
	 *
	 * @var LLMS_Session
	 */
	public $session = null;

	/**
	 * LifterLMS Constructor.
	 *
	 * @since 1.0.0
	 * @since 3.21.1 Unknown
	 * @since 4.0.0 Load `$this->session` at `plugins_loaded` in favor of during class construction.
	 *               Remove deprecated `__autoload()` & initialize new file loader class.
	 * @since 4.13.0 Check site duplicate status on `admin_init`.
	 * @since 5.3.0 Move the loading of the LifterLMS autoloader to the main `lifterlms.php` file.
	 * @since 6.1.0 Automatically load payment gateways.
	 * @since 6.4.0 Moved registration of `LLMS_Shortcodes::init()` with the 'init' hook to `LLMS_Shortcodes::__construct()`.
	 *
	 * @return void
	 */
	private function __construct() {

		/**
		 * Localize as early as possible.
		 *
		 * Since 4.6 the "just_in_time" l10n will load the default (not custom) file first
		 * so we must localize before any l10n functions (like `__()`) are used
		 * so that our custom "safe" location will always load first.
		 */
		$this->localize();

		$this->define_constants();

		$this->init_assets();

		$this->query = new LLMS_Query();

		// Hooks.
		register_activation_hook( __FILE__, array( 'LLMS_Install', 'install' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ), 10, 1 );

		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( $this, 'integrations' ), 1 );
		add_action( 'init', array( $this, 'processors' ), 5 );
		add_action( 'init', array( $this, 'events' ), 5 );
		add_action( 'init', array( $this, 'init_session' ), 6 ); // After table installation which happens at init 5.
		add_action( 'init', array( $this, 'payment_gateways' ) );
		add_action( 'init', array( $this, 'include_template_functions' ) );

		add_action( 'admin_init', array( 'LLMS_Site', 'check_status' ) );

		// Tracking.
		if ( defined( 'DOING_CRON' ) && DOING_CRON && 'yes' === get_option( 'llms_allow_tracking', 'no' ) ) {
			LLMS_Tracker::init();
		}

		/**
		 * Action fired after LifterLMS is fully loaded.
		 *
		 * @since Unknown
		 */
		do_action( 'lifterlms_loaded' );

	}

	/**
	 * Define LifterLMS Constants
	 *
	 * @since 1.0.0
	 * @since 3.17.8 Added `LLMS_PLUGIN_URL` && `LLMS_ASSETS_SUFFIX`.
	 * @since 4.0.0 Moved definitions of `LLMS_PLUGIN_FILE` and `LLMS_PLUGIN_DIR` to the main `lifterlms.php` file.
	 *              Use `llms_maybe_define_constant()` to reduce code complexity.
	 * @since 7.2.0 Added `LLMS_ASSETS_VERSION` constant.
	 *
	 * @return void
	 */
	private function define_constants() {

		llms_maybe_define_constant( 'LLMS_VERSION', $this->version );
		llms_maybe_define_constant( 'LLMS_TEMPLATE_PATH', $this->template_path() );
		llms_maybe_define_constant( 'LLMS_PLUGIN_URL', plugin_dir_url( LLMS_PLUGIN_FILE ) );

		$upload_dir = wp_upload_dir();
		llms_maybe_define_constant( 'LLMS_LOG_DIR', $upload_dir['basedir'] . '/llms-logs/' );
		llms_maybe_define_constant( 'LLMS_TMP_DIR', $upload_dir['basedir'] . '/llms-tmp/' );

		// If we're loading in debug mode.
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$wp_debug     = defined( 'WP_DEBUG' ) && WP_DEBUG;

		// If debugging, load the unminified version otherwise load minified.
		if ( ! defined( 'LLMS_ASSETS_SUFFIX' ) ) {
			define( 'LLMS_ASSETS_SUFFIX', $script_debug ? '' : '.min' );
		}

		// If debugging, use time for asset version otherwise use plugin version.
		if ( ! defined( 'LLMS_ASSETS_VERSION' ) ) {
			define( 'LLMS_ASSETS_VERSION', ( $script_debug || $wp_debug ) ? time() : $this->version );
		}
	}

	/**
	 * Load Hooks
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	public function include_template_functions() {
		include_once 'includes/llms.template.functions.php';
	}

	/**
	 * Init LifterLMS when WordPress Initialises.
	 *
	 * @since 1.0.0
	 * @since 3.21.1 Unknown.
	 * @since 4.0.0 Don't initialize removed `LLMS_Person()` class.
	 * @since 4.12.0 Check site staging/duplicate status & trigger associated actions.
	 * @since 4.13.0 Remove site staging/duplicate check and run only on `admin_init`.
	 * @since 5.8.0 Initialize block templates.
	 *
	 * @return void
	 */
	public function init() {

		do_action( 'before_lifterlms_init' );

		$this->block_templates();
		$this->engagements();
		$this->notifications();

		do_action( 'lifterlms_init' );

	}

	/**
	 * Initialize the core asset handler class.
	 *
	 * @since 4.4.0
	 *
	 * @return LLMS_Assets
	 */
	private function init_assets() {

		$this->assets = new LLMS_Assets( 'llms-core' );

		$this->assets->define( 'scripts', require LLMS_PLUGIN_DIR . 'includes/assets/llms-assets-scripts.php' );
		$this->assets->define( 'styles', require LLMS_PLUGIN_DIR . 'includes/assets/llms-assets-styles.php' );

		return $this->assets;

	}

	/**
	 * Initializes an LLMS_Session() into the $session variable
	 *
	 * @since 4.0.0
	 *
	 * @return LLMS_Session
	 */
	public function init_session() {

		if ( is_null( $this->session ) ) {
			$this->session = new LLMS_Session();
		}

		return $this->session;

	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'llms_template_path', 'lifterlms/' );
	}

	/**
	 * Retrieve the LLMS_Emails singleton.
	 *
	 * @since Unknown
	 *
	 * @return LLMS_Emails
	 */
	public function mailer() {
		return LLMS_Emails::instance();
	}

	/**
	 * Retrieve the LLMS_Achievements singleton.
	 *
	 * @since Unknown
	 *
	 * @return LLMS_Achievements
	 */
	public function achievements() {
		return LLMS_Achievements::instance();
	}

	/**
	 * Retrieve the LLMS_Certificates singleton.
	 *
	 * @since Unknown
	 *
	 * @return LLMS_Certificates
	 */
	public function certificates() {
		return LLMS_Certificates::instance();
	}

	/**
	 * Retrieve the LLMS_Engagements singleton.
	 *
	 * @since Unknown
	 *
	 * @return LLMS_Engagements
	 */
	public function engagements() {
		return LLMS_Engagements::instance();
	}

	/**
	 * Block templates instance.
	 *
	 * @since 5.8.0
	 *
	 * @return LLMS_Block_Templates
	 */
	public function block_templates() {
		return LLMS_Block_Templates::instance();
	}

	/**
	 * Events instance.
	 *
	 * @since 3.36.0
	 *
	 * @return LLMS_Events
	 */
	public function events() {
		return LLMS_Events::instance();
	}

	/**
	 * Grading instance
	 *
	 * @since    3.24.0
	 *
	 * @return   LLMS_Grades
	 */
	public function grades() {
		return LLMS_Grades::instance();
	}

	/**
	 * Get integrations
	 *
	 * @return LLMS_Integrations instance
	 */
	public function integrations() {
		return LLMS_Integrations::instance();
	}

	/**
	 * Retrieve an instance of the notifications class
	 *
	 * @return   LLMS_Notifications
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function notifications() {
		return LLMS_Notifications::instance();
	}

	/**
	 * Get payment gateways.
	 *
	 * @return LLMS_Payment_Gateways
	 */
	public function payment_gateways() {
		return LLMS_Payment_Gateways::instance();
	}

	/**
	 * Load all background processors.
	 *
	 * @since    3.15.0
	 *
	 * @return   LLMS_Processors
	 */
	public function processors() {
		return LLMS_Processors::instance();
	}

	/**
	 * Add plugin settings Action Links
	 *
	 * @since Unknown
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function add_action_links( $links ) {

		$lifter_links = array(
			'<a href="' . admin_url( 'admin.php?page=llms-settings' ) . '">' . __( 'Settings', 'lifterlms' ) . '</a>',
		);

		if ( 3 === count( $links ) ) {
			return $links;
		}

		return array_merge( $links, $lifter_links );
	}

	/**
	 * Localize the plugin
	 *
	 * Language files can be found in the following locations (The first loaded file takes priority):
	 *
	 *   1. wp-content/languages/lifterlms/lifterlms-{LOCALE}.mo
	 *
	 *      This is recommended "safe" location where custom language files can be stored. A file
	 *      stored in this directory will never be automatically overwritten.
	 *
	 *   2. wp-content/languages/plugins/lifterlms-{LOCALE}.mo
	 *
	 *      This is the default directory where WordPress will download language files from the
	 *      WordPress GlotPress server during updates. If you store a custom language file in this
	 *      directory it will be overwritten during updates.
	 *
	 *   3. wp-content/plugins/lifterlms/languages/lifterlms-{LOCALE}.mo
	 *
	 *      This is the the LifterLMS plugin directory. A language file stored in this directory will
	 *      be removed from the server during a LifterLMS plugin update.
	 *
	 * @since Unknown
	 * @since 4.9.0 Use `llms_load_textdomain()`.
	 *
	 * @return void
	 */
	public function localize() {

		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms-functions-l10n.php';
		llms_load_textdomain( 'lifterlms' );

	}

}
