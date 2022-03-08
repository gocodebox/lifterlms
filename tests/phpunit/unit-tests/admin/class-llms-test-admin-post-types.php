<?php
/**
 * Test LLMS_Admin_Post_Types
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_post_types
 *
 * @since 6.0.0
 */
class LLMS_Test_Admin_Post_Types extends LLMS_Unit_Test_Case {

	/**
	 * Set Up Before Class
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.post-types.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Post_types();

	}

	/**
	 * Test use_block_editor_for_post().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_use_block_editor_for_post() {

		$force_version_2 = function( $ver ) {
			return 2;
		};

		$this->assertTrue( $this->main->use_block_editor_for_post( true, $this->factory->post->create_and_get() ) );

		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$post = $this->factory->post->create_and_get( array( 'post_type' => $post_type, 'post_content' => 'Not a block.' ) );

			// V1 Template.
			$this->assertFalse( $this->main->use_block_editor_for_post( true, $post ) );

			add_filter( 'llms_certificate_template_version', $force_version_2 );
			$this->assertTrue( $this->main->use_block_editor_for_post( true, $post ) );
			remove_filter( 'llms_certificate_template_version', $force_version_2 );

		}

	}

}
