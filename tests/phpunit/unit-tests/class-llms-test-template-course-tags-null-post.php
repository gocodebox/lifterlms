<?php
/**
 * Tests that course tags/categories/tracks wrappers and templates bail
 * when global $post is not a WP_Post.
 *
 * Regression coverage for LifterLMS issue #2577 — the wrappers previously
 * loaded the template regardless of $post state, and the templates then
 * dereferenced $post->ID, emitting PHP warnings outside the loop.
 *
 * @group LLMS_Templates
 *
 * @since 10.1.0
 * @version 10.1.0
 */
class LLMS_Test_Template_Course_Tags_Null_Post extends LLMS_UnitTestCase {

	/**
	 * Hold the prior global $post across calls.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @var mixed
	 */
	private $saved_post;

	/**
	 * Standard set_up.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		global $post;
		$this->saved_post = $post;
		$post             = null;
	}

	/**
	 * Restore the original global $post.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @return void
	 */
	public function tear_down() {

		global $post;
		$post = $this->saved_post;

		parent::tear_down();
	}

	/**
	 * Each wrapper must emit nothing when global $post is null.
	 *
	 * @since 10.1.0
	 * @version 10.1.0
	 *
	 * @return void
	 */
	public function test_wrappers_return_silently_when_global_post_is_null() {

		ob_start();
		lifterlms_template_single_course_tags();
		lifterlms_template_single_course_categories();
		lifterlms_template_single_course_tracks();
		$out = ob_get_clean();

		$this->assertSame( '', $out );
	}
}
