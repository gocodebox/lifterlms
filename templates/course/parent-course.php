<?php
/**
 * Back to Course Template
 *
 * @package LifterLMS/Templates
 *
 * @since  1.0.0
 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
 * @since 10.0.6 Moved HTML outside of translatable string for i18n compliance.
 * @version 10.0.6
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post );

echo wp_kses_post(
	sprintf(
		'<p class="llms-parent-course-link">%1$s <a class="llms-lesson-link" href="%2$s">%3$s</a></p>',
		esc_html__( 'Back to:', 'lifterlms' ),
		get_permalink( $lesson->get( 'parent_course' ) ),
		get_the_title( $lesson->get( 'parent_course' ) )
	)
);
