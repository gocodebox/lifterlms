<?php
/**
 * Test Instructor model
 *
 * @package LifterLMS_Tests/Models
 *
 * @group instructor
 *
 * @since 3.34.0
 * @since [version] Added tests for instructor assistants.
 */
class LLMS_Test_Instructor extends LLMS_Unit_Test_Case {

	/**
	 * Test has_student()
	 *
	 * @since 3.34.0
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

	/**
	 * Test has_student() as instructor's assistant.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_has_student_as_assistant() {

		$instructor           = $this->factory->instructor->create_and_get();
		$instructor_assistant = new LLMS_Instructor(
			$this->factory->user->create( array( 'role' => 'instructors_assistant' ) ),
		);
		$student              = $this->factory->student->create_and_get();

		$this->assertFalse( $instructor_assistant->has_student( $student ) );

		$course_1 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_1->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		$course_2 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_2->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		$this->assertFalse( $instructor_assistant->has_student( 'fake' ) );
		$this->assertFalse( $instructor_assistant->has_student( $student ) );
		$this->assertFalse( $instructor_assistant->has_student( $student->get( 'id' ) ) );
		$this->assertFalse( $instructor_assistant->has_student( llms_get_student( $student ) ) );

		$student->enroll( $course_2->get( 'id' ) );
		$student->enroll( $course_1->get( 'id' ) );

		// Assign the assistant to the instructor.
		$instructor_assistant->add_parent( $instructor->get( 'id' ) );
		// The assistant is not a simple instructor for the student.
		$this->assertFalse( $instructor_assistant->has_student( $student ) );
		$this->assertTrue( $instructor_assistant->has_student( $student, true ) );
		// Add the assistant as instructor of a course.
		$course_2->instructors()->set_instructors(
			array(
				array( 'id' => $instructor->get( 'id' ) ),
				array( 'id' => $instructor_assistant->get( 'id' ) ),
			)
		);
		$this->assertTrue( $instructor_assistant->has_student( $student ) );
		$this->assertTrue( $instructor_assistant->has_student( $student, true ) );

		$student->unenroll( $course_1->get( 'id' ) );
		$student->unenroll( $course_2->get( 'id' ) );

		$this->assertFalse( $instructor_assistant->has_student( $student ) );
	}

	/**
	 * Test get_students().
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_students() {

		$instructor = $this->factory->instructor->create_and_get();
		$student_1  = $this->factory->student->create_and_get();
		$student_2  = $this->factory->student->create_and_get();
		$student_3  = $this->factory->student->create_and_get();

		$course_1 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_1->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		$course_2 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_2->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		// Instructor doesn't have access to this.
		$course_3 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );

		$student_1->enroll( $course_1->get( 'id' ) );
		$student_2->enroll( $course_2->get( 'id' ) );
		$student_3->enroll( $course_3->get( 'id' ) );

		// All students.
		$query = $instructor->get_students();
		$this->assertEquals( array( $student_1, $student_2 ), $query->get_students() );

		// Course 1 only.
		$query = $instructor->get_students( array( 'post_id' => $course_1->get( 'id' ) ) );
		$this->assertEquals( array( $student_1 ), $query->get_students() );

		// Course 2 only.
		$query = $instructor->get_students( array( 'post_id' => $course_2->get( 'id' ) ) );
		$this->assertEquals( array( $student_2 ), $query->get_students() );

		// Course 3 (no results).
		$query = $instructor->get_students( array( 'post_id' => $course_3->get( 'id' ) ) );
		$this->assertEquals( array(), $query->get_students() );

		// Mix courses the instructor has and doesn't have, only returns results from course 2.
		$query = $instructor->get_students( array( 'post_id' => array( $course_2->get( 'id' ), $course_3->get( 'id' ) ) ) );
		$this->assertEquals( array( $student_2 ), $query->get_students() );

	}

	/**
	 * Test get_students() as assistant.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_students_as_assistant() {

		$instructor           = $this->factory->instructor->create_and_get();
		$instructor_assistant = new LLMS_Instructor(
			$this->factory->user->create( array( 'role' => 'instructors_assistant' ) ),
		);
		// Assign the assistant to the instructor.
		$instructor_assistant->add_parent( $instructor->get( 'id' ) );
		$student_1  = $this->factory->student->create_and_get();
		$student_2  = $this->factory->student->create_and_get();
		$student_3  = $this->factory->student->create_and_get();

		$course_1 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_1->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		$course_2 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course_2->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );

		// Instructor assistant doesn't have access to this, because their instructor's parent is
		// not an instructor of this course.
		$course_3 = $this->factory->course->create_and_get( array( 'sections' => 0 ) );

		$student_1->enroll( $course_1->get( 'id' ) );
		$student_2->enroll( $course_2->get( 'id' ) );
		$student_3->enroll( $course_3->get( 'id' ) );

		// All students.
		$query = $instructor_assistant->get_students();
		$this->assertEquals( array(), $query->get_students() );
		// Pass `$as_assistant` as `true`.
		$query = $instructor_assistant->get_students( array(), true );
		$this->assertEquals( array( $student_1, $student_2 ), $query->get_students() );

		// Course 1 only.
		$query = $instructor_assistant->get_students( array( 'post_id' => $course_1->get( 'id' ) ), true );
		$this->assertEquals( array( $student_1 ), $query->get_students() );

		// Course 2 only.
		$query = $instructor_assistant->get_students( array( 'post_id' => $course_2->get( 'id' ) ), true );
		$this->assertEquals( array( $student_2 ), $query->get_students() );

		// Course 3 (no results).
		$query = $instructor_assistant->get_students( array( 'post_id' => $course_3->get( 'id' ) ), true );
		$this->assertEquals( array(), $query->get_students() );

		// Mix courses the instructor has and doesn't have, only returns results from course 2.
		$query = $instructor_assistant->get_students(
			array(
				'post_id' => array(
					$course_2->get( 'id' ),
					$course_3->get( 'id' ),
				),
			),
			true
		);
		$this->assertEquals( array( $student_2 ), $query->get_students() );

	}

	/**
	 * Test get_courses(), get_memberships() for an instructor's assistant.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_courses_memberships_as_assistant() {

		$instructor           = $this->factory->instructor->create_and_get();
		$instructor_assistant = new LLMS_Instructor(
			$this->factory->user->create( array( 'role' => 'instructors_assistant' ) ),
		);
		// Assign the assistant to the instructor.
		$instructor_assistant->add_parent( $instructor->get( 'id' ) );

		$pts = array(
			'course' => array(
				'test_function_suffix' => 'courses',
				'create_args'          => array( 'sections' => 0 ),
			),
			'membership' => array(
				'test_function_suffix' => 'memberships',
				'create_args'          => array(),
			),
		);

		foreach ( $pts as $ptf => $config ) {
			$pt = $this->factory->{$ptf}->create_and_get( $config[ 'create_args' ] );
			// Assign the instructor to the post type.
			$pt->instructors()->set_instructors( array( array( 'id' => $instructor->get( 'id' ) ) ) );
			$func = "get_{$config['test_function_suffix']}";
			$this->assertEquals(
				array(),
				$instructor_assistant->$func(),
				$ptf
			);
			$this->assertEquals(
				array(
					$pt
				),
				$instructor_assistant->$func( array(), 'llms_posts', true ), // As instructor's assistant.
				$ptf
			);
		}
	}

	/**
	 * Test get_students() for an instructor with no courses available to access.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function test_get_students_no_post_access() {

		$instructor = $this->factory->instructor->create_and_get();
		$student    = $this->factory->student->create_and_get();

		$course = $this->factory->course->create_and_get( array( 'sections' => 0 ) );

		$student->enroll( $course->get( 'id' ) );

		$tests = array(
			// Query all instructor's posts (which are none).
			array(),
			// Query a post the instructor doesn't own.
			array(
				'post_id' => $course->get( 'id' ),
			),
		);

		foreach ( $tests as $args ) {

			$query = $instructor->get_students( $args );
			$this->assertEquals( array(), $query->get_results() );

			$this->assertEquals( 0, $query->get_found_results() );
			$this->assertEquals( 0, $query->get_max_pages() );
			$this->assertEquals( 0, $query->get_number_results() );

		}

	}

}
