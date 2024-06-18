<?php
/**
 * LifterLMS Course Instructors Info
 *
 * @package LifterLMS/Templates/Course
 *
 * @since 3.0.0
 * @since 4.11.0 Use `llms_template_instructors()`.
 * @version 4.11.0
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID(); // Get the ID of the current post

if ( class_exists( 'Elementor\Plugin' ) && Elementor\Plugin::instance()->documents->get( $post_id )->is_built_with_elementor() ) {
	return;
}
llms_template_instructors();
