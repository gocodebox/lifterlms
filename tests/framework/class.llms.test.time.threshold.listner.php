<?php
/**
 * Output warnings when tests take too long to run
 * @source    http://aaronsaray.com/2017/phpunit-listener-for-long-running-tests-update
 * @since     [version]
 * @version   [version]
 */
class LLMS_Test_Time_Threshold_Listener extends PHPUnit_Framework_BaseTestListener {

    public function startTestSuite( PHPUnit_Framework_TestSuite $suite )  {
    	llms_set_test_time_limit();
    }

	/**
	 * A test ended - print out if it was too long
	 * @param Test $test
	 * @param float $time seconds
	 */
	public function endTest( PHPUnit_Framework_Test $test, $time ) {

		global $llms_test_time_limit;

		if ( $time * 1000 > $llms_test_time_limit ) {
			$error = sprintf(
				'%s::%s ran for %s seconds',
				get_class( $test ),
				$test->getName(),
				$time
			);
			print "\n\033[41m" . $error . "\033[0m\n";
		}

		// reset the time limit in case a test has expanded the limit
		llms_set_test_time_limit();

	}

}
