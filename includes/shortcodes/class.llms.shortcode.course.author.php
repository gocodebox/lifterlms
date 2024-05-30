<?php
/**
 * LifterLMS Course Meta Information Shortcode
 *
 * [lifterlms_course_author]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.6.0
 * @version 3.11.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Author
 *
 * @since 3.6.0
 * @since 3.11.1 Unknown.
 */
class LLMS_Shortcode_Course_Author extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_author';

	/**
	 * Get default shortcode attributes.
	 *
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @since 3.6.0
	 *
	 * @return array
	 */
	protected function get_default_attributes() {
		return array(
			'avatar_size' => 48,
			'bio'         => 'yes',
			'course_id'   => get_the_ID(),
		);
	}

	/**
	 * Retrieve the author ID of the course
	 *
	 * Lessons and Quizzes cascade up
	 *
	 * @since 3.11.1
	 *
	 * @return int|null
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
	 *
	 * @since 3.6.0
	 * @since 3.11.1
	 *
	 * @return void
	 */
	protected function template_function() {

		echo '<div class="llms-meta-info">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in function.
		echo llms_get_author(
			array(
				'avatar_size' => $this->get_attribute( 'avatar_size' ),
				'bio'         => ( 'yes' === $this->get_attribute( 'bio' ) ) ? true : false,
				'user_id'     => $this->get_author_id(),
			)
		);
		echo '</div><!-- .llms-meta-info -->';
	}
}

return LLMS_Shortcode_Course_Author::instance();
