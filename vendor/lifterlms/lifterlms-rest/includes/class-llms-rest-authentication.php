<?php
/**
 * REST API Authentication.
 *
 * @package LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.5
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API Authentication.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.5 is_rest_request() accesses uses `filter_var` instead of `llms_filter_input()`.
 *                     Load all includes to accommodate plugins and themes that call `determine_current_user` early.
 */
class LLMS_REST_Authentication {

	/**
	 * Authenticated API key for the current request.
	 *
	 * @var LLMS_REST_API_Key
	 */
	protected $api_key = null;

	/**
	 * Authentication error object.
	 *
	 * @var WP_Error
	 */
	protected $error = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0-beta.1
	 */
	public function __construct() {

		/**
		 * Disable LifterLMS REST API Key authentication in favor of a custom authentication solution.
		 *
		 * @param bool $use_auth When true, LifterLMS Basic (or header) authorization will be used.
		 */
		$use_auth = apply_filters( 'llms_rest_use_authentication', true );
		if ( $use_auth ) {

			add_filter( 'determine_current_user', array( $this, 'authenticate' ), 15 );
			add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ), 15 );
			add_filter( 'rest_post_dispatch', array( $this, 'send_unauthorized_headers' ), 50 );
			add_filter( 'rest_pre_dispatch', array( $this, 'check_permissions' ), 10, 3 );

		}

	}

	/**
	 * Authenticate an API Request
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.5 Load all includes to accommodate plugins and themes that call `determine_current_user` early.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/determine_current_user/
	 *
	 * @param int|false $user_id WP_User ID of an already authenticated user or false.
	 * @return int|false
	 */
	public function authenticate( $user_id ) {

		// Load includes in case a plugin has triggered authentication early.
		LLMS_REST_API()->includes();

		// 1. If we already have a user, use that user.
		// 2. Only authenticate via ssl.
		// 3. Only authenticate to our end points.
		if ( ! empty( $user_id ) || ! is_ssl() || ! $this->is_rest_request() ) {
			return $user_id;
		}

		$creds = $this->locate_credentials();
		if ( ! $creds ) {
			return false;
		}

		$key = $this->find_key( $creds['key'] );
		if ( ! $key ) {
			return false;
		}

		if ( ! hash_equals( $key->get( 'consumer_secret' ), $creds['secret'] ) ) {
			$this->set_error( llms_rest_authorization_required_error() );
			return false;
		}

		$this->api_key = $key;

		$user_id = $key->get( 'user_id' );
		do_action( 'llms_rest_basic_auth_success', $user_id );

		return $user_id;

	}

	/**
	 * Check for authentication error.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @link https://developer.wordpress.org/reference/hooks/rest_authentication_errors/
	 *
	 * @param WP_Error|null|bool $error Existing error data.
	 * @return WP_Error|null|bool
	 */
	public function check_authentication_error( $error ) {

		// Pass through existing errors.
		if ( ! empty( $error ) ) {
			return $error;
		}

		return $this->get_error();

	}

	/**
	 * Check if the API Key can perform the request.
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed
	 */
	public function check_permissions( $result, $server, $request ) {

		if ( $this->api_key ) {

			$allowed = $this->api_key->has_permission( $request->get_method() );
			if ( ! $allowed ) {
				return llms_rest_authorization_required_error();
			}

			// Update the API key's last access time.
			$this->api_key->set( 'last_access', current_time( 'mysql' ) )->save();

		}

		return $result;
	}

	/**
	 * Find a key via unhashed consumer key
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $consumer_key An unhashed consumer key.
	 * @return LLMS_REST_API_Key|false
	 */
	protected function find_key( $consumer_key ) {

		global $wpdb;

		$consumer_key = llms_rest_api_hash( $consumer_key );

		$key_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}lifterlms_api_keys WHERE consumer_key = %s", $consumer_key ) );

		if ( $key_id ) {
			return LLMS_REST_API()->keys()->get( $key_id );
		}

		return false;

	}

	/**
	 * Locate credentials in the $_SERVER superglobal.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $key_var Variable name for the consumer key.
	 * @param string $secret_var Variable name for the consumer secret.
	 * @return array|false
	 */
	private function get_credentials( $key_var, $secret_var ) {

		// Use `filter_var()` instead of `llms_filter_input()` due to PHP bug with `filter_input()`: https://bugs.php.net/bug.php?id=49184.
		$key    = isset( $_SERVER[ $key_var ] ) ? filter_var( wp_unslash( $_SERVER[ $key_var ] ), FILTER_SANITIZE_STRING ) : null;
		$secret = isset( $_SERVER[ $secret_var ] ) ? filter_var( wp_unslash( $_SERVER[ $secret_var ] ), FILTER_SANITIZE_STRING ) : null;

		if ( ! $key || ! $secret ) {
			return false;
		}

		return compact( 'key', 'secret' );

	}

	/**
	 * Retrieve the auth error object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return WP_Error|null
	 */
	protected function get_error() {
		return $this->error;
	}

	/**
	 * Determine if the request is a request to a LifterLMS REST API endpoint.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.5 Access `$_SERVER['REQUEST_URI']` via `filter_var` instead of `llms_filter_input()`, see https://bugs.php.net/bug.php?id=49184.
	 *
	 * @return bool
	 */
	protected function is_rest_request() {

		$request = isset( $_SERVER['REQUEST_URI'] ) ? filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL ) : null;
		if ( empty( $request ) ) {
			return false;
		}
		if ( empty( $request ) ) {
			return false;
		}

		$request = esc_url_raw( wp_unslash( $request ) );
		$prefix  = trailingslashit( rest_get_url_prefix() );

		$core = ( false !== strpos( $request, $prefix . 'llms/' ) );

		// Allow 3rd parties to use core auth.
		$external = ( false !== strpos( $request, $prefix . 'llms-' ) );

		return apply_filters( 'llms_is_rest_request', $core || $external, $request );

	}

	/**
	 * Get api credentials from headers and then basic auth.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array|false
	 */
	protected function locate_credentials() {

		// Attempt to get creds from headers.
		$creds = $this->get_credentials( 'HTTP_X_LLMS_CONSUMER_KEY', 'HTTP_X_LLMS_CONSUMER_SECRET' );
		if ( $creds ) {
			return $creds;
		}

		// Attempt to get creds from basic auth.
		$creds = $this->get_credentials( 'PHP_AUTH_USER', 'PHP_AUTH_PW' );
		if ( $creds ) {
			return $creds;
		}

		return false;

	}

	/**
	 * Return a WWW-Authenticate header error message when incorrect creds are supplied
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/WWW-Authenticate
	 *
	 * @param WP_REST_Response $response Current response being served.
	 * @return WP_REST_Response
	 */
	public function send_unauthorized_headers( $response ) {

		if ( is_wp_error( $this->get_error() ) ) {
			$auth_message = __( 'LifterLMS REST API', 'lifterlms' );
			$response->header( 'WWW-Authenticate', 'Basic realm="' . $auth_message . '"', true );
		}

		return $response;

	}

	/**
	 * Set authentication error object.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_Error|null $err Error object or null to clear an error.
	 * @return void
	 */
	protected function set_error( $err ) {
		$this->error = $err;
	}

}

return new LLMS_REST_Authentication();
