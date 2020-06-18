<?php
/**
 * LLMS_Session.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Session class.
 *
 * @since 1.0.0
 * @since 3.7.7 Unknown.
 * @since 3.37.7 Added a second parameter to the `get()` method, that represents the default value
 *               to return if the session variable requested doesn't exist.
 * @since 4.0.0 Major refactor to remove reliance on the wp-session-manager library:
 *               + Moved getters & setter methods into LLMS_Abstract_Session_Data
 *               + Added new methods to support built-in DB session management.
 *               + Deprecated legacy methods
 *               + Removed the ability to utilize PHP sessions.
 *               + Removed unused methods.
 */
class LLMS_Session extends LLMS_Abstract_Session_Database_Handler {

	/**
	 * Session cookie name
	 *
	 * @var string
	 */
	protected $cookie = '';

	/**
	 * Timestamp of the session's expiration
	 *
	 * @var int
	 */
	protected $expires;

	/**
	 * Timestamp of when the session is nearing expiration
	 *
	 * @var int
	 */
	protected $expiring;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 3.7.5 Unknown.
	 * @since 4.0.0 Removed PHP sessions.
	 *               Added session auto-destroy on `wp_logout`.
	 *
	 * @return void
	 */
	public function __construct() {

		/**
		 * Customize the name of the LifterLMS User Session Cookie
		 *
		 * @since 4.0.0
		 *
		 * @param string $name Default session cookie name.
		 */
		$this->cookie = apply_filters( 'llms_session_cookie_name', sprintf( 'wp_llms_session_%s', COOKIEHASH ) );

		/**
		 * Trigger cleanup via action.
		 *
		 * This is hooked to an hourly scheduled task.
		 */
		add_action( 'llms_delete_expired_session_data', array( $this, 'clean' ) );

		if ( $this->should_init() ) {

			$this->init_cookie();

			add_action( 'wp_logout', array( $this, 'destroy' ) );
			add_action( 'shutdown', array( $this, 'maybe_save_data' ), 20 );

		}

	}

	/**
	 * Destroys the current session
	 *
	 * Removes session data from the database, expires the cookie,
	 * and resets class variables.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function destroy() {

		// Delete from DB.
		$this->delete( $this->get_id() );

		// Reset class vars.
		$this->id       = '';
		$this->data     = array();
		$this->is_clean = true;

		// Destroy the cookie.
		return llms_setcookie( $this->cookie, '', time() - YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $this->use_secure_cookie(), true );

	}

	/**
	 * Retrieve an validate the session cookie
	 *
	 * @since 4.0.0
	 *
	 * @return false|mixed[]
	 */
	protected function get_cookie() {

		$value = isset( $_COOKIE[ $this->cookie ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $this->cookie ] ) ) : false;

		if ( empty( $value ) || ! is_string( $value ) ) {
			return false;
		}

		/**
		 * Explode the cookie into it's parts.
		 *
		 * @param string|int $0 User ID.
		 * @param int        $1 Expiration timestamp.
		 * @param int        $2 Expiration variance timestamp.
		 * @param string     $3 Cookie hash.
		 */
		$parts = explode( '||', $value );

		if ( empty( $parts[0] ) || empty( $parts[3] ) ) {
			return false;
		}

		$hash_str = sprintf( '%1$s|%2$s', $parts[0], $parts[1] );
		$expected = hash_hmac( 'md5', $hash_str, wp_hash( $hash_str ) );

		if ( ! hash_equals( $expected, $parts[3] ) ) {
			return false;
		}

		return $parts;

	}

	/**
	 * Initialize the session cookie
	 *
	 * Retrieves and validates the cookie,
	 * when there's a valid cookie it will initialize the object
	 * with data from the cookie. Otherwise it sets up and saves
	 * a new session and cookie.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function init_cookie() {

		$cookie = $this->get_cookie();

		$set_cookie = false;

		if ( $cookie ) {

			$this->id       = $cookie[0];
			$this->expires  = $cookie[1];
			$this->expiring = $cookie[2];
			$this->data     = $this->read( $this->id );

			// If the user has logged in, update the session data.
			$update_id = $this->maybe_update_id();

			// If the session is nearing expiration, update the session.
			$extend_expiration = $this->maybe_extend_expiration();

			// If either of these two items are true, the cookie needs to be updated.
			$set_cookie = $update_id || $extend_expiration;

		} else {

			$this->id       = $this->generate_id();
			$this->data     = array();
			$this->is_clean = false;
			$set_cookie     = true;
			$this->set_expiration();

		}

		if ( $set_cookie ) {
			$this->set_cookie();
		}

	}

	/**
	 * Extend the sessions expiration when the session is nearing expiration
	 *
	 * If the user is still active on the site and the cookie is older than the
	 * "expiring" time but not yet expired, renew the session.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean `true` if the expiration was extended, otherwise `false`.
	 */
	protected function maybe_extend_expiration() {

		if ( time() > $this->expiring ) {
			$this->set_expiration();
			$this->is_clean = false;
			return true;
		}

		return false;

	}

	/**
	 * Save session data if not clean
	 *
	 * Callback for `shutdown` action hook.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	public function maybe_save_data() {

		if ( ! $this->is_clean ) {
			return $this->save( $this->expires );
		}

		return false;

	}

	/**
	 * Updates the session id when an anonymous visitor logs in.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean `true` if the id was updated, otherwise `false`.
	 */
	protected function maybe_update_id() {

		$uid = strval( get_current_user_id() );
		if ( $uid && $uid !== $this->get_id() ) {
			$old_id         = $this->get_id();
			$this->id       = $uid;
			$this->is_clean = false;
			$this->delete( $old_id );
			return true;
		}

		return false;

	}

	/**
	 * Determines if the cookie and related save/destroy handler actions should be initialized
	 *
	 * When doing CRON or when on the admin panel we don't want to load, otherwise we do.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	protected function should_init() {

		$init = ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( is_admin() && ! wp_doing_ajax() ) ? false : true;

		/**
		 * Filter whether or not session cookies and related hooks are initialized
		 *
		 * @since 4.0.0
		 *
		 * @param boolean $init Whether or not initialization should take place.
		 */
		return apply_filters( 'llms_session_should_init', $init );

	}

	/**
	 * Set the cookie
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	protected function set_cookie() {

		$hash_str = sprintf( '%1$s|%2$s', $this->get_id(), $this->expires );
		$hash     = hash_hmac( 'md5', $hash_str, wp_hash( $hash_str ) );
		$value    = sprintf( '%1$s||%2$d||%3$d||%4$s', $this->get_id(), $this->expires, $this->expiring, $hash );

		// There's no cookie set or the existing cookie needs to be updated.
		if ( ! isset( $_COOKIE[ $this->cookie ] ) || $_COOKIE[ $this->cookie ] !== $value ) {

			return llms_setcookie( $this->cookie, $value, $this->expires, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $this->use_secure_cookie(), true );

		}

		return false;

	}

	/**
	 * Set cookie expiration and expiring timestamps
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	protected function set_expiration() {

		/**
		 * Filter the lifespan of user session data
		 *
		 * @since 4.0.0
		 *
		 * @param int $duration Lifespan of session data, in seconds.
		 */
		$duration = (int) apply_filters( 'llms_session_data_expiration_duration', HOUR_IN_SECONDS * 6 );

		/**
		 * Filter the user session lifespan variance
		 *
		 * This is subtracted from the session cookie expiration to determine it's "expiring" timestamp.
		 *
		 * When an active session passes it's expiring timestamp but has not yet passed it's expiration timestamp
		 * the session data will be extended and the data session will not be destroyed.
		 *
		 * @since 4.0.0
		 *
		 * @param int $duration Lifespan of session data, in seconds.
		 */
		$variance = (int) apply_filters( 'llms_session_data_expiration_variance', HOUR_IN_SECONDS );

		$this->expires  = time() + $duration;
		$this->expiring = $this->expires - $variance;

	}

	/**
	 * Determine if a secure cookie should be used.
	 *
	 * @since 4.0.0
	 *
	 * @return boolean
	 */
	protected function use_secure_cookie() {

		$secure = llms_is_site_https() && is_ssl();

		/**
		 * Determine whether or not a secure cookie should be used for user session data
		 *
		 * @since 4.0.0
		 *
		 * @param boolean $secure Whether or not a secure cookie should be used.
		 */
		return apply_filters( 'llms_session_use_secure_cookie', $secure );

	}

}
