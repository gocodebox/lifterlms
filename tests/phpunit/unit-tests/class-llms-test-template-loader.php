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

	/**
	 * Mock restriction id when calling `mock_page_restricted()`.
	 *
	 * @var integer
	 */
	private $mock_page_restricted_id = 987;

	/**
	 * Setup test case.
	 *
	 * @since 3.41.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Template_Loader();

	}

	/**
	 * Callback for testing custom restrictions applied through a filter.
	 *
	 * @since 3.41.1
	 * @since 4.10.1 Use `$this->mock_page_restricted_id` for the restriction_id to allow easy customization of the mocked data.
	 *
	 * @param array $restrictions Restriction data array from `llms_page_restricted()`.
	 * @param int   $post_id      WP_Post ID.
	 * @return array
	 */
	public function mock_page_restricted( $restrictions, $post_id  ) {

		$restrictions['is_restricted']  = true;
		$restrictions['reason']         = 'mock';
		$restrictions['restriction_id'] = $this->mock_page_restricted_id;

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

	/**
	 * Test template_loader() with a screen we don't care about modifying
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_default() {

		$this->assertEquals( '/html/wp-content/theme/atheme/mock.php', $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) );

	}

	/**
	 * Test template_loader() on the blog (home) page.
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_is_home() {

		// Mock `llms_page_restricted()` to have a sitewide membership restriction.
		$handler = function( $results ) {
			$results['reason'] = 'sitewide_membership';
			$results['is_restricted'] = true;
			return $results;
		};

		// Mock `is_home()` so it looks like we're on the blog post page.
		global $wp_query;
		$temp = $wp_query->is_home;
		$wp_query->is_home = true;

		// No restrictions.
		$this->assertEquals( '/html/wp-content/theme/atheme/mock.php', $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) );
		$this->assertSame( 0, did_action( 'lifterlms_content_restricted' ) );
		$this->assertSame( 0, did_action( 'llms_content_restricted_by_sitewide_membership' ) );
		$this->assertFalse( has_action( 'loop_start', 'llms_print_notices' ) );

		// Has restrictions.
		add_filter( 'llms_page_restricted', $handler );
		$this->assertEquals( '/html/wp-content/theme/atheme/mock.php', $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) );
		$this->assertSame( 1, did_action( 'lifterlms_content_restricted' ) );
		$this->assertSame( 1, did_action( 'llms_content_restricted_by_sitewide_membership' ) );
		$this->assertEquals( 5, has_action( 'loop_start', 'llms_print_notices' ) );

		// Reset.
		$wp_query->is_home = $temp;
		remove_filter( 'llms_page_restricted', $handler );

	}

	/**
	 * Test template_loader() for restricted pages.
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_page_is_restricted() {

		add_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10, 2 );

		// Modify the template & fire actions.
		$this->assertEquals( 'single-no-access.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );
		$this->assertSame( 1, did_action( 'lifterlms_content_restricted' ) );
		$this->assertSame( 1, did_action( 'llms_content_restricted_by_mock' ) );

		// Courses and memberships return the original template (but still fire actions).
		global $post;
		foreach ( array( 'course', 'llms_membership' ) as $i => $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->mock_page_restricted_id = $post->ID;

			$this->assertEquals( '/html/wp-content/theme/atheme/mock.php', $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) );
			$this->assertSame( $i + 2, did_action( 'lifterlms_content_restricted' ) );
			$this->assertSame( $i + 2, did_action( 'llms_content_restricted_by_mock' ) );

		}

		remove_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10 );

	}

	/**
	 * Test template_loader() with the course catalog.
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_courses() {

		// Post type archive.
		$this->go_to( get_post_type_archive_link( 'course' ) );
		$this->assertEquals( 'archive-course.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );

		// Check the course catalog page.
		LLMS_Install::create_pages();
		$this->go_to( get_permalink( llms_get_page_id( 'courses' ) ) );
		$this->assertEquals( 'archive-course.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );

	}

	/**
	 * Test template_loader() with the membership catalog.
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_memberships() {

		// Post type archive.
		$this->go_to( get_post_type_archive_link( 'llms_membership' ) );
		$this->assertEquals( 'archive-llms_membership.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );

		// Check the membership catalog page.
		LLMS_Install::create_pages();
		$this->go_to( get_permalink( llms_get_page_id( 'memberships' ) ) );
		$this->assertEquals( 'archive-llms_membership.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );

	}

	/**
	 * Test template_loader() on custom taxonomy archives.
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_for_taxonomies() {

		foreach ( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) as $tax ) {

			$term = wp_create_term( 'mock-' . $tax, $tax );
			$this->go_to( get_term_link( $term['term_id'] ) );
			// $this->assertTrue( is_course_taxonomy() );
			$this->assertEquals( sprintf( 'taxonomy-%s.php', $tax ), basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );

		}

	}

	/**
	 * Test template_loader() on certificate pages.
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_certificates() {

		global $post, $wp_query;
		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$wp_query->queried_object = $post;
			$wp_query->is_singular    = true;
			$this->assertEquals( 'single-certificate.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ), $post_type );

		}

	}

	/**
	 * Test template_loader() with a default unrestricted post type
	 *
	 * @since 4.10.1
	 *
	 * @return void
	 */
	public function test_template_loader_default_post_type() {

		global $post;
		$post = $this->factory->post->create_and_get();

		// Not touched.
		$this->assertEquals( '/html/wp-content/theme/atheme/mock.php', $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) );
		$this->assertSame( 0, did_action( 'lifterlms_content_restricted' ) );
		$this->assertSame( 0, did_action( 'llms_content_restricted_by_sitewide_membership' ) );
		$this->assertFalse( has_action( 'loop_start', 'llms_print_notices' ) );

	}

}
