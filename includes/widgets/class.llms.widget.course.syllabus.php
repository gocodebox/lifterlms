<?php
/**
* Course syllabus widget
* Displays all lessons in the course
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Widget_Course_Syllabus extends LLMS_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		WP_Widget::__construct(
			'course_syllabus',
			__( 'Course Syllabus', 'lifterlms' ),
			array( 'description' => __( 'Displays All Course lessons on Course or Lesson page', 'lifterlms' ), )
		);

	}

	/**
	 * Widget Content
	 * Overrides parent class
	 * 
	 * @see  LLMS_Widget()
	 * @return echo
	 */
	public function widget_contents() {
		echo do_shortcode('[lifterlms_course_outline]');
	}

}
