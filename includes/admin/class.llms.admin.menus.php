<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

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
		add_filter( 'custom_menu_order', array( $this, 'submenu_order' ) );

		add_action( 'admin_menu', array( $this, 'display_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_settings_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_analytics_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_students_menu' ) );
		add_action( 'admin_menu', array( $this, 'display_system_report_menu' ) );
	}

	/**
	 * Custom LifterLMS sub menu order
	 * All new sub menu items need to be added to this method
	 * @param  [array] $menu_ord [sub menu array]
	 * @return [array]           [modified sub menu array]
	 */
	public function submenu_order( $menu_ord ) {
		global $submenu;

		if ( isset( $submenu['lifterlms'] ) ) {

			$arr = array();

			foreach ( $submenu['lifterlms'] as $sm ) {
				switch ( $sm[0] ) {

					case __( 'Settings', 'lifterlms' ):	 	$i = 0;  break;
					case __( 'Analytics', 'lifterlms' ):	$i = 1;  break;
					case __( 'Students', 'lifterlms' ):	 	$i = 2;  break;
					case __( 'Emails', 'lifterlms' ):		$i = 3;  break;
					case __( 'Engagements', 'lifterlms' ):	$i = 4;  break;
					case __( 'Achievements', 'lifterlms' ): $i = 5;  break;
					case __( 'Certificates', 'lifterlms' ): $i = 6;  break;
					case __( 'Reviews', 'lifterlms' ):		$i = 7;  break;
					case __( 'Orders', 'lifterlms' ):		$i = 8;  break;
					case __( 'Coupons', 'lifterlms' ):		$i = 9;  break;
					case __( 'Vouchers', 'lifterlms' ):	 	$i = 10; break;
					case __( 'System Report', 'lifterlms' ):$i = 11; break;
				}

				$arr[ $i ] = $sm;
			}

			ksort( $arr );

			array_merge( $arr, $submenu['lifterlms'] );

			$submenu['lifterlms'] = $arr;

		}

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

		global $menu;

		if ( current_user_can( apply_filters( 'lifterlms_admin_menu_access', 'manage_options' ) ) ) {

			$menu[51] = array( '', 'read', 'llms-separator','','wp-menu-separator' );

			add_menu_page( 'lifterlms', 'LifterLMS', apply_filters( 'lifterlms_admin_settings_access', 'manage_options' ), 'lifterlms', 'llms_homepage', plugin_dir_url( LLMS_PLUGIN_FILE ) . 'assets/images/lifterLMS-wp-menu-icon.png', 52 );

			function llms_homepage() {}

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

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Settings', __( 'Settings', 'lifterlms' ), apply_filters( 'lifterlms_admin_settings_access', 'manage_options' ), 'llms-settings', array( $this, 'settings_page_init' ) );
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

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Analytics', __( 'Analytics', 'lifterlms' ), apply_filters( 'lifterlms_admin_analytics_access', 'manage_options' ),
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

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS Students', __( 'Students', 'lifterlms' ), apply_filters( 'lifterlms_admin_students_access', 'manage_options' ), 'llms-students', array( $this, 'students_page_init' ) );
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

	/**
	 * System Report Menu Item
	 *
	 * Sub menu item to Admin Menu
	 *
	 * @return void
	 */
	public function display_system_report_menu() {

		$settings = add_submenu_page( 'lifterlms', 'LifterLMS System report', __( 'System Report', 'lifterlms' ), apply_filters( 'lifterlms_admin_system_report_access', 'manage_options' ), 'llms-system-report', array( $this, 'system_report_page_init' ) );
	}

	/**
	 * Init LLMS_Admin_Settings
	 *
	 * @return void
	 */
	public function system_report_page_init() {
		include_once( 'class.llms.admin.system-report.php' );
		LLMS_Admin_System_Report::output();
	}
}

return new LLMS_Admin_Menus();
