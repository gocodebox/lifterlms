<?php
/**
 * Test LLMS_Block_Library
 *
 * @package LifterLMS/Tests
 *
 * @group blocks
 * @group block_library
 *
 * @since 6.0.0
 */
class LLMS_Test_Block_Library extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		if ( ! llms_is_block_editor_supported_for_certificates() ) {
			$this->markTestSkipped( 'No blocks supported on this version of WordPress.' );
		}

		parent::set_up();
		$this->main     = new LLMS_Block_Library();
		$this->registry = WP_Block_Type_Registry::get_instance();

		$this->deregister_blocks();

	}

	/**
	 * Deregister all LifterLMS blocks.
	 *
	 * @since 6.0.0
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
	 * Test modify_editor_settings()
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_modify_editor_settings() {

		if ( ! class_exists( 'WP_Block_Editor_Context' ) ) {
			$this->markTestSkipped( 'Test not required on this version of WordPress.' );
		}

		$input = array( 'settings' => '123' );

		// Like widgets or site editor.
		$this->assertEquals(
			$input,
			$this->main->modify_editor_settings( $input, new WP_Block_Editor_Context() )
		);

		// Post editor but for the wrong post type.
		$post = $this->factory->post->create_and_get();
		$this->assertEquals(
			$input,
			$this->main->modify_editor_settings( $input, new WP_Block_Editor_Context( compact( 'post' ) ) )
		);

		// Settings already has theme fonts.
		$input_with_theme = $input;
		// Add a theme font.
		_wp_array_set(
			$input_with_theme,
			array( '__experimentalFeatures', 'typography', 'fontFamilies', 'theme' ),
			array(
				array(
					'fontFamily' => '"Awesome Sans"',
					'name'       => 'Awesome Sans',
					'slug'       => 'awesome-sans',
				)
			)
		);

		// Certificate context!
		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );

			$res = $this->main->modify_editor_settings( $input, new WP_Block_Editor_Context( compact( 'post' ) ) );

			// Still has initial settings.
			$this->assertEquals( '123', $res['settings'] );

			// Fonts have been injected.
			$fonts = _wp_array_get( $res, array(
				'__experimentalFeatures',
				'blocks',
				'llms/certificate-title',
				'typography',
				'fontFamilies',
				'custom',
			) );
			$this->assertEquals( array_keys( llms_get_certificate_fonts() ), wp_list_pluck( $fonts, 'slug' ) );


			// Theme fonts are preserved.
			$res = $this->main->modify_editor_settings( $input_with_theme, new WP_Block_Editor_Context( compact( 'post' ) ) );
			// Fonts have been injected.
			$fonts = _wp_array_get( $res, array(
				'__experimentalFeatures',
				'blocks',
				'llms/certificate-title',
				'typography',
				'fontFamilies',
				'custom',
			) );
			$this->assertEquals( array_merge( array( 'awesome-sans' ), array_keys( llms_get_certificate_fonts() ) ), wp_list_pluck( $fonts, 'slug' ) );

		}

	}

	/**
	 * Test register().
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
