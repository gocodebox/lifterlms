<?php
/**
 * Functions that wrap native PHP or WordPress core functions
 *
 * Most of these are pluggable primarily to allow easier testing when
 * running phpunit.
 *
 * @package LifterLMS/Functions
 *
 * @since 5.3.0
 * @version 7.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'llms_current_time' ) ) {
	/**
	 * Retrieve the current time based on specified type.
	 *
	 * This is a wrapper for the WP Core current_time which can be plugged
	 * We plug this during unit testing to allow mocking the current time.
	 *
	 * The 'mysql' type will return the time in the format for MySQL DATETIME field.
	 * The 'timestamp' type will return the current timestamp.
	 * Other strings will be interpreted as PHP date formats (e.g. 'Y-m-d').
	 *
	 * If $gmt is set to either '1' or 'true', then both types will use GMT time.
	 * if $gmt is false, the output is adjusted with the GMT offset in the WordPress option.
	 *
	 * @since 3.4.0
	 * @since 5.3.0 Moved location from `includes/llms.functions.core.php`.
	 *
	 * @link https://developer.wordpress.org/reference/functions/current_time/
	 * @link https://github.com/gocodebox/lifterlms-tests/blob/472c5a286e9f65e2be0c1d6b7edd8d5340d052ed/framework/functions-llms-tests.php#L2-L26
	 *
	 * @param string   $type Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date format string (e.g. 'Y-m-d').
	 * @param int|bool $gmt  Optional. Whether to use GMT timezone. Default false.
	 * @return int|string Integer if $type is 'timestamp', string otherwise.
	 */
	function llms_current_time( $type, $gmt = 0 ) {
		return current_time( $type, $gmt );
	}
}

if ( ! function_exists( 'llms_exit' ) ) {
	/**
	 * Native php exit() wrapper
	 *
	 * This wrapper exists primarily to allow easy testing of code that calls exit().
	 *
	 * @since 5.3.0
	 *
	 * @link https://www.php.net/manual/en/function.exit.php
	 * @link https://github.com/gocodebox/lifterlms-tests/blob/472c5a286e9f65e2be0c1d6b7edd8d5340d052ed/framework/functions-llms-tests.php#L164-L176
	 *
	 * @param int|string $status Exit status passed to `exit()`.
	 * @return void
	 */
	function llms_exit( $status = null ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit( $status );
	}
}

if ( ! function_exists( 'llms_filter_input' ) ) {
	/**
	 * Gets a specific external variable by name and optionally filters it
	 *
	 * This is a pluggable wrapper around native `filter_input` which is plugged in the testing framework
	 * to allow easy mocking of form variables when testing form controller functions and methods.
	 *
	 * @since 3.29.0
	 * @since 5.3.0 Moved location from `includes/llms.functions.core.php`.
	 *
	 * @link https://www.php.net/manual/en/function.filter-input.php
	 * @link https://github.com/gocodebox/lifterlms-tests/blob/472c5a286e9f65e2be0c1d6b7edd8d5340d052ed/framework/functions-llms-tests.php#L113-L162
	 *
	 * @param int    $type          One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
	 * @param string $variable_name Name of a variable to get.
	 * @param int    $filter        Optional. The ID of the filter to apply. Default is `FILTER_DEFAULT`.
	 * @param mixed  $options       Optional. Associative array of options or bitwise disjunction of flags. Default is empty array.
	 *                              If filter accepts options, flags can be provided in "flags" field of array.
	 * @return mixed  Value of the requested variable on success, FALSE if the filter fails, or NULL if
	 *                the variable_name variable is not set. If the flag FILTER_NULL_ON_FAILURE is used,
	 *                it returns FALSE if the variable is not set and NULL if the filter fails.
	 */
	function llms_filter_input( $type, $variable_name, $filter = FILTER_DEFAULT, $options = array() ) {
		return filter_input( $type, $variable_name, $filter, $options );
	}
}

if ( ! function_exists( 'llms_redirect_and_exit' ) ) {
	/**
	 * Redirect and exit
	 *
	 * Wrapper for WP core redirects which automatically calls `exit();`.
	 *
	 * This function is redefined when running phpunit tests to make testing code that redirects (and exits).
	 *
	 * @since 3.19.4
	 * @since 5.3.0 Moved location from `includes/llms.functions.core.php`.
	 * @since 7.4.0 Added `nocache_headers()` to prevent caching of temporary redirects.
	 *
	 * @link https://github.com/gocodebox/lifterlms-tests/blob/472c5a286e9f65e2be0c1d6b7edd8d5340d052ed/framework/functions-llms-tests.php#L178-L199
	 *
	 * @param string $location Full URL to redirect to.
	 * @param array  $options  {
	 *     Optional. Array of options. Default is empty array.
	 *
	 *     @type int  $status HTTP status code of the redirect. Default: `302`.
	 *     @type bool $safe   If true, use `wp_safe_redirect()` otherwise use `wp_redirect()`. Default: `true`.
	 * }
	 * @return void
	 */
	function llms_redirect_and_exit( $location, $options = array() ) {

		$options = wp_parse_args(
			$options,
			array(
				'status' => 302,
				'safe'   => true,
			)
		);

		if ( 302 === $options['status'] ) {
			nocache_headers(); // Prevent caching of redirects.
		}

		$func = $options['safe'] ? 'wp_safe_redirect' : 'wp_redirect';
		$func( $location, $options['status'] );
		exit();
	}
}

if ( ! function_exists( 'llms_setcookie' ) ) {
	/**
	 * Set a cookie.
	 *
	 * A pluggable wrapper for the native PHP function `set_cookie()`.
	 *
	 * The lifterlms-tests library plugs this function during unit testing so we can mock
	 * the returns of methods that set cookies and write tests for those functions.
	 *
	 * @since 4.0.0
	 * @since 5.3.0 Moved location from `includes/llms.functions.core.php`.
	 *
	 * @link https://www.php.net/manual/en/function.setcookie.php
	 * @link https://github.com/gocodebox/lifterlms-tests/blob/trunk/framework/functions-llms-tests.php#L81-L111
	 *
	 * @param string $name     The name of the cookie.
	 * @param string $value    The value of the cookie.
	 * @param int    $expires  The time wehn the cookie expires as a Unix timestamp.
	 * @param string $path     The path on the server where the cookie will be available.
	 * @param string $domain   The (sub)domain that the cookie is available to.
	 * @param bool   $secure   Indicates the cookie should only be transmitted over a secure HTTPS connection.
	 * @param bool   $httponly When `true` the cookie will only be made accessible through the HTTP protocol,
	 *                         preventing it from being accessed by scripting languages (such as Javascript).
	 *
	 * @return boolean
	 */
	function llms_setcookie( $name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httponly = false ) {
		return setcookie( $name, $value, $expires, $path, $domain, $secure, $httponly );
	}
}
