<?php
/**
 * Display content before lessons
 * @since   1.0.0
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();

/**
 * @hooked - lifterlms_template_single_parent_course - 10
 * @hooked - lifterlms_template_single_lesson_video  -  20
 * @hooked - lifterlms_template_single_lesson_audio  -  20
 */
do_action( 'lifterlms_single_lesson_before_summary' );
