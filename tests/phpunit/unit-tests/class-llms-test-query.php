<?php
/**
 * Tests for LLMS_Query class
 *
 * @package LifterLMS/Tests
 *
 * @group query
 *
 * @since [version]
 */
class LLMS_Test_Query extends LLMS_UnitTestCase {

	/**
	 * Set up test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Query();

	}

	/**
	 * Test maybe_404_certificate()
	 *
	 * This test runs in a separate process because something before it is making it hard
	 * to mock the `$wp_query` and `$post` globals.
	 *
	 * @since [version]
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_maybe_404_certificate() {

		global $post, $wp_query;
		$temp = $post;

		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );

		// Not set.
		$post = null;
		$this->main->maybe_404_certificate();
		$this->assertFalse( $wp_query->is_404() );

		$tests = array(
			'llms_my_certificate' => true,
			'post'                => false,
			'page'                => false,
			'course'              => false,
		);

		foreach ( $tests as $post_type => $expect ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$wp_query->init();

			// Logged out user.
			$this->main->maybe_404_certificate();
			$this->assertEquals( $expect, $wp_query->is_404(), $post_type );

			// Logged in admin can always see.
			$wp_query->init();
			wp_set_current_user( $admin );
			$this->main->maybe_404_certificate();
			$this->assertFalse( $wp_query->is_404(), $post_type );

			wp_set_current_user( null );

		}

		$post = $temp;

	}

}
