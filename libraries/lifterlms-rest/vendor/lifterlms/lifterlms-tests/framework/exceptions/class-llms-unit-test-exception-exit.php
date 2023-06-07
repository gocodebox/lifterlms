<?php
/**
 * Allow testing functions that call `exit()`
 *
 * Example usage to set the expected exception:
 *
 *     $this->expectException( LLMS_Unit_Test_Exception_Exit::class );
 *     llms_exit();
 *     // No further code will be executed.
 *
 * Example usage with try/catch:
 *
 *     try {
 *         llms_exit( 1 );
 *     } catch ( LLMS_Unit_Test_Exception_Exit $exception ) {
 *         $this->assertEquals( 1, $exception->get_status() );
 *         // Other assertions here.
 *     }
 *
 * Use `` before calling the function
 * to test the function that calls `exit()`
 *
 * @since Unknown
 */
class LLMS_Unit_Test_Exception_Exit extends Exception {

	/**
	 * Return the exit status supplied to exit() via llms_exit()
	 *
	 * This is a wrapper around getMessage() implemented since it makes sense to retrieve the exit status
	 * from `get_status()` in favor of `getMessage()`, right?
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->getMessage();
	}

}
