<?php

defined( 'ABSPATH' ) || exit;

if ( ! LLMS_CERTIFICATE_BUILDER ) {
	exit;
}


/**
 * Helper class that handles Certificate Migration and Rollbacks
 * @since [version]
 * @version [version]
 */
class LLMS_Certificate_Migrator {

	/**
	 * Migrates a legacy certificate to the new Builder
	 *
	 * @param int $certificate_id Certificate ID.
	 *
	 * @since [version]
	 * @version [version]
	 */
	public static function migrate( $certificate_id ) {

		$new_certificate = $self::legacy( $certificate_id );

		wp_safe_redirect( add_query_arg(
			array(
				'llms-certificate-migrate' => true,
			),
			get_edit_post_link( $new_certificate )
		) );
		exit();
	}

	/**
	 * Rolls back a migrated certificate to legacy
	 *
	 * @param int $certificate_id Certificate ID.
	 *
	 * @return WP_Error|null
	 *
	 * @since [version]
	 * @version [version]
	 */
	public static function rollback( $certificate_id ) {

		$legacy = self::has_legacy( $certificate_id );

		if ( false === $legacy  ) {
			return WP_Error( 'missing-legacy', __( 'Sorry! No legacied certificate found to rollback to.', 'lifterlms' ) );
		}

		self::swap_engagements( $certificate_id, $legacy->ID );

		wp_safe_redirect( add_query_arg(
			array(
				'llms-certificate-migrated' => true,
			),
			get_edit_post_link( $legacy->ID )
		) );
		exit();
	}

	/**
	 * Legacies a certificate.
	 *
	 * @param int $certificate_id Certificate ID.
	 *
	 * @return WP_Error|int
	 *
	 * @since [version]
	 * @version [version]
	 */
	private static function legacy( $certificate_id ) {

		$certificate = get_post( $certificate_id, ARRAY_A );

		// check if this is already an legacied certificate.
		if ( 0 !== $certificate['post_parent'] || 'legacy' === $certificate['post_status'] ) {
			return WP_Error( 'is-legacy', __( 'This is already an legacied version!', 'lifterlms' ) );
		}

		//  check if this already has an legacy
		if ( false !== self::has_legacy( $certificate_id ) ) {
			return WP_Error( 'has-legacy', __( 'An legacied version already exists. Please delete it to legacy this certificate.', 'lifterlms' ) );
		}

		// unset ID so that a new post is created instead of simply updating the existing post.
		unset( $certificate['ID'] );

		// insert new post with the same data as the current post
		$new_certificate_id = wp_insert_post( $certificate );

		// change post status of current certificate ($certificate_id) to legacied.
		$legacied_certificate_args = array(
			'post_id' => $certificate_id,
			'post_status' => 'legacy',
			'post_parent' => $new_certificate_id,
		);

		wp_update_post( $legacied_certificate_args );

		// Copy all metadata
		self::duplicate_meta( $certificate_id, $new_certificate_id );

		// swap engagement $engagement
		self::swap_engagements( $certificate_id, $new_certificate_id );

		// return new certificate ID.
		return $new_certificate_id;
	}

	/**
	 * Swaps the engagement's association with certificate.
	 *
	 * @param int $from_certificate_id Certificate ID to swap from
	 * @param int $to_certificate_id Certificate ID to swap to
	 *
	 * @return array|bool
	 *
	 * @since [version]
	 * @version [version]
	 */
	private static function swap_engagements( $from_certificate_id, $to_certificate_id ) {

		// locate engagement using $old_certificate_id.
		$engagements = get_posts( array(
			'meta_key'   => '_llms_engagement',
			'meta_value' => $from_certificate_id,
		) );

		if ( empty( $engagements ) ) {
			return false;
		}

		// swap the $old_certificate_id with the $new_certificate_id.

		foreach ( $engagements as $engagemnet ) {
			update_post_meta( $engagement->ID, '_llms_engagement', $to_certificate_id );
		}
		// return engagement/ engagement_id.
		return $engagements;

	}

	/**
	 * Checks and returns legacy version of certificate.
	 *
	 * @param int $certificate_id Certificate ID.
	 *
	 * @return WP_Post|bool
	 *
	 * @since [version]
	 * @version [version]
	 */
	public static function has_legacy( $certificate_id ) {
		$legacied_args = array(
			'numberposts' => 1,
			'post_type'   => 'llms-certificate',
			'post_status' => 'legacy',
			'post_parent' => $certificate_id,
		);

		$found_legacies = get_children( $legacied_args );

		return empty( $found_legacies ) ? false : $found_legacies[0];
	}

	/**
	 * @param int $from_certificate_id Certificate ID to copy meta from
	 * @param int $to_certificate_id Certificate ID to copy meta to
	 *
	 * @return int|bool
	 *
	 * @since [version]
	 * @version [version]
	 */
	private static function duplicate_meta( $from_certificate_id, $to_certificate_id ) {

		$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$from_certificate_id" );

		if ( 0 === count( $post_meta_infos ) ) {
			return;
		}

		$sql_query = "INSERT INTO $wpdb->postmeta ( post_id, meta_key, meta_value ) ";

		foreach ( $post_meta_infos as $meta_info ) {
			$meta_key = $meta_info->meta_key;
			if( '_wp_old_slug' === $meta_key ) {
				continue;
			}
			$meta_value = addslashes( $meta_info->meta_value );
			$sql_query_sel[] = "SELECT $to_certificate_id, '$meta_key', '$meta_value'";
		}

		$sql_query .= implode( ' UNION ALL ', $sql_query_sel );

		return $wpdb->query( $sql_query );
	}

	/**
	 *
	 * @param int $certificate_id Certificate ID.
	 *
	 * @return WP_Post|false|null
	 *
	 * @since [version]
	 * @version [version]
	 */
	public static function delete_legacy( $certificate_id ) {
		$legacy = self::has_legacy( $certificate_id );

		if ( $legacy === false ) {
			return WP_Error( 'missing-legacy', __( 'No legacy found for deletion.', 'lifterlms' ) );
		}

		return wp_delete_post( $legacy, true );
	}
}
