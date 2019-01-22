<?php
/**
 * Plugin Name: LifterLMS
 * Plugin URI: https://lifterlms.com/
 * Description: LifterLMS, the #1 WordPress LMS solution, makes it easy to create, sell, and protect engaging online courses.
 * Version: 3.27.0
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 4.8
 * Tested up to: 5.0.3
 */
defined( 'ABSPATH' ) || exit;

/**
 * Autoloader
 */
require_once 'vendor/autoload.php';

/**
 * Main LifterLMS Class
 * @class LifterLMS
 */
final class LifterLMS {

	public $version = '3.27.0';

	protected static $_instance = null;

	public $course_factory = null;
	public $person = null;
	public $query = null;
	public $session = null;

	/**
	 * Main Instance of LifterLMS
	 * Ensures only one instance of LifterLMS is loaded or can be loaded.
	 * @see      LLMS()
	 * @return   LifterLMS - Main instance
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * LifterLMS Constructor.
	 * @return   LifterLMS
	 * @since    1.0.0
	 * @version  3.21.1
	 */
	private function __construct() {

		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		// Define constants
		$this->define_constants();

		// localize as early as possible
		// since 4.6 the "just_in_time" l10n will load the default (not custom) file first
		// so we must localize before any l10n functions (like `__()`) are used
		// so that our custom "safe" location will always load first
		$this->localize();

		//Include required files
		$this->includes();

		// setup session stuff
		$this->session = new LLMS_Session();

		// Hooks
		register_activation_hook( __FILE__, array( 'LLMS_Install', 'install' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( $this, 'integrations' ), 1 );
		add_action( 'init', array( $this, 'processors' ), 5 );
		add_action( 'init', array( $this, 'include_template_functions' ) );
		add_action( 'init', array( 'LLMS_Shortcodes', 'init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ), 10, 1 );

		// tracking
		if ( defined( 'DOING_CRON' ) && DOING_CRON && 'yes' === get_option( 'llms_allow_tracking', 'no' ) ) {
			LLMS_Tracker::init();
		}

		// Loaded action
		do_action( 'lifterlms_loaded' );

	}

	/**
	 * Auto-load LLMS classes.
	 * @param    string  $class  class name being called
	 * @return   void
	 * @since    1.0.0
	 * @version  3.15.0
	 */
	public function autoload( $class ) {

		$class = strtolower( $class );
		// if ( false === strpos( $class, 'llms' ) ) {
		// 	return;
		// }

		$path = null;
		$fileize = str_replace( '_', '.', $class );
		$file = 'class.' . $fileize . '.php';

		if ( strpos( $class, 'llms_meta_box' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/admin/post-types/meta-boxes/';
		} elseif ( strpos( $class, 'llms_widget_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/widgets/';
		} elseif ( strpos( $class, 'llms_integration_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/integrations/';
		} elseif ( strpos( $class, 'llms_controller_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/controllers/';
		} elseif ( 0 === strpos( $class, 'llms_abstract' ) ) {
			$path = $this->plugin_path() . '/includes/abstracts/';
			$file = $fileize . '.php';
		} elseif ( 0 === strpos( $class, 'llms_interface' ) ) {
			$path = $this->plugin_path() . '/includes/interfaces/';
			$file = $fileize . '.php';
		} elseif ( strpos( $class, 'llms_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/';
		}

		if ( $path && is_readable( $path . $file ) ) {
			include_once( $path . $file );
			return;
		}
	}

	/**
	 * Define LifterLMS Constants
	 * @since    1.0.0
	 * @version  3.17.8
	 */
	private function define_constants() {

		if ( ! defined( 'LLMS_PLUGIN_FILE' ) ) {
			define( 'LLMS_PLUGIN_FILE', __FILE__ );
		}

		if ( ! defined( 'LLMS_VERSION' ) ) {
			define( 'LLMS_VERSION', $this->version );
		}

		if ( ! defined( 'LLMS_TEMPLATE_PATH' ) ) {
			define( 'LLMS_TEMPLATE_PATH', $this->template_path() );
		}

		if ( ! defined( 'LLMS_PLUGIN_DIR' ) ) {
			define( 'LLMS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
		}

		if ( ! defined( 'LLMS_SVG_DIR' ) ) {
			define( 'LLMS_SVG_DIR', plugins_url( '/assets/svg/svg.svg', LLMS_PLUGIN_FILE ) );
		}

		$upload_dir = wp_upload_dir();
		if ( ! defined( 'LLMS_LOG_DIR' ) ) {
			define( 'LLMS_LOG_DIR', $upload_dir['basedir'] . '/llms-logs/' );
		}

		if ( ! defined( 'LLMS_TMP_DIR' ) ) {
			define( 'LLMS_TMP_DIR', $upload_dir['basedir'] . '/llms-tmp/' );
		}

		if ( ! defined( 'LLMS_PLUGIN_URL' ) ) {

			/**
			 * URL to the plugin directory for assets, etc
			 * @since   3.17.8
			 * @version 3.17.8
			 */
			define( 'LLMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		}

		if ( ! defined( 'LLMS_ASSETS_SUFFIX' ) ) {

			// if we're loading in debug mode
			$debug = ( defined( 'SCRIPT_DEBUG' ) ) ? SCRIPT_DEBUG : false;

			/* if debugging, load the unminified version
			 * on production, load the minified one
			 */
			$min = ( $debug ) ? '' : '.min';

			/**
			 * Assets suffix
			 * Defines if minified versions of assets should be loaded
			 * @since   3.17.8
			 * @version 3.17.8
			 */
			define( 'LLMS_ASSETS_SUFFIX', $min );
		}

	}

	/**
	 * Include required core classes
	 * @since   1.0.0
	 * @version 3.25.0
	 */
	private function includes() {

		if ( function_exists( 'has_blocks' ) && ! defined( 'LLMS_BLOCKS_VERSION' ) ) {
			require_once 'vendor/lifterlms/lifterlms-blocks/lifterlms-blocks.php';
		}

		require_once 'includes/llms.functions.core.php';
		require_once 'includes/class.llms.install.php';
		require_once 'includes/class.llms.session.php';
		require_once 'includes/class.llms.cache.helper.php';

		require_once 'vendor/gocodebox/action-scheduler/action-scheduler.php';

		require_once 'includes/class.llms.hasher.php';

		require_once 'includes/processors/class.llms.processors.php';
		include_once 'includes/abstracts/abstract.llms.admin.table.php';

		include_once 'includes/admin/class.llms.admin.assets.php';

		// privacy components
		require_once 'includes/privacy/class-llms-privacy.php';

		if ( is_admin() ) {

			include_once 'includes/class.llms.dot.com.api.php';

			include_once 'includes/class.llms.generator.php';
			include_once 'includes/admin/class.llms.admin.import.php';

			include_once 'includes/controllers/class.llms.controller.admin.quiz.attempts.php';

			include_once 'includes/admin/post-types/tables/class.llms.table.student.management.php';

			require_once 'includes/admin/llms.functions.admin.php';
			include_once 'includes/admin/class.llms.admin.menus.php';
			include_once 'includes/admin/class.llms.admin.notices.php';
			include_once 'includes/admin/class.llms.admin.notices.core.php';
			include_once 'includes/admin/class.llms.admin.post-types.php';
			include_once 'includes/admin/post-types/class.llms.post.tables.php';
			if ( ! empty( $_GET['page'] ) && 'llms-setup' === $_GET['page'] ) {
				require_once 'includes/admin/class.llms.admin.setup.wizard.php';
			}

			include_once( 'includes/admin/reporting/widgets/class.llms.analytics.widget.ajax.php' );
			include_once( 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php' );
			include_once( 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php' );
			include_once( 'includes/class.llms.analytics.php' );
			include_once( 'includes/admin/class.llms.admin.reviews.php' );
			require 'includes/abstracts/abstract.llms.admin.metabox.php';
			include_once( 'includes/admin/class.llms.admin.user.custom.fields.php' );
			include_once( 'includes/admin/class.llms.student.bulk.enroll.php' );

			require_once 'includes/admin/class-llms-admin-review.php';

		}

		// legacy
		include_once( 'includes/class.llms.quiz.legacy.php' );

		// nav menus
		require_once 'includes/class.llms.nav.menus.php';

		include 'includes/notifications/class.llms.notifications.php';

		// Date, Number and language formatting
		include_once( 'includes/class.llms.date.php' );
		include_once( 'includes/class.llms.number.php' );

		// oembed
		include_once( 'includes/class.llms.oembed.php' );

		// svg management
		include_once( 'includes/class.llms.svg.php' );

		// Post types
		include_once( 'includes/class.llms.post-types.php' );

		// sidebars
		require_once 'includes/class.llms.sidebars.php';

		// Payment Gateway
		require_once 'includes/abstracts/abstract.llms.payment.gateway.php';
		require_once 'includes/class.llms.gateway.manual.php';

		// Ajax
		include_once( 'includes/class.llms.ajax.php' );
		include_once( 'includes/class.llms.ajax.handler.php' );

		// Hooks
		include_once( 'includes/llms.template.hooks.php' );

		// Models
		require_once 'includes/abstracts/abstract.llms.post.model.php';
		foreach ( glob( LLMS_PLUGIN_DIR . 'includes/models/*.php', GLOB_NOSORT ) as $model ) {
			require_once $model;
		}

		// queries
		include_once( 'includes/abstracts/abstract.llms.database.query.php' );
		include_once( 'includes/class.llms.query.quiz.attempt.php' );
		include_once( 'includes/class.llms.query.user.postmeta.php' );
		include_once( 'includes/class.llms.student.query.php' );
		include_once( 'includes/notifications/class.llms.notifications.query.php' );

		// Classes
		include_once( 'includes/class.llms.lesson.handler.php' );
		include_once( 'includes/class.llms.course.factory.php' );
		include_once( 'includes/class.llms.question.types.php' );
		include_once( 'includes/class.llms.post.relationships.php' );
		include_once( 'includes/class.llms.review.php' );
		include_once( 'includes/class.llms.student.dashboard.php' );
		include_once( 'includes/class.llms.user.permissions.php' );
		include_once( 'includes/class.llms.view.manager.php' );
		include_once( 'includes/class.llms.l10n.js.php' );

		//handler classes
		require_once 'includes/class.llms.person.handler.php';
		require_once 'includes/class.llms.post.handler.php';

		include_once( 'includes/widgets/class.llms.widgets.php' );
		include_once( 'includes/widgets/class.llms.widget.php' );

		include_once( 'includes/class.llms.query.php' );

		// controllers
		include_once 'includes/controllers/class.llms.controller.achievements.php';
		include_once 'includes/controllers/class.llms.controller.certificates.php';
		include_once 'includes/controllers/class.llms.controller.lesson.progression.php';
		include_once 'includes/controllers/class.llms.controller.orders.php';
		include_once 'includes/controllers/class.llms.controller.quizzes.php';

		// form controllers
		include_once 'includes/forms/controllers/class.llms.controller.account.php';
		include_once 'includes/forms/controllers/class.llms.controller.login.php';
		include_once 'includes/forms/controllers/class.llms.controller.registration.php';

		// comments
		include_once( 'includes/class.llms.comments.php' );

		// shortcodes
		require_once 'includes/shortcodes/class.llms.shortcodes.php';
		require_once 'includes/shortcodes/class.llms.shortcode.my.account.php';
		require_once 'includes/shortcodes/class.llms.shortcode.checkout.php';

		$this->query = new LLMS_Query();

		$this->course_factory = new LLMS_Course_Factory();

		if ( ! is_admin() ) {

			require_once 'includes/class.llms.https.php';

			include_once( 'includes/class.llms.template.loader.php' );
			include_once( 'includes/class.llms.frontend.assets.php' );

			// form classes
			include_once( 'includes/forms/frontend/class.llms.frontend.forms.php' );
			include_once( 'includes/forms/frontend/class.llms.frontend.password.php' );

			include_once( 'includes/class.llms.person.php' );

		}

		require_once 'includes/class-llms-grades.php';
		require_once 'includes/class.llms.playnice.php';

	}

	/**
	 * Load Hooks
	 */
	public function include_template_functions() {
		// if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			include_once( 'includes/llms.template.functions.php' );
		// }
	}

	/**
	 * Init LifterLMS when WordPress Initialises.
	 * @return    void [<description>]
	 * @since     1.0.0
	 * @version   3.21.1
	 */
	public function init() {

		do_action( 'before_lifterlms_init' );

		if ( ! is_admin() ) {
			$this->person = new LLMS_Person();
		}

		$this->engagements();
		$this->notifications();

		do_action( 'lifterlms_init' );

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

	public function mailer() {
		return LLMS_Emails::instance();
	}

	public function achievements() {
		return LLMS_Achievements::instance();
	}
	public function certificates() {
		return LLMS_Certificates::instance();
	}

	public function engagements() {
		return LLMS_Engagements::instance();
	}

	/**
	 * Grading instance
	 * @return   obj
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function grades() {
		return LLMS_Grades::instance();
	}

	/**
	 * get integrations
	 * @return object instance
	 */
	public function integrations() {
		return LLMS_Integrations::instance();
	}

	/**
	 * Retrieve an instance of the notifications class
	 * @return   obj
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function notifications() {
		return LLMS_Notifications::instance();
	}

	/**
	 * get payment gateways.
	 * @return array
	 */
	public function payment_gateways() {
		return LLMS_Payment_Gateways::instance();
	}

	/**
	 * Load all background processors and
	 * access to them programattically a processor via LLMS()->processors()->get( $processor )
	 * @return   LLMS_Processors
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function processors() {
		return LLMS_Processors::instance();
	}

	/**
	 * Add Action Links
	 * Settings action links
	 *
	 * @param array $links [array of links]
	 */
	public function add_action_links( $links ) {

		$lifter_links = array(
			'<a href="' . admin_url( 'admin.php?page=llms-settings' ) . '">' . __( 'Settings', 'lifterlms' ) . '</a>'
		);

		if ( count( $links ) == 3 ) {
			return $links;
		}

		return array_merge( $links, $lifter_links );
	}

	/**
	 * Load Localization files
	 *
	 * The first loaded file takes priority
	 *
	 * Files can be found in the following order:
	 * 		WP_LANG_DIR/lifterlms/lifterlms-LOCALE.mo
	 * 		WP_LANG_DIR/plugins/lifterlms-LOCALE.mo
	 *
	 * @return void
	 */
	public function localize() {

		// load locale
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms' );

		// load a lifterlms specific locale file if one exists
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/lifterlms/lifterlms-' . $locale . '.mo' );

		// load localization files
		load_plugin_textdomain( 'lifterlms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

}

/**
 * shame shame shame...
 * http://i.giphy.com/c2YyNySJ1CbFC.gif
 */
// @codingStandardsIgnoreStart
/**
 * Returns the main instance of LLMS
 *
 * @return LifterLMS
 */
function LLMS() {
	return LifterLMS::instance();
}
// @codingStandardsIgnoreEnd
return LLMS();
