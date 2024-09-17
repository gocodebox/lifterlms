<?php
/**
 * LifterLMS_REST_API main class
 *
 * @package  LifterLMS_REST_API/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.26
 */

defined( 'ABSPATH' ) || exit;

require_once LLMS_REST_API_PLUGIN_DIR . 'includes/traits/class-llms-rest-trait-singleton.php';

/**
 * LifterLMS_REST_API class.
 *
 * @since 1.0.0-beta.1
 */
final class LifterLMS_REST_API {

	use LLMS_REST_Trait_Singleton;

	/**
	 * Current version of the plugin.
	 *
	 * @var string
	 */
	public $version = '1.0.2';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.4 Load authentication early.
	 * @since 1.0.0-beta.17 Only localize when loaded as an independent plugin.
	 *
	 * @return void
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_REST_API_VERSION' ) ) {
			define( 'LLMS_REST_API_VERSION', $this->version );
		}

		/**
		 * When loaded as a library included by the LifterLMS core localization is handled by the LifterLMS core.
		 *
		 * When the plugin is loaded by itself as a plugin, we must localize it independently.
		 */
		if ( ! defined( 'LLMS_REST_API_LIB' ) || ! LLMS_REST_API_LIB ) {
			add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		}

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
	 * @since 1.0.0-beta.9 Include memberships controller class file.
	 * @since 1.0.0-beta.18 Include access plans controller class file.
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
			'server/class-llms-rest-access-plans-controller',
			'server/class-llms-rest-courses-controller',
			'server/class-llms-rest-sections-controller',
			'server/class-llms-rest-lessons-controller',
			'server/class-llms-rest-memberships-controller',
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
	 * @since 1.0.0-beta.9 Init memberships controller.
	 * @since 1.0.0-beta.18 Init access plans controller.
	 *
	 * @return void
	 */
	public function rest_api_controllers_init() {

		$controllers = array(
			'LLMS_REST_API_Keys_Controller',
			'LLMS_REST_Courses_Controller',
			'LLMS_REST_Sections_Controller',
			'LLMS_REST_Lessons_Controller',
			'LLMS_REST_Memberships_Controller',
			'LLMS_REST_Instructors_Controller',
			'LLMS_REST_Students_Controller',
			'LLMS_REST_Students_Progress_Controller',
			'LLMS_REST_Enrollments_Controller',
			'LLMS_REST_Webhooks_Controller',
			'LLMS_REST_Access_Plans_Controller',
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
	 * @since 1.0.0-beta.8 Load webhooks actions a little bit later: at init 6 instead of init 10,
	 *                     just after all the db tables are created (init 5),
	 *                     to avoid PHP warnings on first plugin activation.
	 * @since 1.0.0-beta.22 Bump minimum required version to 6.0.0-alpha.1.
	 *                      Use `llms()` in favor of deprecated `LLMS()`.
	 * @since 1.0.0-beta.25 Perform some db clean-up on user deletion.
	 *                      Bump minimum required version to 6.5.0.
	 * @since 1.0.0-beta.26 Bump minimum required version to 7.0.2.
	 *
	 * @return void
	 */
	public function init() {

		// Only load if we have the minimum LifterLMS version installed & activated.
		if ( ! function_exists( 'llms' ) || version_compare( '7.0.2', llms()->version, '>' ) ) {
			return;
		}

		// Load includes.
		$this->includes();

		add_action( 'init', array( $this->webhooks(), 'load' ), 6 );
		add_action( 'deleted_user', array( $this, 'on_user_deletion' ) );

	}

	/**
	 * When a user is deleted in WordPress, delete corresponding LifterLMS REST API data.
	 *
	 * @since 1.0.0-beta.25
	 *
	 * @param int $user_id The ID of the just deleted WP_User.
	 * @return void
	 */
	public function on_user_deletion( $user_id ) {

		global $wpdb;

		// Delete user's API keys.
		$wpdb->delete(
			"{$wpdb->prefix}lifterlms_api_keys",
			array(
				'user_id' => $user_id,
			),
			array( '%d' )
		);// db-cache ok.
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
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.17 Fixed the name of the MO loaded from the safe directory: `lifterlms-{$locale}.mo` to `lifterlms-rest-{$locale}.mo`.
	 *                      Fixed double slash typo in plugin textdomain path argument.
	 *                      Fixed issue causing language files to not load properly.
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Load locale.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms' );

		// Load from the LifterLMS "safe" directory if it exists.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/lifterlms/lifterlms-rest-' . $locale . '.mo' );

		// Load from the default plugins language file directory.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/plugins/lifterlms-rest-' . $locale . '.mo' );

		// Load from the plugin's language file directory.
		load_textdomain( 'lifterlms', LLMS_REST_API_PLUGIN_DIR . '/i18n/lifterlms-rest-' . $locale . '.mo' );

	}

	/**
	 * Retrieve an instance of the webhooks management singleton.
	 *
	 * @example $webhooks = LLMS_REST_API()->webhooks();
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_REST_Webhooks
	 */
	public function webhooks() {
		return LLMS_REST_Webhooks::instance();
	}

}
