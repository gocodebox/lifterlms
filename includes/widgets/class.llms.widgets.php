<?php
/**
 * Base Widgets Class
 *
 * Calls WP register widgets for each widget in filter lifterlms_widgets
 *
 * @package LifterLMS/Widgets/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Widgets
 *
 * @since 1.0.0
 * @since 3.12.0 Unknown.
 */
class LLMS_Widgets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

	}

	/**
	 * Registers all lifterlms_widgets
	 *
	 * @since 1.0.0
	 * @since 3.12.0 Unknown.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	public function register_widgets() {

		$widgets = apply_filters(
			'lifterlms_widgets',
			array(
				'LLMS_Widget_Course_Progress',
				'LLMS_Widget_Course_Syllabus',
			)
		);

		if ( class_exists( 'bbPress' ) && 'yes' === get_option( 'llms_integration_bbpress_enabled', 'no' ) ) {

			$widgets[] = 'LLMS_BBP_Widget_Course_Forums_List';
		}

		foreach ( $widgets as $widget ) {

			register_widget( $widget );
		}
	}
}

return new LLMS_Widgets();
