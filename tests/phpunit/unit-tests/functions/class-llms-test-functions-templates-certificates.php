<?php
/**
 * Test certificate template functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_template
 * @group functions_template_certificates
 *
 * @since [version]
 */
class LLMS_Test_Functions_Templates_Certificates extends LLMS_UnitTestCase {

	/**
	 * Retrieve a certificate for testing.
	 *
	 * @since [version]
	 *
	 * @param array $args Certificate creation arguments.
	 * @return LLMS_User_Certificate
	 */
	private function get_cert( $args = array() ) {
		return llms_get_certificate( $this->factory->post->create( wp_parse_args( $args, array( 'post_type' => 'llms_my_certificate' ) ) ) );
	}

	/**
	 * Test llms_certificate_content() with a v1 certificate template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_certificate_content_v1() {

		$cert = $this->get_cert();

		$output = $this->get_output( 'llms_certificate_content', array( $cert ) );

		$this->assertStringContains( '<div class="llms-certificate-container" style="width:800px; height:616px;">', $output );
		$this->assertStringContains( sprintf( '<div id="certificate-%d" class="">', $cert->get( 'id' ) ), $output );

	}

	/**
	 * Test llms_certificate_content() with a v2 certificate template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_certificate_content_v2() {

		$cert = $this->get_cert( array( 'post_content' => '' ) );

		$this->assertOutputContains(
			sprintf( '<div id="certificate-%d" class="llms-certificate-container cert-template-v2">', $cert->get( 'id' ) ),
			'llms_certificate_content',
			array( $cert )
		);

	}

	/**
	 * Test llms_certificate_styles() with an invalid post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_certificate_styles_not_a_cert() {

		global $post;
		$post = $this->factory->post->create_and_get();

		$this->assertOutputEmpty( 'llms_certificate_styles' );

		$post = null;

	}

	/**
	 * Test llms_certificate_styles() with an v1 template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_certificate_styles_v1() {

		global $post;
		$post =  $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_certificate' ) );

		$this->assertOutputEmpty( 'llms_certificate_styles' );

		$post = null;

	}

	/**
	 * Test llms_certificate_styles() with an v2 template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_certificate_styles_v2() {

		global $post;
		$post =  $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_certificate', 'post_content' => '' ) );

		$output = $this->get_output( 'llms_certificate_styles' );
		$this->assertStringContains( '<style type="text/css">', $output );
		$this->assertStringContains( '<style type="text/css" media="print">', $output );

		$post = null;

	}

	/**
	 * Test llms_certificate_actions().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_certificate_actions() {

		$cert = $this->get_cert();

		// Cannot manage.
		wp_set_current_user( null );
		$this->assertOutputEmpty( 'llms_certificate_actions', array( $cert ) );

		// Can manage.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertOutputContains(
			'<div class="llms-print-certificate no-print" id="llms-print-certificate">',
			'llms_certificate_actions', array( $cert )
		);

	}

	// public function test_llms_get_certificate_preview() {}
	// public function test_llms_the_certificate_preview() {}

	/**
	 * Test llms_get_certificates_loop_columns().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_certificates_loop_columns() {
		$this->assertTrue( is_int( llms_get_certificates_loop_columns() ) );
	}

	// public function test_lifterlms_template_certificates_loop() {}

}
