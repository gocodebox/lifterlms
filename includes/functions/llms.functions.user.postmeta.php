<?php
/**
 * CRUD LifterLMS User Postmeta Data
 *
 * All functions are pluggable.
 *
 * @package LifterLMS/Functions
 *
 * @since 3.21.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * CRUD LifterLMS User Postmeta Data.
 *
 * @since 3.21.0
 * @since 3.33.0 Added `llms_bulk_delete_user_postmeta`.
 *               Also now `llms_delete_user_postmeta` returns true only if at least one existing user postmeta has been successfully deleted.
 * @since 3.36.3 Fix doc and indentation.
 */
if ( ! function_exists( 'llms_delete_user_postmeta' ) ) :
	/**
	 * Delete user postmeta data.
	 *
	 * @since 3.21.0
	 * @since 3.33.0 Returns true only if at least one existing user postmeta has been successfully deleted.
	 *
	 * @param int    $user_id    WP User ID.
	 * @param int    $post_id    WP Post ID.
	 * @param string $meta_key   Optional. Meta key for lookup, if not supplied, all matching items will be removed. Default null.
	 * @param mixed  $meta_value Optional. Meta value for lookup, if not supplied, all matching items will be removed. Default null.
	 *
	 * @return bool False if no postmetas has been deleted either because they do not exist or because of an error during the
	 *              actual row deletion from the db. True if at least one existing user postmeta has been successfully deleted.
	 */
	function llms_delete_user_postmeta( $user_id, $post_id, $meta_key = null, $meta_value = null ) {

		$ret = false;

		$existing = _llms_query_user_postmeta( $user_id, $post_id, $meta_key, maybe_unserialize( $meta_value ) );
		if ( $existing ) {
			foreach ( $existing as $obj ) {
				$item = new LLMS_User_Postmeta( $obj->meta_id, false );
				if ( ! $item->delete() ) {
					$ret = $ret || false;
				} else {
					$ret = true;
				}
			}
		}

		return $ret;

	}
endif;

if ( ! function_exists( 'llms_bulk_delete_user_postmeta' ) ) :
	/**
	 * Bulk remove user postmeta data.
	 *
	 * @since 3.33.0
	 *
	 * @param int   $user_id WP User ID.
	 * @param int   $post_id WP Post ID.
	 * @param array $data    Optional. Associative array of meta keys => meta values to delete.
	 *                       If not meta values supplied, all matching items will be removed. Default empty array.
	 * @return array|boolean On error returns an associative array of the submitted keys, each item will be true for success or false for error.
	 *                       On success returns true.
	 */
	function llms_bulk_delete_user_postmeta( $user_id, $post_id, $data = array() ) {

		$res = array_fill_keys( array_keys( $data ), null );
		$err = false;

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				$delete      = llms_delete_user_postmeta( $user_id, $post_id, $key, $value );
				$res[ $key ] = $delete;
				if ( ! $delete ) {
					$err = true;
				}
			}
		} else {
			$res = llms_delete_user_postmeta( $user_id, $post_id );
			$err = ! $res;
		}

		return $err ? $res : true;

	}
endif;

if ( ! function_exists( 'llms_get_user_postmeta' ) ) :
	/**
	 * Get user postmeta data or dates by user, post, and key.
	 *
	 * @since 3.21.0
	 *
	 * @param int    $user_id  WP User ID.
	 * @param int    $post_id  WP Post ID.
	 * @param string $meta_key Optional. Meta key, if not supplied returns associative array of all metadata found for the given user / post. Default null.
	 * @param bool   $single   Optional. If true, returns only the data. Default true.
	 * @param string $return   Optional. Determine if the meta value or updated date should be returned [meta_value,updated_date]. Default 'meta_value'.
	 * @return mixed
	 */
	function llms_get_user_postmeta( $user_id, $post_id, $meta_key = null, $single = true, $return = 'meta_value' ) {

		$single = is_null( $meta_key ) ? false : $single;

		$res = array();

		$metas = _llms_query_user_postmeta( $user_id, $post_id, $meta_key );
		if ( count( $metas ) ) {
			foreach ( $metas as $meta ) {
				if ( $meta_key ) {
					$res[ $meta_key ][] = maybe_unserialize( $meta->$return );
				} else {
					$res[ $meta->meta_key ][] = maybe_unserialize( $meta->$return );
				}
			}
		}

		if ( $single ) {
			return count( $res ) ? $res[ $meta_key ][0] : '';
		} elseif ( $meta_key ) {
			return count( $res ) ? $res[ $meta_key ] : array();
		}

		return $res;

	}
endif;

if ( ! function_exists( 'llms_update_user_postmeta' ) ) :
	/**
	 * Update user postmeta data.
	 *
	 * @since 3.21.0
	 * @since [version] Add optional $updated_date argument.
	 *
	 * @param int    $user_id      WP User ID.
	 * @param int    $post_id      WP Post ID.
	 * @param string $meta_key     Meta key.
	 * @param mixed  $meta_value   Meta value (don't serialize serializable values).
	 * @param bool   $unique       Optional. If true, updates existing value (if it exists).
	 *                             If false, will add a new record (allowing multiple records with the same key to exist).
	 *                             Default true.
	 * @param string $updated_date The optional MySQL date to set the `updated_date` column. Defaults to `llms_current_time( 'mysql' )`.
	 * @return bool
	 */
	function llms_update_user_postmeta( $user_id, $post_id, $meta_key, $meta_value, $unique = true, $updated_date = null ) {

		$item = false;

		// if unique is true, make an update to the existing item (if it exists).
		if ( $unique ) {

			// locate the item.
			$existing = _llms_query_user_postmeta( $user_id, $post_id, $meta_key );
			if ( $existing ) {

				// load it and make sure it exists.
				$item = new LLMS_User_Postmeta( $existing[0]->meta_id, false );
				if ( ! $item->exists() ) {
					$item = false;
				}
			}
		}

		if ( ! $item ) {
			$item = new LLMS_User_Postmeta();
		}

		// Set up the data we want to store.
		$updated_date = $updated_date ?? llms_current_time( 'mysql' );
		$meta_value   = maybe_serialize( $meta_value );
		$item->setup( compact( 'user_id', 'post_id', 'meta_key', 'meta_value', 'updated_date' ) );
		return $item->save();

	}
endif;

if ( ! function_exists( 'llms_bulk_update_user_postmeta' ) ) :
	/**
	 * Update bulk update user postmeta data.
	 *
	 * @since 3.21.0
	 *
	 * @param int   $user_id WP User ID.
	 * @param int   $post_id WP Post ID.
	 * @param array $data    Optional. Associative array of meta keys => meta values to update.
	 *                       Default empty array.
	 * @param bool  $unique  Optional. If true, updates existing value (if it exists).
	 *                       If false, will add a new record (allowing multiple records with the same key to exist).
	 *                       Deafult true.
	 * @return array|true On error returns an associative array of the submitted keys, each item will be true for success or false for error.
	 *                    On success returns true.
	 */
	function llms_bulk_update_user_postmeta( $user_id, $post_id, $data = array(), $unique = true ) {

		$res          = array_fill_keys( array_keys( $data ), null );
		$err          = false;
		$updated_date = llms_current_time( 'mysql' );
		foreach ( $data as $key => $val ) {
			$update      = llms_update_user_postmeta( $user_id, $post_id, $key, $val, $unique, $updated_date );
			$res[ $key ] = $update;
			if ( ! $update ) {
				$err = true;
			}
		}

		return $err ? $res : true;

	}
endif;

if ( ! function_exists( '_llms_query_user_postmeta' ) ) :
	/**
	 * Query user postmeta data.
	 * This function is marked for internal use only.
	 *
	 * @since 3.21.0
	 *
	 * @access private
	 *
	 * @param int    $user_id WP User ID.
	 * @param int    $post_id WP Post ID.
	 * @param string $meta_key   Optional. Meta key. Default null.
	 * @param string $meta_value Optional. Meta value. Default null.
	 * @return array
	 */
	function _llms_query_user_postmeta( $user_id, $post_id, $meta_key = null, $meta_value = null ) {

		global $wpdb;

		$key = $meta_key ? $wpdb->prepare( 'AND meta_key = %s', $meta_key ) : '';
		$val = $meta_value ? $wpdb->prepare( 'AND meta_value = %s', $meta_value ) : '';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$res = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta
				 WHERE user_id = %d AND post_id = %d {$key} {$val} ORDER BY updated_date DESC",
				$user_id,
				$post_id
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $res;

	}
endif;
