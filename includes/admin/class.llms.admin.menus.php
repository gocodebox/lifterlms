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
					case 'Settings':
					case __( 'Settings', 'lifterlms' ):	 	$i = 0;  break;
					case 'Analytics':
					case __( 'Analytics', 'lifterlms' ):	$i = 1;  break;
					case 'System Report':
					case __( 'System Report', 'lifterlms' ):$i = 2; break;
					case 'Analytics (New)': $i = 4; break;
				}

				if ( isset( $i ) ) {
					$arr[ $i ] = $sm;
				}

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
	 * @return void
	 */
	public function display_admin_menu() {

		global $menu;

		if ( current_user_can( apply_filters( 'lifterlms_admin_menu_access', 'manage_options' ) ) ) {

			$menu[51] = array( '', 'read', 'llms-separator','','wp-menu-separator' );

			add_menu_page( 'lifterlms', 'LifterLMS', apply_filters( 'lifterlms_admin_settings_access', 'manage_options' ), 'lifterlms', array( $this, 'settings_page_init' ), plugin_dir_url( LLMS_PLUGIN_FILE ) . 'assets/images/lifterLMS-wp-menu-icon.png', 51 );

			add_submenu_page( 'lifterlms', 'LifterLMS Settings', __( 'Settings', 'lifterlms' ), apply_filters( 'lifterlms_admin_settings_access', 'manage_options' ), 'llms-settings', array( $this, 'settings_page_init' ) );

			add_submenu_page( 'lifterlms', 'LifterLMS Analytics', 'Analytics', apply_filters( 'lifterlms_admin_analytics_access', 'manage_options' ), 'llms-analytics', array( $this, 'analytics_page_init' ) );

			add_submenu_page( 'lifterlms', 'LifterLMS System report', __( 'System Report', 'lifterlms' ), apply_filters( 'lifterlms_admin_system_report_access', 'manage_options' ), 'llms-system-report', array( $this, 'system_report_page_init' ) );

		}

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
	 * Students Menu Item
	 *
	 * Sub menu item to Admin Menu
	 *
	 * @return void
	 */
	public function analytics_page_init() {
		include LLMS_PLUGIN_DIR . 'includes/admin/analytics/class.llms.view.analytics.php';
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
