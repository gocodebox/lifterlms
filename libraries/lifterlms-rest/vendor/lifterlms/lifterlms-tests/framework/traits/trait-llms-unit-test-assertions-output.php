<?php
/**
 * Assertions related to checking for WP_Error things.
 *
 * @since 1.3.0
 * @version 3.2.1
 */
trait LLMS_Unit_Test_Assertions_Output {

	/**
	 * Assert that the output of a function contains a string
	 *
	 * @param   string    $contains string that should be found in the output.
	 * @param   callable  $func function to be called.
	 * @param   array     $args parameters passed to $func as an indexed array.
	 * @return  void
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function assertOutputContains( $contains, $func, $args = array() ) {

		$this->assertTrue( false !== strpos( $this->get_output( $func, $args ), $contains ) );

	}

	/**
	 * Assert that the output of a function doesn't contain a string
	 *
	 * @param   string    $contains string that shouldn't be found in the output.
	 * @param   callable  $func function to be called.
	 * @param   array     $args parameters passed to $func as an indexed array.
	 * @return  void
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function assertOutputNotContains( $contains, $func, $args = array() ) {

		$this->assertTrue( false === strpos( $this->get_output( $func, $args ), $contains ) );

	}

	/**
	 * Assert that the output of a function is empty.
	 *
	 * @param   callable  $func function to be called.
	 * @param   array     $args parameters passed to $func as an indexed array.
	 * @return  void
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function assertOutputEmpty( $func, $args = array() ) {

		$this->assertEmpty( $this->get_output( $func, $args ) );

	}

	/**
	 * Assert that the output of a function equals the expectation
	 *
	 * @param   string    $expected expected output.
	 * @param   callable  $func function to be called.
	 * @param   array     $args parameters passed to $func as an indexed array.
	 * @return  void
	 * @since   1.3.0
	 * @version 1.3.0
	 */
	public function assertOutputEquals( $expected, $func, $args = array() ) {

		$this->assertEquals( $expected, $this->get_output( $func, $args ) );

	}

	/**
	 * Get the output of of a callable.
	 *
	 * @since 1.3.0
	 * @since 3.2.1 Allow getting the ouput of private/protected methods.
	 *
	 * @param callable $func Function to be called.
	 * @param array    $args Parameters passed to $func as an indexed array.
	 * @return string
	 */
	public function get_output( $func, $args = array() ) {

		ob_start();

		// Is it a not accessible method?
		if ( is_array( $func ) && ! is_callable( $func ) && LLMS_Unit_Test_Util::get_private_method( ...$func ) ) {
			array_push( $func, $args );
			LLMS_Unit_Test_Util::call_method( ...$func );
		} else {
			$func(...$args);
		}

		return ob_get_clean();

	}

}
