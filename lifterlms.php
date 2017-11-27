<?php
/**
 * Plugin Name: LifterLMS
 * Plugin URI: https://lifterlms.com/
 * Description: LifterLMS, the #1 WordPress LMS solution, makes it easy to create, sell, and protect engaging online courses.
 * Version: 3.14.9
 * Author: Thomas Patrick Levy, codeBOX LLC
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 4.0
 * Tested up to: 4.9
 *
 * @package     LifterLMS
 * @category 	Core
 * @author 		codeBOX
 */

/**
 * Restrict direct access
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Autoloader
 */
require_once 'vendor/autoload.php';

/**
 * Main LifterLMS Class
 *
 * @class LifterLMS
 */
final class LifterLMS {

	public $version = '3.14.9';

	protected static $_instance = null;

	/**
	 * Array of background handler instances
	 * @var  array
	 */
	public $background_handlers = array();

	public $course_factory = null;
	public $person = null;
	public $query = null;
	public $session = null;

	/**
	 * Main Instance of LifterLMS
	 *
	 * Ensures only one instance of LifterLMS is loaded or can be loaded.
	 *
	 * @static
	 * @see LLMS()
	 * @return LifterLMS - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * LifterLMS Constructor.
	 * @access public
	 * @return LifterLMS
	 */
	private function __construct() {

		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		// Define constants
		$this->define_constants();

		//Include required files
		$this->includes();

		// setup session stuff
		$this->session = new LLMS_Session();

		//Hooks
		register_activation_hook( __FILE__, array( 'LLMS_Install', 'install' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( $this, 'integrations' ), 1 );
		add_action( 'init', array( $this, 'init_background_handlers' ), 5 );
		add_action( 'init', array( $this, 'include_template_functions' ) );
		add_action( 'init', array( 'LLMS_Shortcodes', 'init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ), 10, 1 );

		// tracking
		if ( defined( 'DOING_CRON' ) && DOING_CRON && 'yes' === get_option( 'llms_allow_tracking', 'no' ) ) {
			LLMS_Tracker::init();
		}

		//Loaded action
		do_action( 'lifterlms_loaded' );

	}

	/**
	 * Auto-load LLMS classes.
	 * @param    string  $class  class name being called
	 * @return   void
	 * @since    1.0.0
	 * @version  ??
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

		if ( ! defined( 'LLMS_LOG_DIR' ) ) {
			$upload_dir = wp_upload_dir();
			define( 'LLMS_LOG_DIR', $upload_dir['basedir'] . '/llms-logs/' );
		}

	}

	/**
	 * Include required core classes
	 * @since   1.0.0
	 * @version 3.14.7
	 */
	private function includes() {

		require_once 'includes/llms.functions.core.php';
		require_once 'includes/class.llms.install.php';
		require_once 'includes/class.llms.session.php';

		require_once 'vendor/gocodebox/action-scheduler/action-scheduler.php';

		include_once 'includes/abstracts/abstract.llms.admin.table.php';

		if ( is_admin() ) {

			include_once 'includes/class.llms.generator.php';
			include_once 'includes/admin/class.llms.admin.import.php';

			include_once 'includes/admin/post-types/tables/class.llms.table.student.management.php';

			require_once 'includes/admin/llms.functions.admin.php';
			include_once 'includes/admin/class.llms.admin.menus.php';
			include_once 'includes/admin/class.llms.admin.notices.php';
			include_once 'includes/admin/class.llms.admin.notices.core.php';
			include_once 'includes/admin/class.llms.admin.post-types.php';
			include_once 'includes/admin/class.llms.admin.assets.php';
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

		}

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
		include_once( 'includes/class.llms.student.query.php' );
		include_once( 'includes/notifications/class.llms.notifications.query.php' );

		// Classes
		include_once( 'includes/class.llms.lesson.handler.php' );
		include_once( 'includes/class.llms.quiz.php' );
		include_once( 'includes/class.llms.course.factory.php' );
		include_once( 'includes/class.llms.review.php' );
		include_once( 'includes/class.llms.student.dashboard.php' );
		include_once( 'includes/class.llms.user.permissions.php' );
		include_once( 'includes/class.llms.view.manager.php' );

		//handler classes
		require_once 'includes/class.llms.person.handler.php';
		require_once 'includes/class.llms.post.handler.php';

		include_once( 'includes/class.llms.widgets.php' );
		include_once( 'includes/class.llms.widget.php' );

		include_once( 'includes/class.llms.query.php' );

		// controllers
		include_once( 'includes/controllers/class.llms.controller.orders.php' );
		include_once( 'includes/controllers/class.llms.controller.account.php' );
		include_once( 'includes/controllers/class.llms.controller.quizzes.php' );
		include_once( 'includes/controllers/class.llms.controller.registration.php' );

		// comments
		include_once( 'includes/class.llms.comments.php' );

		// shortcodes
		require_once 'includes/class.llms.shortcodes.php';
		require_once 'includes/shortcodes/class.llms.shortcode.my.account.php';
		require_once 'includes/shortcodes/class.llms.shortcode.checkout.php';

		$this->query = new LLMS_Query();

		$this->course_factory = new LLMS_Course_Factory();

		if ( ! is_admin() ) {

			require_once 'includes/class.llms.https.php';

			include_once( 'includes/class.llms.template.loader.php' );
			include_once( 'includes/class.llms.frontend.assets.php' );
			include_once( 'includes/class.llms.frontend.forms.php' );
			include_once( 'includes/class.llms.frontend.password.php' );
			include_once( 'includes/class.llms.person.php' );

		}

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
	 */
	public function init() {

		do_action( 'before_lifterlms_init' );

		$this->localize();

		if ( ! is_admin() ) {
			$this->person = new LLMS_Person();
		}

		$this->engagements();
		$this->notifications();

		do_action( 'lifterlms_init' );

	}

	public function init_background_handlers() {

		require_once 'includes/libraries/wp-background-processing/wp-async-request.php';
		require_once 'includes/libraries/wp-background-processing/wp-background-process.php';
		require_once 'includes/class.llms.background.enrollment.php';

		$this->background_handlers['enrollment'] = new LLMS_Background_Enrollment();

	}

	/**
	 * Retrieve an instance of the notifications class
	 * @return   obj
	 * @since    ??
	 * @version  ??
	 */
	public function notifications() {
		return LLMS_Notifications::instance();
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
	 * get payment gateways.
	 *
	 * @return array
	 */
	public function payment_gateways() {
		return LLMS_Payment_Gateways::instance();
	}

	public function mailer() {
		return LLMS_Emails::instance();
	}

	/**
	 * get integrations
	 * @return object instance
	 */
	public function integrations() {
		return LLMS_Integrations::instance();
	}

	public function engagements() {
		return LLMS_Engagements::instance();
	}

	public function certificates() {
		return LLMS_Certificates::instance();
	}

	public function achievements() {
		return LLMS_Achievements::instance();
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
