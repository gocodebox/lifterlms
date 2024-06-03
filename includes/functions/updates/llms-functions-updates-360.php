<?php
/**
 * Update functions for version 3.6.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add course and membership visibility settings
 *
 * Default course is catalog only and default membership is catalog & search.
 * Courses were NOT SEARCHABLE in earlier versions.
 *
 * @since 3.6.0
 *
 * @return void
 */
function llms_update_360_set_product_visibility() {
	$query = new WP_Query(
		array(
			'post_status'    => 'any',
			'post_type'      => array( 'course', 'llms_membership' ),
			'posts_per_page' => -1,
		)
	);
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$visibility = ( 'course' === $post->post_type ) ? 'catalog' : 'catalog_search';
			wp_set_object_terms( $post->ID, $visibility, 'llms_product_visibility', false );
		}
	}
}

/**
 * Update db version at conclusion of 3.6.0 updates
 *
 * @since 3.6.0
 *
 * @return void
 */
function llms_update_360_update_db_version() {

	LLMS_Install::update_db_version( '3.6.0' );

}
