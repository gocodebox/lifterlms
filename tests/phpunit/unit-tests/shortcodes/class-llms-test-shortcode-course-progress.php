<?php
/**
 * Test the [lifterlms_course_progress] Shortcode
 *
 * @group shortcodes
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Shortcode_Course_Progress extends LLMS_ShortcodeTestCase {

	/**
	 * Test shortcode registration
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_registration() {
		$this->assertTrue( shortcode_exists( 'lifterlms_course_progress' ) );
	}

	/**
	 * Test shortcode output
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_output() {

		$course = $this->factory->post->create( array(
			'post_type' => 'course',
		) );

		// Alter some globals just to emulate we're in a singular course.
		global $post, $wp_query;
		$temp_q = $wp_query;
		$temp_p = $post;

		$wp_query->queried_object = get_post($course);
		$wp_query->is_singular = true;
		$post = $wp_query->queried_object;


		$expected_shortcode = '<div class="llms-progress">
		<div class="progress__indicator">0%</div>
		<div class="llms-progress-bar">
			<div class="progress-bar-complete" data-progress="0%"  style="width:0%"></div>
		</div></div>';

		// Test against logged out user.
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress]' );
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress check_enrollment=0]' );

		// Progress should not be shown to logged out users if check_enrollment=1.
		$this->assertShortcodeOutputEquals( '', '[lifterlms_course_progress check_enrollment=1]' );

		// Get a student and try again
		$student = $this->get_mock_student( true );

		$student->enroll( $course );

		// Student enrolled: progress should always be shown
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress]' );
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress check_enrollment=0]' );
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress check_enrollment=1]' );

		$student->unenroll( $course );
		// Student unenrolled but logged in: same as logged out.
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress]' );
		$this->assertShortcodeOutputEquals( $expected_shortcode, '[lifterlms_course_progress check_enrollment=0]' );
		$this->assertShortcodeOutputEquals( '', '[lifterlms_course_progress check_enrollment=1]' );

		// Reset globals alterations.
		$wp_query = $temp_q;
		$post     = $temp_p;

	}

}
