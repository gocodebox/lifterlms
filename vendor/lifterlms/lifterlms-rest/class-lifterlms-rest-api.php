<?php
/**
 * LifterLMS_REST_API main class.
 *
 * @package  LifterLMS_REST_API/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.6
 */

defined( 'ABSPATH' ) || exit;

require_once LLMS_REST_API_PLUGIN_DIR . 'includes/traits/class-llms-rest-trait-singleton.php';

/**
 * LifterLMS_REST_API class.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.4 Load authentication early.
 * @since 1.0.0-beta.6 Load webhook actions early.
 */
final class LifterLMS_REST_API {

	use LLMS_REST_Trait_Singleton;

	/**
	 * Current version of the plugin.
	 *
	 * @var string
	 */
	public $version = '1.0.0-beta.6';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Load authentication early.
	 *
	 * @return void
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_REST_API_VERSION' ) ) {
			define( 'LLMS_REST_API_VERSION', $this->version );
		}

		// i18n.
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		// Authentication needs to run early to handle basic auth.
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-authentication.php';

		// Load everything else.
		add_action( 'plugins_loaded', array( $this, 'init' ), 10 );

	}

	/**
	 * Include files and instantiate classes.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Load authentication early.
	 *
	 * @return void
	 */
	public function includes() {

		// Abstracts.
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/abstracts/class-llms-rest-database-resource.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/abstracts/class-llms-rest-webhook-data.php';

		// Functions.
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/llms-rest-functions.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/server/llms-rest-server-functions.php';

		// Models.
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/models/class-llms-rest-api-key.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/models/class-llms-rest-webhook.php';

		// Classes.
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-api-keys.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-api-keys-query.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-capabilities.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-install.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-webhooks.php';
		include_once LLMS_REST_API_PLUGIN_DIR . 'includes/class-llms-rest-webhooks-query.php';

		// Include admin classes.
		if ( is_admin() ) {
			include_once LLMS_REST_API_PLUGIN_DIR . 'includes/admin/class-llms-rest-admin-settings.php';
			include_once LLMS_REST_API_PLUGIN_DIR . 'includes/admin/class-llms-rest-admin-form-controller.php';
		}

		add_action( 'rest_api_init', array( $this, 'rest_api_includes' ), 5 );
		add_action( 'rest_api_init', array( $this, 'rest_api_controllers_init' ), 10 );

	}

	/**
	 * Retrieve an instance of the API Keys management singleton.
	 *
	 * @example $keys = LLMS_REST_API()->keys();
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_REST_API_Keys
	 */
	public function keys() {
		return LLMS_REST_API_Keys::instance();
	}

	/**
	 * Include REST api specific files.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function rest_api_includes() {

		$includes = array(

			// Abstracts first.
			'abstracts/class-llms-rest-controller-stubs',
			'abstracts/class-llms-rest-controller',
			'abstracts/class-llms-rest-users-controller',
			'abstracts/class-llms-rest-posts-controller',

			// Functions.
			'server/llms-rest-server-functions',

			// Controllers.
			'server/class-llms-rest-api-keys-controller',
			'server/class-llms-rest-courses-controller',
			'server/class-llms-rest-sections-controller',
			'server/class-llms-rest-lessons-controller',
			'server/class-llms-rest-enrollments-controller',
			'server/class-llms-rest-instructors-controller',
			'server/class-llms-rest-students-controller',
			'server/class-llms-rest-students-progress-controller',
			'server/class-llms-rest-webhooks-controller',

		);

		foreach ( $includes as $include ) {
			include_once LLMS_REST_API_PLUGIN_DIR . 'includes/' . $include . '.php';
		}
	}

	/**
	 * Instantiate REST api Controllers.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function rest_api_controllers_init() {

		$controllers = array(
			'LLMS_REST_API_Keys_Controller',
			'LLMS_REST_Courses_Controller',
			'LLMS_REST_Sections_Controller',
			'LLMS_REST_Lessons_Controller',
			'LLMS_REST_Instructors_Controller',
			'LLMS_REST_Students_Controller',
			'LLMS_REST_Students_Progress_Controller',
			'LLMS_REST_Enrollments_Controller',
			'LLMS_REST_Webhooks_Controller',
		);

		foreach ( $controllers as $controller ) {
			$controller_instance = new $controller();
			$controller_instance->register_routes();
		}

	}

	/**
	 * Include all required files and classes.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.6 Load webhooks actions at init 1 instead of init 10.
	 *
	 * @return void
	 */
	public function init() {

		// only load if we have the minimum LifterLMS version installed & activated.
		if ( function_exists( 'LLMS' ) && version_compare( '3.32.0', LLMS()->version, '<=' ) ) {

			// load includes.
			$this->includes();

			add_action( 'init', array( $this->webhooks(), 'load' ), 1 );

		}

	}

	/**
	 * Load l10n files.
	 * The first loaded file takes priority.
	 *
	 * Files can be found in the following order:
	 *      WP_LANG_DIR/lifterlms/lifterlms-LOCALE.mo
	 *      WP_LANG_DIR/plugins/lifterlms-LOCALE.mo
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// load locale.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms' );

		// load a lifterlms specific locale file if one exists.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/lifterlms/lifterlms-' . $locale . '.mo' );

		// load localization files.
		load_plugin_textdomain( 'lifterlms', false, dirname( plugin_basename( __FILE__ ) ) . '//i18n' );

	}

	/**
	 * Retrieve an instance of the webhooks management singleton.
	 *
	 * @example $webhooks = LLMS_REST_API()->webhooks();
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_REST_API_Webhooks
	 */
	public function webhooks() {
		return LLMS_REST_Webhooks::instance();
	}

}
