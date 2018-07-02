<?php

/**
 * Plug llms_crrent_time() to allow mocking of the current time via the $llms_mock_time global
 * @param  string       $type   Type of time to retrieve. Accepts 'mysql', 'timestamp', or PHP date format string (e.g. 'Y-m-d').
 * @param  int|bool     $gmt    Optional. Whether to use GMT timezone. Default false.
 * @return int|string           Integer if $type is 'timestamp', string otherwise.
 * @since    3.4.0
 * @version  3.17.0
 */
function llms_current_time( $type, $gmt = 0 ) {
	global $llms_mock_time;
	if ( ! empty( $llms_mock_time ) ) {

		switch ( $type ) {
			case 'mysql':
				return date( 'Y-m-d H:i:s', $llms_mock_time );
			case 'timestamp':
				return $llms_mock_time;
			default:
				return date( $type, $llms_mock_time );
		}

	}
	return current_time( $type, $gmt );
}

/**
 * Set the mocked current time
 * @param    mixed     $time  date time string parsable by date()
 * @return   void
 * @since    3.4.0
 * @version  3.4.0
 */
function llms_mock_current_time( $time ) {
	global $llms_mock_time;
	$llms_mock_time = strtotime( $time );
}

/**
 * Reset current time after mocking it
 * @return   void
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_reset_current_time() {
	global $llms_mock_time;
	$llms_mock_time = null;
}




/**
 * Plug llms_redirect_and_exit() to throw a redirect exception instead of redirecting and exiting
 * @param    string     $location  full URL to redirect to
 * @param    array      $options   array of options
 *                                 $status  int   HTTP status code of the redirect [default: 302]
 *                                 $safe    bool  If true, use `wp_safe_redirect()` otherwise use `wp_redirect()` [default: true]
 * @return   void
 * @since    [version]
 * @version  [version]
 */
function llms_redirect_and_exit( $location, $options = array() ) {

	$options = wp_parse_args( $options, array(
		'status' => 302,
		'safe' => true,
	) );

	throw new LLMS_Testing_Exception_Redirect( $location, $options['status'], null, $options['safe'] );

}

/**
 * Set the time limit threshold
 * @param    int        $limit   time limit in milliseconds
 * @return   void
 * @since    3.17.4
 * @version  3.17.4
 */
function llms_set_test_time_limit( $limit = 4000 ) {
	global $llms_test_time_limit;
	$llms_test_time_limit = $limit;
}
