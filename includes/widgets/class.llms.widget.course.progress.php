<?php
/**
* Course progress widget
* Displays course progress
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Widget_Course_Progress extends LLMS_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

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
	 * Overrides parent class
	 *
	 * @see  LLMS_Widget()
	 * @return echo
	 */
	public function widget_contents( $args, $instance ) {
		echo do_shortcode( '[lifterlms_course_progress]' );
	}

}
