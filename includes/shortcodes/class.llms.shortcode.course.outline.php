<?php
/**
 * LifterLMS Course Outline Shortcode
 *
 * [lifterlms_course_outline]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.5.1
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Outline
 *
 * @since 3.5.1
 * @since 3.19.2 Unknown.
 */
class LLMS_Shortcode_Course_Outline extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_course_outline';

	/**
	 * Retrieve the default course id depending on the current post
	 *
	 * @return   int|null
	 * @since    3.5.1
	 * @version  3.17.7
	 */
	private function get_course_id() {

		global $post;
		$post_id = isset( $post->ID ) ? $post->ID : null;

		$course_id = null;

		if ( $post_id ) {

			if ( 'course' !== $post->post_type ) {

				// get the parent
				$parent = llms_get_post_parent_course( $post );
				if ( $parent ) {
					$course_id = $parent->get( 'id' );
				}
			} else {

				$course_id = $post_id;

			}
		}

		return $course_id;

	}

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @return   array
	 * @since    3.5.1
	 * @version  3.5.1
	 */
	protected function get_default_attributes() {
		return array(
			'collapse'     => 0, // outputs a collapsible syllabus when true
			'course_id'    => $this->get_course_id(),
			'outline_type' => 'full', // full, current_section
			'toggles'      => 0, // outputs open/close all toggles when true
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 3.5.1
	 * @since 3.19.2 Unknown.
	 * @since [version] Add fallback for editor block rendering.
	 *
	 * @return string
	 */
	protected function get_output() {

		// Get a reference to the current page where the shortcode is displayed.
		global $post;

		$current_id = $post->ID ?? null;

		$id = $this->get_attribute( 'course_id' );

		if ( ! $id ) {
			$id = $current_id;
		}

		// Show the first course when in Editor if none selected.
		if ( ! $id && llms_is_editor_block_rendering() ) {
			$id = LLMS_Shortcodes_Blocks::get_placeholder_course_id();
		}

		$course  = new LLMS_Course( $id );
		$student = llms_get_student();

		$args = array(
			'collapse'        => $this->get_attribute( 'collapse' ),
			'course'          => $course,
			'current_section' => null,
			'current_lesson'  => null,
			'sections'        => array(),
			'student'         => $student,
			'toggles'         => $this->get_attribute( 'toggles' ),
		);

		if ( ! $course ) {
			return '';
		}

		$next_lesson = $student ? llms_get_post( $student->get_next_lesson( $course->get( 'id' ) ) ) : false;

		if ( 'lesson' === get_post_type() ) {
			$args['current_lesson'] = get_the_ID();
		}

		// show only the current section
		if ( $next_lesson && 'current_section' === $this->get_attribute( 'outline_type' ) ) {

			$section = llms_get_post( $next_lesson->get( 'parent_section' ) );

			$args['sections'][]      = $section;
			$args['current_section'] = $section->get( 'id' );

		} else {

			if ( 'lesson' === get_post_type() ) {
				$lesson = llms_get_post( get_the_ID() );
			} else {
				$lesson = $next_lesson;
			}

			$args['sections']        = $course->get_sections();
			$args['current_section'] = ! empty( $lesson ) && is_a( $lesson, 'LLMS_Post_Model' ) ? $lesson->get( 'parent_section' ) : false;

		}

		ob_start();
		llms_get_template( 'course/outline-list-small.php', $args );
		return ob_get_clean();

	}

}

return LLMS_Shortcode_Course_Outline::instance();
