<?php
/**
 * Tests for LifterLMS Order Metabox
 *
 * @package LifterLMS/Tests
 *
 * @group metabox_access
 * @group admin
 * @group metaboxes
 * @group metaboxes_post_type
 *
 * @since 3.36.1
 * @version 3.36.1
 */
class LLMS_Test_Meta_Box_Access extends LLMS_PostTypeMetaboxTestCase {

	/**
	 * Setup test
	 *
	 * @since 3.36.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->metabox = new LLMS_Meta_Box_Access();

	}

	/**
	 * Test the get_screens() method.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_get_screens() {

		$this->assertEquals( array( 'post', 'page' ), $this->metabox->get_screens() );

	}

	/**
	 * Save with no user should fail.
	 *
	 * @since 3.36.1
	 *
	 * @return [type]
	 */
	public function test_save_no_user() {

		$post = $this->factory->post->create();

		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );

	}

	/**
	 * Save with no nonce should fail.
	 *
	 * @since 3.36.1
	 *
	 * @return [type]
	 */
	public function test_save_no_nonce() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$post = $this->factory->post->create();
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );

	}

	/**
	 * Save with invalid nonce will fail.
	 *
	 * @since 3.36.1
	 *
	 * @return [type]
	 */
	public function test_save_invalid_nonce() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$post = $this->factory->post->create();
		$this->mockPostRequest( $this->add_nonce_to_array( array(), false ) );
		$this->assertEquals( -1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );

	}

	/**
	 * Test save method.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_save() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$post = $this->factory->post->create();
		$post_data = $this->add_nonce_to_array( array() );

		// Nothing saved, value is reset.
		$this->mockPostRequest( $post_data );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );
		$this->assertEquals( '', get_post_meta( $post, '_llms_is_restricted', true ) );

		// Toggle restrictions on.
		$post_data['_llms_is_restricted'] = 'yes';
		$this->mockPostRequest( $post_data );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );
		$this->assertEquals( 'yes', get_post_meta( $post, '_llms_is_restricted', true ) );

		// Restrict to a single membership.
		$post_data['_llms_restricted_levels'] = array( 1 );
		$this->mockPostRequest( $post_data );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );
		$this->assertEquals( 'yes', get_post_meta( $post, '_llms_is_restricted', true ) );
		$this->assertEquals( array( 1 ), get_post_meta( $post, '_llms_restricted_levels', true ) );

		// Multiple memberships.
		$post_data['_llms_restricted_levels'] = array( 2, 3 );
		$this->mockPostRequest( $post_data );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );
		$this->assertEquals( 'yes', get_post_meta( $post, '_llms_is_restricted', true ) );
		$this->assertEquals( array( 2, 3 ), get_post_meta( $post, '_llms_restricted_levels', true ) );

		// Disable restrictions.
		unset( $post_data['_llms_is_restricted'] );
		$this->mockPostRequest( $post_data );
		$this->assertEquals( 1, LLMS_Unit_Test_Util::call_method( $this->metabox, 'save', array( $post ) ) );
		$this->assertEquals( '', get_post_meta( $post, '_llms_is_restricted', true ) );

	}

}
