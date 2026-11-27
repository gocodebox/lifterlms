<?php
/**
 * Tests for LLMS_Admin_Due_Dates class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group due_dates
 *
 * @since [version]
 */
class LLMS_Test_Admin_Due_Dates extends LLMS_UnitTestCase {

	/**
	 * Quiz with a course due date of July 1, 2030.
	 *
	 * @var LLMS_Quiz
	 */
	private $quiz;

	/**
	 * Setup test class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-due-dates.php';
	}

	/**
	 * Setup test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		update_option( 'timezone_string', 'UTC' );

		$course     = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 1 )[0] );
		$this->quiz = llms_get_post( $course->get_lessons()[0]->get( 'quiz' ) );
		$this->quiz->set( 'due_method', 'date' );
		$this->quiz->set( 'due_date', '2030-07-01' );
	}

	/**
	 * Mock a per-student due date form submission and run the save.
	 *
	 * @since [version]
	 *
	 * @param int   $student_id WP_User ID of the student.
	 * @param array $vars       Additional posted fields.
	 * @return void
	 */
	private function save( $student_id, $vars = array() ) {

		$this->mockPostRequest(
			array_merge(
				array(
					'_llms_due_date_nonce' => wp_create_nonce( 'llms-due-date-override' ),
					'llms_due_item_id'     => $this->quiz->get( 'id' ),
					'llms_due_student_id'  => $student_id,
				),
				$vars
			)
		);

		try {
			( new LLMS_Admin_Due_Dates() )->save();
		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {
			return;
		}
	}

	/**
	 * Test saving a new date, "no due date" and a reset, and that they take precedence over the course due date.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save() {

		$student_id = $this->factory->student->create();
		$default    = gmmktime( 23, 59, 59, 7, 1, 2030 );
		$this->assertSame( $default, $this->quiz->get_due_timestamp( $student_id ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Without a valid nonce nothing is saved.
		$this->save( $student_id, array( '_llms_due_date_nonce' => 'fake', 'llms_due_date' => '2030-07-10' ) );
		$this->assertFalse( $this->quiz->get_due_date_override( $student_id ) );

		// A date without a time is due at the end of that day.
		$this->save( $student_id, array( 'llms_due_date' => '2030-07-10', 'llms_due_reason' => "Doctor's note <b>& travel</b>" ) );
		$this->assertSame( gmmktime( 23, 59, 59, 7, 10, 2030 ), $this->quiz->get_due_timestamp( $student_id ) );
		// The reason is stored as plain text, without HTML entities.
		$this->assertSame( "Doctor's note & travel", $this->quiz->get_due_date_override( $student_id )['reason'] );

		// The 23:59 the form shows for a date without a time stays the end of the day.
		$this->save( $student_id, array( 'llms_due_date' => '2030-07-10', 'llms_due_time' => '23:59' ) );
		$this->assertSame( gmmktime( 23, 59, 59, 7, 10, 2030 ), $this->quiz->get_due_timestamp( $student_id ) );

		$this->save( $student_id, array( 'llms_due_date' => '2030-07-12', 'llms_due_time' => '09:30' ) );
		$this->assertSame( gmmktime( 9, 30, 0, 7, 12, 2030 ), $this->quiz->get_due_timestamp( $student_id ) );

		// A date that doesn't exist isn't saved.
		$this->save( $student_id, array( 'llms_due_date' => '2030-02-30' ) );
		$this->assertSame( gmmktime( 9, 30, 0, 7, 12, 2030 ), $this->quiz->get_due_timestamp( $student_id ) );

		$this->save( $student_id, array( 'llms_due_none' => '1' ) );
		$this->assertFalse( $this->quiz->get_due_timestamp( $student_id ) );

		$this->save( $student_id, array( 'llms_due_reset' => '1' ) );
		$this->assertFalse( $this->quiz->get_due_date_override( $student_id ) );
		$this->assertSame( $default, $this->quiz->get_due_timestamp( $student_id ) );

		// Another student keeps the course due date throughout.
		$this->assertSame( $default, $this->quiz->get_due_timestamp( $this->factory->student->create() ) );
	}

	/**
	 * Test who can save a per-student due date.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_save_permissions() {

		$course     = $this->quiz->get_course();
		$student_id = $this->factory->student->create();
		$instructor = $this->factory->instructor->create();
		$vars       = array( 'llms_due_date' => '2030-07-10' );

		$assert_denied = function ( $student_id ) use ( $vars ) {
			try {
				$this->save( $student_id, $vars );
				$this->fail( 'The save was not denied.' );
			} catch ( WPDieException $exception ) {
				$this->assertFalse( $this->quiz->get_due_date_override( $student_id ) );
			}
		};

		// An instructor of another course.
		wp_set_current_user( $instructor );
		$assert_denied( $student_id );

		// The course's instructor, for a student who isn't theirs and for a user that doesn't exist.
		$course->set_instructors( array( array( 'id' => $instructor ) ) );
		$assert_denied( $student_id );
		$assert_denied( $student_id + 1000 );

		// The course's instructor for their own student.
		llms_enroll_student( $student_id, $course->get( 'id' ) );
		$this->save( $student_id, $vars );
		$this->assertSame( gmmktime( 23, 59, 59, 7, 10, 2030 ), $this->quiz->get_due_timestamp( $student_id ) );
	}
}
