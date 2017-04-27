<?php
/**
 * Tests for LifterLMS Access Functions
 * @since    3.7.3
 * @version  3.7.3
 */
class LLMS_Test_Functions_Access extends LLMS_UnitTestCase {

	/**
	 * Get a formatted date for setting time period related restrictions
	 * @param    string     $offset  adjust day via strtotime
	 * @param    string     $format  desired retured format, passed to date()
	 * @return   string
	 * @since    3.7.3
	 * @version  3.7.3
	 */
	private function get_date( $offset = '+7 days', $format = 'm/d/y' ) {
		return date( $format, strtotime( $offset, current_time( 'timestamp' ) ) );
	}

	/**
	 * Test the llms_is_post_restricted_by_prerequisite() function
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function test_llms_is_post_restricted_by_prerequisite() {

		$courses = $this->generate_mock_courses( 3, 2, 1, 1 );

		$prereq_course_id = $courses[0];

		$course_id = $courses[1];
		$course = llms_get_post( $course_id );

		$track = wp_insert_term( 'mock track', 'course_track' );
		$track_id = $track['term_id'];
		$course_in_track_id = $courses[2];
		wp_set_post_terms( $course_in_track_id, $track_id, 'course_track' );

		$lessons = $course->get_lessons( 'ids' );

		$lesson_2 = llms_get_post( $lessons[1] );
		$lesson_2->set( 'has_prerequisite', 'yes' );
		$lesson_2->set( 'prerequisite', $lessons[0] );

		$test_ids = array_merge( $lessons, $course->get_quizzes() );

		$this->prereq_tests( $test_ids, $course, $prereq_course_id, $track_id );

		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );

		// results should all be the same with the student b/c nothing completed
		$this->prereq_tests( $test_ids, $course, $prereq_course_id, $track_id, $student_id );

		// results differ once student completes coures
		$this->complete_courses_for_student( $student_id, $courses );

		$this->prereq_tests( $test_ids, $course, $prereq_course_id, $track_id, $student_id );

	}

	/**
	 * test_llms_is_post_restricted_by_prerequisite() runs this series of assertions several times
	 * @param    array      $test_ids          array of post ids to test the llms_is_post_restricted_by_prerequisite() against
	 * @param    obj        $course            course objet
	 * @param    int        $prereq_course_id  post id of the prereq course
	 * @param    int        $track_id          term id of the prereq track
	 * @param    int        $user_id           wp user id of a student
	 * @return   void
	 * @since    3.7.3
	 * @version  3.7.3
	 */
	private function prereq_tests( $test_ids = array(), $course, $prereq_course_id, $track_id, $user_id = null ) {

		$student = $user_id ? new LLMS_Student( $user_id ) : null;

		foreach ( $test_ids as $test_id ) {

			$course->set( 'has_prerequisite', 'no' );
			$course->set( 'prerequisite', '' );
			$course->set( 'prerequisite_track', '' );

			$post = llms_get_post( $test_id );
			if ( 'lesson' === get_post_type( $test_id ) && $post->has_prerequisite() ) {

				$lesson_prereq_id = $post->get( 'prerequisite' );
				$lesson_res = $student && $student->is_complete( $lesson_prereq_id, 'lesson' ) ? false : array(
					'type' => 'lesson',
					'id' => $lesson_prereq_id,
				);
				$this->assertEquals( $lesson_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			} else {

				// no prereq
				$this->assertFalse( llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			}


			// set a course prereq
			$course->set( 'has_prerequisite', 'yes' );
			$course->set( 'prerequisite', $prereq_course_id );

			$prereq_course_res = $student && $student->is_complete( $prereq_course_id, 'course' ) ? false : array(
				'type' => 'course',
				'id' => $prereq_course_id,
			);
			$this->assertEquals( $prereq_course_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			// set a track prereq
			$course->set( 'prerequisite_track', $track_id );

			// checks course prereq first and only returns one
			$this->assertEquals( $prereq_course_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

			// no course prereq, returns track id
			$course->set( 'prerequisite', '' );
			$prereq_track_res = $student && $student->is_complete( $track_id, 'course_track' ) ? false : array(
				'type' => 'course_track',
				'id' => $track_id,
			);
			$this->assertEquals( $prereq_track_res, llms_is_post_restricted_by_prerequisite( $test_id, $user_id ) );

		}

	}

	/**
	 * Test the llms_is_post_restricted_by_time_period() function
	 * @return   void
	 * @since    3.7.3
	 * @version  3.7.3
	 */
	public function test_llms_is_post_restricted_by_time_period() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );
		$course_id = $courses[0];
		$course = llms_get_post( $course_id );

		$test_ids = array_merge( array( $course_id ), $course->get_lessons( 'ids' ), $course->get_quizzes() );

		foreach ( $test_ids as $test_post_id ) {

			$course->set( 'time_period', 'no' );

			// no time period
			$this->assertFalse( llms_is_post_restricted_by_time_period( $test_post_id ) );

			// enable the restriction
			$course->set( 'time_period', 'yes' );

			// no dates set the course is closed without dates
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// start date in the future
			$course->set( 'start_date', $this->get_date( '+7 days' ) );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// start date in past
			$course->set( 'start_date', $this->get_date( '-7 days' ) );
			$this->assertFalse( llms_is_post_restricted_by_time_period( $test_post_id ) );

			// start date in past and end date in past
			$course->set( 'end_date', $this->get_date( '-5 days' ) );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// no start date, end date in past
			$course->set( 'start_date', '' );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

			// no start date end in future
			$course->set( 'end_date', $this->get_date( '+7 days' ) );
			$this->assertEquals( $course_id, llms_is_post_restricted_by_time_period( $test_post_id ) );

		}

	}


}
