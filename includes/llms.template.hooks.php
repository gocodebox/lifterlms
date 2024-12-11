<?php
/**
 * LifterLMS template hooks
 *
 * Defines all action hooks used by various templates.
 *
 * @package LifterLMS/Hooks
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main content wrappers
 *
 * @since Unknown
 */
add_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
add_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );

/**
 * Single Course
 *
 * @since Unknown
 */
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_video', 20 );
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_audio', 30 );

add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_start', 5 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_length', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_difficulty', 20 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tracks', 25 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_categories', 30 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tags', 35 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_course_author', 40 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_end', 50 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_prerequisites', 55 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_pricing_table', 60 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_progress', 60 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_syllabus', 90 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_reviews', 100 );

/**
 * Single Lesson
 *
 * @since Unknown
 * @since 7.5.0 Maybe add favorite template.
 */
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_parent_course', 10 );
if ( llms_is_favorites_enabled() ) {
	add_action( 'lifterlms_single_lesson_before_summary', 'llms_template_favorite', 10 );
}
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_lesson_video', 20 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_lesson_audio', 20 );

add_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_complete_lesson_link', 10 );
add_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_lesson_navigation', 20 );

/**
 * Course & Membership Loops
 *
 * @since Unknown
 */
add_action( 'lifterlms_archive_description', 'lifterlms_archive_description', 10 );
add_action( 'lifterlms_before_loop', 'lifterlms_loop_start', 10 );
add_action( 'lifterlms_loop', 'lifterlms_loop', 10 );
add_action( 'lifterlms_after_loop', 'lifterlms_loop_end', 10 );

/**
 * Course & Membership Loop Items
 *
 * @since Unknown
 */
add_action( 'lifterlms_before_loop_item', 'lifterlms_loop_featured_video', 8 );

add_action( 'lifterlms_before_loop_item', 'lifterlms_loop_link_start', 10 );

add_action( 'lifterlms_before_loop_item_title', 'lifterlms_template_loop_thumbnail', 10 );
add_action( 'lifterlms_before_loop_item_title', 'lifterlms_template_loop_progress', 15 );

add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_author', 10 );
add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_length', 15 );
add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_difficulty', 20 );
add_action( 'lifterlms_after_loop_item_title', 'lifterlms_template_loop_lesson_count', 22 );

add_action( 'lifterlms_after_loop_item', 'lifterlms_loop_link_end', 5 );

/**
 * Course Syllabus
 *
 * @since 7.5.0
 */
if ( llms_is_favorites_enabled() ) {
	add_action( 'llms_lesson_preview_after_title', 'llms_template_syllabus_favorite_lesson_preview', 10 );
}

/**
 * Emails
 *
 * @since Unknown
 */
add_action( 'lifterlms_email_header', 'llms_email_header', 10, 1 );
add_action( 'lifterlms_email_body', 'llms_email_body', 10, 1 );
add_action( 'lifterlms_email_footer', 'llms_email_footer', 10 );

/**
 * Pricing Tables
 *
 * @since Unknown
 * @since 3.38.0 Added `lifterlms_product_not_purchasable`.
 */
add_action( 'llms_access_plan', 'llms_template_access_plan', 10 );

add_action( 'llms_before_access_plan', 'llms_template_access_plan_feature', 10 );

add_action( 'llms_acces_plan_content', 'llms_template_access_plan_title', 10 );
add_action( 'llms_acces_plan_content', 'llms_template_access_plan_pricing', 20 );
add_action( 'llms_acces_plan_content', 'llms_template_access_plan_restrictions', 30 );
add_action( 'llms_acces_plan_content', 'llms_template_access_plan_description', 40 );

add_action( 'llms_acces_plan_footer', 'llms_template_access_plan_trial', 10 );
add_action( 'llms_acces_plan_footer', 'llms_template_access_plan_button', 20 );

add_action( 'lifterlms_product_not_purchasable', 'llms_template_product_not_purchasable', 10 );

/**
 * Privacy
 *
 * @since Unknown
 */
add_action( 'llms_registration_privacy', 'llms_privacy_policy_form_field', 10 );
add_action( 'llms_registration_privacy', 'llms_agree_to_terms_form_field', 20 );

/**
 * Quizzes
 *
 * @since Unknown
 */
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_wrapper_start', 5 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_return_link', 10 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_results', 15 );

add_action( 'llms_single_quiz_attempt_results', 'lifterlms_template_quiz_attempt_results', 10 );
add_action( 'llms_single_quiz_attempt_results_main', 'lifterlms_template_quiz_attempt_results_questions_list', 10 );

add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_meta_info', 25 );

add_action( 'lifterlms_single_quiz_after_summary', 'lifterlms_template_quiz_wrapper_end', 5 );
add_action( 'lifterlms_single_quiz_after_summary', 'lifterlms_template_start_button', 10 );

/**
 * Questions
 *
 * @since Unknown
 */
add_action( 'lifterlms_single_question_before_summary', 'lifterlms_template_question_wrapper_start', 10 );

add_action( 'lifterlms_single_question_content', 'lifterlms_template_question_description', 10 );
add_action( 'lifterlms_single_question_content', 'lifterlms_template_question_image', 20 );
add_action( 'lifterlms_single_question_content', 'lifterlms_template_question_video', 30 );
add_action( 'lifterlms_single_question_content', 'lifterlms_template_question_content', 40 );

add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_question_wrapper_end', 10 );

/**
 * Student Dashboard
 *
 * @since 7.8.0
 */
add_action( 'lifterlms_before_student_dashboard', 'lifterlms_template_student_dashboard_wrapper_open', 10 );

add_action( 'lifterlms_before_student_dashboard_content', 'lifterlms_template_student_dashboard_navigation', 10 );
add_action( 'lifterlms_before_student_dashboard_content', 'lifterlms_template_student_dashboard_header', 20 );

add_action( 'lifterlms_student_dashboard_header', 'lifterlms_template_student_dashboard_title', 10 );

add_action( 'lifterlms_student_dashboard_index', 'lifterlms_template_student_dashboard_my_courses', 10 );
add_action( 'lifterlms_student_dashboard_index', 'lifterlms_template_student_dashboard_my_achievements', 20 );
add_action( 'lifterlms_student_dashboard_index', 'lifterlms_template_student_dashboard_my_certificates', 30 );
add_action( 'llms_achievement_content', 'llms_the_achievement', 10 );
add_action( 'llms_certificate_preview', 'llms_the_certificate_preview', 10 );
add_action( 'lifterlms_student_dashboard_index', 'lifterlms_template_student_dashboard_my_memberships', 40 );

add_action( 'llms_my_grades_course_table', 'lifterlms_template_student_dashboard_my_grades_table', 10, 2 );

add_action( 'llms_view_order_information', 'llms_template_view_order_information', 10 );
add_action( 'llms_view_order_actions', 'llms_template_view_order_actions', 10 );
add_action( 'llms_view_order_transactions', 'llms_template_view_order_transactions', 10, 2 );

add_action( 'lifterlms_after_student_dashboard', 'lifterlms_template_student_dashboard_wrapper_close', 10 );

/**
 * Single Membership
 *
 * @since Unknown
 */
add_action( 'lifterlms_single_membership_after_summary', 'lifterlms_template_pricing_table', 10 );

/**
 * Sidebar
 *
 * LifterLMS *does not* automatically output sidebars on course
 * and membership catalogs.
 *
 * But there is a "stub" that themes (or other plugins) can utilize
 * in order to add a sidebar to the catalogs.
 *
 * @since Unknown
 */
add_action( 'lifterlms_sidebar', 'lifterlms_get_sidebar', 10 );

/**
 * Single Certificate
 *
 * @since 6.0.0
 */
add_action( 'wp_head', 'llms_certificate_styles' );
add_action( 'llms_display_certificate', 'llms_certificate_content', 10 );
add_action( 'llms_display_certificate', 'llms_certificate_actions', 20 );

if ( ! is_admin() ) {
	add_filter( 'post_class', 'llms_post_classes', 20, 3 );
}
