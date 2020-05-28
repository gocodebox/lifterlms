<?php
/**
 * Update functions for version 3.8.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add visibility settings to all access plans and delete the "featured" meta values for all access plans
 *
 * @since 3.8.0
 *
 * @return void
 */
function llms_update_380_set_access_plan_visibility() {
	$query = new WP_Query(
		array(
			'post_status'    => 'any',
			'post_type'      => array( 'llms_access_plan' ),
			'posts_per_page' => -1,
		)
	);
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$plan       = llms_get_post( $post );
			$visibility = $plan->is_featured() ? 'featured' : 'visible';
			wp_set_object_terms( $post->ID, $visibility, 'llms_access_plan_visibility', false );
			delete_post_meta( $post->ID, '_llms_featured' );
		}
	}
}

/**
 * Update db version at conclusion of 3.8.0 updates
 *
 * @since 3.8.0
 *
 * @return void
 */
function llms_update_380_update_db_version() {

	LLMS_Install::update_db_version( '3.8.0' );

}
