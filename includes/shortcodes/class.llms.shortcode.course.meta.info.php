<?php
/**
 * LifterLMS Course Meta Information Shortcode
 *
 * [lifterlms_course_meta_info]
 *
 * @since    3.6.0
 * @version  3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Shortcode_Course_Meta_Info extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_course_meta_info';

	/**
	 * Call the template function for the course element
	 *
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function template_function() {

		echo '<div class="llms-meta-info">';
		lifterlms_template_single_length();
		lifterlms_template_single_difficulty();
		lifterlms_template_single_course_tracks();
		lifterlms_template_single_course_categories();
		lifterlms_template_single_course_tags();
		echo '</div><!-- .llms-meta-info -->';

	}

}

return LLMS_Shortcode_Course_Meta_Info::instance();
