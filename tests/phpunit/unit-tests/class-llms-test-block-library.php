<?php
/**
 * Test LLMS_Block_Library
 *
 * @package LifterLMS/Tests
 *
 * @group blocks
 * @group block_library
 *
 * @since [version]
 */
class LLMS_Test_Block_Library extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main     = new LLMS_Block_Library();
		$this->registry = WP_Block_Type_Registry::get_instance();

		$this->deregister_blocks();

	}

	/**
	 * Deregister all LifterLMS blocks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function deregister_blocks() {

		foreach ( array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_blocks' ) ) as $id ) {
			$id = 'llms/' . $id;
			if ( $this->registry->is_registered( $id ) ) {
				$this->registry->unregister( $id );
			}
		}

	}

	/**
	 * Test register().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_wrong_context() {

		$this->main->register();
		$this->assertFalse( $this->registry->is_registered( 'llms/certificate-title' ) );

	}

	/**
	 * Test register() when in the block editor for an existing post.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_post_dot_php() {

		global $pagenow;
		$original_pagenow = $pagenow;
		$pagenow = 'post.php';

		$block_id = 'llms/certificate-title';

		// No post ID set so post type can't be found.
		$this->main->register();
		$this->assertFalse( $this->registry->is_registered( $block_id ) );

		// Regular post, nothing to register.
		$this->mockGetRequest( array( 'post' => $this->factory->post->create() ) );
		$this->main->register();
		$this->assertFalse( $this->registry->is_registered( $block_id ) );

		// Valid post.
		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$this->mockGetRequest( array( 'post' => $this->factory->post->create( compact( 'post_type' ) ) ) );

			$this->main->register();
			$this->assertTrue( $this->registry->is_registered( 'llms/certificate-title' ) );

			// Ensure _doing_it_wrong() isn't thrown when registering a block that's already registered.
			$this->main->register();

			$this->deregister_blocks();
		}

		$pagenow = $original_pagenow;

	}


	/**
	 * Test register() when in the block editor for a new post.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_post_new_dot_php() {

		global $pagenow;
		$original_pagenow = $pagenow;
		$pagenow = 'post-new.php';

		$block_id = 'llms/certificate-title';

		// No post type set so the post type is assumed to be a post.
		$this->main->register();
		$this->assertFalse( $this->registry->is_registered( $block_id ) );

		// Valid post.
		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$this->mockGetRequest( compact( 'post_type' ) );

			$this->main->register();
			$this->assertTrue( $this->registry->is_registered( 'llms/certificate-title' ) );

			// Ensure _doing_it_wrong() isn't thrown when registering a block that's already registered.
			$this->main->register();

			$this->deregister_blocks();
		}

		$pagenow = $original_pagenow;

	}

}
