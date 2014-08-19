<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Classes
 * @version     0.1
 */

/**
 * Restrict direct access
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'LLMS_Install' ) ) :

/**
 * LLMS_Install Class
 */

class LLMS_Install {

	protected $min_wp_version = '3.5';
	public $current_wp_version;

	/**
	 * LLMS_Install Constructor.
	 * @access public
	 * @return LLMS_Install
	 */
	public function __construct() {
		$this->current_wp_version = get_bloginfo( 'version' );
		register_activation_hook( LLMS_PLUGIN_FILE, array( $this, 'install' ) );
		add_action( 'admin_init', array( $this, 'check_wp_version' ) );	
		}

	//custom error notice ~ this needs to be moved to it's own class / factory
	public function custom_error_notice(){
     	global $current_screen;
     	if ( $current_screen->parent_base == 'plugins' )
         	echo '<div class="error"><p>Warning - LifterLMS is only compatable with Wordpress version '
         		. $this->min_wp_version . ' or higher. Your current version of Wordpress is ' . $this->current_wp_version  .
         		'. You may experience issues with this plugin until you upgrade your version of Wordpress.</p></div>';
	}

	/**
	 * Check if installed WP version is compatable with plugin requirements. 
	 */
	public function check_wp_version() {
		
		if ( version_compare( get_bloginfo( 'version' ), $this->min_wp_version, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'custom_error_notice' ));
		}

		$this->install();
	}

	/**
	 * Install LLMS
	 */
	public function install() {
		//nothing to do yet	
	}

}

endif;

return new LLMS_Install();