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

echo '<p class="llms-parent-course-link">' . sprintf(
	// translators: %s: parent course title.
	__( 'Back to: %s', 'lifterlms' ),
	'<a class="llms-lesson-link" href="' . esc_url( get_permalink( $lesson->get( 'parent_course' ) ) ) . '">' . esc_html( get_the_title( $lesson->get( 'parent_course' ) ) ) . '</a>'
) . '</p>';
