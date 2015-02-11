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

		$course_syllabus = $course->get_syllabus();
		$html = '';
		
		$syllabus = $course->get_student_progress();

		//var_dump( $syllabus );
		$html .= '<div class="llms-widget-syllabus">';
			$html .= '<ul>';


			//get section data
			foreach ( $syllabus->sections as $section ) {

				$html .= '<li>';
					$html .= '<span class="section-title">' . $section['title'] . '</span>';
						
						foreach ( $syllabus->lessons as $lesson ) {

							if ( $lesson['parent_id'] == $section['id'] ) {

								$html .= '<ul>';
									$html .= '<li>';
										$html .= '<span class="llms-lesson-complete ' . ( $lesson['is_complete'] ? 'done' : '' ) . '"><i class="fa fa-check-circle"></i></span>';
										$html .= '<span class="lesson-title ' . ( $lesson['is_complete'] ? 'done' : '' ) . '"><a href="' . get_permalink( $lesson['id'] ) . '">' . $lesson['title'] . '</a></span>';
									$html .= '</li>';
								$html .= '</ul>';

							}
						
						}

				$html .= '</li>';

			}

			$html .= '</ul>';

		$html .= '</div>';

		echo $html;

	}

}
