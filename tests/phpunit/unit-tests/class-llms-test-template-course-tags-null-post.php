<?php
/**
 * Tests that the course tags, categories, and tracks template functions bail
 * when global $post is not a WP_Post.
 *
 * Regression coverage for LifterLMS issue #2577. The Course Information block
 * calls these functions directly, so a block theme template rendered without a
 * global post (for example a 404 or empty search page) loaded the templates and
 * read $post->ID on null.
 *
 * @group LLMS_Templates
 *
 * @since [version]
 */
class LLMS_Test_Template_Course_Tags_Null_Post extends LLMS_UnitTestCase {

	/**
	 * Hold the prior global $post across calls.
	 *
	 * @since [version]
	 *
	 * @var mixed
	 */
	private $saved_post;

	/**
	 * Standard set_up.
	 *
	 * @since [version]
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
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		global $post;
		$post = $this->saved_post;

		parent::tear_down();
	}

	/**
	 * Each template function must emit nothing when global $post is null.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_template_functions_return_silently_when_global_post_is_null() {

		ob_start();
		lifterlms_template_single_course_tags();
		lifterlms_template_single_course_categories();
		lifterlms_template_single_course_tracks();
		$out = ob_get_clean();

		$this->assertSame( '', $out );
	}

	/**
	 * The Course Information block must render without warnings when global $post is null.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_course_information_block_renders_when_global_post_is_null() {

		$block = array(
			'blockName'    => 'llms/course-information',
			'attrs'        => array(
				'show_length'     => false,
				'show_difficulty' => false,
			),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);

		$this->assertIsString( render_block( $block ) );
	}
}
