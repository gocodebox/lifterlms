<?php
/**
 * Test LLMS_Certificates
 *
 * @package LifterLMS/Tests
 *
 * @group certificates
 *
 * @since 3.37.3
 * @version 3.37.4
 */
class LLMS_Test_Certificates extends LLMS_UnitTestCase {

	/**
	 * Test trigger_engagement() method.
	 *
	 * @since 3.37.3
	 * @since 3.37.4 Use `$this->create_certificate_template()` from test case base.
	 *
	 * @return void
	 */
	public function test_trigger_engagement() {

		$user = $this->factory->user->create();
		$template = $this->create_certificate_template();
		$related = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$earned = $this->earn_certificate( $user, $template, $related );

		// User ID.
		$this->assertEquals( $user, $earned[0] );

		// Related ID.
		$this->assertEquals( $related, $earned[2] );

	}

	/**
	 * Retrieve a certificate export, bypassing the cache.
	 *
	 * @since 3.37.3
	 * @since 3.37.4 Use `$this->create_certificate_template()` from test case base.
	 *
	 * @return void
	 */
	public function test_get_export_no_cache() {

		$user = $this->factory->user->create();
		$template = $this->create_certificate_template();
		$related = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$earned = $this->earn_certificate( $user, $template, $related );

		$cert_id = $earned[1];

		$path = LLMS()->certificates()->get_export( $cert_id );
		$this->assertTrue( false !== strpos( $path, '/uploads/llms-tmp/certificate-mock-certificate-title' ) );
		$this->assertTrue( false !== strpos( $path, '.html' ) );

	}

	/**
	 * Retrieve a certificate export using caching.
	 *
	 * @since 3.37.3
	 * @since 3.37.4 Use `$this->create_certificate_template()` from test case base.
	 *
	 * @return void
	 */
	public function test_get_export_with_cache() {

		$user = $this->factory->user->create();
		$template = $this->create_certificate_template();
		$related = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$earned = $this->earn_certificate( $user, $template, $related );

		$cert_id = $earned[1];

		// Generate a new cert when item not found in the cache.
		$orig_path = LLMS()->certificates()->get_export( $cert_id, true );
		$this->assertTrue( false !== strpos( $orig_path, '/uploads/llms-tmp/certificate-mock-certificate-title' ) );

		// Store the filepath for future use.
		$this->assertEquals( $orig_path, get_post_meta( $cert_id, '_llms_export_filepath', true ) );

		// Get it again, should return the original path from the cache.
		$cached_path = LLMS()->certificates()->get_export( $cert_id, true );
		$this->assertEquals( $orig_path, $cached_path );

		// Delete the file (simulate LLMS_TMP_DIR file expiration).
		unlink( $orig_path );

		// Should regen since the file saved in meta data doesn't exist anymore.
		$new_path = LLMS()->certificates()->get_export( $cert_id, true );
		$this->assertTrue( $orig_path !== $new_path );

	}

}
