<?php
/**
 * Base session data class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 4.0.0
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Session
 *
 * @since 4.0.0
 */
abstract class LLMS_Abstract_Session_Database_Handler extends LLMS_Abstract_Session_Data {

	/**
	 * Cache group name, used for WP caching functions
	 *
	 * @var string
	 */
	protected $cache_group = 'llms_session_id';

	/**
	 * Delete all sessions from the database
	 *
	 * This method is the callback function for the `llms_delete_expired_session_data` cron event, which
	 * deletes expired sessions hourly.
	 *
	 * This method is also used by the admin tool to remove *all* sessions on demand.
	 *
	 * @since 4.0.0
	 *
	 * @param boolean $expired_only If `true`, only delete expired sessions, otherwise deletes all events.
	 * @return int
	 */
	public function clean( $expired_only = true ) {

		global $wpdb;

		$query = "DELETE FROM {$wpdb->prefix}lifterlms_sessions";
		if ( $expired_only ) {
			$query .= $wpdb->prepare( ' WHERE expires < %d', time() );
		}

		LLMS_Cache_Helper::invalidate_group( $this->cache_group );

		return $wpdb->query( $query ); // phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

	}

	/**
	 * Delete a session from the database
	 *
	 * @since 4.0.0
	 *
	 * @param string $id Session key.
	 * @return boolean
	 */
	public function delete( $id ) {

		wp_cache_delete( $this->get_cache_key( $id ), $this->cache_group );

		global $wpdb;
		return (bool) $wpdb->delete(  // phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prefix . 'lifterlms_sessions',
			array(
				'session_key' => $id,
			)
		);

	}

	/**
	 * Retrieve a prefixed cache key
	 *
	 * @since 4.0.0
	 *
	 * @param string $key Unprefixed cache key.
	 * @return string
	 */
	protected function get_cache_key( $key ) {
		return LLMS_Cache_Helper::get_prefix( $this->cache_group ) . $key;
	}

	/**
	 * Save the session to the database
	 *
	 * @since 4.0.0
	 *
	 * @param int $expires Timestamp of the session expiration.
	 * @return boolean
	 */
	public function save( $expires ) {

		// Only save if we have data to save.
		if ( $this->is_clean ) {
			return false;
		}

		global $wpdb;
		$save = $wpdb->query(  // phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}lifterlms_sessions ( `session_key`, `data`, `expires` ) VALUES ( %s, %s, %d )
				ON DUPLICATE KEY UPDATE `data` = VALUES ( `data` ), `expires` = VALUES ( `expires` )",
				$this->get_id(),
				maybe_serialize( $this->data ),
				$expires
			)
		);

		wp_cache_set( $this->get_cache_key( $this->get_id() ), $this->data, $this->cache_group, $expires - time() );
		$this->is_clean = true;

		return (bool) $save;

	}

	/**
	 * Retrieve session data from the database
	 *
	 * @since 4.0.0
	 *
	 * @param string $key     Session key.
	 * @param array  $default Default value used when no data exists.
	 * @return string|array
	 */
	public function read( $key, $default = array() ) {

		$cache_key = $this->get_cache_key( $key );
		$data      = wp_cache_get( $cache_key, $this->cache_group );

		if ( false === $data ) {

			global $wpdb;

			$data = $wpdb->get_var( $wpdb->prepare( "SELECT `data` FROM {$wpdb->prefix}lifterlms_sessions WHERE `session_key` = %s", $key ) );  // phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery

			if ( is_null( $data ) ) {
				$data = $default;
			}

			$duration = $this->expires - time();
			if ( 0 < $duration ) {
				wp_cache_set( $cache_key, $data, $this->cache_group, $duration );
			}
		}

		return maybe_unserialize( $data );

	}

}
