<?php
/**
 * Single Course After
 *
 * @author   LifterLMS
 * @package  LifterLMS/Templates
 * @since    1.0.0
 * @version  3.0.3
 */
defined( 'ABSPATH' ) || exit;

/**
 * @hooked - lifterlms_template_single_meta_wrapper_start - 5
 * @hooked - lifterlms_template_single_length - 10
 * @hooked - lifterlms_template_single_difficulty - 20
 * @hooked - lifterlms_template_single_course_tracks - 25
 * @hooked - lifterlms_template_single_course_categories - 30
 * @hooked - lifterlms_template_single_course_tags - 35
 * @hooked - lifterlms_template_course_author - 40
 * @hooked - lifterlms_template_single_meta_wrapper_end - 50
 * @hooked - lifterlms_template_single_prerequisites - 55
 * @hooked - lifterlms_template_pricing_table - 60
 * @hooked - lifterlms_template_single_course_progress - 60
 * @hooked - lifterlms_template_single_syllabus - 90
 */
do_action( 'lifterlms_single_course_after_summary' );
