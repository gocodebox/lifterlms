<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Admin_Menus' ) ) :

/**
* Admin Assets Class
*
* Sets up admin menu items.
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_Menus {

	/**
	* Constructor
	*
	* executes menu setup functions on admin_menu
	*/
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'display_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_settings_menu') );
		
	}	

	/**
	* Admin Menu
	*
	* Sets main (parent) lifterLMS menu item
	* TODO: Remove llms_homepage function and replace with actual page reference like settings. 
	*
	* @return void
	*/
	public function display_admin_menu() {
		//global $menu, $lifterlms;

		if ( current_user_can( 'manage_options' ) ) {

			$lifterLMS = add_menu_page('lifterLMS', 'lifterLMS', 'manage_options', 'lifterlms', 'llms_homepage', plugin_dir_url(LLMS_PLUGIN_FILE) . 'assets/images/lifterLMS-wp-menu-icon.png', '50.15973');

			function llms_homepage() {
	    		global $title;
	    		?>
	        	<h2><?php echo $title; ?></h2>
	        	IT'S ALIVE!!!!
	        	<?php
			}
		}
	}

	/**
	* Settings Menu Item
	*
	* Sub menu item to Admin Menu
	*
	* @return void
	*/
	public function display_settings_menu() {

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Settings', 'Settings', 'manage_options',
		 	'llms-settings', array( $this, 'settings_page_init' ) );
	}

	/**
	 * Init LLMS_Admin_Settings
	 *
	 * @return void
	 */
	public function settings_page_init() {
		include_once( 'class.llms.admin.settings.php' );
		LLMS_Admin_Settings::output();
	}

}

endif;

return new LLMS_Admin_Menus();
