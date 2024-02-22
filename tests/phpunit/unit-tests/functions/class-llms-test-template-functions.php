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
	 * @since [version]
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
}
