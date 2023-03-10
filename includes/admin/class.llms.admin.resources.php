<?php
/**
 * Admin Resources Screen
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Resources Screen class.
 *
 * @since [version]
 */
class LLMS_Admin_Resources {

	/**
	 * Retrieve an instance of the WP_Screen for the resources screen.
	 *
	 * @since [version]
	 *
	 * @return WP_Screen|boolean Returns a `WP_Screen` object when on the resources screen, otherwise returns `false`.
	 */
	public static function get_screen() {

		$screen = get_current_screen();
		if ( $screen instanceof WP_Screen && 'lifterlms_page_llms-resources' === $screen->id ) {
			return $screen;
		}

		return false;

	}

	/**
	 * Handle HTML output on the screen.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/resources.php';
	}

	/**
	 * Retrieves the HTML of a view from the views/dashboard directory.
	 *
	 * @since [version]
	 *
	 * @param string $file The file basename of the view to retrieve.
	 * @return string The HTML content of the view.
	 */
	private static function get_view( $file ) {

		ob_start();
		include 'views/resources/' . $file . '.php';
		return ob_get_clean();

	}

}
