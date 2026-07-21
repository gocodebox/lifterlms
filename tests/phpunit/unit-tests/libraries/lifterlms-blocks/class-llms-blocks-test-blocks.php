<?php
/**
 * Test main plugin file.
 *
 * @package LifterLMS_Blocks/Tests
 *
 * @since 1.5.1
 * @since 1.6.0 Update `test_add_block_category` test to accommodate form fields cat.
 * @since 1.10.0 Update `test_get_dynamic_block_names` to test against core blocks available in 5.1.
 * @version 2.2.1
 */
class LLMS_Blocks_Test_Blocks extends LLMS_Blocks_Unit_Test_Case {

	/**
	 * Test the add_block_category() method
	 *
	 * @since 1.5.1
	 * @since 1.6.0 Update test to accommodate form fields cat.
	 * @since 2.0.0 Update test to reflect updated cat order.
	 *
	 * @return void
	 */
	public function test_add_block_category() {

		$obj = new LLMS_Blocks();

		$our_cats = array(
			array(
				'slug'  => 'llms-user-info-fields',
				'title' => 'User Information',
			),
			array(
				'slug'  => 'llms-custom-fields',
				'title' => 'Custom User Information',
			),
			array(
				'slug'  => 'llms-blocks',
				'title' => 'LifterLMS Blocks',
			),
		);

		$existing = array(
			'slug'  => 'fake-cat-slug',
			'title' => 'Fake Cat Title',
		);

		$this->assertSame( $our_cats, $obj->add_block_category( array() ) );
		$this->assertSame( array( $our_cats[0], $our_cats[1], $existing, $our_cats[2] ), $obj->add_block_category( array( $existing ) ) );

	}

	/**
	 * Ensure the WP core method get_block_categories() results in the `llms-blocks` category being added.
	 *
	 * @since 1.5.1
	 *
	 * @return void
	 */
	public function test_add_block_category_integration() {

		$slugs = wp_list_pluck( get_block_categories( $this->factory->post->create_and_get() ), 'slug' );
		$this->assertTrue( in_array( 'llms-blocks', $slugs, true ) );

	}

	/**
	 * Test the admin_print_scripts() method.
	 *
	 * @since 1.5.1
	 * @since 2.0.0 Force using block editor.
	 *
	 * @return void
	 */
	public function test_admin_print_scripts() {

		$obj = new LLMS_Blocks();

		// no current screen.
		$this->assertOutputEmpty( array( $obj, 'admin_print_scripts' ) );

		// Don't Display.
		foreach ( array( 'dashboard', 'options-general', 'tools', 'users-new', 'plugin-editor', 'edit-page' ) as $screen ) {
			set_current_screen( $screen );
			$this->assertOutputEmpty( array( $obj, 'admin_print_scripts' ) );
		}

		// Make sure we use the block editor.
		add_filter( 'use_block_editor_for_post', '__return_true' );
		add_filter( 'use_block_editor_for_post_type', '__return_true' );

		// Display
		foreach ( array( 'post', 'page', 'course', 'llms_membership' ) as $screen ) {
			set_current_screen( $screen );
			// This is where "is_block_editor" is actually set.
			get_current_screen()->is_block_editor(true);
			$this->assertOutputContains( '<script>window.llms.dynamic_blocks', array( $obj, 'admin_print_scripts' ) );
		}

		remove_filter( 'use_block_editor_for_post', '__return_true' );
		remove_filter( 'use_block_editor_for_post_type', '__return_true' );

	}

	/**
	 * Test the get_dynamic_block_names() method.
	 *
	 * @since 1.5.1
	 * @since 1.10.0 Replace core/search with core/shortcode because search wasn't available in 5.1.
	 *
	 * @return void
	 */
	public function test_get_dynamic_block_names() {

		$obj = new LLMS_Blocks();

		$res = LLMS_Unit_Test_Util::call_method( $obj, 'get_dynamic_block_names' );

		$this->assertTrue( in_array( 'core/archives', $res, true ) );
		$this->assertTrue( in_array( 'core/shortcode', $res, true ) );
		$this->assertFalse( in_array( 'llms/course-information', $res, true ) );
		$this->assertFalse( in_array( 'llms/pricing-table', $res, true ) );

	}

	/**
	 * Test load_textdomain()
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function test_load_textdomain() {

		$main = new LLMS_Blocks();

		$dirs = array(
			WP_LANG_DIR . '/lifterlms', // "Safe" directory.
			WP_LANG_DIR . '/plugins', // Default language directory.
			LLMS_BLOCKS_PLUGIN_DIR . '/i18n', // Plugin language directory.
		);

		foreach ( $dirs as $dir ) {

			// Make sure the initial strings work.
			$this->assertEquals( 'LifterLMS Blocks', __( 'LifterLMS Blocks', 'lifterlms' ) );
			$this->assertEquals( 'Post visibility.', __( 'Post visibility.', 'lifterlms' ) );

			// Load from the "safe" directory.
			$file = LLMS_Unit_Test_Files::copy_asset( 'lifterlms-blocks-en_US.mo', $dir );
			$main->load_textdomain();

			$this->assertEquals( 'BetterLMS Blocks', __( 'LifterLMS Blocks', 'lifterlms' ) );
			$this->assertEquals( 'Item visibility.', __( 'Post visibility.', 'lifterlms' ) );

			// Clean up.
			LLMS_Unit_Test_Files::remove( $file );
			unload_textdomain( 'lifterlms' );

		}

	}

	/**
	 * Test that `LLMS_Blocks->init()` adds the correct block category filter.
	 *
	 * @since 2.2.1
	 *
	 * @return void
	 */
	public function test_add_block_category_filter() {

		global $wp_version;
		$blocks   = new LLMS_Blocks();
		$callback = array( $blocks, 'add_block_category' );

		# LLMS_Blocks->add_block_category() should not be registered as a filter yet.
		$this->assertFalse( has_filter( 'block_categories', $callback ) );
		$this->assertFalse( has_filter( 'block_categories_all', $callback ) );

		# Test WordPress version < 5.8
		$wp_versions = array(
			'5.7-alpha-49644-src',
			'5.7-alpha.1',
			'5.7-beta1-src',
			'5.7-beta1',
			'5.7-beta1-50172-src',
			'5.7-RC1-src',
			'5.7-RC1',
			'5.7-RC1-50425-src',
			'5.7-src',
			'5.7',
			'5.7.1-alpha-50514-src',
			'5.7.1-src',
			'5.7.1',
		);
		foreach ( $wp_versions as $wp_version ) {
			$blocks->init();
			$this->assertNotFalse( has_filter( 'block_categories', $callback ), $wp_version );
			$this->assertFalse( has_filter( 'block_categories_all', $callback ), $wp_version );
			remove_filter( 'block_categories', $callback );
			remove_filter( 'block_categories_all', $callback );
		}

		# Test WordPress version >= 5.8
		$wp_versions = array(
			'5.8-alpha-50427-src',
			'5.8-alpha.1',
			'5.8-beta1-src',
			'5.8-beta1',
			'5.8-beta1-51132-src',
			'5.8-RC1-src',
			'5.8-RC1',
			'5.8-RC1-51270-src',
			'5.8-src',
			'5.8',
			'5.8.1-src',
			'5.8.1',
			'5.10-alpha-8675309-src',
			'5.10-beta1-8675310-src',
			'5.10-beta1-8675310',
			'5.10-RC1-8675311',
			'5.10-src',
			'5.10',
		);
		foreach ( $wp_versions as $wp_version ) {
			$blocks->init();
			$this->assertFalse( has_filter( 'block_categories', $callback ), $wp_version );
			$this->assertNotFalse( has_filter( 'block_categories_all', $callback ), $wp_version );
			remove_filter( 'block_categories', $callback );
			remove_filter( 'block_categories_all', $callback );
		}
	}

}
