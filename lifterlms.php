<?php
/**
*Plugin Name: LifterLMS
*Plugin URI: http://gocodebox.com
*Description: Only the greatest LMS plugin ever created!
*Version: 0.1
*Author: codeBOX
*Author URI: http://gocodebox.com
*
*Requires at least: 3.8
*Tested up to: 3.9
*
* @package 		LifterLMS
* @category 	Core
* @author 		codeBOX
*/

/**
 * Restrict direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'LifterLMS') ) :

/**
 * Main LifterLMS Class
 *
 * @class LifterLMS
 */
final class LifterLMS {

	public $version = '0.1';

	protected static $_instance = null;

	public $session = null;

	public $course_factory = null;

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
		add_action( 'init', array( $this, 'include_template_functions' ) );

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
	}

	/**
	 * Include required core classes
	 */
	private function includes() {
		include_once( 'includes/llms.functions.core.php' );
		include_once( 'includes/class.llms.install.php' );


		if ( is_admin() ) {
			include_once( 'includes/admin/class.llms.admin.php' );
		}
		
			// Post types
			include_once( 'includes/class.llms.post-types.php' );

			// Ajax
			include_once( 'includes/class.llms.ajax.php' );

			// Hooks
			include_once( 'includes/llms.template.hooks.php' );

			// Classes
			include_once( 'includes/class.llms.course.php' );		
			include_once( 'includes/class.llms.course.factory.php' );

			$this->course_factory = new LLMS_Course_Factory(); 
		

		if ( ! is_admin() ) {
			$this->frontend_includes();
		}
	
	}

	/**
	 * Include required frontend classes.
	 */
	public function frontend_includes() {
		include_once( 'includes/class.llms.template.loader.php' );
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

		$this->course_factory = new LLMS_Course_Factory(); 

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
		return apply_filters( 'LLMS_TEMPLATE_PATH', 'lifterlms/' );
	}
 	
}

endif;

/**
 * Returns the main instance of LLMS
 *
 * @return LifterLMS
 */
function LLMS() {
	return LifterLMS::instance();
}

return new LifterLMS();
