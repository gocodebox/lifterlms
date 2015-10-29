<?php
/**
* Plugin Name: LifterLMS
* Plugin URI: http://lifterlms.com/
* Description: LifterLMS is the easiest way for anyone to create a Learning Management System on the Wordpress platform.
* Version: 1.4.0
* Author: codeBOX
* Author URI: http://gocodebox.com
*
* Requires at least: 3.8
* Tested up to: 4.3.1
*
* @package 		LifterLMS
* @category 	Core
* @author 		codeBOX
*/

/**
 * Restrict direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require 'vendor/autoload.php';

/**
 * Main LifterLMS Class
 *
 * @class LifterLMS
 */
final class LifterLMS {

	public $version = '1.4.0';

	protected static $_instance = null;

	public   $session = null;

	public $person = null;

	public $course_factory = null;

	public $query = null;

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
	public function __construct() {
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, "autoload" ) );

		// Define constants
		$this->define_constants();

		//Include required files
		$this->includes();

		//Hooks
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( $this, 'integrations' ), 1 );
		add_action( 'init', array( $this, 'include_template_functions' ) );
		add_action( 'init', array( 'LLMS_Shortcodes', 'init' ) );
		//add_action( 'init', array( 'LLMS_Widgets', 'init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );
		
		// load localization files
		add_action( 'plugins_loaded', array( $this, 'localize' ) );

		//Loaded action
		do_action( 'lifterlms_loaded' );
	}

	/**
	 * Auto-load LLMS classes.
	 *
	 * @param mixed $class
	 * @return void
	 */
	public function autoload( $class ) {
		$path = null;
		$class = strtolower( $class );
		$file = 'class.' . str_replace( '_', '.', $class ) . '.php';

		if ( strpos( $class, 'llms_meta_box' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/admin/post-types/meta-boxes/';
		}
		elseif ( strpos( $class, 'llms_shortcode_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/shortcodes/';
		}
		elseif ( strpos( $class, 'llms_widget_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/widgets/';
		}
		elseif ( strpos( $class, 'llms_integration_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/integrations/';
		}
		elseif ( strpos( $class, 'llms_gateway_' ) === 0 ) {
			$path = $this->plugin_path() . '/includes/payment_gateways/';
		}
		elseif (strpos( $class, 'llms_' ) === 0 ) {
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
			define( 'LLMS_PLUGIN_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/');
		}

		if ( ! defined( 'LLMS_SVG_DIR' ) ) {
			define( 'LLMS_SVG_DIR', plugins_url( '/assets/svg/svg.svg', LLMS_PLUGIN_FILE ) );
		}
	}

	/**
	 * Include required core classes
	 */
	private function includes() {

		require_once( 'plugin-updates/plugin-update-checker.php');
		
		include_once( 'includes/llms.functions.core.php' );
		include_once( 'includes/class.llms.install.php' );
		include_once( 'includes/class.llms.session.php' );
		include_once( 'includes/class.llms.session.handler.php' );


		if ( is_admin() ) {
			include_once( 'includes/admin/post-types/meta-boxes/fields/llms.class.meta.box.fields.php' );
			include_once( 'includes/admin/post-types/meta-boxes/fields/llms.interface.meta.box.field.php');
			include_once( 'includes/admin/llms.class.admin.metabox.php' );			
			include_once( 'includes/admin/class.llms.admin.php' );
			include_once( 'includes/admin/class.llms.admin.forms.php' );	
			include_once( 'includes/class.llms.activate.php' );
			include_once( 'includes/class.llms.analytics.php' );
			include_once( 'includes/admin/class.llms.admin.reviews.php' );
		}

		// Date, Number and language formatting
		include_once( 'includes/class.llms.date.php' );
		include_once( 'includes/class.llms.number.php' );
		include_once( 'includes/class.llms.language.php' );

		// svg management
		include_once( 'includes/class.llms.svg.php' );

		// Post types
		include_once( 'includes/class.llms.post-types.php' );

		// Payment Gateway
		include_once( 'includes/class.llms.payment.gateway.php' );

		// Ajax
		include_once( 'includes/class.llms.ajax.php' );
		include_once( 'includes/class.llms.ajax.handler.php' );

		// Hooks
		include_once( 'includes/llms.template.hooks.php' );

		// Classes
		include_once( 'includes/class.llms.product.php' );
		include_once( 'includes/class.llms.course.php' );
		include_once( 'includes/class.llms.section.php' );
		include_once( 'includes/class.llms.lesson.php' );
		include_once( 'includes/class.llms.lesson.handler.php' );
		include_once( 'includes/class.llms.quiz.php' );
		include_once( 'includes/class.llms.question.php' );
		include_once( 'includes/class.llms.course.factory.php' );
		include_once( 'includes/class.llms.review.php' );

		//handler classes
		include_once( 'includes/class.llms.post.handler.php' );
		include_once( 'includes/class.llms.person.handler.php' );

		include_once( 'includes/class.llms.widgets.php' );
		include_once( 'includes/class.llms.widget.php' );

		include_once( 'includes/class.llms.query.php' );

		$this->query = new LLMS_Query();

		$this->course_factory = new LLMS_Course_Factory();

		$session_class = apply_filters( 'lifterlms_session_handler', 'LLMS_Session_Handler' );
		$this->session = new $session_class();


		if ( ! is_admin() ) {
			$this->frontend_includes();
		}

	}

	/**
	 * Include required frontend classes.
	 */
	public function frontend_includes() {
		include_once( 'includes/class.llms.template.loader.php' );
		include_once( 'includes/class.llms.frontend.assets.php' );
		include_once( 'includes/class.llms.frontend.forms.php' );
		include_once( 'includes/class.llms.frontend.password.php' );
		include_once( 'includes/class.llms.person.php' );
		include_once( 'includes/class.llms.shortcodes.php' );
		include_once( 'includes/shortcodes/class.llms.shortcode.my.account.php' );
		include_once( 'includes/shortcodes/class.llms.shortcode.checkout.php' );

		include_once( 'includes/payment_gateways/class.llms.payment.gateway.paypal.php' );

		
		//include_once( 'includes/widgets/class.llms.widget.progress.php' );
	}

	/**
	 * Load Hooks
	 */
	public function include_template_functions() {
		include_once( 'includes/llms.template.functions.php' );
	}

	/**
	 * Init LifterLMS when WordPress Initialises.
	 */
	public function init() {

		do_action( 'before_lifterlms_init' );

		if ( ! is_admin() ) {
			$this->person = new LLMS_Person();
		}

		// Email Actions
		$email_actions = array(
			'lifterlms_created_person',
			'lifterlms_lesson_completed_engagement',
			'lifterlms_custom_engagement'
		);

		foreach ( $email_actions as $action )
			add_action( $action, array( $this, 'send_transactional_email' ), 10, 10 );

		$engagement_actions = array(
			'lifterlms_lesson_completed',
			'lifterlms_section_completed',
			'lifterlms_course_completed',
			'user_register',
			'lifterlms_course_track_completed',
		);

		foreach( $engagement_actions as $action ) {
			add_action( $action, array( $this, 'trigger_engagement' ), 10, 10 );
		}

		$MyUpdateChecker = PucFactory::buildUpdateChecker(
		'http://updates.gocodebox.com/?action=get_metadata&slug=lifterlms',
		__FILE__, 
		'lifterlms', 3
		);

		$MyUpdateChecker->addQueryArgFilter('get_update_keys');

		do_action( 'lifterlms_init' );

	}

	/**
	 * Send Transactional Email
	 * @return void
	 */
	public function send_transactional_email() {
		$this->mailer();
		$args = func_get_args();
		do_action_ref_array( current_filter() . '_notification', $args );
	}

	/**
	 * Trigger Engagemnt
	 * @return [type] [description]
	 */
	public function trigger_engagement() {
		$this->engagements();
		$args = func_get_args();
		do_action_ref_array( current_filter() . '_notification', $args );
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
		return apply_filters( 'LLMS_TEMPLATE_PATH', 'lifterlms/' );
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

	public function activate() {
		return LLMS_Activate::get_instance();
	}

	/**
	 * Process order class
	 *
	 * @return array
	 */
	public function checkout() {
		return LLMS_Order::instance();
	}

	/**
	 * Add Action Links
	 * Settings action links
	 * 
	 * @param array $links [array of links]
	 */
	public function add_action_links ( $links ) {

		$lifter_links = array(
			'<a href="' . admin_url( 'admin.php?page=llms-settings' ) . '">' . __( 'Settings', 'lifterlms' ) . '</a>'
		);

		if (count($links) == 3) {
			return $links;
		}

		return array_merge( $links, $lifter_links );
	}

	/**
	 * Load Localization files
	 * @return void
	 */
	public function localize() {
		
		// load localization files
		load_plugin_textdomain('lifterlms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	}

}

/**
 * Returns the main instance of LLMS
 *
 * @return LifterLMS
 */
function LLMS() {
	return LifterLMS::instance();
}

return new LifterLMS();
