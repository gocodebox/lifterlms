<?php
/**
 * Course progress widget
 *
 * Displays course progress
 *
 * @package LifterLMS/Widgets/Classes
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Widget_Course_Progress
 *
 * @since 1.0.0
 */
class LLMS_Widget_Course_Progress extends LLMS_Widget {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		WP_Widget::__construct(
			'course_progress',
			__( 'Course Progress', 'lifterlms' ),
			array(
				'description' => __( 'Displays Course Progress on Course or Lesson', 'lifterlms' ),
			)
		);

	}

	/**
	 * Widget Content
	 *
	 * Overrides parent class
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function widget_contents( $args, $instance ) {
		echo do_shortcode( '[lifterlms_course_progress]' );
	}

}
