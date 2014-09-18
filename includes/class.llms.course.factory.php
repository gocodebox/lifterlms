<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Classes
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class LLMS_Course_Factory {

	public function get_course( $the_course = false, $args = array() ) {
		global $post;

		// if ( false === $the_course ) {
		 	$the_course = $post;
		// } elseif ( is_numeric( $the_course ) ) {
		// 	$the_course = get_post( $the_course);
		// }

		// if ( ! $the_course ) {
		// 	return false;
		// }
		// if ( is_object ( $the_course ) ) {
		// 	$course_id = absint( $the_course->ID );
		// 	$post_type  = $the_course->post_type;
		// }

		// if ( in_array( $post_type, array( 'course', 'course_variation' ) ) ) {
		// 	if ( isset( $args['course_type'] ) ) {
		// 		$course_type = $args['course_type'];
		// 	} elseif ( 'course_variation' == $post_type ) {
		// 		$course_type = 'variation';
		// 	} else {
		// 		$terms        = get_the_terms( $course_id, 'course_type' );
		// 		$course_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
		// 	}

		// 	$classname = 'LLMS_Course_' . implode( '_', array_map( 'ucfirst', explode( '.', $course_type ) ) );
		// } else {
		// 	$classname = false;
		// 	$course_type = false;
		// }

		// // Filter classname so that the class can be overridden if extended.
		// $classname = apply_filters( 'lifterlms_course_class', $classname, $course_type, $post_type, $course_id );

		// if ( ! class_exists( $classname ) )
		 	$classname = 'LLMS_Course_Simple';

		return new LLMS_Course_Simple($the_course, $args );//$classname( $the_course, $args );
	}

}

