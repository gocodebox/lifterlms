<?php
/**
 * LifterLMS Unit Test Case Base clase
 * @since    3.3.1
 * @version  3.9.2
 */
class LLMS_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Automatically complete a percentage of courses for a student
	 * @param    integer    $student_id  WP User ID of a student
	 * @param    array      $course_ids  array of WP Post IDs for the courses
	 * @param    integer    $perc        percentage of each course complete
	 *                                   percentage is based off the total number of lessons in the course
	 *                                   fractions will be rounded up
	 * @return   void
	 * @since    3.7.3
	 * @version  3.9.0
	 */
	protected function complete_courses_for_student( $student_id = 0, $course_ids = array(), $perc = 100 ) {

		$student = new LLMS_Student( $student_id );

		foreach ( $course_ids as $course_id ) {

			$course = llms_get_post( $course_id );

			// enroll the student if not already enrolled
			if ( ! $student->is_enrolled( $course_id ) ) {
				$student->enroll( $course_id );
			}

			$lessons = $course->get_lessons( 'ids' );
			$num_lessons = count( $lessons );
			$stop = 100 === $perc ? $num_lessons : round( ( $perc / 100 ) * $num_lessons );

			foreach ( $lessons as $i => $lid ) {

				// stop once we reach the stopping point
				if ( $i > $stop ) {
					break;
				}

				$lesson = llms_get_post( $lid );
				if ( $lesson->has_quiz() ) {

					$attempt = LLMS_Quiz_Attempt::init( $lesson->get( 'assigned_quiz' ), $lid, $student->get_id() )->save()->start();
					while ( $attempt->get_next_question() ) {

						$question_id = $attempt->get_next_question();
						$question = llms_get_post( $question_id );
						$options = $question->get_options();
						$attempt->answer_question( $question_id, rand( 0, ( count( $options ) - 1 ) ) );

					}

					$attempt->end();

				} else {
					$student->mark_complete( $lid, 'lesson' );
				}


			}

		}
	}

	/**
	 * Generates a set of mock courses
	 * @param    integer    $num_courses   number of courses to generate
	 * @param    integer    $num_sections  number of sections to generate for each course
	 * @param    integer    $num_lessons   number of lessons to generate for each section
	 * @param    integer    $num_quizzes   number of quizzes to generate for each section
	 *                                     quizzes will be attached to the last lessons ie each section
	 *                                     if you generate 3 lessons / section and 1 quiz / section the quiz
	 *                                     will always be the 3rd lesson
	 * @return   array 					   indexed array of course ids
	 * @since    3.7.3
	 * @version  3.7.3
	 */
	protected function generate_mock_courses( $num_courses = 1, $num_sections = 5, $num_lessons = 5, $num_quizzes = 1, $num_questions = 5 ) {

		$courses = array();
		$i = 1;
		while ( $i <= $num_courses ) {
			$courses[] = $this->get_mock_course_array( $i, $num_sections, $num_lessons, $num_quizzes, $num_questions );
			$i++;
		}

		$gen = new LLMS_Generator( array( 'courses' => $courses ) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->set_default_post_status( 'publish' );
		$gen->generate();
		if ( ! $gen->is_error() ) {
			return $gen->get_generated_courses();
		}

	}

	/**
	 * Generates an array of course data which can be passed to a Generator
	 * @param    int     $iterator      number for use as course number
	 * @param    int     $num_sections  number of sections to generate for the course
	 * @param    int     $num_lessons   number of lessons for each section in the course
	 * @param    int     $num_quizzes   number of quizzes for each section in the course
	 * @return   array
	 * @since    3.7.3
	 * @version  3.9.2
	 */
	private function get_mock_course_array( $iterator, $num_sections, $num_lessons, $num_quizzes, $num_questions ) {

		$mock = array(
			'title' => sprintf( 'mock course %d', $iterator ),
		);

		$sections = array();
		$sections_i = 1;
		while ( $sections_i <= $num_sections ) {

			$section = array(
				'title' => sprintf( 'mock section %d', $sections_i ),
				'lessons' => array(),
			);

			$lessons_i = 1;

			$quizzes_start_i = $num_lessons - $num_quizzes + 1;

			while ( $lessons_i <= $num_lessons ) {

				$lesson = array(
					'title' => sprintf( 'mock lesson %d', $lessons_i ),
				);

				if ( $lessons_i >= $quizzes_start_i ) {

					$lesson['assigned_quiz'] = array(
						'title' => sprintf( 'mock quiz %d', $lessons_i ),
					);

					$questions = array();
					$questions_i = 1;
					while ( $questions_i <= $num_questions ) {

						$options_i = 1;
						$total_options = rand( 2, 5 );
						$correct_option = rand( $options_i, $total_options );
						$options = array();
						while( $options_i <= $total_options ) {
							$options[] = array(
								'option_text' => sprintf( 'option %d', $options_i ),
								'correct_option' => ( $options_i === $correct_option ),
							);
							$options_i++;
						}
						$questions[] = array(
							'title' => sprintf( 'question %d', $questions_i ),
							'type' => 'single_choice',
							'options' => $options,
						);

						$questions_i++;

					}

					$lesson['assigned_quiz']['questions'] = $questions;

				}

				array_push( $section['lessons'], $lesson );
				$lessons_i++;
			}

			array_push( $sections, $section );

			$sections_i++;

		}

		$mock['sections'] = $sections;

		return $mock;

	}

	protected function get_mock_student() {
		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );
		return llms_get_student( $student_id );
	}

}
