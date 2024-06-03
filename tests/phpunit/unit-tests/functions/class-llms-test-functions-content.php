<?php
/**
 * Tests for LifterLMS User Postmeta functions
 *
 * @package LifterLMS/Tests
 *
 * @group functions
 * @group content_functions
 *
 * @since 3.25.1
 */
class LLMS_Test_Functions_Content extends LLMS_UnitTestCase {

	/**
	 * Helper to retrieve filtered post content for a given post
	 *
	 * @since 4.17.0
	 *
	 * @param WP_Post $post Post object
	 * @return string
	 */
	private function get_post_content( $post ) {
		return trim( apply_filters( 'the_content', $post->post_content ) );
	}

	/**
	 * Retrieve a mock post of a give type with expected content and excerpts.
	 *
	 * @since 4.17.0
	 *
	 * @param WP_Post $post Post object
	 * @return WP_Post
	 */
	private function get_mock_post( $post_type ) {

		global $post;
		$post = $this->factory->post->create_and_get( array(
			'post_type'    => $post_type,
			'post_content' => '<p>Post Content</p>',
			'post_excerpt' => '<p>Post Excerpt</p>',
		) );

		return $post;

	}

	/**
	 * Callback for `llms_page_restricted` filter to force a page to look restricted
	 *
	 * @since 4.17.0
	 *
	 * @param array $restrictions Restriction data array from llms_page_restricted().
	 * @return array
	 */
	public function make_restricted( $restrictions ) {
		$restrictions['is_restricted'] = true;
		return $restrictions;
	}

	/**
	 * Test llms_get_post_content() for various post types
	 *
	 * This test was never a very good one but it's retained as it does ensure WP core post types
	 * are not affected by our functions.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content() {

		llms_post_content_init();

		$content = '<p>Lorem ipsum dolor sit amet.</p>';
		$post_types = array( 'llms_membership', 'course', 'lesson', 'llms_quiz', 'post', 'page' );
		foreach ( $post_types as $post_type ) {

			global $post;
			$post = $this->factory->post->create_and_get( array(
				'post_type'    => $post_type,
				'post_content' => $content,
			) );

			if ( in_array( $post_type, array( 'post', 'page', 'llms_membership' ), true ) ) {
				$this->assertEquals( $content, $this->get_post_content( $post ) );
			} else {
				$this->assertNotEquals( $content, $this->get_post_content( $post ) );
			}

		}

	}

	/**
	 * Test llms_get_post_content() for the course post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_course_restricted_no_sales_page() {

		$before = did_action( 'lifterlms_single_course_before_summary' );
		$after  = did_action( 'lifterlms_single_course_after_summary' );

		llms_post_content_init();
		$post = $this->get_mock_post( 'course' );

		$res = $this->get_post_content( $post );

		// Starts with the default post content.
		$this->assertSame( 0, strpos( $res, '<p>Post Content</p>' ) );

		// Additions added to the end.
		$additions = array(
			'<div class="llms-meta-info">',
			'<section class="llms-instructor-info">',
			'<div class="llms-syllabus-wrapper">',
		);
		foreach ( $additions as $add ) {
			$this->assertStringContains( $add, $res );
		}

		$this->assertEquals( ++$before, did_action( 'lifterlms_single_course_before_summary' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_single_course_after_summary' ) );

	}

	/**
	 * Test llms_get_post_content() for the course post type with restrictions and a salse page.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_course_restricted_with_sales_page() {

		$before = did_action( 'lifterlms_single_course_before_summary' );
		$after  = did_action( 'lifterlms_single_course_after_summary' );

		add_filter( 'llms_page_restricted', array( $this, 'make_restricted' ) );

		llms_post_content_init();
		$post = $this->get_mock_post( 'course' );

		update_post_meta( $post->ID, '_llms_sales_page_content_type', 'content' );

		$res = $this->get_post_content( $post );

		// Starts with the post's excerpt post content.
		$this->assertSame( 0, strpos( $res, '<p>Post Excerpt</p>' ) );

		// Post's content should not be found.
		$this->assertSame( false, strpos( $res, '<p>Post Content</p>' ) );

		// Additions added to the end.
		$additions = array(
			'<div class="llms-meta-info">',
			'<section class="llms-instructor-info">',
			'<div class="llms-syllabus-wrapper">',
		);
		foreach ( $additions as $add ) {
			$this->assertStringContains( $add, $res );
		}

		$this->assertEquals( ++$before, did_action( 'lifterlms_single_course_before_summary' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_single_course_after_summary' ) );

		remove_filter( 'llms_page_restricted', array( $this, 'make_restricted' ) );

	}

	/**
	 * Test llms_get_post_content() for the membership post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_membership_restricted_no_sales_page() {

		$before = did_action( 'lifterlms_single_membership_before_summary' );
		$after  = did_action( 'lifterlms_single_membership_after_summary' );

		llms_post_content_init();
		$post = $this->get_mock_post( 'llms_membership' );

		$res = $this->get_post_content( $post );

		// No additions to the post content.
		$this->assertEquals( '<p>Post Content</p>', $res );

		$this->assertEquals( ++$before, did_action( 'lifterlms_single_membership_before_summary' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_single_membership_after_summary' ) );

	}

	/**
	 * Test llms_get_post_content() for the membership post type with restrictions and a salse page.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_membership_restricted_with_sales_page() {

		$before = did_action( 'lifterlms_single_membership_before_summary' );
		$after  = did_action( 'lifterlms_single_membership_after_summary' );

		$handler = function( $restrictions ) {
			$restrictions['is_restricted'] = true;
			return $restrictions;
		};
		add_filter( 'llms_page_restricted', $handler );

		llms_post_content_init();
		$post = $this->get_mock_post( 'llms_membership' );

		update_post_meta( $post->ID, '_llms_sales_page_content_type', 'content' );

		$res = $this->get_post_content( $post );

		// Just the excerpt.
		$this->assertEquals( '<p>Post Excerpt</p>', $res );

		$this->assertEquals( ++$before, did_action( 'lifterlms_single_membership_before_summary' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_single_membership_after_summary' ) );

		remove_filter( 'llms_page_restricted', $handler );

	}

	/**
	 * Test llms_get_post_content() for the lesson post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_lesson() {

		$before = did_action( 'lifterlms_single_lesson_before_summary' );
		$after  = did_action( 'lifterlms_single_lesson_after_summary' );

		llms_post_content_init();
		$post = $this->get_mock_post( 'lesson' );

		$res = $this->get_post_content( $post );

		// Starts with the back to course link.
		$this->assertSame( 0, strpos( $res, '<p class="llms-parent-course-link">' ) );

		$additions = array(
			'<p>Post Content</p>', // Default content.
			'<nav class="llms-course-navigation">',
		);
		foreach ( $additions as $add ) {
			$this->assertStringContains( $add, $res );
		}

		$this->assertEquals( ++$before, did_action( 'lifterlms_single_lesson_before_summary' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_single_lesson_after_summary' ) );

	}

	/**
	 * Test llms_get_post_content() for a restricted lesson post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_lesson_restricted() {

		add_filter( 'llms_page_restricted', array( $this, 'make_restricted' ) );

		$before = did_action( 'lifterlms_no_access_main_content' );
		$after  = did_action( 'lifterlms_no_access_after' );

		llms_post_content_init();
		$post = $this->get_mock_post( 'lesson' );

		$res = $this->get_post_content( $post );

		$this->assertSame( '', $res );

		$this->assertEquals( ++$before, did_action( 'lifterlms_no_access_main_content' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_no_access_after' ) );

		remove_filter( 'llms_page_restricted', array( $this, 'make_restricted' ) );

	}

	/**
	 * Test llms_get_post_content() for the quiz post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_quiz() {

		$before = did_action( 'lifterlms_single_quiz_before_summary' );
		$after  = did_action( 'lifterlms_single_quiz_after_summary' );

		llms_post_content_init();
		$post = $this->get_mock_post( 'llms_quiz' );

		$res = $this->get_post_content( $post );

		// Starts with a wrapper.
		$this->assertSame( 0, strpos( $res, '<div class="llms-quiz-wrapper" id="llms-quiz-wrapper">' ) );

		$additions = array(
			'<div class="llms-return">',
			'<p>Post Content</p>', // Default content.
			'</div><!--end #llms-quiz-wrapper -->',
		);
		foreach ( $additions as $add ) {
			$this->assertStringContains( $add, $res );
		}

		$this->assertEquals( ++$before, did_action( 'lifterlms_single_quiz_before_summary' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_single_quiz_after_summary' ) );

	}

	/**
	 * Test llms_get_post_content() for a restricted quiz post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_quiz_restricted() {

		add_filter( 'llms_page_restricted', array( $this, 'make_restricted' ) );

		$before = did_action( 'lifterlms_no_access_main_content' );
		$after  = did_action( 'lifterlms_no_access_after' );

		llms_post_content_init();
		$post = $this->get_mock_post( 'llms_quiz' );

		$res = $this->get_post_content( $post );

		$this->assertSame( '', $res );

		$this->assertEquals( ++$before, did_action( 'lifterlms_no_access_main_content' ) );
		$this->assertEquals( ++$after, did_action( 'lifterlms_no_access_after' ) );

		remove_filter( 'llms_page_restricted', array( $this, 'make_restricted' ) );

	}

	/**
	 * Test that llms_get_post_content() will return early if the `$post` global is not set.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_content_no_global() {

		llms_post_content_init();

		$input = 'whatever';
		$this->assertEquals( $input, llms_get_post_content( $input ) );

	}

	/**
	 * Test llms_get_post_sales_page_content() for an unsupported post type.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_sales_page_content_unsupported() {
		$this->assertEquals( 'default content', llms_get_post_sales_page_content( $this->factory->post->create_and_get(), 'default content' ) );
	}

	/**
	 * Test llms_get_post_sales_page_content() for supported post types.
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_get_post_sales_page_content_supported() {

		$post_excerpt = 'excerpt content';

		foreach ( array( 'course', 'llms_membership' ) as $post_type ) {

			$post = $this->factory->post->create_and_get( compact( 'post_type', 'post_excerpt' ) );
			update_post_meta( $post->ID, '_llms_sales_page_content_type', 'redirect' );
			$this->assertEquals( 'default content', llms_get_post_sales_page_content( $post, 'default content' ) );

			update_post_meta( $post->ID, '_llms_sales_page_content_type', 'content' );

			$this->assertEquals( "<p>excerpt content</p>\n", llms_get_post_sales_page_content( $post, 'default content' ) );
		}

	}

	/**
	 * Test llms_post_content_init() when filters should be applied
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_post_content_init() {

		remove_filter( 'the_content', 'llms_get_post_content' );

		$this->assertTrue( llms_post_content_init() );
		$this->assertEquals( 10, has_filter( 'the_content', 'llms_get_post_content' ) );

	}

	/**
	 * Test llms_post_content_init() when on the admin panel
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_post_content_init_is_admin() {

		remove_filter( 'the_content', 'llms_get_post_content' );

		set_current_screen( 'admin.php' );

		$this->assertFalse( llms_post_content_init() );
		$this->assertFalse( has_filter( 'the_content', 'llms_get_post_content' ) );

		set_current_screen( 'front' ); // Reset.

	}

	/**
	 * Test llms_post_content_init() when filters should be applied
	 *
	 * @since 4.17.0
	 *
	 * @return void
	 */
	public function test_llms_post_content_custom() {

		$this->assertTrue( llms_post_content_init( 'a_fake_callback', 85 ) );
		$this->assertEquals( 85, has_filter( 'the_content', 'a_fake_callback' ) );

		remove_filter( 'the_content', 'a_fake_callback' );

	}

}
