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
abstract class LLMS_Abstract_Session_Data {

	/**
	 * Session data
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Session ID
	 *
	 * The session ID is the logged-in user ID
	 * or a unique ID for an anonymous user.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Determines if there's session data to be saved.
	 *
	 * If `true` no data needs to be saved, if `false` data
	 * needs to be saved.
	 *
	 * @var boolean
	 */
	protected $is_clean = true;

	/**
	 * Generate a session key for the current user/visitor.
	 *
	 * A logged-in user will use their WP_User ID while logged-out
	 * users will be assigned a random string.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function generate_id() {

		// Use the current user id if the user is logged in.
		if ( is_user_logged_in() ) {
			return strval( get_current_user_id() );
		}

		// Generate a random id.
		require_once ABSPATH . 'wp-includes/class-phpass.php';
		$hasher = new PasswordHash( 8, false );
		return md5( $hasher->get_random_bytes( 32 ) );

	}

	/**
	 * Retrieve session ID.
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Moved from `LLMS_Sessions`, automatically generates an ID if it doesn't exist.
	 *
	 * @return string Session ID.
	 */
	public function get_id() {
		if ( empty( $this->id ) ) {
			$this->id = $this->generate_id();
		}
		return $this->id;
	}

	/**
	 * Retrieve a session variable.
	 *
	 * @since 1.0.0
	 * @since 3.37.7 Added the `$default` parameter that represents the default value
	 *               to return if the session variable requested doesn't exist.
	 * @since 4.0.0 Moved from `LLMS_Session`.
	 *
	 * @param string $key     The key of the session variable.
	 * @param mixed  $default Optional. The default value to return if no session variable is found with the provided key. Default `false`.
	 * @return mixed
	 */
	public function get( $key, $default = false ) {

		$key = sanitize_key( $key );
		return isset( $this->data[ $key ] ) ? maybe_unserialize( $this->data[ $key ] ) : $default;

	}

	/**
	 * Set a session variable.
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Moved from `LLMS_Session`.
	 *
	 * @param string $key   The key of the session variable.
	 * @param mixed  $value The value of the session variable.
	 * @return mixed
	 */
	public function set( $key, $value ) {

		/**
		 * Using `isset()` allows us to explicitly save a value of `false`
		 * since the `get()` method will return the default value `false` making it look
		 * as if the value hasn't changed (when it actually has).
		 */
		if ( ! isset( $this->$key ) || $value !== $this->get( $key ) ) {
			$this->data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->is_clean                     = false;
		}

		return $this->get( $key );

	}

	/**
	 * Magic get
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Moved from `LLMS_Session`.
	 *
	 * @param string $key The key of the session variable.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic set
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Moved from `LLMS_Session`.
	 *
	 * @param string $key   The key of the session variable.
	 * @param string $value The value of the session variable.
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Magic isset
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Use `sanitize_key()` (like other methods in this class) instead of `sanitize_title()`.
	 *
	 * @param string $key The key of the session variable.
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->data[ sanitize_key( $key ) ] );
	}

	/**
	 * Magic unset
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Use `sanitize_key()` when removing session var.
	 *
	 * @param string $key The key of the session variable.
	 * @return void
	 */
	public function __unset( $key ) {
		if ( isset( $this->$key ) ) {
			unset( $this->data[ sanitize_key( $key ) ] );
			$this->is_clean = false;
		}
	}

}
