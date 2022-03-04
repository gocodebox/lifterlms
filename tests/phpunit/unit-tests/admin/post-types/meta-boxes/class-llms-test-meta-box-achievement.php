<?php
/**
 * Tests for LifterLMS Achievement Metabox.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_achievement
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Achievement extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Achievement();

	}

	/**
	 * Test the get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals( array( 'llms_achievement', 'llms_my_achievement' ), LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' ) );

	}

	/**
	 * Test get fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields_template() {

		$this->metabox->post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_achievement' ) );

		$this->assertEqualSets(
			array(
				'_llms_achievement_title',
				'_llms_achievement_content',
			),
			array_column(
				$this->metabox->get_fields()[0]['fields'],
				'id'
			)
		);

	}

	/**
	 * Test get fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields_award() {

		$this->metabox->post = $this->factory->post->create_and_get( array( 'post_type' => 'llms_my_achievement' ) );

		$this->assertEqualSets(
			array(
				'_llms_achievement_content',
			),
			array_column(
				$this->metabox->get_fields()[0]['fields'],
				'id'
			)
		);

	}

	/**
	 * Test save achievement content in the post_content.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_field_db() {

		// Set-up global post.
		global $post;
		$original_post = $post;

		// Set current user to an admin.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' ) as $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );

			$this->metabox->post = $post;

			$content = 'Some content to save';

			// Simulate saving with the achievement_content field set.
			$updates = array(
				$this->metabox->prefix . 'achievement_content' => $content,
			);
			$this->mockPostRequest( $this->add_nonce_to_array( $updates ) );

			// Save.
			$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post->ID ) ) );
			// Refresh post.
			$post = get_post( $post->ID );

			// Skip backwards compat function so we can ensure the postmeta is truly not saved.
			remove_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );
			$this->assertEquals( '', get_post_meta( $post->ID, $this->metabox->prefix . 'achievement_content', true ) );
			add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

			$this->assertEquals( $content, $post->post_content );

		}

		// Reset global post.
		$post = $original_post;
		// Reset current user.
		wp_set_current_user( 0 );

	}

}
