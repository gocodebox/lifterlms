<?php
/**
 * Course template functions
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lifterlms_template_course_author' ) ) {
	/**
	 * Get single post author template
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	function lifterlms_template_course_author() {
		llms_get_template( 'course/author.php' );
	}
}
