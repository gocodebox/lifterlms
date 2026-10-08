<?php
/**
 * Update functions for version 11.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_11_0_0;

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
	return '11.0.0';
}

/**
 * Replace the stock "weak password is required" meter description.
 *
 * Core forms store the password helper text in post content. The default
 * minimum strength is weak, and that sentence reads as if a weak password
 * is what the site wants. Only the exact stock sentence is replaced, in
 * English and in the current locale. Custom descriptions, and medium or
 * strong requirements, are left as they are.
 *
 * @since [version]
 *
 * @return false Always returns `false` because every matching form is updated in one pass.
 */
function replace_weak_password_meter_descriptions() {

	global $wpdb;

	$replacement = \llms_get_password_meter_description( 'weak' );

	$searches = array(
		'A weak password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.',
		sprintf(
			// Translators: %s = Minimum password strength.
			\__( 'A %s password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.', 'lifterlms' ),
			\llms_get_minimum_password_strength_name( 'weak' )
		),
	);

	foreach ( array_unique( $searches ) as $search ) {

		if ( '' === $search || $search === $replacement ) {
			continue;
		}

		$like = '%' . $wpdb->esc_like( $search ) . '%';

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( %s, %s ) AND post_content LIKE %s",
				'llms_form',
				'wp_block',
				$like
			)
		); // db call ok; no-cache ok.

		if ( empty( $ids ) ) {
			continue;
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->posts} SET post_content = REPLACE( post_content, %s, %s ) WHERE post_type IN ( %s, %s ) AND post_content LIKE %s",
				$search,
				$replacement,
				'llms_form',
				'wp_block',
				$like
			)
		); // db call ok; no-cache ok.

		foreach ( $ids as $post_id ) {
			clean_post_cache( (int) $post_id );
		}
	}

	return false;
}

/**
 * Update db version to 11.0.0.
 *
 * @since [version]
 *
 * @return false
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
