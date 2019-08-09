<?php
/**
 * Test INsturctor model
 *
 * @package  LifterLMS_Tests/Models
 *
 * @group LLMS_Instructor
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_Instructor extends LLMS_Unit_Test_Case {

	/**
	 * Test something
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_has_student() {

		$instructor = $this->factory->instructor->create_and_get();
		$student    = $this->factory->student->create_and_get();

		$this->assertFalse( $instructor->has_student( $student ) );

		$course_1 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_1->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		$course_2 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_2->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		$this->assertFalse( $instructor->has_student( 'fake' ) );
		$this->assertFalse( $instructor->has_student( $student ) );
		$this->assertFalse( $instructor->has_student( $student->get( 'id' ) ) );
		$this->assertFalse( $instructor->has_student( llms_get_student( $student ) ) );

		$student->enroll( $course_2->get( 'id' ) );

		$this->assertTrue( $instructor->has_student( $student ) );

		$student->enroll( $course_1->get( 'id' ) );

		$this->assertTrue( $instructor->has_student( $student ) );

		$student->unenroll( $course_1->get( 'id' ) );
		$student->unenroll( $course_2->get( 'id' ) );

		$this->assertFalse( $instructor->has_student( $student ) );

	}

}
