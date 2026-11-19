<?php
/**
 * Update functions for version 11.1.1
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_11_1_1;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since [version]
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '11.1.1';
}

/**
 * Link protected question images to their quiz.
 *
 * The course builder linked picture choice images to their quiz with `_llms_quiz_id` but not the
 * question's own image, so students could not view a protected question image while taking the quiz.
 * Walks `_llms_image` rows of questions once, using `llms_question_image_quiz_link_cursor` so each
 * batch continues after the last meta ID. Only protected attachments are changed. A question inside
 * a question group is linked to the group's quiz.
 *
 * Returns `true` while a full page was processed so the background updater calls it again,
 * otherwise `false`. Re-running is safe.
 *
 * @since [version]
 *
 * @return bool
 */
function link_question_images_to_quizzes() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();
	$cursor   = (int) \get_option( 'llms_question_image_quiz_link_cursor', 0 );

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT pm.meta_id, pm.post_id
			FROM {$wpdb->postmeta} AS pm
			INNER JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
			WHERE pm.meta_key = '_llms_image'
			  AND pm.meta_id > %d
			  AND p.post_type = 'llms_question'
			ORDER BY pm.meta_id ASC
			LIMIT %d
			",
			$cursor,
			$per_page
		)
	); // db call ok; no-cache ok.

	foreach ( $rows as $row ) {

		$cursor = (int) $row->meta_id;
		$image  = \get_post_meta( (int) $row->post_id, '_llms_image', true );

		$media_id = is_array( $image ) && isset( $image['id'] ) ? \absint( $image['id'] ) : 0;
		if ( ! $media_id || ! ( new \LLMS_Media_Protector() )->is_media_protected( $media_id ) ) {
			continue;
		}

		$quiz_id = \absint( \get_post_meta( (int) $row->post_id, '_llms_parent_id', true ) );
		if ( $quiz_id && 'llms_question' === \get_post_type( $quiz_id ) ) {
			$quiz_id = \absint( \get_post_meta( $quiz_id, '_llms_parent_id', true ) );
		}
		if ( ! $quiz_id || 'llms_quiz' !== \get_post_type( $quiz_id ) ) {
			continue;
		}

		$quiz_ids = \get_post_meta( $media_id, '_llms_quiz_id', true );
		if ( ! is_array( $quiz_ids ) ) {
			$quiz_ids = array();
		}
		if ( ! in_array( $quiz_id, array_map( 'absint', $quiz_ids ), true ) ) {
			$quiz_ids[] = $quiz_id;
			\update_post_meta( $media_id, '_llms_quiz_id', $quiz_ids );
		}
	}

	if ( count( $rows ) < $per_page ) {
		\delete_option( 'llms_question_image_quiz_link_cursor' );
		return false;
	}

	\update_option( 'llms_question_image_quiz_link_cursor', $cursor, false );
	return true;
}

/**
 * Update db version to 11.1.1.
 *
 * @since [version]
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
