<?php
/**
 * Set the mocked current time
 * @param    mixed     $time  date time string parsable by date()
 * @return   void
 * @since    3.4.0
 * @version  3.28.0
 * @deprecated 3.28.0
 */
function llms_mock_current_time( $time ) {
	llms_tests_mock_current_time( $time );
}

/**
 * Reset current time after mocking it
 * @return   void
 * @since    3.16.0
 * @version  3.28.0
 * @deprecated 3.28.0
 */
function llms_reset_current_time() {
	llms_tests_reset_current_time();
}
