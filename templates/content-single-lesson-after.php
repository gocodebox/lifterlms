<?php
/**
 * Display content after lesson content
 *
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook: lifterlms_single_lesson_after_summary
 *
 * @hooked - lifterlms_template_complete_lesson_link - 10
 * @hooked - lifterlms_template_lesson_navigation    - 20
 */
do_action( 'lifterlms_single_lesson_after_summary' );
