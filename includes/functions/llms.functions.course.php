<?php
/**
 * LifterLMS Course Functions
 *
 * @package LifterLMS/Functions
 *
 * @since Unknown
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get course object
 *
 * @since Unknown
 * @since 3.37.13 Use `LLMS_Course` in favor of the deprecated `LLMS_Course_Factory::get_course()` method.
 *
 * @param WP_Post|int|false $the_course Course post object or id. If `false` uses the global `$post` object.
 * @param array             $args       Arguments to pass to the LLMS_Course Constructor.
 * @return array
 */
function get_course( $the_course = false, $args = array() ) {

	if ( ! $the_course ) {
		global $post;
		$the_course = $post;
	}

	return new LLMS_Course( $the_course, $args );

}

/**
 * Get Product
 *
 * @since Unknown
 * @since 3.37.13 Use `LLMS_Lesson` in favor of the deprecated `LLMS_Course_Factory::get_lesson()` method.
 *
 * @param WP_Post|int|false $the_lesson Lesson post object or id. If `false` uses the global `$post` object.
 * @param array             $args        Arguments to pass to the LLMS_Lesson Constructor.
 * @return LLMS_Product
 */
function get_lesson( $the_lesson = false, $args = array() ) {

	if ( ! $the_lesson ) {
		global $post;
		$the_lesson = $post;
	}

	return new LLMS_Lesson( $the_lesson, $args );

}

/**
 * Get Favorites Count
 *
 * @since [version]
 *
 * @param WP_Post|int|false $object_id Lesson post object or id. If `false` uses the global `$post` object.
 * @param array             $args        Arguments to pass to the LLMS_Lesson Constructor.
 * @return Favorites Count
 */
function get_total_favorites( $object_id = false, $meta_key, $meta_value = '', $args = array() ) {

	global $wpdb;

	$key = $meta_key ? $wpdb->prepare( 'AND meta_key = %s', $meta_key ) : '';

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$res = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE post_id = %d {$key} ORDER BY updated_date DESC",
			$object_id
		)
	);
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	return count( $res );

}
