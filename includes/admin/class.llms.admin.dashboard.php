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
 * Admin Dashboard Screen class.
 *
 * @since [version]
 */
class LLMS_Admin_Dashboard {

	/**
	 * Retrieve an instance of the WP_Screen for the dashboard screen.
	 *
	 * @since [version]
	 *
	 * @return WP_Screen|boolean Returns a `WP_Screen` object when on the dashboard screen, otherwise returns `false`.
	 */
	public static function get_screen() {

		$screen = get_current_screen();
		if ( $screen instanceof WP_Screen && 'lifterlms_page_llms-dashboard' === $screen->id ) {
			return $screen;
		}

		return false;

	}

	/**
	 * Register Dashboard's meta boxes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function register_meta_boxes() {

		add_meta_box(
			'llms_dashboard_quick_links',
			__( 'Quick Links', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-dashboard',
			'normal',
			'default',
			array( 'view' => 'quick-links' )
		);

		add_meta_box(
			'llms_dashboard_addons',
			__( 'Most Popular Add-ons, Courses, and Resources', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-dashboard',
			'normal',
			'default',
			array( 'view' => 'addons' )
		);

		add_meta_box(
			'llms_dashboard_blog',
			__( 'LifterLMS Blog', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-dashboard',
			'side',
			'default',
			array( 'view' => 'blog' )
		);

		add_meta_box(
			'llms_dashboard_podcast',
			__( 'LifterLMS Podcast', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-dashboard',
			'side',
			'default',
			array( 'view' => 'podcast' )
		);

		/**
		 * Fired after adding the meta boxes on the LifterLMS admin dashboard page.
		 *
		 * Third parties can hook here to remove LifterLMS core meta boxes.
		 *
		 * @since [version]
		 */
		do_action( 'llms_dashboard_meta_boxes_added' );

	}

	/**
	 * Prints the dashboard's meta box html.
	 *
	 * @since [version]
	 *
	 * @param mixed $data_object Often this is the object that's the focus of the current screen,
	 *                           for example a `WP_Post` or `WP_Comment` object.
	 * @param array $box         Meta Box configuration array.
	 * @return void
	 */
	public static function meta_box( $data_object, $box ) {

		if ( isset( $box['args']['view'] ) ) {
			echo self::get_view( $box['args']['view'] );
		}

	}

	/**
	 * Handle HTML output on the screen.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/dashboard.php';
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
		include 'views/dashboard/' . $file . '.php';
		return ob_get_clean();

	}

}
