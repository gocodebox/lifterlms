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

	/**
	 * Test that only logged-in users enrolled in a course with reviews enabled can write a review.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_process_review_requires_enrollment() {
		$enrolled     = $this->factory->student->create();
		$not_enrolled = $this->factory->student->create();
		$course       = $this->factory->course->create( array( 'sections' => 0 ) );
		$disabled     = $this->factory->course->create( array( 'sections' => 0 ) );

		update_post_meta( $course, '_llms_reviews_enabled', 'yes' );
		llms_enroll_student( $enrolled, $course );
		llms_enroll_student( $enrolled, $disabled );

		$submit = function ( $user_id, $course_id ) {
			wp_set_current_user( $user_id );
			$this->mockPostRequest(
				array(
					'pageID'            => $course_id,
					'review_text'       => 'Review text',
					'review_title'      => 'Review title',
					'llms_review_nonce' => wp_create_nonce( 'llms-review' ),
				)
			);
			( new LLMS_Reviews() )->process_review();
			return get_posts(
				array(
					'post_type'   => 'llms_review',
					'post_status' => 'publish',
					'post_parent' => $course_id,
					'author'      => $user_id,
				)
			);
		};

		// Logged-in user not enrolled in the course.
		$this->assertFalse( LLMS_Reviews::current_user_can_write_review( $course ) );
		$this->assertCount( 0, $submit( $not_enrolled, $course ) );

		// Guest.
		$this->assertCount( 0, $submit( 0, $course ) );

		// Enrolled student, reviews disabled.
		$this->assertCount( 0, $submit( $enrolled, $disabled ) );

		// Enrolled student, reviews enabled: the review is saved with the student as its author.
		$this->assertCount( 1, $submit( $enrolled, $course ) );
	}

	/**
	 * Test that the review form is only output for students enrolled in the course.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_output_requires_enrollment() {
		$enrolled     = $this->factory->student->create();
		$not_enrolled = $this->factory->student->create();
		$course       = $this->factory->course->create( array( 'sections' => 0 ) );

		update_post_meta( $course, '_llms_reviews_enabled', 'yes' );
		llms_enroll_student( $enrolled, $course );

		$this->go_to( get_permalink( $course ) );

		wp_set_current_user( $not_enrolled );
		$html = $this->get_output( array( 'LLMS_Reviews', 'output' ) );
		$this->assertStringNotContainsString( 'review_box', $html );
		$this->assertStringNotContainsString( 'thank_you_box', $html );

		wp_set_current_user( $enrolled );
		$this->assertStringContainsString( 'id="review_box"', $this->get_output( array( 'LLMS_Reviews', 'output' ) ) );
	}
}
