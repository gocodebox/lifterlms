<?php
/**
 * Admin Helper Screen
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 7.1.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Helper Screen class.
 *
 * @since 7.1.0
 */
class LLMS_Admin_Helper {

	/**
	 * Retrieve an instance of the WP_Screen for the helper screen.
	 *
	 * @since 7.1.0
	 *
	 * @return WP_Screen|boolean Returns a `WP_Screen` object when on the helper screen, otherwise returns `false`.
	 */
	public static function get_screen() {

		$screen = get_current_screen();
		if ( $screen instanceof WP_Screen && 'lifterlms_page_llms-helper' === $screen->id ) {
			return $screen;
		}

		return false;

	}

	/**
	 * Register Helpers's meta boxes.
	 *
	 * @since 7.1.0
	 *
	 * @return void
	 */
	public static function register_meta_boxes() {

		add_meta_box(
			'llms_helper_video',
			__( 'Welcome Video', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-helper',
			'normal',
			'default',
			array( 'view' => 'video' )
		);

		add_meta_box(
			'llms_helper_resources',
			__( 'Resources', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-helper',
			'normal',
			'default',
			array( 'view' => 'resources' )
		);

		add_meta_box(
			'llms_helper_documentation',
			__( 'Most Popular Documentation for a Quick Start', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-helper',
			'normal',
			'default',
			array( 'view' => 'documentation' )
		);

		add_meta_box(
			'llms_helper_developers',
			__( 'Developer Hub', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-helper',
			'side',
			'default',
			array( 'view' => 'developers' )
		);

		add_meta_box(
			'llms_helper_support',
			__( 'Get Support', 'lifterlms' ),
			array( __CLASS__, 'meta_box' ),
			'toplevel_page_llms-helper',
			'side',
			'default',
			array( 'view' => 'support' )
		);
		/**
		 * Fired after adding the meta boxes on the LifterLMS admin helper page.
		 *
		 * Third parties can hook here to remove LifterLMS core meta boxes.
		 *
		 * @since 7.1.0
		 */
		do_action( 'llms_helper_meta_boxes_added' );

	}

	/**
	 * Prints the helper's meta box html.
	 *
	 * @since 7.1.0
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
	 * @since 7.1.0
	 *
	 * @return void
	 */
	public static function output() {
		include 'views/helper.php';
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
		include 'views/helper/' . $file . '.php';
		return ob_get_clean();

	}

}
