<?php
/**
 * Course template functions
 *
 * @package LifterLMS/Functions
 *
 * @since 4.11.0
 * @version 4.11.0
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
