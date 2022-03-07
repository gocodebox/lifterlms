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
 * @since 6.0.0
 */
class LLMS_Test_Functions_Certificates extends LLMS_UnitTestCase {

	/**
	 * Test llms_get_certificate().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate() {

		$post = $this->factory->post->create();

		// Invalid post type.
		$this->assertFalse( llms_get_certificate( $post ) );

		// Non-existent post.
		$this->assertFalse( llms_get_certificate( $post + 1 ) );

		// Template post without preview flag.
		$template_post = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );
		$this->assertFalse( llms_get_certificate( $template_post ) );

		// Template post with preview flag.
		$preview = llms_get_certificate( $template_post, true );
		$this->assertInstanceOf( 'LLMS_User_Certificate', $preview );
		$this->assertEquals( $template_post, $preview->get( 'id' ) );

		// Earned cert.
		$earned_post = $this->factory->post->create( array( 'post_type' => 'llms_my_certificate' ) );
		$earned = llms_get_certificate( $earned_post );
		$this->assertInstanceOf( 'LLMS_User_Certificate', $earned );
		$this->assertEquals( $earned_post, $earned->get( 'id' ) );

		// From global.
		global $post;
		$post = get_post( $earned_post );
		$global = llms_get_certificate();
		$this->assertInstanceOf( 'LLMS_User_Certificate', $global );
		$this->assertEquals( $earned_post, $global->get( 'id' ) );

		$post = null;

	}

	/**
	 * Test llms_get_certificate_content().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_content() {

		// Invalid post.
		$post = $this->factory->post->create();
		$this->assertEquals( '', llms_get_certificate_content( $post ) );

		// Template: merge the stored content.
		$template_id = $this->factory->post->create( array(
			'post_type' => 'llms_certificate',
			'post_content' => 'Cert ID: {certificate_id}',
		) );
		$this->assertEquals( "<p>Cert ID: {$template_id}</p>\n", llms_get_certificate_content( $template_id ) );

		// Earned cert: return the stored content.
		$earned_id = $this->factory->post->create( array(
			'post_type' => 'llms_certificate',
			'post_content' => 'Just some content.',
		) );
		$this->assertEquals( "<p>Just some content.</p>\n", llms_get_certificate_content( $earned_id ) );

	}

	/**
	 * Test llms_get_certificate_fonts().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_fonts() {

		$this->assertIsArray( llms_get_certificate_fonts() );

	}

	public function test_llms_get_certificate_image() {}

	/**
	 * Test llms_get_certificate_merge_codes()
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
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

	/**
	 * Test llms_get_certificate_title().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_get_certificate_title() {

		// Invalid post type.
		$post = $this->factory->post->create();
		$this->assertEquals( '', llms_get_certificate_title( $post ) );

		$template_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate', 'post_title' => 'Not Returned' ) );
		update_post_meta( $template_id, '_llms_certificate_title', 'Cert Title!' );

		$this->assertEquals( 'Cert Title!', llms_get_certificate_title( $template_id ) );

		$earned_id = $this->factory->post->create( array( 'post_type' => 'llms_my_certificate', 'post_title' => 'A Title' ) );
		$this->assertEquals( 'A Title', llms_get_certificate_title( $earned_id ) );

	}

	/**
	 * Test llms_is_block_editor_supported_for_certificates()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_llms_is_block_editor_supported_for_certificates() {

		global $wp_version;
		$orig = $wp_version;

		$tests = array(
			// Unsupported versions.
			array( '5.5.0', false ),
			array( '5.6.0', false ),
			array( '5.6.1', false ),
			array( '5.7', false ),
			array( '5.7.0', false ),
			array( '5.7.5', false ),
			// Supported versions.
			array( '5.8', true ),
			array( '5.8.0', true ),
			array( '5.8.1', true ),
			array( '5.8.3', true ),
			array( '5.9', true ),
			array( '5.9-alpha', true ),
			array( '5.9-RC1', true ),
			// Future versions?
			array( '6.0', true ),
			array( '6.0.1', true ),
		);

		foreach ( $tests as $test ) {
			list( $wp_version, $expect ) = $test;
			$this->assertEquals( $expect, llms_is_block_editor_supported_for_certificates(), $wp_version );

			// Test "-src" version.
			$wp_version .= '-src';
			$this->assertEquals( $expect, llms_is_block_editor_supported_for_certificates(), $wp_version);
		}

		$wp_version = $orig;

	}

}
