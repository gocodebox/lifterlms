<?php

/**
 * Determines if a certificate is legacy
 *
 * @param $certificate_id Post ID of certificate
 *
 * @return bool
 *
 * @since    [version]
 * @version  [version]
 */
function llms_certificate_is_legacy( $certificate_id ) {
	/*
	 * One reliable difference between a legacy certificate and a builder built certificate
	 *  is the presence of meta keys '_llms_certificate_title' and '_llms_certificate_image' in the db
	 *  since these are deleted after migration.
	 * Since get_post_meta() cannot distinguish between a meta key that is present but empty
	 *  from one that is absent in the db, a direct db query is needed.
	 *
	 * (The other reliable difference is the markup of the content)
	 */

	global $wpdb;

	$query_sql = "SELECT * FROM $wpdb->postmeta WHERE post_id=%d AND ( meta_key = '_llms_certificate_title' OR meta_key = '_llms_certificate_image' )";

	$meta_info = $wpdb->get_results( $wpdb->prepare( $query_sql, $certificate_id ) );

	// no legacy metadata found, not legacy
	if ( empty( $meta_info ) ) {
		return false;
	}

	// metadata was found, is legacy
	return true;

}
