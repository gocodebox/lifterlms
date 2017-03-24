<?php
/**
 * LifterLMS Course Meta Information Shortcode
 *
 * [lifterlms_course_author]
 *
 * @since    3.6.0
 * @version  3.6.0
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
	 * Call the template function for the course element
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function template_function() {

		echo '<div class="llms-meta-info">';
		echo llms_get_author( array(
			'avatar_size' => $this->get_attribute( 'avatar_size' ),
			'bio' => ( 'yes' === $this->get_attribute( 'bio' ) ) ? true : false,
		) );
		echo '</div><!-- .llms-meta-info -->';

	}

}

return LLMS_Shortcode_Course_Author::instance();
