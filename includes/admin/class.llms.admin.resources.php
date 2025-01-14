<?php
/**
 * Admin Resources Screen
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 7.4.1
 * @version 7.4.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Resources Screen class.
 *
 * @since 7.4.1
 */
class LLMS_Admin_Resources {

	/**
	 * Retrieve an instance of the WP_Screen for the resources screen.
	 *
	 * @since 7.4.1
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
	 * Register Resource's meta boxes.
	 *
	 * @since 7.4.1
	 *
	 * @return void
	 */
	public static function register_meta_boxes() {

		add_meta_box(
			'llms_dashboard_welcome_video',
			__( 'Welcome to LifterLMS', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-resources',
			'normal',
			'default',
			array( 'view' => 'welcome-video' )
		);

		add_meta_box(
			'llms_dashboard_resource_links',
			__( 'Resource Links', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-resources',
			'normal',
			'default',
			array( 'view' => 'resource-links' )
		);

		add_meta_box(
			'llms_dashboard_getting_started',
			__( 'Getting Started', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-resources',
			'side',
			'default',
			array( 'view' => 'getting-started' )
		);

		/**
		 * Fired after adding the meta boxes on the LifterLMS admin resources page.
		 *
		 * Third parties can hook here to remove LifterLMS core meta boxes.
		 *
		 * @since 7.4.1
		 */
		do_action( 'llms_resources_meta_boxes_added' );
	}

	/**
	 * Prints the resource's meta box html.
	 *
	 * @since 7.4.1
	 *
	 * @param mixed $data_object Often this is the object that's the focus of the current screen,
	 *                           for example a `WP_Post` or `WP_Comment` object.
	 * @param array $box         Meta Box configuration array.
	 * @return void
	 */
	public static function meta_box( $data_object, $box ) {

		if ( isset( $box['args']['view'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the view file.
			echo self::get_view( $box['args']['view'] );
		}
	}

	/**
	 * Handle HTML output on the screen.
	 *
	 * @since 7.4.1
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/resources.php';
	}

	/**
	 * Retrieves the HTML of a view from the views/dashboard directory.
	 *
	 * @since 7.4.1
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
