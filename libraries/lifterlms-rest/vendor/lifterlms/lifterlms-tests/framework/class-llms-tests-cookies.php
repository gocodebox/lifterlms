<?php
/**
 * Get/Set mock cookie data set by `llms_setcookie()`.
 *
 * @since 1.7.0
 * @version 1.7.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Test_Cookies class.
 *
 * @since 1.7.0
 * @since 1.7.1 Also set the cookie in the $_COOKIE superglobal.
 * @since 1.7.2 Unset global $_COOKIE when performing an unset.
 */
class LLMS_Tests_Cookies {

	/**
	 * Array of mocked cookie data.
	 *
	 * @var array[]
	 */
	protected $cookies = array();

	/**
	 * Indicates the return of the `set()` method.
	 *
	 * @var bool
	 */
	protected $set_return_value = true;

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $instance = null;

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since 1.7.0
	 *
	 * @return LLMS_Test_Cookies
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Sets the expected return of `set()` to be `false` indicating an error setting the cookie.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function expect_error() {
		$this->set_return_value = false;
	}

	/**
	 * Sets the expected return of `set()` to be `true` indicating the cookie was successfully set.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function expect_success() {
		$this->set_return_value = true;
	}

	/**
	 * Get a single cookie by name.
	 *
	 * @since 1.7.0
	 *
	 * @param string $name The name of the cookie.
	 * @return array|null
	 */
	public function get( $name ) {
		return isset( $this->cookies[ $name ] ) ? $this->cookies[ $name ] : null;
	}

	/**
	 * Retrieve all mock cookies.
	 *
	 * @since 1.7.0
	 *
	 * @return array[]
	 */
	public function get_all() {
		return $this->cookies;
	}

	/**
	 * Set a cookie.
	 *
	 * This is a wrapper for the native PHP function `set_cookie()` which can be plugged.
	 *
	 * The lifterlms-tests library plugs this function during unit testing so we can mock
	 * the returns of methods that set cookies and write tests for those functions.
	 *
	 * @since 1.7.0
	 * @since 1.7.1 Also set the cookie in the $_COOKIE superglobal.
	 *
	 * @link https://www.php.net/manual/en/function.setcookie.php
	 *
	 * @param string $name The name of the cookie.
	 * @param string $value The value of the cookie.
	 * @param int $expires The time wehn the cookie expires as a Unix timestamp.
	 * @param string $path The path on the server where the cookie will be available.
	 * @param string $domain The (sub)domain that the cookie is available to.
	 * @param bool $secure Indicates the cookie should only be transmitted over a secure HTTPS connection.
	 * @param bool $httponly When `true` the cookie will only be made accessible through the HTTP protocol,
	 *                       preventing it from being accessed by scripting languages (such as Javascript).
	 *
	 * @return boolean
	 */
	public function set( $name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httponly = false ) {
		if ( $this->set_return_value ) {
			$this->cookies[ $name ] = compact( 'value', 'expires', 'path', 'domain', 'secure', 'httponly' );
			$_COOKIE[ $name ] = $value;
		}
		return $this->set_return_value;
	}

	/**
	 * Delete a single cookie by name.
	 *
	 * @since 1.7.0
	 * @since 1.7.2 Unset global $_COOKIE when performing an unset.
	 *
	 * @param string $name The name of the cookie.
	 * @return void
	 */
	public function unset( $name ) {
		unset( $this->cookies[ $name ] );
		unset( $_COOKIE[ $name ] );
	}

	/**
	 * Delete all cookies.
	 *
	 * @since 1.7.0
	 * @since 1.7.2 Unset global $_COOKIE when performing an unset.
	 *
	 * @return void
	 */
	public function unset_all() {
		foreach ( array_keys( $this->cookies ) as $key ) {
			unset( $_COOKIE[ $key ] );
		}
		$this->cookies = array();
	}

}
