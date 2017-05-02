<?php
/**
 * LifterLMS Prerequisite Display
 * @since    3.0.0
 * @version  3.7.5
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;

$course = new LLMS_Course( $post );

?>

<?php if ( $course->has_prerequisite( 'course' ) && ! $course->is_prerequisite_complete( 'course' ) ) :
	$prereq_id = $course->get_prerequisite_id( 'course' ); ?>

	<?php llms_print_notice( sprintf( __( 'Before starting this course you must complete the required prerequisite course: %s', 'lifterlms' ), '<a href="' . get_permalink( $prereq_id ) . '">' . get_the_title( $prereq_id ) . '</a>' ), 'error' ); ?>

<?php endif; ?>

<?php if ( $course->has_prerequisite( 'course_track' ) && ! $course->is_prerequisite_complete( 'course_track' ) ) :
	$track = new LLMS_Track( $course->get_prerequisite_id( 'course_track' ) ); ?>

	<?php llms_print_notice( sprintf( __( 'Before starting this course you must complete the required prerequisite track: %s', 'lifterlms' ), '<a href="' . $track->get_permalink() . '">' . $track->term->name . '</a>' ), 'error' ); ?>

<?php endif;
