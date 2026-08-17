<?php
/**
 * Tests for Grading methods
 * @group    grades
 * @since    3.24.0
 */
class LLMS_Test_Grades extends LLMS_UnitTestCase {

	/**
	 * Test the `instance()` method.
	 *
	 * @since 3.24.0
	 * @since 5.3.0 Rename `_instance` property to `instance`.
	 *
	 * @return void
	 */
	public function test_instance() {

		$this->assertTrue( is_a( LLMS_Grades::instance(), 'LLMS_Grades' ) );
		$this->assertClassHasStaticAttribute( 'instance', 'LLMS_Grades' );

	}

	/**
	 * test calculate_grade() method
	 * @return   void
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function test_calculate_grade() {

		$grader = llms()->grades();

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
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function test_get_grade() {

		$grader = llms()->grades();

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
			// Cache is busted by `lifterlms_quiz_completed`, so the cached read sees the new grade.
			$this->assertEquals( $grade, $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ) ) ); // cached
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
	 * Test get_grade() treats an empty string in the cache as a miss.
	 *
	 * Simulates a persistent object cache (Redis, Memcached) deserializing a cached null as ''.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_grade_empty_string_cache_treated_as_miss() {

		$grader  = llms()->grades();
		$student = $this->get_mock_student();
		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 1 )[0] );

		$student->enroll( $course->get( 'id' ) );

		$course_id  = $course->get( 'id' );
		$student_id = $student->get( 'id' );
		$group      = LLMS_Grades::get_cache_group( $student_id );
		$cache_key  = LLMS_Cache_Helper::get_prefix( $group ) . sprintf( '%d_grade', $course_id );

		// Simulate a persistent cache returning '' for a cached null grade.
		wp_cache_set( $cache_key, '', $group );

		// Should treat '' as a cache miss and recalculate, returning null since no quizzes were taken.
		$this->assertNull( $grader->get_grade( $course_id, $student_id ) );

	}

	/**
	 * Test get_grade() returns a numeric grade from the cache without recalculating.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_grade_numeric_cache_returned() {

		$grader  = llms()->grades();
		$student = $this->get_mock_student();
		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 1 )[0] );

		$student->enroll( $course->get( 'id' ) );

		$course_id  = $course->get( 'id' );
		$student_id = $student->get( 'id' );
		$group      = LLMS_Grades::get_cache_group( $student_id );
		$cache_key  = LLMS_Cache_Helper::get_prefix( $group ) . sprintf( '%d_grade', $course_id );

		wp_cache_set( $cache_key, 85.5, $group );

		$this->assertEquals( 85.5, $grader->get_grade( $course_id, $student_id ) );

	}

	/**
	 * Test get_grade() treats null in the cache as a valid "no grade" hit.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_grade_null_cache_is_valid_hit() {

		$grader  = llms()->grades();
		$student = $this->get_mock_student();
		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 1 )[0] );

		$student->enroll( $course->get( 'id' ) );

		$course_id  = $course->get( 'id' );
		$student_id = $student->get( 'id' );
		$group      = LLMS_Grades::get_cache_group( $student_id );
		$cache_key  = LLMS_Cache_Helper::get_prefix( $group ) . sprintf( '%d_grade', $course_id );

		wp_cache_set( $cache_key, null, $group );

		$this->assertNull( $grader->get_grade( $course_id, $student_id ) );

	}

	/**
	 * Test that deleting a quiz attempt invalidates the student's cached grades.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_grade_cache_invalidated_on_attempt_deletion() {

		$grader  = llms()->grades();
		$student = $this->get_mock_student();
		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 1 )[0] );

		$student->enroll( $course->get( 'id' ) );

		$lesson  = llms_get_post( $course->get_lessons( 'ids' )[0] );
		$quiz_id = $lesson->get( 'quiz' );

		$this->take_quiz( $quiz_id, $student->get( 'id' ), 100 );
		$this->assertEquals( 100, $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ) ) );

		// Delete the only attempt; the cached grade must not survive.
		$attempt = $student->quizzes()->get_best_attempt( $quiz_id );
		$attempt->delete();

		$this->assertNull( $grader->get_grade( $lesson->get( 'id' ), $student->get( 'id' ) ) );

	}

	/**
	 * test round() method
	 * @return   void
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function test_round() {

		$this->assertEquals( 0, llms()->grades()->round( 0 ) );
		$this->assertEquals( 1.5, llms()->grades()->round( 1.5 ) );
		$this->assertEquals( 25, llms()->grades()->round( 25 ) );
		$this->assertEquals( 25.0, llms()->grades()->round( 25.0 ) );
		$this->assertEquals( 1.67, llms()->grades()->round( 1.666 ) );
		$this->assertEquals( 251.67, llms()->grades()->round( 251.666 ) );
		$this->assertEquals( 82.12, llms()->grades()->round( 82.123 ) );
		$this->assertEquals( 98.13, llms()->grades()->round( 98.125 ) );
		$this->assertEquals( 75.12, llms()->grades()->round( 75.12 ) );
		$this->assertEquals( 0.02, llms()->grades()->round( 0.015559 ) );

	}

}
