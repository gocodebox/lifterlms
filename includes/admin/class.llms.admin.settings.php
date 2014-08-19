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

if ( ! class_exists( 'LLMS_Admin_Settings' ) ) :

/**
 * LLMS_Admin_Settings Class
 */
class LLMS_Admin_Settings {

	private static $settings = array();

	/**
	 * Include the settings classes
	 */
	public static function get_settings_pages() {

		if ( empty( self::$settings ) ) {
			$settings = array();
			include_once( 'settings/class.llms.settings.page.php' );
			$settings[] = include( 'settings/class.llms.settings.general.php' );
		}

		return self::$settings;
	}

	/**
	 * Settings page.
	 *
	 * Displays the main LifterLMS settings page.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {

		self::get_settings_pages();

		include 'views/html.admin.settings.php';
	}

}

endif;