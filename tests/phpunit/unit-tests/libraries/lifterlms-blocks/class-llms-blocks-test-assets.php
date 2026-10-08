<?php
/**
 * Test assets
 *
 * @package LifterLMS_Blocks/Tests
 *
 * @group assets
 *
 * @since 1.10.0
 */
class LLMS_Blocks_Test_Assets extends LLMS_Blocks_Unit_Test_Case {

	/**
	 * Deregister assets registered during tests.
	 *
	 * @since 1.10.0
	 * @since 2.0.0 Add backwards compat script.
	 *
	 * @return void
	 */
	private function deregister_assets() {

		wp_deregister_script( 'llms-blocks-editor' );
		wp_deregister_script( 'llms-blocks-editor-bc' );
		wp_deregister_style( 'llms-blocks-editor' );

	}

	/**
	 * Test the constructor
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function test_constructor() {

		// Other tests in the suite may have already registered the assets.
		$this->deregister_assets();

		$main = new LLMS_Blocks_Assets();

		$this->assertTrue( $main->assets instanceof LLMS_Assets );

		// Both scripts are defined.
		$this->assertTrue( $main->assets->register_script( 'llms-blocks-editor' ) );
		$this->assertTrue( $main->assets->register_style( 'llms-blocks-editor' ) );

		$this->assertEquals( 5, has_action( 'enqueue_block_editor_assets', array( $main, 'editor_assets' ) ) );

		$this->deregister_assets();

	}

	/**
	 * Test editor_assets() on the widgets screen where they shouldn't be loaded.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function test_editor_assets_widgets_screen() {

		$main = new LLMS_Blocks_Assets();
		$this->deregister_assets();

		$this->assertAssetNotEnqueued( 'script', 'llms-blocks-editor' );
		$this->assertAssetNotEnqueued( 'style', 'llms-blocks-editor' );

		set_current_screen( 'widgets' );
		$main->editor_assets();

		$this->assertAssetNotEnqueued( 'script', 'llms-blocks-editor' );
		$this->assertAssetNotEnqueued( 'style', 'llms-blocks-editor' );

		set_current_screen( 'front' );

	}

	/**
	 * Test editor_assets()
	 *
	 * @since 1.10.0
	 * @since 2.2.0 Set current screen.
	 *
	 * @return void
	 */
	public function test_editor_assets() {

		$main = new LLMS_Blocks_Assets();
		$this->deregister_assets();

		$this->assertAssetNotEnqueued( 'script', 'llms-blocks-editor' );
		$this->assertAssetNotEnqueued( 'style', 'llms-blocks-editor' );

		set_current_screen( 'post' );
		$main->editor_assets();

		$this->assertAssetIsEnqueued( 'script', 'llms-blocks-editor' );
		$this->assertAssetIsEnqueued( 'style', 'llms-blocks-editor' );

		set_current_screen( 'front' );

	}

	/**
	 * Test backwards compat asset define and enqueue
	 *
	 * @since 2.0.0
	 * @since 2.2.0 Set current screen.
	 *
	 * @return void
	 */
	public function test_backwards_compat_assets() {

		set_current_screen( 'post' );

		wp_dequeue_script( 'llms-blocks-editor-bc' );

		global $wp_version;
		$temp = $wp_version;

		// Asset not defined during setup.
		$wp_version = '5.7.0';
		$main = new LLMS_Blocks_Assets();
		$this->assertFalse( array_key_exists( 'llms-blocks-editor-bc', LLMS_Unit_Test_Util::call_method( $main->assets, 'get_definitions', array( 'script' ) ) ) );

		$main->editor_assets();
		$this->assertAssetNotEnqueued( 'script', 'llms-blocks-editor-bc' );

		// Asset is defined during setup.
		$wp_version = '5.6.4';
		$main = new LLMS_Blocks_Assets();
		$this->assertTrue( array_key_exists( 'llms-blocks-editor-bc', LLMS_Unit_Test_Util::call_method( $main->assets, 'get_definitions', array( 'script' ) ) ) );

		$main->editor_assets();
		$this->assertAssetIsEnqueued( 'script', 'llms-blocks-editor-bc' );

		$wp_version = $temp;

		set_current_screen( 'front' );

	}

}
