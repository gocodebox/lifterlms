<?php
/**
 * Test LLMS_Privacy_Exporters
 *
 * @package LifterLMS/Tests/Privacy
 *
 * @group privacy
 * @group privacy_exporters
 *
 * @since 11.1.0
 */
class LLMS_Test_Privacy_Exporters extends LLMS_UnitTestCase {

	/**
	 * Test quiz_data() exports each attempt in the quiz group with its description.
	 *
	 * @since 11.1.0
	 *
	 * @return void
	 */
	public function test_quiz_data() {

		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 5 )[0] );
		$student = $this->factory->student->create_and_get();
		$attempt = $this->take_quiz( $course->get_lessons()[0]->get( 'quiz' ), $student->get_id(), 80 );

		$export = LLMS_Privacy_Exporters::quiz_data( $student->get( 'user_email' ), 1 );

		$this->assertTrue( $export['done'] );
		$this->assertCount( 1, $export['data'] );
		$this->assertSame( 'lifterlms_quizzes', $export['data'][0]['group_id'] );
		$this->assertSame( 'Student quiz attempt data', $export['data'][0]['group_description'] );
		$this->assertSame( $attempt->get_key(), wp_list_pluck( $export['data'][0]['data'], 'value', 'name' )['Attempt ID'] );
	}

	/**
	 * Test that a student's due date accommodation and the due dates changed for them are exported,
	 * and that the erasers remove the accommodation and the reasons but keep the due dates.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_due_date_data() {

		update_option( 'timezone_string', 'UTC' );
		update_option( 'date_format', 'Y-m-d' );
		update_option( 'time_format', 'H:i' );

		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 2, 2, 1 )[0] );
		$quizzes = array_map(
			function ( $lesson ) {
				return llms_get_post( $lesson->get( 'quiz' ) );
			},
			$course->get_lessons()
		);
		$student = $this->factory->student->create_and_get();
		$email   = $student->get( 'user_email' );

		// Nothing to export.
		$this->assertSame( array(), LLMS_Privacy_Exporters::due_date_data( $email, 1 )['data'] );
		$this->assertArrayNotHasKey( 'Due Date Accommodation', wp_list_pluck( LLMS_Privacy_Exporters::student_data( $email, 1 )['data'][0]['data'], 'value', 'name' ) );

		update_user_option( $student->get_id(), 'llms_due_date_accommodation', 'extend' );
		update_user_option( $student->get_id(), 'llms_due_date_extension_days', 7 );
		$quizzes[0]->set_due_date_override( $student->get_id(), gmmktime( 17, 0, 0, 7, 10, 2030 ), 'Illness' );
		$quizzes[1]->set_due_date_override( $student->get_id(), 'none' );

		// Another student's due date isn't exported.
		$quizzes[0]->set_due_date_override( $this->factory->student->create(), gmmktime( 17, 0, 0, 7, 20, 2030 ), 'Travel' );

		$this->assertSame(
			'Every due date extended by 7 days',
			wp_list_pluck( LLMS_Privacy_Exporters::student_data( $email, 1 )['data'][0]['data'], 'value', 'name' )['Due Date Accommodation']
		);

		$export = LLMS_Privacy_Exporters::due_date_data( $email, 1 );
		$this->assertTrue( $export['done'] );
		$this->assertCount( 2, $export['data'] );
		$this->assertSame( 'lifterlms_due_dates', $export['data'][0]['group_id'] );

		$first = wp_list_pluck( $export['data'][0]['data'], 'value', 'name' );
		$this->assertSame( $quizzes[0]->get( 'title' ), $first['Title'] );
		$this->assertSame( '2030-07-10 17:00', $first['Due Date'] );
		$this->assertSame( 'Illness', $first['Reason'] );

		$second = wp_list_pluck( $export['data'][1]['data'], 'value', 'name' );
		$this->assertSame( 'No due date', $second['Due Date'] );
		$this->assertArrayNotHasKey( 'Reason', $second );

		// Erase: the accommodation and the reason go, the due dates stay.
		$this->assertContains( 'Removed student "Due Date Accommodation"', LLMS_Privacy_Erasers::student_data( $email, 1 )['messages'] );
		$this->assertFalse( $student->get_due_date_accommodation() );

		$erased = LLMS_Privacy_Erasers::due_date_data( $email, 1 );
		$this->assertTrue( $erased['items_removed'] );
		$this->assertCount( 1, $erased['messages'] );
		$this->assertSame( '', $quizzes[0]->get_due_date_override( $student->get_id() )['reason'] );
		$this->assertSame( gmmktime( 17, 0, 0, 7, 10, 2030 ), $quizzes[0]->get_due_timestamp( $student->get_id() ) );
		$this->assertFalse( $quizzes[1]->get_due_timestamp( $student->get_id() ) );

		// Nothing left to erase.
		$this->assertFalse( LLMS_Privacy_Erasers::due_date_data( $email, 1 )['items_removed'] );
	}
}
