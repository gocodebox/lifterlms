<?php
/**
 * Admin Resources Screen
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 7.1.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Resources Screen class.
 *
 * @since 7.1.0
 */
class LLMS_Admin_Resources {

	/**
	 * Retrieve an instance of the WP_Screen for the resources screen.
	 *
	 * @since 7.1.0
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
	 * @since 7.1.0
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/resources.php';
	}

	/**
	 * Retrieves the HTML of a view from the views/dashboard directory.
	 *
	 * @since 7.1.0
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
