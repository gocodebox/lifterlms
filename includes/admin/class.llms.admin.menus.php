<?php
/**
 * Setup menus in WP Admin.
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Admin
 * @version     0.1
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'LLMS_Admin_Menus' ) ) :

/**
 * LLMS_Admin Class
 */
class LLMS_Admin_Menus {

	/**
	 * LLMS_Admin_Menus Constructor.
	 * @access public
	 * @return LLMS_Admin_Menus
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
	}	

	/**
 	 * Add LifterLMS top level menu items.
	 */
	public function admin_menu() {
		global $menu, $lifterlms;

		if ( current_user_can( 'manage_options' ) ) {
			//$menu[] = array( '', 'read', 'seperator-lifterlms', '', 'wp-menu-seperator lifterlms' );
			$main_page = add_menu_page('Lifter LMS', 'Lifter LMS', 'manage_options', 'lifterlms', 'llms_homepage', '', 7);

			//temporary until I need to start buildting template library.
			function llms_homepage() {
	    		global $title;
	    		?>
	        	<h2><?php echo $title;?></h2>
	        	IT'S ALIVE!!!!
	        	<?php
			}
		}
	}

	/**
	 * Add Settings sub-menu item
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page( 'lifterlms', 'LifterLMS Settings', 'Settings', 'manage_options',
			__FILE__.'_settings', array( $this, 'settings_page' ) );

		add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
	}

	/**
	 * to do: put anything we need to load in memory when initializing the settings page. 
	 */
	public function settings_page_init() {
		//nothing to do yet.
	}

	/**
	 * Init the settings page
	 */
	public function settings_page() {
		include_once( 'class.llms.admin.settings.php' );
		LLMS_Admin_Settings::output();
	}

}

endif;

return new LLMS_Admin_Menus();

