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
 * Link protected quiz question media to its quiz.
 *
 * The course builder linked picture choice images to their quiz with `_llms_quiz_id`, but not the question's
 * own image or images in its description and clarifications, so students could not view them while taking the
 * quiz or on its results. Walks questions once by ID, using `llms_question_media_quiz_link_cursor` so each batch
 * continues after the last question. Only protected attachments are changed. A question inside a question group
 * is linked to the group's quiz.
 *
 * Returns `true` while a full page was processed so the background updater calls it again,
 * otherwise `false`. Re-running is safe.
 *
 * @since [version]
 *
 * @return bool
 */
function link_question_media_to_quizzes() {

	global $wpdb;

	$per_page = \llms_update_util_get_items_per_page();
	$cursor   = (int) \get_option( 'llms_question_media_quiz_link_cursor', 0 );

	$question_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'llms_question' AND ID > %d ORDER BY ID ASC LIMIT %d",
			$cursor,
			$per_page
		)
	); // db call ok; no-cache ok.

	$protector = new \LLMS_Media_Protector();

	foreach ( $question_ids as $question_id ) {

		$question_id = (int) $question_id;
		$cursor      = $question_id;

		$quiz_id = \absint( \get_post_meta( $question_id, '_llms_parent_id', true ) );
		if ( $quiz_id && 'llms_question' === \get_post_type( $quiz_id ) ) {
			$quiz_id = \absint( \get_post_meta( $quiz_id, '_llms_parent_id', true ) );
		}
		if ( ! $quiz_id || 'llms_quiz' !== \get_post_type( $quiz_id ) ) {
			continue;
		}

		$image     = \get_post_meta( $question_id, '_llms_image', true );
		$media_ids = array_merge(
			array( is_array( $image ) && isset( $image['id'] ) ? $image['id'] : 0 ),
			$protector->get_media_ids_in_content( \get_post_field( 'post_content', $question_id ) ),
			$protector->get_media_ids_in_content( \get_post_meta( $question_id, '_llms_clarifications', true ) )
		);

		foreach ( array_filter( array_map( 'absint', $media_ids ) ) as $media_id ) {
			if ( $protector->is_media_protected( $media_id ) ) {
				$protector->link_to_quiz( $media_id, $quiz_id );
			}
		}
	}

	if ( count( $question_ids ) < $per_page ) {
		\delete_option( 'llms_question_media_quiz_link_cursor' );
		return false;
	}

	\update_option( 'llms_question_media_quiz_link_cursor', $cursor, false );
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
