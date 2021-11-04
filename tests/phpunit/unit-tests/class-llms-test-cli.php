<?php
/**
 * Test inclusion and initialization of the LLMS-CLI library
 *
 * @package LifterLMS/Tests
 *
 * @group cli
 * @group packages
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_CLI extends LLMS_Unit_Test_Case {

	/**
	 * Test rest package exists and is loaded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_cli_package_exists() {
		$this->assertTrue( function_exists( 'llms_cli' ) );
	}

}
