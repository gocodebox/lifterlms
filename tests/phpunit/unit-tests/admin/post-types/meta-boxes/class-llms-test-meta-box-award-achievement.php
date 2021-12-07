<?php
/**
 * Tests for LifterLMS Award Achievement Meta Box.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_award_achievement
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Meta_Box_Award_Achievement extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Award_Achievement();

	}

	/**
	 * Tear down test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		// Reset current screen.
		llms_tests_reset_current_screen();
	}

	/**
	 * Test the get_screens() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals(
			array( 'llms_my_achievement' ),
			LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )
		);

	}

	/**
	 * Test get fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_fields() {

		// Set-up global post.
		global $post;
		$original_post = $post;
		$post          = $this->factory->post->create_and_get(
			array(
				'post_type' => LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )[0],
			)
		);

		$this->assertEquals(
			array( $this->metabox->prefix . 'achievement_content' ),
			array_column( $this->metabox->get_fields()[0]['fields'], 'id' )
		);

		// Reset global post.
		$post = $original_post;

	}

	/**
	 * Test save achievement content in the post_content.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_achievement_content() {

		// Set-up global post.
		global $post;
		$original_post = $post;
		$post          = $this->factory->post->create_and_get(
			array(
				'post_type' => LLMS_Unit_Test_Util::call_method( $this->metabox, 'get_screens' )[0],
			)
		);

		// Set current user to an admin.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Simulate saving with the achievement_content field set.
		$updates = array(
			$this->metabox->prefix . 'achievement_content' => 'Some content to save',
		);
		$this->mockPostRequest( $this->add_nonce_to_array( $updates ) );

		// Save.
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post->ID ) ) );
		// Refresh post.
		$post = get_post( $post->ID );

		$this->assertEquals( '' , get_post_meta( $post->ID, $this->metabox->prefix . 'achievement_content', true ) );
		$this->assertEquals( 'Some content to save', $post->post_content );

		// Reset global post.
		$post = $original_post;
		// Reset current user.
		wp_set_current_user( 0 );

	}

}
