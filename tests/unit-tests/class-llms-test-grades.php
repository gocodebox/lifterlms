<?php
/**
 * Tests for Grading methods
 * @group    grades
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Grades extends LLMS_UnitTestCase {

	/**
	 * test instance() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_instance() {

		$this->assertTrue( is_a( LLMS_Grades::instance(), 'LLMS_Grades' ) );
		$this->assertClassHasStaticAttribute( '_instance', 'LLMS_Grades' );

	}

	/**
	 * test calculate_grade() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_calculate_grade() {

		$grader = LLMS()->grades();

		$student = $this->get_mock_student();
		$course = llms_get_post( $this->generate_mock_courses( 1, 2, 5, 5, 10 )[0] );

		$student->enroll( $course->get( 'id' ) );

		// no grade yet
		$this->assertNull( $grader->calculate_grade( $course, $student ) );

		$possible_grades = array( 0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100 );
		$lesson_points = array();
		$lesson_grades = array();

		foreach ( $course->get_lessons() as $i => $lesson ) {

			// calculate the ongoing grade as quizzes are completed
			if ( 0 !== $i ) {
				$total_points = array_sum( $lesson_points );
				$course_grade = 0;
				foreach ( $lesson_grades as $i => $grade ) {
					if ( $lesson_points[ $i ] ) {
						$course_grade += $grade * ( $lesson_points[ $i ] / $total_points );
					}
				}
				$this->assertEquals( round( $course_grade, 2 ), $grader->calculate_grade( $course, $student ) );
			}

			$points = rand( 0, 5 );
			$lesson->set( 'points', $points );
			$lesson_points[] = $points;

			// no grade on the lesson yet
			$this->assertNull( $grader->calculate_grade( $lesson, $student ) );

			$quiz_id = $lesson->get( 'quiz' );
			if ( ! $quiz_id ) {
				continue;
			}

			$grade = $possible_grades[ rand( 0, count( $possible_grades ) - 1 ) ];
			$this->take_quiz( $quiz_id, $student->get( 'id' ), $grade );
			$this->assertEquals( $grade, $grader->calculate_grade( $lesson, $student ) );
			$lesson_grades[] = $grade;

		}

		$total_points = array_sum( $lesson_points );
		$course_grade = 0;
		foreach ( $lesson_grades as $i => $grade ) {
			if ( $lesson_points[ $i ] ) {
				$course_grade += $grade * ( $lesson_points[ $i ] / $total_points );
			}
		}

		// checkout overall course grade once completed
		$this->assertEquals( round( $course_grade, 2 ), $grader->calculate_grade( $course, $student ) );

	}

	/**
	 * test get_grade() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_grade() {

		$grader = LLMS()->grades();

		$student = $this->get_mock_student();
		$course = llms_get_post( $this->generate_mock_courses( 1, 2, 5, 5, 10 )[0] );

		$student->enroll( $course->get( 'id' ) );

		// no grade yet
		$this->assertNull( $grader->get_grade( $course->get( 'id' ), $student->get( 'id' ) ) );

		$possible_grades = array( 0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100 );
		$lesson_points = array();
		$lesson_grades = array();

		foreach ( $course->get_lessons() as $i => $lesson ) {

			// calculate the ongoing grade as quizzes are completed
			if ( 0 !== $i ) {
				$total_points = array_sum( $lesson_points );
				$course_grade = 0;
				foreach ( $lesson_grades as $i => $grade ) {
					if ( $lesson_points[ $i ] ) {
						$course_grade += $grade * ( $lesson_points[ $i ] / $total_points );
					}
				}
				$this->assertEquals( round( $course_grade, 2 ), $grader->get_grade( $course->get( 'id' ), $student->get( 'id' ), false ) );
				$this->assertEquals( round( $course_grade, 2 ), $grader->get_grade( $course->get( 'id' ), $student->get( 'id' ) ) );
			}

			$points = rand( 0, 5 );
			$lesson->set( 'points', $points );
			$lesson_points[] = $points;

			// no grade on the lesson yet
			$this->assertNull( $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ) ) );

			$quiz_id = $lesson->get( 'quiz' );
			if ( ! $quiz_id ) {
				continue;
			}

			$grade = $possible_grades[ rand( 0, count( $possible_grades ) - 1 ) ];
			$this->take_quiz( $quiz_id, $student->get( 'id' ), $grade );
			$this->assertNull( $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ) ) ); // cached
			$this->assertEquals( $grade, $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ), false ) ); // no cache
			$this->assertEquals( $grade, $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ) ) ); // cached
			$lesson_grades[] = $grade;

		}

		$total_points = array_sum( $lesson_points );
		$course_grade = 0;
		foreach ( $lesson_grades as $i => $grade ) {
			if ( $lesson_points[ $i ] ) {
				$course_grade += $grade * ( $lesson_points[ $i ] / $total_points );
			}
		}

		// checkout overall course grade once completed
		$this->assertEquals( round( $course_grade, 2 ), $grader->get_grade( $course->get( 'id' ), $student->get( 'id' ), false ) );
		$this->assertEquals( round( $course_grade, 2 ), $grader->get_grade( $course->get( 'id' ), $student->get( 'id' ) ) );


	}

	/**
	 * test round() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_round() {

		$this->assertEquals( 0, LLMS()->grades()->round( 0 ) );
		$this->assertEquals( 1.5, LLMS()->grades()->round( 1.5 ) );
		$this->assertEquals( 25, LLMS()->grades()->round( 25 ) );
		$this->assertEquals( 25.0, LLMS()->grades()->round( 25.0 ) );
		$this->assertEquals( 1.67, LLMS()->grades()->round( 1.666 ) );
		$this->assertEquals( 251.67, LLMS()->grades()->round( 251.666 ) );
		$this->assertEquals( 82.12, LLMS()->grades()->round( 82.123 ) );
		$this->assertEquals( 98.13, LLMS()->grades()->round( 98.125 ) );
		$this->assertEquals( 75.12, LLMS()->grades()->round( 75.12 ) );
		$this->assertEquals( 0.02, LLMS()->grades()->round( 0.015559 ) );

	}

}
