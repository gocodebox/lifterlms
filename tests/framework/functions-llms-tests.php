<?php
/**
 * Set the mocked current time
 * @param    mixed     $time  date time string parsable by date()
 * @return   void
 * @since    3.4.0
 * @version  [version]
 * @deprecated [version]
 */
function llms_mock_current_time( $time ) {
	llms_tests_mock_current_time( $time );
}

/**
 * Reset current time after mocking it
 * @return   void
 * @since    3.16.0
 * @version  [version]
 * @deprecated [version]
 */
function llms_reset_current_time() {
	llms_tests_reset_current_time();
}

/**
 * Plug llms_redirect_and_exit() to throw a redirect exception instead of redirecting and exiting
 * @param    string     $location  full URL to redirect to
 * @param    array      $options   array of options
 *                                 $status  int   HTTP status code of the redirect [default: 302]
 *                                 $safe    bool  If true, use `wp_safe_redirect()` otherwise use `wp_redirect()` [default: true]
 * @return   void
 * @since    3.19.4
 * @version  3.19.4
 */
function llms_redirect_and_exit( $location, $options = array() ) {

	$options = wp_parse_args( $options, array(
		'status' => 302,
		'safe' => true,
	) );

	throw new LLMS_Testing_Exception_Redirect( $location, $options['status'], null, $options['safe'] );

}
