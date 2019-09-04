<?php
/**
 * The Template for displaying all single courses.
 *
 * @author      codeBOX
 * @package     lifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

/**
 * @todo move these notices somewhere else
 * @var  LLMS_Course
 */
$course = new LLMS_Course( get_the_ID() );

if ( 'yes' === $course->get( 'time_period' ) ) {
	// if the start date hasn't passed yet
	if ( ! $course->has_date_passed( 'start_date' ) ) {

		llms_add_notice( $course->get( 'course_opens_message' ), 'notice' );

	} elseif ( $course->has_date_passed( 'end_date' ) ) {

		llms_add_notice( $course->get( 'course_closed_message' ), 'error' );

	}
}

llms_print_notices();
do_action( 'lifterlms_single_course_before_summary' );

