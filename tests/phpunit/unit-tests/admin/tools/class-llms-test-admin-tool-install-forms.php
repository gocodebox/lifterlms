<?php
/**
 * Tests for the LLMS_Admin_Tool_Install_Forms class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 *
 * @since 5.0.0
 */
class LLMS_Test_Admin_Tool_Install_Forms extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include abstract class.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-admin-tool.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/tools/class-llms-admin-tool-install-forms.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Tool_Install_Forms();

	}

	/**
	 * Retrieve a list of core reusable block post ids
	 *
	 * @since 5.0.0
	 *
	 * @return int[]
	 */
	private function get_block_posts() {

		$blocks = new WP_Query( array(
			'post_type'    => 'wp_block',
			'meta_key'     => '_llms_field_id',
			'meta_compare' => 'EXISTS',
		) );
		return wp_list_pluck( $blocks->posts, 'ID' );

	}

	/**
	 * Retrieve a list of LLMS Form post objects
	 *
	 * @since 5.0.0
	 *
	 * @return WP_Post[]
	 */
	private function get_form_posts() {

		$forms = new WP_Query( array( 'post_type' => 'llms_form' ) );
		return $forms->posts;

	}

	/**
	 * Test get_description()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_description() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_description' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_label()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_label() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_label' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_text()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_text() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_text' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_reusable_blcoks()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_get_reusable_blocks() {

		LLMS_Forms::instance()->install();

		$list = $this->main->get_reusable_blocks();

		foreach ( $list as $id ) {
			$this->assertTrue( is_numeric( $id ) );
			$block = get_post( $id );
			$this->assertEquals( 'wp_block', $block->post_type );
			$this->assertStringContains( '(Reusable)', $block->post_title );
			$this->assertNotEmpty( get_post_meta( $id, '_llms_field_id', true ) );
		}

	}

	/**
	 * Test handle()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_handle() {

		LLMS_Forms::instance()->install();

		foreach ( $this->get_form_posts() as $form ) {
			wp_update_post( array(
				'ID'           => $form->ID,
				'post_content' => 'overwritten',
			) );
		}

		$original_blocks = $this->get_block_posts();

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'handle' ) );

		foreach ( $this->get_form_posts() as $form ) {
			$this->assertNotEquals( 'overwritten', $form->post_content );
		}

		$new_blocks = $this->get_block_posts();
		$this->assertNotEmpty( $new_blocks );
		foreach ( $original_blocks as $id ) {
			$this->assertFalse( in_array( $id, $new_blocks, true ) );
		}

	}

}
