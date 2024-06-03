<?php
/**
 * Test inclusion and initialization of the LLMS-CLI library
 *
 * @package LifterLMS/Tests
 *
 * @group cli
 * @group packages
 *
 * @since 5.5.0
 * @version 5.5.0
 */
class LLMS_Test_CLI extends LLMS_Unit_Test_Case {

	/**
	 * Test rest package exists and is loaded.
	 *
	 * @since 5.5.0
	 *
	 * @return void
	 */
	public function test_cli_package_exists() {
		$this->assertTrue( function_exists( 'llms_cli' ) );
	}

}
