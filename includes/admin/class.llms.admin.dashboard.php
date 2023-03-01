<?php
/**
 * Admin Dashboard Screen
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Dashboard Screen
 *
 * @since [version]
 */
class LLMS_Admin_Dashboard {

	/**
	 * Retrieve an instance of the WP_Screen for the dashboard screen
	 *
	 * @since [version]
	 *
	 * @return WP_Screen|boolean Returns a `WP_Screen` object when on the dashboard screen, otherwise returns `false`.
	 */
	protected function get_screen() {

		$screen = get_current_screen();
		if ( $screen instanceof WP_Screen && 'lifterlms_page_llms-dashboard' === $screen->id ) {
			return $screen;
		}

		return false;

	}

	/**
	 * Retrieves the HTML of a view from the views/dashboard directory.
	 *
	 * @since [version]
	 *
	 * @param string $file The file basename of the view to retrieve.
	 * @return string The HTML content of the view.
	 */
	protected function get_view( $file ) {

		ob_start();
		include 'views/dashboard/' . $file . '.php';
		return ob_get_clean();

	}

	/**
	 * Handle HTML output on the screen
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/dashboard.php';
	}

}

return new LLMS_Admin_Dashboard();
