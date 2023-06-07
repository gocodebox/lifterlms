<?php
/**
 * Assertions related to checking for WP_Error things
 *
 * @since    1.3.0
 * @version  1.4.0
 */
trait LLMS_Unit_Test_Assertions_WP_Error {

	/**
	 * Assert that a give object is a WP_Error
	 *
	 * @param   WP_Error $wp_err
	 * @return  void
	 * @since   1.2.1
	 * @version 1.3.0
	 */
	public function assertIsWPError( $wp_err ) {

		$this->assertTrue( is_a( $wp_err, 'WP_Error' ) );

	}

	/**
	 * Arrest that a given object has an expected WP_Error code.
	 *
	 * @param   string    $expected expected error code
	 * @param   WP_Error $wp_err
	 * @return  void
	 * @since   1.2.1
	 * @version 1.3.0
	 */
	public function assertWPErrorCodeEquals( $expected, $wp_err ) {

		$this->assertEquals( $expected, $wp_err->get_error_code() );

	}

	/**
	 * Assert that a given object has an expected WP_Error message.
	 *
	 * @since 1.4.0
	 *
	 * @param string $expected Expected error message.
	 * @param WP_Error $wp_err Error object.
	 * @return void
	 */
	public function assertWPErrorMessageEquals( $expected, $wp_err ) {

		$this->assertEquals( $expected, $wp_err->get_error_message() );

	}

	/**
	 * Assert that a given object has an expected WP_Error data.
	 *
	 * @since 1.4.0
	 *
	 * @param array $expected Expected error data.
	 * @param WP_Error $wp_err Error object.
	 * @return void
	 */
	public function assertWPErrorDataEquals( $expected, $wp_err ) {

		$this->assertEquals( $expected, $wp_err->get_error_data() );

	}

}
