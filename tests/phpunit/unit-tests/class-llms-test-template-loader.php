<?php
/**
 * Test LLMS_Template_Loader
 *
 * @package LifterLMS/Tests
 *
 * @group template_loader
 *
 * @since 3.41.1
 */
class LLMS_Test_Template_Loader extends LLMS_UnitTestCase {

	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Template_Loader();

	}

	/**
	 * Callback for testing custom restrictions applied through a filter.
	 *
	 * Used by `test_maybe_restrict_post_content_restricted_by_other()` method.
	 *
	 * @since 3.41.1
	 *
	 * @param array $restrictions Restriction data array from `llms_page_restricted()`.
	 * @param int   $post_id      WP_Post ID.
	 * @return array
	 */
	public function mock_page_restricted( $restrictions, $post_id  ) {

		$restrictions['is_restricted']  = true;
		$restrictions['reason']         = 'mock';
		$restrictions['restriction_id'] = 987;

		return $restrictions;

	}

	/**
	 * Retrieve a WP_Post object for restriction-related tests.
	 *
	 * @since 3.41.1
	 *
	 * @param string $post_type Post type to be created.
	 * @return WP_Post
	 */
	protected function get_post_for_restrictions( $post_type = 'post' ) {
		return $this->factory->post->create_and_get( array(
			'post_type'    => $post_type,
			'post_content' => 'content',
			'post_excerpt' => 'excerpt',
		) );
	}

	/**
	 * Assertion helper for restriction-related tests.
	 *
	 * @since 3.41.1
	 *
	 * @param WP_Post $post    Post object
	 * @param string  $content Expected post_content string.
	 * @param string  $excerpt Expected post_excerpt string.
	 * @return void
	 */
	protected function assertContentEquals( $post, $content = 'content', $excerpt = 'excerpt' ) {

		$this->assertEquals( $content, $post->post_content );
		$this->assertEquals( $excerpt, $post->post_excerpt );

	}

	/**
	 * Test maybe_restrict_post_content(): for a skipped post type.
	 *
	 * @since 3.41.1
	 *
	 * @return void
	 */
	public function test_maybe_restrict_post_content_skipped_post_type() {

		global $wp_query;

		$post = $this->get_post_for_restrictions( 'course' );

		$this->main->maybe_restrict_post_content( $post, $wp_query );

		$this->assertContentEquals( $post );

	}

	/**
	 * Test maybe_restrict_post_content(): for a valid post type that's not restricted
	 *
	 * @since 3.41.1
	 *
	 * @return void
	 */
	public function test_maybe_restrict_post_content_not_restricted() {

		global $wp_query, $post;

		$post = $this->get_post_for_restrictions();

		$this->main->maybe_restrict_post_content( $post, $wp_query );

		$this->assertContentEquals( $post );

	}

	/**
	 * Test maybe_restrict_post_content(): for a post restricted by a membership (when not accessible by the user)
	 *
	 * @since 3.41.1
	 *
	 * @return void
	 */
	public function test_maybe_restrict_post_content_restricted_by_membership_not_accessible() {

		global $wp_query, $post;

		$membership = llms_get_post( $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) ) );

		$membership->set( 'restriction_add_notice', 'yes' );
		$membership->set( 'restriction_notice', 'no access.' );

		$post = $this->get_post_for_restrictions();

		update_post_meta( $post->ID, '_llms_restricted_levels', array( $membership->get( 'id' ) ) );
		update_post_meta( $post->ID, '_llms_is_restricted', 'yes' );

		$this->main->maybe_restrict_post_content( $post, $wp_query );

		$this->assertContentEquals( $post, 'no access.', 'no access.' );

	}

	/**
	 * Test maybe_restrict_post_content(): for a post restricted by a membership that is accessible by the user
	 *
	 * @since 3.41.1
	 *
	 * @return void
	 */
	public function test_maybe_restrict_post_content_restricted_by_membership_is_accessible() {

		global $wp_query, $post;

		$membership = llms_get_post( $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) ) );

		$membership->set( 'restriction_add_notice', 'yes' );
		$membership->set( 'restriction_notice', 'no access.' );

		$post = $this->get_post_for_restrictions();

		update_post_meta( $post->ID, '_llms_restricted_levels', array( $membership->get( 'id' ) ) );
		update_post_meta( $post->ID, '_llms_is_restricted', 'yes' );

		$student = $this->get_mock_student();
		$student->enroll( $membership->get( 'id' ) );
		wp_set_current_user( $student->get( 'id' ) );

		$this->main->maybe_restrict_post_content( $post, $wp_query );

		$this->assertContentEquals( $post );



	}

	/**
	 * Test maybe_restrict_post_content(): for a custom restriction applied via filter by a 3rd party.
	 *
	 * @since 3.41.1
	 *
	 * @return void
	 */
	public function test_maybe_restrict_post_content_restricted_by_other() {

		add_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10, 2 );

		global $wp_query, $post;

		$post = $this->get_post_for_restrictions();

		$this->main->maybe_restrict_post_content( $post, $wp_query );

		$this->assertContentEquals( $post, 'This content is restricted', 'This content is restricted' );

		remove_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10 );

	}

}
