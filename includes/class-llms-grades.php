<?php
/**
 * Get & Set grades for gradable post types
 *
 * @package LifterLMS/Classes
 *
 * @since 3.24.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Grades
 *
 * @since 3.24.0
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Removed the deprecated `LLMS_Grades::$_instance` property.
 */
class LLMS_Grades {

	use LLMS_Trait_Singleton;

	/**
	 * Determines the rounding precision used by grading functions
	 *
	 * @var  int
	 */
	private $rounding_precision = 2;

	/**
	 * Private constructor
	 *
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	private function __construct() {

		$this->rounding_precision = apply_filters( 'llms_grade_rounding_precision', $this->rounding_precision );

	}

	/**
	 * Calculates the grades for elements that have a list of children which are averaged / weighted to come up with the total grade
	 *
	 * @param    array        $children list of child objects
	 * @param    LLMS_Student $student  A LLMS_Student object.
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	private function calculate_grade_from_children( $children, $student ) {

		$grade  = null;
		$grades = array();

		// Loop through all the children and compile the overall grade & points data.
		foreach ( $children as $child_id ) {

			$child = llms_get_post( $child_id );
			$grade = $this->get_grade( $child_id, $student, false );

			// Non numeric grade (null) hasn't been taken yet or no gradable elements exist on the child.
			if ( ! is_numeric( $grade ) ) {
				continue;
			}

			$points = $child->get( 'points' );

			// If no points assigned to the child, the grade doesn't count towards the overall grade.
			if ( ! $points ) {
				continue;
			}

			// Add the grade & points for further processing after we have all the data.
			$grades[] = array(
				'grade'  => $grade,
				'points' => $points,
			);

		}

		// If we have at least one grade.
		if ( count( $grades ) ) {

			// Get the total available points for all children with a numeric grade & a points value.
			$total_points = array_sum( wp_list_pluck( $grades, 'points' ) );

			// If we don't have any points this element can't have an overall grade.
			if ( $total_points ) {

				// Sum up the adjusted grade.
				$grade = 0;
				foreach ( $grades as $data ) {
					// Calculate the adjusted the grade.
					// Grade multiplied by available points over total points.
					$grade += $data['grade'] * ( $data['points'] / $total_points );
				}
			}
		}

		return $grade;

	}

	/**
	 * Calculate the grade for a course
	 *
	 * @param    LLMS_Course  $course  A LLMS_Course object.
	 * @param    LLMS_Student $student A LLMS_Student object.
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	private function calculate_course_grade( $course, $student ) {

		return apply_filters(
			'llms_calculate_course_grade',
			$this->calculate_grade_from_children( $course->get_lessons( 'ids' ), $student ),
			$course,
			$student
		);

	}

	/**
	 * Main grade calculation function
	 * Calculates the grade for a gradable post model
	 * DOES NOT CACHE RESULTS!
	 * See get_grade() for a function which uses caching
	 *
	 * @param    LLMS_Post_Model $post    A LLMS_Post_Model object.
	 * @param    LLMS_Student    $student A LLMS_Student object.
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function calculate_grade( $post, $student ) {

		$grade = null;

		$post_type = $post->get( 'type' );
		switch ( $post_type ) {

			case 'course':
				/** @var LLMS_Course $post */
				$grade = $this->calculate_course_grade( $post, $student );
				break;

			case 'lesson':
				/** @var LLMS_Lesson $post */
				$grade = $this->calculate_lesson_grade( $post, $student );
				break;

			case 'llms_quiz':
				$attempt = $student->quizzes()->get_best_attempt( $post->get( 'id' ) );
				if ( $attempt ) {
					$grade = $attempt->get( 'grade' );
				}

				break;

			// 3rd party / custom element grading.
			default:
				$grade = apply_filters( 'llms_calculate_' . $post_type . '_grade', $grade, $post, $student );

		}

		// Round numeric results.
		if ( is_numeric( $grade ) ) {
			$grade = $this->round( $grade );
		}

		return apply_filters( 'llms_calculate_grade', $grade, $post, $student );

	}

	/**
	 * Calculates the grade for a lesson
	 *
	 * @param    LLMS_Lesson  $lesson  A LLMS_Lesson object.
	 * @param    LLMS_Student $student A LLMS_Student object.
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	private function calculate_lesson_grade( $lesson, $student ) {

		$grade = null;

		if ( $lesson->is_quiz_enabled() ) {

			$grade = $this->get_grade( $lesson->get( 'quiz' ), $student, false );

		}

		return apply_filters( 'llms_calculate_lesson_grade', $grade, $lesson, $student );

	}

	/**
	 * Main grade getter function
	 *
	 * Uses caching by default and can bypass cache when requested
	 *
	 * @since 3.24.0
	 * @since 4.4.4 Don't pass the `$use_cache` parameter to the `calculate_grade()` method.
	 *
	 * @param    WP_Post|int  $post_id   An instance of WP_Post or a WP Post ID.
	 * @param    LLMS_Student $student   A LLMS_Student object.
	 * @param    bool         $use_cache when true, retrieves from cache if available
	 * @return   float|null
	 */
	public function get_grade( $post_id, $student, $use_cache = true ) {

		$post    = llms_get_post( $post_id );
		$student = llms_get_student( $student );

		$grade = $use_cache ? $this->get_grade_from_cache( $post, $student ) : false;

		// Grade not found in cache or we're not using the cache.
		if ( false === $grade ) {

			$grade = $this->calculate_grade( $post, $student );

			// Store in the cache.
			wp_cache_set(
				sprintf( '%d_grade', $post->get( 'id' ) ),
				$grade,
				sprintf( 'student_%d', $student->get( 'id' ) )
			);

		}

		return apply_filters( 'llms_get_grade', $grade, $post, $student );

	}

	/**
	 * Retrieve a grade from the wp_cache
	 *
	 * @param    LLMS_Post_Model $post    A LLMS_Post_Model object.
	 * @param    LLMS_Student    $student A LLMS_Student object.
	 * @return   mixed             grade as a float
	 *                             null if there's no grade for the post
	 *                             false if the grade wasn't found in the cache
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	private function get_grade_from_cache( $post, $student ) {

		return wp_cache_get(
			sprintf( '%d_grade', $post->get( 'id' ) ),
			sprintf( 'student_%d', $student->get( 'id' ) )
		);

	}

	/**
	 * Round grades according to filterable rounding options set during construction
	 *
	 * @param    float $grade  Grade to round
	 * @return   float
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function round( $grade ) {

		return round( $grade, $this->rounding_precision );

	}

}
