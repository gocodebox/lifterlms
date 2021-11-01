<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @package LifterLMS/Tests
 *
 * @group functions
 * @group functions_certificates
 * @group certificates
 *
 * @since [version]
 */
class LLMS_Test_Functions_Certificates extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_certificate_merge_codes()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_merge_codes() {

		$ret = llms_get_certificate_merge_codes();
		$this->assertIsArray( $ret );
		foreach ( $ret as $code => $desc ) {

			$this->assertEquals( '{', $code[0] );
			$this->assertEquals( '}', $code[strlen( $code ) - 1] );
			$this->assertIsString( $desc );

		}

	}

}
