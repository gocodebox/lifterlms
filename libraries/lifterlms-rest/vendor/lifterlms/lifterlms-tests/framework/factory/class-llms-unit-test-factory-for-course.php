<?php

/**
 * Unit test factory for courses.
 *
 * Note: The below `@method` notations are defined solely for the benefit of IDEs,
 * as a way to indicate expected return values from the given factory methods.
 *
 * @method LLMS_Course create_and_get( $args = array(), $generation_definitions = null )
 */
class LLMS_Unit_Test_Factory_For_Course extends WP_UnitTest_Factory_For_Post {

	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'status'  => 'publish',
			'title'   => new WP_UnitTest_Generator_Sequence( 'Course title %s' ),
			'content' => new WP_UnitTest_Generator_Sequence( 'Course content %s' ),
			'excerpt' => new WP_UnitTest_Generator_Sequence( 'Course excerpt %s' ),

			'sections' => 2,
			'lessons' => 5,
			'quizzes' => 1,
			'questions' => 5,

		);
	}

	public function create_object( $args ) {

		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

		$gen = new LLMS_Generator( array(
			'courses' => array( $this->get_structure( $args ) )
		) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->set_default_post_status( $args['status'] );
		$gen->generate();

		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

		return $gen->get_generated_courses()[0];
	}

	public function get_object_by_id( $post_id ) {
		return llms_get_post( $post_id );
	}

	/**
	 * Retrieve the structure of a course for use in a LLMS Generator
	 * @param   array    $args creation args.
	 * @return  array
	 */
	public function get_structure( $args ) {

		$course = $args;

		foreach ( array( 'sections', 'lessons', 'quizzes', 'questions' ) as $part ) {
			$var_name = 'num_' . $part;
			$$var_name = $args[ $part ];
			unset( $course[ $part ] );
		}

		$sections = array();
		$sections_i = 1;
		while ( $sections_i <= $num_sections ) {

			$section = array(
				'title' => sprintf( 'Section %d', $sections_i ),
				'lessons' => array(),
			);

			$lessons_i = 1;

			$quizzes_start_i = $num_lessons - $num_quizzes + 1;

			while ( $lessons_i <= $num_lessons ) {

				$lesson = array(
					'title' => sprintf( 'Lesson %d', $lessons_i ),
				);

				if ( $lessons_i >= $quizzes_start_i ) {

					$lesson['quiz_enabled'] = 'yes';
					$lesson['quiz'] = array(
						'title' => sprintf( 'Quiz %d', $lessons_i ),
					);

					$questions = array();
					$questions_i = 1;
					while ( $questions_i <= $num_questions ) {

						$options_i = 1;
						$total_options = rand( 2, 5 );
						$correct_option = rand( $options_i, $total_options );
						$choices = array();
						while( $options_i <= $total_options ) {
							$choices[] = array(
								'choice' => sprintf( 'Choice %d', $options_i ),
								'choice_type' => 'text',
								'correct' => ( $options_i === $correct_option ),
							);
							$options_i++;
						}
						$questions[] = array(
							'title' => sprintf( 'Question %d', $questions_i ),
							'question_type' => 'choice',
							'choices' => $choices,
							'points' => 1,
						);

						$questions_i++;

					}

					$lesson['quiz']['questions'] = $questions;

				}

				array_push( $section['lessons'], $lesson );
				$lessons_i++;
			}

			array_push( $sections, $section );
			$sections_i++;

		}

		$course['sections'] = $sections;
		return $course;

	}

}
