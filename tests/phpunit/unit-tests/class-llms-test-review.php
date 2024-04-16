<?php
/**
 * Test LLMS_Review
 *
 * @package LifterLMS/Tests
 *
 * @group review
 *
 * @since 7.5.3
 */
class LLMS_Test_Review extends LLMS_UnitTestCase {

	/**
	 * Test processing a review.
	 *
	 * @since 7.5.3
	 *
	 * @return void
	 */
	public function test_process_review() {
		$student = $this->factory->student->create();
		$course = $this->factory->course->create();

		// enable reviews for course
		update_post_meta( $course, '_llms_reviews_enabled', 'yes' );

		llms_enroll_student( $student, $course );

		wp_set_current_user( $student );

		$review_content = array(
			'pageID' => $course,
			'review_text' => 'This is a review',
			'review_title' => 'Great course',
			'llms_review_nonce' => wp_create_nonce( 'llms-review' ),
		);

		$this->mockPostRequest( $review_content );

		$llms_reviews = new LLMS_Reviews();
		$llms_reviews->process_review();

		$reviews = get_posts( array(
			'post_type' => 'llms_review',
			'post_status' => 'publish',
			'post_content' => $review_content['review_text'],
			'post_title' => $review_content['review_title'],
		) );

		$this->assertCount( 1, $reviews );
	}


	/**
	 * Test processing a review with invalid nonce.
	 *
	 * @since 7.5.3
	 *
	 * @return void
	 */
	public function test_process_review_fails_with_invalid_nonce() {
		$student = $this->factory->student->create();
		$course = $this->factory->course->create();

		// enable reviews for course
		update_post_meta( $course, '_llms_reviews_enabled', 'yes' );

		llms_enroll_student( $student, $course );

		wp_set_current_user( $student );

		$review_content = array(
			'pageID' => $course,
			'review_text' => 'This is a review',
			'review_title' => 'Great course',
			'llms_review_nonce' => wp_create_nonce( 'fake' ),
		);

		$this->mockPostRequest( $review_content );

		$llms_reviews = new LLMS_Reviews();
		$llms_reviews->process_review();

		$reviews = get_posts( array(
			'post_type' => 'llms_review',
			'post_status' => 'publish',
			'post_content' => $review_content['review_text'],
			'post_title' => $review_content['review_title'],
		) );

		$this->assertCount( 0, $reviews );
	}
}
