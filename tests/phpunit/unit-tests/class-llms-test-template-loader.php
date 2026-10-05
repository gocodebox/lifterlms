<?php
/**
 * Test LLMS_Template_Loader
 *
 * @package LifterLMS/Tests
 *
 * @group template_loader
 *
 * @since 3.41.1
 * @since 6.0.0 Added tests for the block loader.
 * @since 6.4.0 Updated tests on single restricted content template loading.
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
	 * Test maybe_prepare_feed_content_restriction(): filters are only attached on feed requests.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_maybe_prepare_feed_content_restriction() {

		global $wp_query;

		$wp_query->is_feed = false;
		$this->main->maybe_prepare_feed_content_restriction();
		$this->assertFalse( has_filter( 'the_content_feed', array( $this->main, 'maybe_restrict_feed_content' ) ) );
		$this->assertFalse( has_filter( 'the_excerpt_rss', array( $this->main, 'maybe_restrict_feed_content' ) ) );

		$wp_query->is_feed = true;
		$this->main->maybe_prepare_feed_content_restriction();
		$this->assertEquals( 9999, has_filter( 'the_content_feed', array( $this->main, 'maybe_restrict_feed_content' ) ) );
		$this->assertEquals( 9999, has_filter( 'the_excerpt_rss', array( $this->main, 'maybe_restrict_feed_content' ) ) );

		$wp_query->is_feed = false;
		remove_filter( 'the_content_feed', array( $this->main, 'maybe_restrict_feed_content' ), 9999 );
		remove_filter( 'the_excerpt_rss', array( $this->main, 'maybe_restrict_feed_content' ), 9999 );

	}

	/**
	 * Test maybe_restrict_feed_content(): lessons and quizzes are never output in feeds, regardless of the user.
	 *
	 * Feeds may be cached without a per-user key, so a free lesson, an enrolled student, and an
	 * administrator all get the same empty output.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_lesson_and_quiz() {

		global $post, $wp_query;

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$lesson    = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'lesson',
				'post_content' => 'UNIQUE_LESSON_BODY',
			)
		);
		update_post_meta( $lesson->ID, '_llms_parent_course', $course_id );

		$wp_query->is_singular = false;
		$wp_query->is_search   = true;

		$this->add_feed_restriction_filters();

		// Anonymous, paid lesson.
		$post = $lesson;
		$this->assertSame( '', apply_filters( 'the_content_feed', 'UNIQUE_LESSON_BODY', 'rss2' ) );
		$this->assertSame( '', apply_filters( 'the_excerpt_rss', 'UNIQUE_LESSON_BODY' ) );
		$this->assertTrue( $wp_query->is_search );
		$this->assertFalse( $wp_query->is_singular );

		// Free lesson.
		update_post_meta( $lesson->ID, '_llms_free_lesson', 'yes' );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'UNIQUE_LESSON_BODY', 'rss2' ) );
		update_post_meta( $lesson->ID, '_llms_free_lesson', 'no' );

		// Enrolled student.
		$student = $this->get_mock_student( true );
		$student->enroll( $course_id );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'UNIQUE_LESSON_BODY', 'rss2' ) );

		// Administrator.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'UNIQUE_LESSON_BODY', 'rss2' ) );

		// Quiz.
		$post = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'llms_quiz',
				'post_content' => 'UNIQUE_QUIZ_BODY',
			)
		);
		$this->assertSame( '', apply_filters( 'the_content_feed', 'UNIQUE_QUIZ_BODY', 'rss2' ) );

		wp_set_current_user( 0 );
		$this->remove_feed_restriction_filters();

	}

	/**
	 * Test maybe_restrict_feed_content(): course feeds output the public sales description for every viewer.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_course_sales_content() {

		global $post, $wp_query;

		$post = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'course',
				'post_content' => 'UNIQUE_COURSE_BODY',
				'post_excerpt' => 'PUBLIC_EXCERPT',
			)
		);

		$wp_query->is_singular = false;
		$wp_query->is_search   = false;
		setup_postdata( $post );

		$this->add_feed_restriction_filters();

		// Anonymous: sales excerpt replaces the body, no enrollment templates.
		$content = apply_filters( 'the_content_feed', 'UNIQUE_COURSE_BODY', 'rss2' );
		$this->assertStringNotContainsString( 'UNIQUE_COURSE_BODY', $content );
		$this->assertStringContainsString( 'PUBLIC_EXCERPT', $content );
		$this->assertStringNotContainsString( 'llms-', $content );
		$this->assertSame( 'PUBLIC_EXCERPT', apply_filters( 'the_excerpt_rss', 'PUBLIC_EXCERPT' ) );

		// Enrolled student gets the same output.
		$student = $this->get_mock_student( true );
		$student->enroll( $post->ID );
		$this->assertSame( $content, apply_filters( 'the_content_feed', 'UNIQUE_COURSE_BODY', 'rss2' ) );
		wp_set_current_user( 0 );

		// No excerpt: the course description itself is public, minus dynamic blocks and shortcodes.
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_excerpt' => '',
				'post_content' => '<!-- wp:paragraph --><p>UNIQUE_COURSE_BODY</p><!-- /wp:paragraph --><!-- wp:llms/pricing-table /-->[lifterlms_pricing_table]',
			)
		);
		$post = get_post( $post->ID );
		setup_postdata( $post );
		$described = apply_filters( 'the_content_feed', 'UNIQUE_COURSE_BODY', 'rss2' );
		$this->assertStringContainsString( 'UNIQUE_COURSE_BODY', $described );
		$this->assertStringNotContainsString( 'llms-', $described );
		$this->assertStringNotContainsString( 'lifterlms_pricing_table', $described );

		// Generated excerpt comes from the same static rendering.
		$generated = apply_filters( 'the_excerpt_rss', 'Course Information Enroll Now UNIQUE_COURSE_BODY' );
		$this->assertStringContainsString( 'UNIQUE_COURSE_BODY', $generated );
		$this->assertStringNotContainsString( 'Enroll Now', $generated );

		// Sales page redirect: no public body.
		update_post_meta( $post->ID, '_llms_sales_page_content_type', 'url' );
		update_post_meta( $post->ID, '_llms_sales_page_content_url', 'https://example.com/sales' );

		$redirected = apply_filters( 'the_content_feed', 'UNIQUE_COURSE_BODY', 'rss2' );
		$this->assertStringNotContainsString( 'UNIQUE_COURSE_BODY', $redirected );
		$this->assertSame( 'This content is restricted', $redirected );
		$this->assertSame( 'This content is restricted', apply_filters( 'the_excerpt_rss', 'UNIQUE_COURSE_BODY' ) );

		wp_reset_postdata();
		$this->remove_feed_restriction_filters();

	}

	/**
	 * Test maybe_restrict_feed_content(): certificates are never output in feeds, regardless of the user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_certificate() {

		global $post;

		$author = $this->factory->user->create();
		$admin  = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$post   = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'llms_my_certificate',
				'post_author'  => $author,
				'post_content' => 'SECRET_CERT_BODY',
			)
		);

		$this->add_feed_restriction_filters();

		wp_set_current_user( 0 );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'SECRET_CERT_BODY', 'rss2' ) );
		$this->assertSame( '', apply_filters( 'the_excerpt_rss', 'SECRET_CERT_BODY' ) );

		$certificate = new LLMS_User_Certificate( $post->ID );
		$certificate->set( 'allow_sharing', 'yes' );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'SECRET_CERT_BODY', 'rss2' ) );

		wp_set_current_user( $author );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'SECRET_CERT_BODY', 'rss2' ) );

		wp_set_current_user( $admin );
		$this->assertSame( '', apply_filters( 'the_content_feed', 'SECRET_CERT_BODY', 'rss2' ) );

		$post = $this->factory->post->create_and_get(
			array(
				'post_type'    => 'llms_certificate',
				'post_content' => 'TEMPLATE_BODY',
			)
		);
		$this->assertSame( '', apply_filters( 'the_content_feed', 'TEMPLATE_BODY', 'rss2' ) );

		wp_set_current_user( 0 );
		$this->remove_feed_restriction_filters();

	}

	/**
	 * Attach feed restriction callbacks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function add_feed_restriction_filters() {

		add_filter( 'the_content_feed', array( $this->main, 'maybe_restrict_feed_content' ), 9999 );
		add_filter( 'the_excerpt_rss', array( $this->main, 'maybe_restrict_feed_content' ), 9999 );

	}

	/**
	 * Remove feed restriction callbacks.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function remove_feed_restriction_filters() {

		remove_filter( 'the_content_feed', array( $this->main, 'maybe_restrict_feed_content' ), 9999 );
		remove_filter( 'the_excerpt_rss', array( $this->main, 'maybe_restrict_feed_content' ), 9999 );

	}

	/**
	 * Test maybe_restrict_feed_content(): for a valid post type that's not restricted.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_not_restricted() {

		global $post;

		$post = $this->get_post_for_restrictions();

		$this->assertEquals( 'content', $this->main->maybe_restrict_feed_content( 'content' ) );

	}

	/**
	 * Test maybe_restrict_feed_content(): for a post restricted by a membership (when not accessible by the user).
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_restricted_by_membership_not_accessible() {

		global $post;

		$membership = llms_get_post( $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) ) );

		$membership->set( 'restriction_add_notice', 'yes' );
		$membership->set( 'restriction_notice', 'no access.' );

		$post = $this->get_post_for_restrictions();

		update_post_meta( $post->ID, '_llms_restricted_levels', array( $membership->get( 'id' ) ) );
		update_post_meta( $post->ID, '_llms_is_restricted', 'yes' );

		$this->assertEquals( 'no access.', $this->main->maybe_restrict_feed_content( 'content' ) );

	}

	/**
	 * Test maybe_restrict_feed_content(): a membership-restricted post stays restricted for a member and an admin.
	 *
	 * Feeds may be cached without a per-user key, so a request from a user with access must not
	 * expose the body.
	 *
	 * @since 10.1.0
	 * @since [version] Members and administrators get the restriction notice too.
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_restricted_by_membership_is_accessible() {

		global $post;

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

		$this->assertEquals( 'no access.', $this->main->maybe_restrict_feed_content( 'content' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertEquals( 'no access.', $this->main->maybe_restrict_feed_content( 'content' ) );

		wp_set_current_user( 0 );

	}

	/**
	 * Test maybe_restrict_feed_content(): sitewide membership restriction applies to every viewer.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_sitewide_membership() {

		global $post;

		$membership = llms_get_post( $this->factory->post->create( array(
			'post_type' => 'llms_membership',
		) ) );
		update_option( 'lifterlms_membership_required', $membership->get( 'id' ) );

		$post = $this->get_post_for_restrictions();

		$this->assertEquals( 'This content is restricted', $this->main->maybe_restrict_feed_content( 'content' ) );

		$student = $this->get_mock_student( true );
		$student->enroll( $membership->get( 'id' ) );
		$this->assertEquals( 'This content is restricted', $this->main->maybe_restrict_feed_content( 'content' ) );

		wp_set_current_user( 0 );
		delete_option( 'lifterlms_membership_required' );

	}

	/**
	 * Test maybe_restrict_feed_content(): for a custom restriction applied via filter by a 3rd party.
	 *
	 * @since 10.1.0
	 *
	 * @return void
	 */
	public function test_maybe_restrict_feed_content_restricted_by_other() {

		add_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10, 2 );

		global $post;

		$post = $this->get_post_for_restrictions();

		$this->assertEquals( 'This content is restricted', $this->main->maybe_restrict_feed_content( 'content' ) );

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
	 * @since 6.4.0 Updated to reflect changes in the `template_loader()` method for single restricted content template.
	 *
	 * @return void
	 */
	public function test_template_loader_page_is_restricted() {

		add_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10, 2 );

		// Modify the template & fire actions.
		// `template_loader()` returns the original template for single restricted content.
		$this->assertEquals( 'mock.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ) );
		// And defers the single restricted content template redirect at `template_include|100`.
		$this->assertSame( 100, has_filter( 'template_include', array( $this->main, 'maybe_force_php_template' ) ) );
		// `maybe_force_php_template()` returns the single restricted content template.
		$this->assertEquals( 'single-no-access.php', basename( $this->main->maybe_force_php_template( '/html/wp-content/theme/atheme/mock.php' ) ) );
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
	 * Test block_template_loader() for restricted pages.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_block_template_loader_page_is_restricted() {

		! function_exists( 'wp_is_block_theme' ) && $this->markTestSkipped( 'FSE not available.' );

		add_filter( 'llms_is_block_theme', '__return_true', 11 );

		add_filter( 'llms_page_restricted', array( $this, 'mock_page_restricted' ), 10, 2 );

		// Modify the template & fire actions.
		// Check we're going to load the expected block template.
		$this->assertEquals(
			llms()->block_templates()->add_llms_block_templates(
				array(),
				array( 'slug__in' => array( LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . 'single-no-access' ) )
			),
			$this->main->block_template_loader( array(), array(), 'wp_template' )
		);
		// Check we're going to prevent the loading of the PHP template.
		$this->assertTrue( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
		$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ) );
		remove_filter( 'llms_force_php_template_loading', '__return_false' );

		$this->assertSame( 1, did_action( 'lifterlms_content_restricted' ) );
		$this->assertSame( 1, did_action( 'llms_content_restricted_by_mock' ) );

		// Courses and memberships return the original template (but still fire actions).
		global $post;
		foreach ( array( 'course', 'llms_membership' ) as $i => $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->mock_page_restricted_id = $post->ID;

			// Check we're not to going a specific lifterlms block template.
			$this->assertEquals(
				array(),
				$this->main->block_template_loader( array(), array(), 'wp_template' ),
				$post_type
			);

			// Check we're not going to prevent the loading of the PHP template.
			$this->assertFalse( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ), $post_type );
			// But still the default template is loaded by the template loader.
			$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ), $post_type );

			$this->assertSame( $i + 2, did_action( 'lifterlms_content_restricted' ), $post_type );
			$this->assertSame( $i + 2, did_action( 'llms_content_restricted_by_mock' ), $post_type );

		}

		remove_filter( 'llms_is_block_theme', '__return_true', 11 );

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
	 * Test block_template_loader() on courses archives with a block theme.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_block_template_loader_for_courses() {

		! function_exists( 'wp_is_block_theme' ) && $this->markTestSkipped( 'FSE not available.' );

		add_filter( 'llms_is_block_theme', '__return_true', 11 );

		// Post type archive.
		$this->go_to( get_post_type_archive_link( 'course' ) );
		// Check we're going to load the expected block template.
		$this->assertEquals(
			llms()->block_templates()->add_llms_block_templates(
				array(),
				array( 'slug__in' => array( LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . 'archive-course' ) )
			),
			$this->main->block_template_loader( array(), array(), 'wp_template' )
		);
		// Check we're going to prevent the loading of the PHP template.
		$this->assertTrue( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
		$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ) );
		remove_filter( 'llms_force_php_template_loading', '__return_false' );

		// Check the course catalog page.
		LLMS_Install::create_pages();
		$this->go_to( get_permalink( llms_get_page_id( 'courses' ) ) );
		// Check we're going to load the expected block template.
		$this->assertEquals(
			llms()->block_templates()->add_llms_block_templates(
				array(),
				array( 'slug__in' => array( LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . 'archive-course' ) )
			),
			$this->main->block_template_loader( array(), array(), 'wp_template' )
		);
		// Check we're going to prevent the loading of the PHP template.
		$this->assertTrue( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
		$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ) );
		remove_filter( 'llms_force_php_template_loading', '__return_false' );

		remove_filter( 'llms_is_block_theme', '__return_true', 11 );

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
	 * Test block_template_loader() on membership archives with a block theme.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_block_template_loader_for_memberships() {

		! function_exists( 'wp_is_block_theme' ) && $this->markTestSkipped( 'FSE not available.' );

		add_filter( 'llms_is_block_theme', '__return_true', 11 );

		// Post type archive.
		$this->go_to( get_post_type_archive_link( 'llms_membership' ) );
		// Check we're going to load the expected block template.
		$this->assertEquals(
			llms()->block_templates()->add_llms_block_templates(
				array(),
				array( 'slug__in' => array( LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . 'archive-llms_membership' ) )
			),
			$this->main->block_template_loader( array(), array(), 'wp_template' )
		);
		// Check we're going to prevent the loading of the PHP template.
		$this->assertTrue( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
		$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ) );
		remove_filter( 'llms_force_php_template_loading', '__return_false' );

		// Check the membership catalog page.
		LLMS_Install::create_pages();
		$this->go_to( get_permalink( llms_get_page_id( 'memberships' ) ) );
		// Check we're going to load the expected block template.
		$this->assertEquals(
			llms()->block_templates()->add_llms_block_templates(
				array(),
				array( 'slug__in' => array( LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . 'archive-llms_membership' ) )
			),
			$this->main->block_template_loader( array(), array(), 'wp_template' )
		);
		// Check we're going to prevent the loading of the PHP template.
		$this->assertTrue( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
		$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ) );
		remove_filter( 'llms_force_php_template_loading', '__return_false' );

		remove_filter( 'llms_is_block_theme', '__return_true', 11 );

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
	 * Test block_template_loader() on custom taxonomy archives with a block theme.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_block_template_loader_for_taxonomies() {

		! function_exists( 'wp_is_block_theme' ) && $this->markTestSkipped( 'FSE not available.' );

		add_filter( 'llms_is_block_theme', '__return_true', 11 );

		foreach ( array( 'course_cat', 'course_tag', 'course_difficulty', 'course_track', 'membership_tag', 'membership_cat' ) as $tax ) {

			$this->assertFalse( has_filter( 'llms_force_php_template_loading', '__return_false' ) );
			$term = wp_create_term( 'mock-' . $tax, $tax );
			$this->go_to( get_term_link( $term['term_id'] ) );
			// Check we're going to load the expected block template.
			$this->assertEquals(
				llms()->block_templates()->add_llms_block_templates(
					array(),
					array( 'slug__in' => array( LLMS_Block_Templates::LLMS_BLOCK_TEMPLATES_PREFIX . 'taxonomy-' . $tax ) )
				),
				$this->main->block_template_loader( array(), array( 'archive' ), 'wp_template' ),
				$tax
			);
			// Check we're going to prevent the loading of the PHP template.
			$this->assertTrue( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
			$this->assertEquals( 'template-canvas.php', $this->main->template_loader( 'template-canvas.php' ) );
			remove_filter( 'llms_force_php_template_loading', '__return_false' );

		}

		remove_filter( 'llms_is_block_theme', '__return_true', 11 );

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
	 * Test block_template_loader() on certificate pages with block theme.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_block_template_loader_for_certificates() {

		! function_exists( 'wp_is_block_theme' ) && $this->markTestSkipped( 'FSE not available.' );

		add_filter( 'llms_is_block_theme', '__return_true', 11 );

		global $wp_query, $post;

		foreach ( array( 'llms_certificate', 'llms_my_certificate' ) as $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$wp_query->queried_object = $post;
			$wp_query->is_singular    = true;

			$this->assertFalse( has_filter( 'llms_force_php_template_loading', '__return_false' ) );

			// Check we're not going to load a certificate block template, it doesn't exist.
			$this->assertEquals(
				array(),
				$this->main->block_template_loader( array(), array(), 'wp_template' ),
				$post_type
			);

			// Check we're not going to prevent the loading of the PHP template.
			$this->assertFalse( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );
			// The PHP template is loaded instead.
			$this->assertEquals( 'single-certificate.php', basename( $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) ), $post_type );

		}

		remove_filter( 'llms_is_block_theme', '__return_true', 11 );

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

	/**
	 * Test block_template_loader() with a default unrestricted post type.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_block_template_loader_default_post_type() {

		! function_exists( 'wp_is_block_theme' ) && $this->markTestSkipped( 'FSE not available.' );

		add_filter( 'llms_is_block_theme', '__return_true', 11 );

		global $post;
		$post = $this->factory->post->create_and_get();

		// Not touched.

		$this->assertFalse( has_filter( 'llms_force_php_template_loading', '__return_false' ) );

		// Check we're not going to load a certificate block template, it doesn't exist.
		$this->assertEquals(
			array(),
			$this->main->block_template_loader( array(), array(), 'wp_template' )
		);

		// Check we're not going to prevent the loading of the PHP template.
		$this->assertFalse( (bool) has_filter( 'llms_force_php_template_loading', '__return_false' ) );

		$this->assertEquals( '/html/wp-content/theme/atheme/mock.php', $this->main->template_loader( '/html/wp-content/theme/atheme/mock.php' ) );
		$this->assertSame( 0, did_action( 'lifterlms_content_restricted' ) );
		$this->assertSame( 0, did_action( 'llms_content_restricted_by_sitewide_membership' ) );
		$this->assertFalse( has_action( 'loop_start', 'llms_print_notices' ) );

		remove_filter( 'llms_is_block_theme', '__return_true', 11 );

	}

}
