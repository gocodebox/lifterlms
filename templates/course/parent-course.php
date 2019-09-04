<?php
/**
 * Back to Course Template
 *
 * @since  1.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post );

printf( __( '<p class="llms-parent-course-link">Back to: <a class="llms-lesson-link" href="%1$s">%2$s</a></p>', 'lifterlms' ), get_permalink( $lesson->get_parent_course() ), get_the_title( $lesson->get_parent_course() ) );
