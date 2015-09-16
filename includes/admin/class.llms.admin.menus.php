<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Admin Assets Class
*
* Sets up admin menu items.
*
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

		//resort sub menu items
		add_filter( 'custom_menu_order', array( $this, 'wpse_73006_submenu_order' ) );
		
		add_action( 'admin_menu', array( $this, 'display_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_settings_menu') );
		add_action( 'admin_menu', array( $this, 'display_analytics_menu') );
		add_action( 'admin_menu', array( $this, 'display_students_menu') );
	}	

	/**
	 * Custom LifterLMS sub menu order
	 * All new sub menu items need to be added to this method
	 * @param  [array] $menu_ord [sub menu array]
	 * @return [array]           [modified sub menu array]
	 */
	public function wpse_73006_submenu_order( $menu_ord ) {
	    global $submenu;

	    $arr = array();
	    $arr[] = $submenu['lifterlms'][8];  // Settings
	    $arr[] = $submenu['lifterlms'][9];  // Analytics
	    $arr[] = $submenu['lifterlms'][10]; // Students
	    $arr[] = $submenu['lifterlms'][5];  // Membership
	    $arr[] = $submenu['lifterlms'][1];  // Emails
	    $arr[] = $submenu['lifterlms'][2];  // Certificates
	    $arr[] = $submenu['lifterlms'][3];  // Achievements
	    $arr[] = $submenu['lifterlms'][4];  // Engagements
	    $arr[] = $submenu['lifterlms'][0];  // Orders
	    $arr[] = $submenu['lifterlms'][6];  // Coupons
	    $arr[] = $submenu['lifterlms'][7];  // Reviews

	    $submenu['lifterlms'] = $arr;

	    return $menu_ord;
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

		if ( current_user_can( 'edit_posts' ) ) {

			$lifterLMS = add_menu_page('lifterlms', 'LifterLMS', 'edit_posts', 'lifterlms', 'llms_homepage', plugin_dir_url(LLMS_PLUGIN_FILE) . 'assets/images/lifterLMS-wp-menu-icon.png', '50.15973');

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

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Settings', 'Settings', 'edit_posts',
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

	/**
	* Analytics Menu Item
	*
	* Sub menu item to Admin Menu
	*
	* @return void
	*/
	public function display_analytics_menu() {

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Analytics', 'Analytics', apply_filters('lifterlms_analytics_access', 'edit_posts'),
		 	'llms-analytics', array( $this, 'analytics_page_init' ) );
	}

	/**
	 * Init LLMS_Admin_Analytics
	 *
	 * @return void
	 */
	public function analytics_page_init() {
		include_once( 'class.llms.admin.analytics.php' );
		LLMS_Admin_Analytics::output();
	}

	/**
	* Students Menu Item
	*
	* Sub menu item to Admin Menu
	*
	* @return void
	*/
	public function display_students_menu() {

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Students', 'Students', 'edit_posts',
		 	'llms-students', array( $this, 'students_page_init' ) );
	}

	/**
	 * Init LLMS_Admin_Students
	 *
	 * @return void
	 */
	public function students_page_init() {
		include_once( 'class.llms.admin.students.php' );
		LLMS_Admin_Students::output();
	}
}

return new LLMS_Admin_Menus();
