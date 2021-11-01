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

	/**
	 * Test llms_get_certificate_sequential_id()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_sequential_id() {

		$template_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );


		// Default ID (skips incrementing).
		$this->assertEquals( 1, llms_get_certificate_sequential_id( $template_id, true ) );

		// Increment the ID.
		$this->assertEquals( 2, llms_get_certificate_sequential_id( $template_id, true ) );

		// Retrieve the stored ID.
		$this->assertEquals( 2, llms_get_certificate_sequential_id( $template_id, false ) );

		// Set it to a new value & retrieve it.
		update_post_meta( $template_id, '_llms_sequential_id', 923409 );
		$this->assertEquals( 923409, llms_get_certificate_sequential_id( $template_id, false ) );

		// Increment it and retrieve it.
		$this->assertEquals( 923410, llms_get_certificate_sequential_id( $template_id, true ) );
		$this->assertEquals( 923410, llms_get_certificate_sequential_id( $template_id, false ) );

	}

}
