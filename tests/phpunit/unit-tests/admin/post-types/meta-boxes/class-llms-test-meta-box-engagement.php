<?php
/**
 * Tests for LifterLMS Engagement Metabox.
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_engagement
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since [version]
 */
class LLMS_Test_Meta_Box_Engagement extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Engagement();

	}

	/**
	 * Assert that saving a "certificate earned" trigger stores `any` as the trigger post.
	 *
	 * A certificate is generated for each student when it is earned, so the trigger cannot be
	 * scoped to a fixed post. Storing an empty string here prevents the engagement from ever
	 * being matched by `LLMS_Engagements::get_engagements()`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_trigger_post_certificate_earned() {

		// Set-up global post.
		global $post;
		$original_post = $post;

		// Set current user to an admin.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$post                = $this->factory->post->create_and_get( array( 'post_type' => 'llms_engagement' ) );
		$this->metabox->post = $post;

		$updates = array(
			$this->metabox->prefix . 'trigger_type' => 'certificate_earned',
		);
		$this->mockPostRequest( $this->add_nonce_to_array( $updates ) );

		LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post->ID ) );

		$this->assertEquals( 'any', get_post_meta( $post->ID, $this->metabox->prefix . 'engagement_trigger_post', true ) );

		// Reset global post.
		$post = $original_post;
		// Reset current user.
		wp_set_current_user( 0 );

	}

	/**
	 * Assert that an unknown (third-party) trigger without a filter still stores an empty string.
	 *
	 * Preserves the historical behavior for triggers which don't register a trigger-post field.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_trigger_post_unknown_trigger() {

		// Set-up global post.
		global $post;
		$original_post = $post;

		// Set current user to an admin.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$post                = $this->factory->post->create_and_get( array( 'post_type' => 'llms_engagement' ) );
		$this->metabox->post = $post;

		$updates = array(
			$this->metabox->prefix . 'trigger_type' => 'mock_trigger',
		);
		$this->mockPostRequest( $this->add_nonce_to_array( $updates ) );

		LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post->ID ) );

		$this->assertEquals( '', get_post_meta( $post->ID, $this->metabox->prefix . 'engagement_trigger_post', true ) );

		// Reset global post.
		$post = $original_post;
		// Reset current user.
		wp_set_current_user( 0 );

	}

	/**
	 * Assert that a trigger scoped to a post type with an empty picker stores `any`.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_trigger_post_empty_picker_stores_any() {

		// Set-up global post.
		global $post;
		$original_post = $post;

		// Set current user to an admin.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$post                = $this->factory->post->create_and_get( array( 'post_type' => 'llms_engagement' ) );
		$this->metabox->post = $post;

		$updates = array(
			$this->metabox->prefix . 'trigger_type' => 'course_completed',
		);
		$this->mockPostRequest( $this->add_nonce_to_array( $updates ) );

		LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post->ID ) );

		$this->assertEquals( 'any', get_post_meta( $post->ID, $this->metabox->prefix . 'engagement_trigger_post', true ) );

		// Reset global post.
		$post = $original_post;
		// Reset current user.
		wp_set_current_user( 0 );

	}

}
