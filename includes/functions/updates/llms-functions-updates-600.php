<?php
/**
 * Update functions for version 6.0.0.
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_6_0_0;

defined( 'ABSPATH' ) || exit;

/**
 * Migrate deprecated meta values for earned achievements.
 *
 * @since [version]
 *
 * @return bool Returns `true` if more records need to be updated and `false` upon completion.
 */
function migrate_achievements() {
	return _migrate_posts( 'achievement' );
}

/**
 * Migrate deprecated meta values for earned certificates.
 *
 * @since [version]
 *
 * @return bool Returns `true` if more records need to be updated and `false` upon completion.
 */
function migrate_certificates() {
	return _migrate_posts( 'certificate' );
}

/**
 * Update db version to 6.0.0.
 *
 * @since [version]
 *
 * @return boolean
 */
function update_db_version() {
	\LLMS_Install::update_db_version( '6.0.0' );
	return false;
}

/**
 * Migrate deprecated meta values for user awards by type.
 *
 * Queries 50 earned awards at a time and migrates their data by moving meta data
 * to the new location and then deleting the deprecated meta values.
 *
 * @since [version]
 *
 * @param string $type Award type, either "achievement" or "certificate".
 * @return boolean Returns `true` if there are more results and `false` if there are no further results.
 */
function _migrate_posts( $type ) {

	// Intentionally undocumented *private* filter used internally for running tests against small pages.
	$per_page = apply_filters( 'llms_update_600_per_page', 50 );

	$query = new \WP_Query( array(
		'orderby'        => array( 'ID' => 'ASC' ),
		'post_type'      => "llms_my_{$type}",
		'posts_per_page' => $per_page,
		'no_found_rows'  => true, // We don't care about found rows since we'll run the query as many times as needed anyway.
		'fields'         => 'ids', // We just need the ID for the updates we'll perform.
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => "_llms_{$type}_title",
				'compare' => 'EXISTS',
			),
			array(
				'key'     => "_llms_{$type}_template",
				'compare' => 'EXISTS',
			),
			array(
				'key'     => "_llms_{$type}_image",
				'compare' => 'EXISTS',
			),
		),
	) );

	// Don't trigger deprecations.
	remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
	foreach ( $query->posts as $id ) {
		_migrate_post( $id, $type );
	}
	// Re-enable deprecations.
	add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

	// If there was 50 results assume there's another page and run again, otherwise we're done.
	return ( $per_page === count( $query->posts ) );

}

/**
 * Migrate meta values for a single award.
 *
 * Performs the following updates:
 *   + Copies lifterlms_user_postmeta user data to the post_author property.
 *   + Moves the title from postmeta to the post_title property.
 *   + Moves the template relationship from meta to the post_parent property.
 *   + Moves the award image from custom meta to the post's featured image.
 *
 * And then deletes the previous metadata after performing the necessary updates.
 *
 * @since [version]
 *
 * @param int    $id   WP_Post ID.
 * @param string $type Award type, either "achievement" or "certificate".
 * @return void
 */
function _migrate_post( $id, $type ) {

	$obj = 'achievement' === $type ? new \LLMS_User_Achievement( $id ) : new \LLMS_User_Certificate( $id );

	$updates = array(
		'author' => $obj->get_user_id(),
	);

	$title = get_post_meta( $id, "_llms_{$type}_title", true );
	if ( $title ) {
		$updates['title'] = $title;
	}

	$template = get_post_meta( $id, "_llms_{$type}_template", true );
	if ( $template ) {
		$updates['parent'] = $template;
	}
	$obj->set_bulk( $updates );

	$image = get_post_meta( $id, "_llms_{$type}_image", true );
	if ( $image ) {
		set_post_thumbnail( $id, $image );
	}

	delete_post_meta( $id, "_llms_{$type}_title" );
	delete_post_meta( $id, "_llms_{$type}_template" );
	delete_post_meta( $id, "_llms_{$type}_image" );

}
