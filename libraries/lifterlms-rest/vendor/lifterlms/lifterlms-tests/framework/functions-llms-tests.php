<?php
/**
 * Plug llms_current_time() to allow mocking of the current time via the $llms_tests_mock_time global
 *
 * @since 1.2.0
 *
 * @param string   $type Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date format string (e.g. 'Y-m-d').
 * @param int|bool $gmt   Optional. Whether to use GMT timezone. Default false.
 * @return int|string Integer if $type is 'timestamp', string otherwise.
 */
function llms_current_time( $type, $gmt = 0 ) {
	global $llms_tests_mock_time;
	if ( ! empty( $llms_tests_mock_time ) ) {

		switch ( $type ) {
			case 'mysql':
				return date( 'Y-m-d H:i:s', $llms_tests_mock_time );
			case 'timestamp':
				return $llms_tests_mock_time;
			default:
				return date( $type, $llms_tests_mock_time );
		}

	}
	return current_time( $type, $gmt );
}

/**
 * Set the mocked current time
 *
 * @since 1.2.0
 *
 * @param mixed $time Date time string parseable by date().
 * @return void
 */
function llms_tests_mock_current_time( $time ) {
	global $llms_tests_mock_time;
	$llms_tests_mock_time = is_numeric( $time ) ? $time : strtotime( $time );
}

/**
 * Mock the WP_Screen object
 *
 * @since 1.13.0
 *
 * @param string $id Screen ID.
 * @return void
 */
function llms_tests_mock_current_screen( $id ) {
	set_current_screen( $id );
}

/**
 * Reset the WP_Screen object
 *
 * I can't find anything officially documenting the proper way to do this but this line seems to indicate
 * you can reset it by using `front` as the current screen, see link below.
 *
 * @since 1.13.0
 *
 * @link https://core.trac.wordpress.org/browser/tags/5.4/src/wp-admin/includes/class-wp-screen.php#L277
 *
 * @return [type] [description]
 */
function llms_tests_reset_current_screen() {
	set_current_screen( 'front' );
}

/**
 * Reset current time after mocking it
 *
 * @since 1.2.0
 *
 * @return void
 */
function llms_tests_reset_current_time() {
	global $llms_tests_mock_time;
	$llms_tests_mock_time = null;
}

/**
 * Mock the LifterLMS Core `llms_setcookie()` so functions setting cookies can be tested.
 *
 * Mocks the function to use `LLMS_Tests_Cookies::set()` which accepts the same arguments as
 * `llms_setcookie()` and php native `setcookie()`.
 *
 * In any classes extending the base testcase you can access set cookies via `$this->cookies`.
 *
 * @since 1.7.0
 * @since 1.12.2 Include cookies class if not already loaded.
 *
 * @see LLMS_Tests_Cookies
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
function llms_setcookie( $name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httponly = false ) {
	if ( ! class_exists( 'LLMS_Tests_Cookies' ) ) {
		include 'class-llms-tests-cookies.php';
	}
	$cookies = LLMS_Tests_Cookies::instance();
	return $cookies->set( $name, $value, $expires, $path, $domain, $secure, $httponly );
}

/**
 * Plug core `llms_filter_input` to allow data to be mocked via the mock request test case methods.
 *
 * @since Unknown
 *
 * @param int    $type          One of INPUT_GET, INPUT_POST, INPUT_COOKIE, INPUT_SERVER, or INPUT_ENV.
 * @param string $variable_name Name of a variable to get.
 * @param int    $filter        The ID of the filter to apply.
 * @param mixed  $options       Associative array of options or bitwise disjunction of flags. If filter accepts options, flags can be provided in "flags" field of array.
 * @return mixed Value of the requested variable on success, FALSE if the filter fails, or NULL if the variable_name variable is not set. If the flag FILTER_NULL_ON_FAILURE is used, it returns FALSE if the variable is not set and NULL if the filter fails.
 */
function llms_filter_input( $type, $variable_name, $filter = FILTER_DEFAULT, $options = array() ) {

	// Get the raw data.
	switch( $type ) {

		case INPUT_POST:
			$data = $_POST;
			break;

		case INPUT_GET:
			$data = $_GET;
			break;

		case INPUT_SERVER:
			$data = $_SERVER;
			break;

		case INPUT_ENV:
			$data = $_ENV;
			break;

		case INPUT_COOKIE:
			$data = $_COOKIE;
			break;

		default:
			$data = array();

	}

	if ( isset( $data[ $variable_name ] ) ) {

		return filter_var( $data[ $variable_name ], $filter, $options );

	}

	return null;

}

/**
 * Plugs the llms_exit() function to throw an exit exception instead of exiting
 *
 * @since 2.1.0
 *
 * @param string|int $status Exit status.
 * @return void
 *
 * @throws LLMS_Unit_Test_Exception_Exit
 */
function llms_exit( $status = null ) {
	throw new LLMS_Unit_Test_Exception_Exit( $status );
}

/**
 * Plug llms_redirect_and_exit() to throw a redirect exception instead of redirecting and exiting
 *
 * @since Unknown
 *
 * @param string $location Full URL to redirect to.
 * @param array  $options {
 *     @type int  $status HTTP status code of the redirect [default: 302].
 *     @type bool $safe   If true, use `wp_safe_redirect()` otherwise use `wp_redirect()` [default: true].
 *
 * }
 * @return void
 *
 * @throws LLMS_Unit_Test_Exception_Redirect
 */
function llms_redirect_and_exit( $location, $options = array() ) {
	$options = wp_parse_args( $options, array(
		'status' => 302,
		'safe' => true,
	) );
	throw new LLMS_Unit_Test_Exception_Redirect( $location, $options['status'], null, $options['safe'] );
}


