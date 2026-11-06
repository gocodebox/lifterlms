<?php
/**
 * Test template functions
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group template_functions
 *
 * @since 4.8.0
 * @version 7.5.0
 */
class LLMS_Test_Template_Functions extends LLMS_UnitTestCase {

	/**
	 * Test `lifterlms_template_single_reviews()` outputs the Write a Review content.
	 *
	 * @since 7.5.3
	 *
	 * @return void
	 */
	public function test_lifterlms_template_single_reviews() {
		global $post;

		// create student
		$student = $this->factory->student->create();
		wp_set_current_user( $student );

		$post = $this->factory->course->create();

		// enable reviews
		update_post_meta( $post, '_llms_reviews_enabled', 'yes' );

		// run the template function
		ob_start();
		lifterlms_template_single_reviews();
		$output = ob_get_clean();

		$this->assertStringContainsString( __( 'Write a Review', 'lifterlms' ), $output );
	}

	/**
	 * Test bottom-placed lesson video renders after the content, and before the Lesson Progression block on block lessons.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_lesson_media_bottom_placement() {

		$embed = function() {
			return '<iframe class="mock-video"></iframe>';
		};
		add_filter( 'pre_oembed_result', $embed );

		$classic = $this->factory->post->create( array( 'post_type' => 'lesson', 'post_content' => '<p>Classic body</p>' ) );
		$blocks  = $this->factory->post->create( array( 'post_type' => 'lesson', 'post_content' => "<!-- wp:paragraph --><p>Block body</p><!-- /wp:paragraph -->\n<!-- wp:llms/lesson-progression /-->" ) );
		foreach ( array( $classic, $blocks ) as $id ) {
			update_post_meta( $id, '_llms_video_embed', 'https://example.tld/video' );
			update_post_meta( $id, '_llms_video_placement', 'bottom' );
		}

		$render = function( $id ) {
			$this->go_to( get_permalink( $id ) );
			the_post();
			ob_start();
			do_action( 'lifterlms_single_lesson_before_summary' );
			$before = ob_get_clean();
			ob_start();
			do_action( 'lifterlms_single_lesson_after_summary' );
			$after = ob_get_clean();
			return array( $before, do_blocks( get_post( $id )->post_content ), $after );
		};

		// Classic lesson: after the content, via the after summary hook.
		list( $before, $content, $after ) = $render( $classic );
		$this->assertStringNotContainsString( 'mock-video', $before . $content );
		$this->assertStringContainsString( 'mock-video', $after );

		// Block lesson: inside the content, right before the Lesson Progression block, and nowhere else.
		list( $before, $content, $after ) = $render( $blocks );
		$this->assertStringNotContainsString( 'mock-video', $before . $after );
		$this->assertSame( 1, substr_count( $content, 'mock-video' ) );
		$this->assertGreaterThan( strpos( $content, 'Block body' ), strpos( $content, 'mock-video' ) );

		remove_filter( 'pre_oembed_result', $embed );
	}
}
