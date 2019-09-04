<?php
/**
 * LLMS_Session Class
 *
 * @since    1.0.0
 * @version  3.7.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Session {

	/**
	 * Session data
	 *
	 * @var array
	 * @access private
	 */
	private $session;

	/**
	 * Use php session
	 *
	 * @var bool
	 * @access private
	 */
	private $use_php_sessions = false;

	/**
	 * Session prefix
	 *
	 * @var string
	 * @access private
	 */
	private $prefix = '';

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @version  3.7.5
	 */
	public function __construct() {

		$this->use_php_sessions = $this->use_php_sessions();

		if ( $this->use_php_sessions ) {

			if ( is_multisite() ) {

				$this->prefix = '_' . get_current_blog_id();

			}

			// Use PHP SESSION (must be enabled via the LLMS_USE_PHP_SESSIONS constant)
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
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @return void
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
	 * Retrieve session ID
	 *
	 * @access public
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @param string $key
	 * @param string $value
	 * @return string
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
	 * Force the cookie expiration variant time to 23 hours
	 *
	 * @access public
	 * @param int $exp Default expiration (1 hour)
	 * @return int
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( $exp * 30 * 60 * 23 );
	}

	/**
	 * Force the cookie expiration time to 24 hours
	 *
	 * @access public
	 * @param int $exp Default expiration (1 hour)
	 * @return int
	 */
	public function set_expiration_time( $exp ) {
		return ( 30 * 60 * 24 );
	}

	/**
	 * Determine should we use php session or wp
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
	 */
	public function maybe_start_session() {

		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

	/**
	 * __get function
	 *
	 * @param string $key
	 * @return string
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * __set function
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * __isset function
	 *
	 * @param string $key
	 * @return void
	 */
	public function __isset( $key ) {
		return isset( $this->session[ sanitize_title( $key ) ] );
	}

	/**
	 * __unset function
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset( $key ) {
		if ( isset( $this->session[ $key ] ) ) {
			unset( $this->session[ $key ] );
		}
	}

}
