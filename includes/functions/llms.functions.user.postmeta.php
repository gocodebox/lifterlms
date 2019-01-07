<?php
/**
 * CRUD LifterLMS User Postmeta Data
 * All functions are pluggable
 * @since    3.21.0
 * @version  3.21.0
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'llms_delete_user_postmeta' ) ) :
	/**
	 * Delete user postmeta data
	 * @param    int        $user_id     WP User ID
	 * @param    int        $post_id     WP Post ID
	 * @param    string     $meta_key    optional meta key for lookup, if not supplied, all matching items will be removed
	 * @param    mixed      $meta_value  optional meta value for lookup, if not supplied, all matching items will be removed
	 * @return   bool
	 * @since    3.21.0
	 * @version  3.21.0
	 */
	function llms_delete_user_postmeta( $user_id, $post_id, $meta_key = null, $meta_value = null ) {

		$ret = true;

		$existing = _llms_query_user_postmeta( $user_id, $post_id, $meta_key, maybe_unserialize( $meta_value ) );
		if ( $existing ) {
			foreach ( $existing as $obj ) {
				$item = new LLMS_User_Postmeta( $obj->meta_id, false );
				if ( ! $item->delete() ) {
					$ret = false;
				}
			}
		}

		return $ret;

	}
endif;

if ( ! function_exists( 'llms_get_user_postmeta' ) ) :
	/**
	 * Get user postmeta data or dates by user, post, and key
	 * @param    int        $user_id   WP User ID
	 * @param    int        $post_id   WP Post ID
	 * @param    string     $meta_key  optional key, if not supplied returns associative array of all metadata found for the given user / post
	 * @param    bool       $single    if true, returns only the data
	 * @param    string     $return    determine if the meta value or updated date should be returned [meta_value,updated_date]
	 * @return   mixed
	 * @since    3.21.0
	 * @version  3.21.0
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
	 * Update user postmeta data
	 * @param    int        $user_id     WP User ID
	 * @param    int        $post_id     WP Post ID
	 * @param    string     $meta_key    meta key
	 * @param    mixed      $meta_value  meta value (don't serialize serializable values)
	 * @param    bool       $unique      if true, updates existing value (if it exists)
	 *                                   if false, will add a new record (allowing multiple records with the same key to exist)
	 * @return   bool
	 * @since    3.21.0
	 * @version  3.21.0
	 */
	function llms_update_user_postmeta( $user_id, $post_id, $meta_key, $meta_value, $unique = true ) {

		$item = false;

		// if unique is true, make an update to the existing item (if it exists)
		if ( $unique ) {

			// locate the item
			$existing = _llms_query_user_postmeta( $user_id, $post_id, $meta_key );
			if ( $existing ) {

				// load it and make sure it exists
				$item = new LLMS_User_Postmeta( $existing[0]->meta_id, false );
				if ( ! $item->exists() ) {
					$item = false;
				}
			}
		}

		if ( ! $item ) {
			$item = new LLMS_User_Postmeta();
		}

		// setup the data we want to store
		$updated_date = llms_current_time( 'mysql' );
		$meta_value = maybe_serialize( $meta_value );
		$item->setup( compact( 'user_id', 'post_id', 'meta_key', 'meta_value', 'updated_date' ) );
		return $item->save();

	}
endif;

if ( ! function_exists( 'llms_bulk_update_user_postmeta' ) ) :
	/**
	 * Update bulk update user postmeta data
	 * @param    int        $user_id     WP User ID
	 * @param    int        $post_id     WP Post ID
	 * @param    array      $data        key=>val associative array of meta keys => meta values to update/add
	 * @param    bool       $unique      if true, updates existing value (if it exists)
	 *                                   if false, will add a new record (allowing multiple records with the same key to exist)
	 * @return   array|true              on error returns an associative array of the submitted keys, each item will be true for success or false for error
	 *                                   on success returns true
	 * @since    3.21.0
	 * @version  3.21.0
	 */
	function llms_bulk_update_user_postmeta( $user_id, $post_id, $data = array(), $unique = true ) {

		$res = array_fill_keys( array_keys( $data ), null );
		$err = false;
		foreach ( $data as $key => $val ) {
			$update = llms_update_user_postmeta( $user_id, $post_id, $key, $val, $unique );
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
	 * Query user postmeta data
	 * This function is marked for internal use only.
	 * @access   private
	 * @param    int        $user_id     WP User ID
	 * @param    int        $post_id     WP Post ID
	 * @param    string     $meta_key    optional meta key
	 * @param    string     $meta_value  optional meta value
	 * @return   array
	 * @since    3.21.0
	 * @version  3.21.0
	 */
	function _llms_query_user_postmeta( $user_id, $post_id, $meta_key = null, $meta_value = null ) {

		global $wpdb;

		$key = $meta_key ? $wpdb->prepare( 'AND meta_key = %s', $meta_key ) : '';
		$val = $meta_value ? $wpdb->prepare( 'AND meta_value = %s', $meta_value ) : '';

		$res = $wpdb->get_results( $wpdb->prepare( "
		SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta
		WHERE user_id = %d AND post_id = %d {$key} {$val} ORDER BY updated_date DESC",
			$user_id, $post_id
		) );

			return $res;

	}
endif;


