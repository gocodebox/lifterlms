<?php
/**
 * Test LLMS_Privacy_Exporters
 *
 * @package LifterLMS/Tests/Privacy
 *
 * @group privacy
 * @group privacy_exporters
 *
 * @since [version]
 */
class LLMS_Test_Privacy_Exporters extends LLMS_UnitTestCase {

	/**
	 * Test quiz_data() exports each attempt in the quiz group with its description.
	 *
	 * @since [version]
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
}
