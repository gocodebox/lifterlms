<?php
/**
 * Assertions related to checking for WP_Error things
 *
 * @since 1.5.0
 * @version 1.5.0
 */
trait LLMS_Unit_Test_Assertions_String {

	/**
	 * Assert that a string contains another string.
	 *
	 * @since 1.5.0
	 *
	 * @param string $contains String that should be found within $string.
	 * @param string $string String to check.
	 * @return void
	 */
	public function assertStringContains( $contains, $string ) {

		$this->assertTrue( false !== strpos( $string, $contains ) );

	}

	/**
	 * Assert that a string does not contain another string.
	 *
	 * @since 1.5.0
	 *
	 * @param string $contains String that should not be found within $string.
	 * @param string $string String to check.
	 * @return void
	 */
	public function assertStringNotContains( $contains, $string ) {

		$this->assertTrue( false === strpos( $string, $contains ) );

	}

}
