<?php
/**
 * LifterLMS Course Meta Information Shortcode
 *
 * [lifterlms_course_author]
 *
 * @since    3.6.0
 * @version  3.11.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_Course_Author extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_course_author';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 * @return   array
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function get_default_attributes() {
		return array(
			'avatar_size' => 48,
			'bio' => 'yes',
			'course_id' => get_the_ID(),
		);
	}

	/**
	 * Retrieve the author ID of th course
	 * Lessons and Quizzes cascade up
	 * @return   int|null
	 * @since    3.11.1
	 * @version  3.11.1
	 */
	private function get_author_id() {

		$post = llms_get_post( $this->get_attribute( 'course_id' ) );
		if ( ! $post ) {
			return null;
		}
		if ( in_array( $post, array( 'lesson', 'quiz' ) ) ) {
			$course = llms_get_post_parent_course( $post->get( 'id' ) );
			if ( ! $course ) {
				return null;
			}
		}
		return $post->get( 'author' );

	}

	/**
	 * Call the template function for the course element
	 * @return   void
	 * @since    3.6.0
	 * @version  3.11.1
	 */
	protected function template_function() {

		echo '<div class="llms-meta-info">';
		echo llms_get_author( array(
			'avatar_size' => $this->get_attribute( 'avatar_size' ),
			'bio' => ( 'yes' === $this->get_attribute( 'bio' ) ) ? true : false,
			'user_id' => $this->get_author_id(),
		) );
		echo '</div><!-- .llms-meta-info -->';

	}

}

return LLMS_Shortcode_Course_Author::instance();
