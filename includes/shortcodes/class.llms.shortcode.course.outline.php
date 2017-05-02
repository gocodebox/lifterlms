<?php
/**
 * LifterLMS Course Outline Shortcode
 *
 * [lifterlms_course_outline]
 *
 * @since    3.5.1
 * @version  3.5.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_Course_Outline extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_course_outline';

	/**
	 * Retrieve the default course id depending on the current post
	 * @return   int|null
	 * @since    3.5.1
	 * @version  3.5.3
	 */
	private function get_course_id() {

		global $post;
		$post_id = isset( $post->ID ) ? $post->ID : null;

		$course_id = null;

		if ( $post_id ) {

			switch ( $post->post_type ) {

				case 'course':
					$course_id = $post_id;
				break;

				case 'lesson':
					$lesson = llms_get_post( $post_id );
					$course_id = $lesson->get( 'parent_course' );
				break;

				case 'llms_quiz':
					$quiz = llms_get_post( $post_id );
					$lesson_id = $quiz->get_assoc_lesson( get_current_user_id() );
					if ( ! $lesson_id ) {
						$session = LLMS()->session->get( 'llms_quiz' );
						$lesson_id = ( $session && isset( $session->assoc_lesson ) ) ? $session->assoc_lesson : false;
					}
					if ( $lesson_id ) {
						$lesson = llms_get_post( $lesson_id );
						$course_id = $lesson->get( 'parent_course' );
					}
				break;

			}
		}

		return $course_id;

	}

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 * @return   array
	 * @since    3.5.1
	 * @version  3.5.1
	 */
	protected function get_default_attributes() {
		return array(
			'collapse' => 0, // outputs a collapsible syllabus when true
			'course_id' => $this->get_course_id(),
			'outline_type' => 'full', // full, current_section
			'toggles' => 0, // outputs open/close all toggles when true
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    3.5.1
	 * @version  3.6.1
	 */
	protected function get_output() {

		$course = new LLMS_Course( $this->get_attribute( 'course_id' ) );
		$student = new LLMS_Student();

		$args = array(
			'collapse' => $this->get_attribute( 'collapse' ),
			'course' => $course,
			'current_section' => null,
			'sections' => array(),
			'student' => $student,
			'toggles' => $this->get_attribute( 'toggles' ),
		);

		if ( ! $course ) {
			return '';
		}

		$next_lesson = llms_get_post( $student->get_next_lesson( $course->get( 'id' ) ) );

		// show only the current section
		if ( 'current_section' === $this->get_attribute( 'outline_type' ) ) {

			$section = llms_get_post( $next_lesson->get( 'parent_section' ) );

			$args['sections'][] = $section;
			$args['current_section'] = $section->get( 'id' );

		} // End if().
		else {

			if ( 'lesson' === get_post_type() ) {
				$lesson = llms_get_post( get_the_ID() );
			} else {
				$lesson = $next_lesson;
			}

			$args['sections'] = $course->get_sections();
			$args['current_section'] = ! empty( $lesson ) && is_a( $lesson, 'LLMS_Post_Model' ) ? $lesson->get( 'parent_section' ) : false;

		}

		ob_start();
		llms_get_template( 'course/outline-list-small.php', $args );
		return ob_get_clean();

	}

}

return LLMS_Shortcode_Course_Outline::instance();
