<?php
/**
 * LLMS_Locks class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locks that make overlapping requests working on the same thing run one after another.
 *
 * A request acquires a lock before it checks something an overlapping request could change, such as whether
 * a student is already enrolled, and holds it until its own change is saved. A request asking for the same lock
 * waits for the first one to release it, and then checks against the saved data.
 *
 * The locks are database named locks (`GET_LOCK()`). Locks a request still holds are released when it ends, and
 * the database releases them when the connection closes, so a request that ends early can't leave one behind.
 *
 * A lock is an extra safeguard and never a requirement. `acquire()` returns `false`, without printing or logging
 * a database error, when the lock can't be acquired: another request held it until the timeout, or the database
 * doesn't support named locks or refuses them (for example Galera and Percona XtraDB clusters). The caller then
 * continues without the lock, as it did before locks existed.
 *
 * MySQL before 5.7.5 and MariaDB before 10.0.2 hold one named lock per connection: acquiring a second lock releases
 * the first one, which leaves the request with the most recent lock only.
 *
 * @since [version]
 */
class LLMS_Locks {

	/**
	 * Database names of the locks held by the current request, by lock key.
	 *
	 * @var string[]
	 */
	private static $held = array();

	/**
	 * Callback that releases, when the request ends, the locks it still holds.
	 *
	 * @var Closure|null
	 */
	private static $release_on_shutdown;

	/**
	 * Waits for a lock, then holds it until it is released or the request ends.
	 *
	 * @since [version]
	 *
	 * @param string $key     Identifies what is locked, such as `checkout_enroll_{$user_id}_{$product_id}`.
	 * @param int    $timeout Seconds to wait for a request that already holds the lock.
	 * @return bool Whether the lock is held. When `false`, continue without it.
	 */
	public static function acquire( $key, $timeout = 10 ) {

		if ( isset( self::$held[ $key ] ) ) {
			return true;
		}

		/**
		 * Filters how many seconds a request waits for a lock that another request holds.
		 *
		 * Return `0` to skip the lock.
		 *
		 * @since [version]
		 *
		 * @param int    $timeout Seconds to wait for the lock.
		 * @param string $key     The lock key.
		 */
		$timeout = absint( apply_filters( 'llms_lock_timeout', $timeout, $key ) );
		if ( ! $timeout ) {
			return false;
		}

		global $wpdb;

		// Database drop-ins that send reads to replicas would take the lock on a replica: keep it, and the reads it protects, on the primary.
		if ( method_exists( $wpdb, 'send_reads_to_primaries' ) ) {
			// LudicrousDB, which deprecated the name below.
			$wpdb->send_reads_to_primaries();
		} elseif ( method_exists( $wpdb, 'send_reads_to_masters' ) ) {
			// HyperDB.
			$wpdb->send_reads_to_masters();
		}

		// Named locks are server-wide, so scope the name to this site. An md5 keeps it under the 64 character limit.
		$name = 'llms_' . md5( DB_NAME . '|' . $wpdb->prefix . '|' . $key );

		if ( '1' !== (string) self::query( $wpdb->prepare( 'SELECT GET_LOCK( %s, %d )', $name, $timeout ) ) ) {
			return false;
		}

		self::$held[ $key ] = $name;

		// The database releases a lock when its connection closes, but a persistent connection outlives the request.
		if ( ! self::$release_on_shutdown ) {
			self::$release_on_shutdown = function () {
				array_map( array( __CLASS__, 'release' ), array_keys( self::$held ) );
			};
		}
		add_action( 'shutdown', self::$release_on_shutdown );

		return true;
	}

	/**
	 * Releases a lock acquired by the current request.
	 *
	 * Locks that aren't released are released when the request ends.
	 *
	 * @since [version]
	 *
	 * @param string $key The lock key passed to `acquire()`.
	 * @return bool Whether the lock was released. `false` when the request doesn't hold the lock.
	 */
	public static function release( $key ) {

		if ( ! isset( self::$held[ $key ] ) ) {
			return false;
		}

		global $wpdb;

		$released = '1' === (string) self::query( $wpdb->prepare( 'SELECT RELEASE_LOCK( %s )', self::$held[ $key ] ) );

		unset( self::$held[ $key ] );

		return $released;
	}

	/**
	 * Runs a lock query without printing or logging a database error.
	 *
	 * Databases without named locks answer with an error, which would otherwise break a redirect or JSON response.
	 *
	 * @since [version]
	 *
	 * @param string $sql Prepared SQL.
	 * @return string|null The query's result, `null` when it failed.
	 */
	private static function query( $sql ) {

		global $wpdb;

		$suppress = $wpdb->suppress_errors();
		$result   = $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Named locks are never cached; prepared by the callers.
		$wpdb->suppress_errors( $suppress );

		return $result;
	}
}
