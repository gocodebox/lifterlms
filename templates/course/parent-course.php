<?php
/**
 * Back to Course Template
 *
 * @package LifterLMS/Templates
 *
 * @since  1.0.0
 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
 * @version 5.7.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post );

echo wp_kses_post( sprintf( __( '<p class="llms-parent-course-link">Back to: <a class="llms-lesson-link" href="%1$s">%2$s</a></p>', 'lifterlms' ), get_permalink( $lesson->get( 'parent_course' ) ), get_the_title( $lesson->get( 'parent_course' ) ) ) );
