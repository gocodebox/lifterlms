<?php
/**
 * @author 		codeBOX
 * @category 	Core
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
add_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );

add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_featured_image', 10 );
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_video', 10 );
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_audio', 10 );
//add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_title', 10 );
//add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_course_content', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_price', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_lesson_length', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_difficulty', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_purchase_link', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_syllabus', 10 );




add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_featured_image', 10 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_parent_course', 10 );
//add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_single_title', 10 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_lesson_video', 10 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_lesson_audio', 10 );
//add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_single_full_description', 10 );
add_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_complete_lesson_link', 10 );
add_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_lesson_navigation', 10 );





add_action( 'lifterlms_before_shop_loop_item_title', 'lifterlms_template_loop_course_thumbnail', 10 );

add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_length', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_price', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_length', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_difficulty', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_view_link', 10 );

add_action( 'lifterlms_before_memberships_loop_item_title', 'lifterlms_template_loop_course_thumbnail', 10 );
add_action( 'lifterlms_after_memberships_loop_item_title', 'lifterlms_template_loop_price', 10 );
add_action( 'lifterlms_after_memberships_loop_item_title', 'lifterlms_template_loop_view_link', 10 );


//add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_title', 10 );
add_action( 'lifterlms_single_membership_before_summary', 'lifterlms_template_single_featured_image', 10 );
add_action( 'lifterlms_single_membership_before_summary', 'lifterlms_template_single_price', 10 );

//add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_membership_full_description', 10 );
add_action( 'lifterlms_single_membership_after_summary', 'lifterlms_template_single_purchase_link', 10 );

add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_return_link', 10 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_results', 10 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_passing_percent', 10 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_attempts', 10 );


add_action( 'lifterlms_single_quiz_after_summary', 'lifterlms_template_start_button', 10 );

add_action( 'lifterlms_single_question_before_summary', 'lifterlmslifterlms_template_question_wrapper_start', 10 );
add_action( 'lifterlms_single_question_before_summary', 'lifterlms_template_single_question_count', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_single_single_choice', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_single_prev_question', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_single_next_question', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlmslifterlms_template_question_wrapper_end', 10 );


// add_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
// add_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );


// //course template
// add_action( 'lifterlms_before_single_course_header', 'lifterlms_template_single_featured_image', 10 );

// add_action( 'lifterlms_single_course_header', 'lifterlms_template_single_title', 10 );
// add_action( 'lifterlms_single_course_header', 'lifterlms_template_single_price', 10 );
// add_action( 'lifterlms_single_course_header', 'lifterlms_template_single_lesson_length', 10 );
// add_action( 'lifterlms_single_course_header', 'lifterlms_template_single_difficulty', 10 );
// add_action( 'lifterlms_single_course_header', 'lifterlms_template_single_purchase_link', 10 );

// add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_video', 10 );
// add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_audio', 10 );
// add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_course_content', 10 );
// add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_syllabus', 10 );


// //lesson template
// add_action( 'lifterlms_before_single_lesson_header', 'lifterlms_template_single_featured_image', 10 );

// add_action( 'lifterlms_single_lesson_header', 'lifterlms_template_single_parent_course', 10 );
// add_action( 'lifterlms_single_lesson_header', 'lifterlms_template_single_title', 10 );

// add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_single_lesson_video', 10 );
// add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_single_lesson_audio', 10 );
// add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_single_full_description', 10 );
// add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_complete_lesson_link', 10 );
// add_action( 'lifterlms_single_lesson_summary', 'lifterlms_template_lesson_navigation', 10 );


// //product loop
// add_action( 'lifterlms_before_shop_loop_item_title', 'lifterlms_template_loop_course_thumbnail', 10 );

// add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_length', 10 );
// add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_price', 10 );
// add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_length', 10 );
// add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_difficulty', 10 );
// add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_view_link', 10 );

// add_action( 'lifterlms_before_memberships_loop_item_title', 'lifterlms_template_loop_course_thumbnail', 10 );
// add_action( 'lifterlms_after_memberships_loop_item_title', 'lifterlms_template_loop_price', 10 );
// add_action( 'lifterlms_after_memberships_loop_item_title', 'lifterlms_template_loop_view_link', 10 );


// add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_title', 10 );
// add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_featured_image', 10 );
// add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_price', 10 );

// add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_membership_full_description', 10 );
// add_action( 'lifterlms_single_membership_summary', 'lifterlms_template_single_purchase_link', 10 );





