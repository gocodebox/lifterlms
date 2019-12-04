<?php
/**
 * Test LLMS_Controller_Certificates
 *
 * @package LifterLMS/Tests/Controllers
 *
 * @group controllers
 * @group certificates
 * @group controller_certificates
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Controller_Certificates extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->instance = new LLMS_Controller_Certificates();

	}

	/**
	 * Test maybe_allow_public_query(): no authorization data in query string.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_no_auth() {
		$this->assertEquals( array(), $this->instance->maybe_allow_public_query( array() ) );
	}

	/**
	 * Test maybe_allow_public_query(): authorization present but invalid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_invalid_auth() {

		// Doesn't exist.
		$args = array(
			'publicly_queryable' => false,
		);

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'fake',
		) );

		$this->assertEquals( $args, $this->instance->maybe_allow_public_query( $args ) );

		// Post exists but submitted nocne is incorrect.
		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );
		update_post_meta( $post_id, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'incorrect-nonce',
		) );
		$this->assertEquals( $args, $this->instance->maybe_allow_public_query( $args ) );

	}

	/**
	 * Test maybe_allow_public_query(): authorization present and exists but on an invalid post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_invalid_post_type() {

		$post_id = $this->factory->post->create();
		update_post_meta( $post_id, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'mock-nonce',
		) );

		$args = array(
			'publicly_queryable' => false,
		);

		$this->assertEquals( $args, $this->instance->maybe_allow_public_query( $args ) );

	}

	/**
	 * Test maybe_allow_public_query(): valid auth and post type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_allow_public_query_update() {

		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_certificate' ) );
		update_post_meta( $post_id, '_llms_auth_nonce', 'mock-nonce' );

		$this->mockGetRequest( array(
			'_llms_cert_auth' => 'mock-nonce',
		) );

		$args = array(
			'publicly_queryable' => false,
		);
		$expect = array(
			'publicly_queryable' => true,
		);

		$this->assertEquals( $expect, $this->instance->maybe_allow_public_query( $args ) );

	}

}
