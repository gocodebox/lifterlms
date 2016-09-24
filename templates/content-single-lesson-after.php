<?php
/**
 * Display content after lesson content
 * @since    1.0.0
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * @hooked - lifterlms_template_complete_lesson_link - 10
 * @hooked - lifterlms_template_lesson_navigation    - 20
 */
do_action( 'lifterlms_single_lesson_after_summary' );
