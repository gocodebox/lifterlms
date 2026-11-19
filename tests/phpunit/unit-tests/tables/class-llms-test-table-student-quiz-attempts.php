<?php
/**
 * Test the student quiz attempts reporting table.
 *
 * @package LifterLMS/Tests/Tables
 *
 * @group reporting_tables
 * @group due_dates
 *
 * @since [version]
 */
class LLMS_Test_Table_Student_Quiz_Attempts extends LLMS_UnitTestCase {

	/**
	 * Test that the export has plain grade and date cells for a late attempt with a penalty,
	 * with the score, penalty and late flag in their own columns at the end.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_export_late_attempt() {

		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/tables/llms.table.student.quiz.attempts.php';

		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 5 )[0] );
		$quiz    = llms_get_post( $course->get_lessons()[0]->get( 'quiz' ) );
		$student = $this->factory->student->create();

		$quiz->set( 'due_method', 'date' );
		$quiz->set( 'due_date', '2020-01-01' );

		$attempt = $this->take_quiz( $quiz->get( 'id' ), $student, 80 );
		$attempt->set( 'late_penalty', 20 );
		$attempt->set( 'late_penalty_note', 'Two days late.' );
		$attempt->calculate_grade()->save();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$table  = new LLMS_Table_Student_Quiz_Attempts();
		$export = $table->get_export( array( 'student_id' => $student ) );

		$this->assertSame( array( 'id', 'quiz', 'course', 'lesson', 'attempt', 'grade', 'start_date', 'end_date', 'score', 'late_penalty', 'late_penalty_note', 'late' ), array_keys( $export[1] ) );

		$end = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->get( 'end_date' ) ) );
		$this->assertSame( '60% (Pass)', $export[1]['grade'] );
		$this->assertSame( $end, $export[1]['end_date'] );
		$this->assertSame( '80%', $export[1]['score'] );
		$this->assertSame( '20%', $export[1]['late_penalty'] );
		$this->assertSame( 'Two days late.', $export[1]['late_penalty_note'] );
		$this->assertSame( 'Yes', $export[1]['late'] );

		// On screen the badges are still shown.
		$this->assertStringContainsString( 'llms-due-badge--late', $table->get_table_html() );
	}
}
