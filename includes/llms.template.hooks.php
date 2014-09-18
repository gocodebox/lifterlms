<?php
/**
 * @author 		codeBOX
 * @category 	Core
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_title', 5 );
add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_short_description', 10 );
add_action( 'lifterlms_single_course_summary', 'lifterlms_template_single_price', 20 );
