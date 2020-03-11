<?php
/**
 * LLMS_Session.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 3.37.7
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Session class.
 *
 * @since 1.0.0
 * @since 3.7.7 Unknown.
 * @since 3.37.7 Added a second parameter to the `get()` method, that represents the default value
 *               to return if the session variable requested doesn't exist.
 */
class LLMS_Session {

	/**
	 * Session data
	 *
	 * @var array|WP_Session
	 */
	private $session;

	/**
	 * Use php session
	 *
	 * @var bool
	 */
	private $use_php_sessions = false;

	/**
	 * Session prefix
	 *
	 * @var string
	 */
	private $prefix = '';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 3.7.5 Unkwnown.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->use_php_sessions = $this->use_php_sessions();

		if ( $this->use_php_sessions ) {

			if ( is_multisite() ) {

				$this->prefix = '_' . get_current_blog_id();

			}

			// Use PHP SESSION (must be enabled via the LLMS_USE_PHP_SESSIONS constant).
			add_action( 'init', array( $this, 'maybe_start_session' ), -2 );

		} else {

			require_once plugin_dir_path( LLMS_PLUGIN_FILE ) . 'vendor/ericmann/wp-session-manager/wp-session-manager.php';

			add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
			add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		}

		if ( empty( $this->session ) && ! $this->use_php_sessions ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		} else {
			add_action( 'init', array( $this, 'init' ), -1 );
		}

	}

	/**
	 * Setup the WP_Session instance.
	 *
	 * @since ??
	 *
	 * @return array|WP_Session
	 */
	public function init() {

		if ( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION[ 'llms' . $this->prefix ] ) && is_array( $_SESSION[ 'llms' . $this->prefix ] ) ? $_SESSION[ 'llms' . $this->prefix ] : array();
		} else {
			$this->session = @WP_Session::get_instance();
		}

		return $this->session;
	}

	/**
	 * Retrieve session ID.
	 *
	 * @since ??
	 *
	 * @return string Session ID.
	 */
	public function get_id() {
		return $this->session->session_id;
	}

	/**
	 * Retrieve a session variable.
	 *
	 * @since 1.0.0
	 * @since 3.37.7 Added the `$default` parameter that represents the default value
	 *               to return if the session variable requested doesn't exist.
	 *
	 * @param string $key     The key of the session variable.
	 * @param mixed  $default Optional. The default value to return if no session variable is found with the provided key. Default `false`.
	 * @return mixed
	 */
	public function get( $key, $default = false ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key of the session variable.
	 * @param string $value The value of the session variable.
	 * @return mixed
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		if ( $this->use_php_sessions ) {

			$_SESSION[ 'llms' . $this->prefix ] = $this->session;
		}

		return $this->session[ $key ];
	}

	/**
	 * Force the cookie expiration variant time to 23 hours.
	 *
	 * @since ??
	 *
	 * @param int $exp Default expiration (1 hour).
	 * @return int
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( $exp * 30 * 60 * 23 );
	}

	/**
	 * Force the cookie expiration time to 24 hours.
	 *
	 * @since ??
	 *
	 * @param int $exp Default expiration (1 hour).
	 * @return int
	 */
	public function set_expiration_time( $exp ) {
		return ( 30 * 60 * 24 );
	}

	/**
	 * Determine should we use php session or wp.
	 *
	 * @since ??
	 *
	 * @return bool
	 */
	public function use_php_sessions() {

		$ret = false;

		if ( defined( 'LLMS_USE_PHP_SESSIONS' ) && LLMS_USE_PHP_SESSIONS ) {
			$ret = true;
		} elseif ( defined( 'LLMS_USE_PHP_SESSIONS' ) && ! LLMS_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'llms_use_php_sessions', $ret );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function maybe_start_session() {

		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

	/**
	 * __get function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key of the session variable.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * __set function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The key of the session variable.
	 * @param string $value The value of the session variable.
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * __isset function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key of the session variable.
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->session[ sanitize_title( $key ) ] );
	}

	/**
	 * __unset function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key of the session variable.
	 * @return void
	 */
	public function __unset( $key ) {
		if ( isset( $this->session[ $key ] ) ) {
			unset( $this->session[ $key ] );
		}
	}

}
