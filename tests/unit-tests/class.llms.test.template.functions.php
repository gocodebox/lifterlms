<?php
/**
 * Tests for template functions
 * @group    LLMS_Functions_Templates
 * @since    [version]
 * @version  [version]
 */
class LLMS_Functions_Templates extends LLMS_UnitTestCase {

	private function get_output( $func, $params = array() ) {

		ob_start();
		call_user_func_array( $func, $params );
		return ob_get_clean();

	}

	/**
	 * Test lifterlms_course_continue_button() func
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_lifterlms_course_continue_button() {

		global $post;
		$func = 'lifterlms_course_continue_button';

		// student to use
		$student = $this->get_mock_student();

		// course to use
		$course_id = $this->generate_mock_courses()[0];
		$course = llms_get_post( $course_id );

		// blog post to test globals against
		$post_id = $this->factory->post->create( array(
			'post_title' => 'Test Post',
		) );


		// call function with no parameters (using only defaults)
		// no student and no post set right now
		$this->assertEmpty( $this->get_output( $func ) );

		// set the global post to be a blog post
		$post = get_post( $post_id );

		// call function with no parameters (using only defaults)
		// post is a blog post & no student
		$this->assertEmpty( $this->get_output( $func ) );

		// set global to be a course but still no student
		$post = get_post( $course_id );
		$this->assertEmpty( $this->get_output( $func ) );

		// set the current student (should display a continue button)
		wp_set_current_user( $student->get_id() );

		// student setup but no enrollment
		$this->assertEmpty( $this->get_output( $func ) );

		// enroll student
		llms_enroll_student( $student->get_id(), $course_id );

		// 0 progress, "Get Started" text displays in button
		$this->assertTrue( ( false !== strpos( $this->get_output( $func ), 'Get Started' ) ) );

		// Progress > 0, "Continue" text displays in button
		$this->complete_courses_for_student( $student->get_id(), array( $course_id ), 85 );
		$this->assertTrue( ( false !== strpos( $this->get_output( $func ), 'Continue' ) ) );

		// 100% progress, "Course Complete" text displays
		$this->complete_courses_for_student( $student->get_id(), array( $course_id ), 100 );
		$this->assertTrue( ( false !== strpos( $this->get_output( $func ), 'Course Complete' ) ) );

		// use a lesson, same result as last
		$post = get_post( $course->get_lessons( 'ids' )[0] );
		$this->assertTrue( ( false !== strpos( $this->get_output( $func ), 'Course Complete' ) ) );

		// reset global
		$post = null;

	}

}
