<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
* Course Functions
*
* Misc functions used for user interactions with courses and lessons
* TODO: refactor and re-organize
*
* @author codeBOX
* @project lifterLMS
*/

/**
 * Get page object
 *
 * @param string $the_course = false, $args = array()
 * @return array
 */
function get_course( $the_course = false, $args = array() ) {

	return LLMS()->course_factory->get_course( $the_course, $args );

}

/**
 * get lesson object
 *
 * @param mixed $the_lesson = false, $args = array()
 * @return marray
 */
function get_lesson( $the_lesson = false, $args = array() ) {

	return LLMS()->course_factory->get_lesson( $the_lesson, $args );

}
