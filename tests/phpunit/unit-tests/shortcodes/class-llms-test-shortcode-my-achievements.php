<?php
/**
 * Test the My Achievements shortcode.
 *
 * @package LifterLMS/Tests
 *
 * @group shortcodes
 * @group shortcode_my_achievements
 *
 * @since 11.1.0
 */
class LLMS_Test_Shortcode_My_Achievements extends LLMS_ShortcodeTestCase {

	/**
	 * Class name of the Shortcode Class.
	 *
	 * @var string
	 */
	public $class_name = 'LLMS_Shortcode_My_Achievements';

	/**
	 * Test that another user's achievements are only output to users who can view that student.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_output_user_id() {

		$course  = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$student = $this->factory->student->create();
		$this->earn_achievement( $student, $this->create_achievement_template( 'Secret Achievement Title' ), $course->get( 'id' ) );

		$teaching = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$course->instructors()->set_instructors( array( array( 'id' => $teaching ) ) );

		$users = array(
			'guest'                => array( 0, false ),
			'other student'        => array( $this->factory->student->create(), false ),
			'other instructor'     => array( $this->factory->user->create( array( 'role' => 'instructor' ) ), false ),
			'teaching instructor'  => array( $teaching, true ),
			'administrator'        => array( $this->factory->user->create( array( 'role' => 'administrator' ) ), true ),
			'the student'          => array( $student, true ),
		);

		foreach ( $users as $label => list( $user_id, $can_view ) ) {
			wp_set_current_user( $user_id );
			$output = do_shortcode( sprintf( '[lifterlms_my_achievements user_id="%d"]', $student ) );
			$this->assertSame( $can_view, false !== strpos( $output, 'Secret Achievement Title' ), $label );
		}

		// The student's own achievements are output by default.
		wp_set_current_user( $student );
		$this->assertStringContainsString( 'Secret Achievement Title', do_shortcode( '[lifterlms_my_achievements]' ) );

		wp_set_current_user( 0 );

	}

}
