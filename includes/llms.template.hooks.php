<?php
/**
* Template Add Actions
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

//Main Content Wrappers
add_action( 'lifterlms_before_main_content', 'lifterlms_output_content_wrapper', 10 );
add_action( 'lifterlms_after_main_content', 'lifterlms_output_content_wrapper_end', 10 );

/**
 * COURSE
 */
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_featured_image', 10 );
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_video', 10 );
add_action( 'lifterlms_single_course_before_summary', 'lifterlms_template_single_audio', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_price', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_lesson_length', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_difficulty', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_purchase_link', 10 );
add_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_syllabus', 10 );





//Before Course Archive Loop Item Title
add_action( 'lifterlms_before_shop_loop_item_title', 'lifterlms_template_loop_course_thumbnail', 10 );

//After Course Archive Loop Item Title
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_price', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_length', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_difficulty', 10 );
add_action( 'lifterlms_after_shop_loop_item_title', 'lifterlms_template_loop_view_link', 10 );






/**
 * LESSON
 */
//Before Lesson Summary
// add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_featured_image', 10 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_parent_course', 10 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_lesson_video', 10 );
add_action( 'lifterlms_single_lesson_before_summary', 'lifterlms_template_single_lesson_audio', 10 );
add_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_complete_lesson_link', 10 );
add_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_lesson_navigation', 10 );

/**
 * MEMBERSHIP
 */
//Before Membership Archive Loop Item Title
add_action( 'lifterlms_before_memberships_loop_item_title', 'lifterlms_template_loop_course_thumbnail', 10 );
add_action( 'lifterlms_after_memberships_loop_item_title', 'lifterlms_template_loop_price', 10 );
add_action( 'lifterlms_after_memberships_loop_item_title', 'lifterlms_template_loop_view_link', 10 );

//Before Membership Summary
add_action( 'lifterlms_single_membership_before_summary', 'lifterlms_template_single_featured_image', 10 );
add_action( 'lifterlms_single_membership_before_summary', 'lifterlms_template_single_price', 10 );

//After Membership Summary
add_action( 'lifterlms_single_membership_after_summary', 'lifterlms_template_single_purchase_link', 10 );

/**
 * QUIZ
 */
//before Quiz Summary
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_timer', 5 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_wrapper_start', 5 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_return_link', 10 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_results', 15 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_summary', 20 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_passing_percent', 25 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_attempts', 30 );
add_action( 'lifterlms_single_quiz_before_summary', 'lifterlms_template_quiz_time_limit', 35 );

//After Quiz Summary
add_action( 'lifterlms_single_quiz_after_summary', 'lifterlms_template_quiz_wrapper_end', 5 );
add_action( 'lifterlms_single_quiz_after_summary', 'lifterlms_template_start_button', 10 );
add_action( 'lifterlms_single_quiz_after_summary', 'lifterlms_template_quiz_question', 15 );



//Before Question Summary
add_action( 'lifterlms_single_question_before_summary', 'lifterlmslifterlms_template_question_wrapper_start', 10 );
add_action( 'lifterlms_single_question_before_summary', 'lifterlms_template_single_question_count', 10 );

//After Question Summary
add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_single_single_choice_ajax', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_single_prev_question', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlms_template_single_next_question', 10 );
add_action( 'lifterlms_single_question_after_summary', 'lifterlmslifterlms_template_question_wrapper_end', 10 );


/**
 * MY ACCOUNT
 */
add_action( 'lifterlms_my_account_navigation', 'lifterlms_template_my_account_navigation', 10 );



