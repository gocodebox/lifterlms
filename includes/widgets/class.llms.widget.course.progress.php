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
			array( 'description' => __( 'Displays Course Progress on Course or Lesson', 'lifterlms' ), )
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

		// course progress bar
		if ( is_course() ) {
			$course_id = get_the_ID();
		} elseif( is_lesson() ) {
			$lesson = new LLMS_Lesson( get_the_ID() );
			$course_id = $lesson->get_parent_course();
		} else {
			return self::_warn( 'Course progress can only be displayed on course or lesson posts!' );
		}

		$course = new LLMS_Course ( $course_id );

		$course_progress = $course->get_percent_complete();

		echo $course->post->post_title;
		echo lifterlms_course_progress_bar( $course_progress, false, false, false );

	}

}
