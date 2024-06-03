<?php
/**
 * Test Admin Notices Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group metaboxes
 *
 * @since 6.0.0
 */
class LLMS_Test_Admin_Meta_Boxes extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/post-types/class.llms.meta.boxes.php';
	}

	/**
	 * Setup the test case.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Meta_Boxes();

	}

	/**
	 * Test maybe_modify_post_thumbnail_html().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_maybe_modify_post_thumbnail_html() {

		$types = array(
			'post'             => false,
			'llms_achievement' => true,
			'llms_certificate' => true,
		);

		foreach ( $types as $post_type => $modified ) {

			$post = $this->factory->post->create( compact( 'post_type' ) );

			// Without image.
			$res = $this->main->maybe_modify_post_thumbnail_html( 'Content', $post, '' );

			if ( $modified ) {
				$this->assertStringContainsString( 'Using the global default.', $res );
				$this->assertStringContainsString( '<img ', $res );
				$this->assertStringContainsString( 'Content', $res );
			} else {
				$this->assertEquals( 'Content', $res );
			}

			// With an image.
			$res = $this->main->maybe_modify_post_thumbnail_html( 'Content', $post, 123 );
			$this->assertEquals( 'Content', $res );

		}

	}

	/**
	 * Test maybe_modify_title_placeholder().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_maybe_modify_title_placeholder() {

		$types = array(
			'post'             => 'Default Placeholder',
			'llms_achievement' => 'Default Placeholder (for internal use only)',
			'llms_certificate' => 'Default Placeholder (for internal use only)',
		);

		foreach ( $types as $post_type => $expect ) {
			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->assertEquals( $expect, $this->main->maybe_modify_title_placeholder( 'Default Placeholder', $post ) );
		}

	}

}
