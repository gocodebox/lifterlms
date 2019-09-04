<?php
defined( 'ABSPATH' ) || exit;

/**
 * Get & Set grades for gradable post types
 *
 * @since    3.24.0
 * @version  3.24.0
 */
class LLMS_Grades {

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $_instance = null;

	/**
	 * Determines the rounding precision used by grading functions
	 *
	 * @var  int
	 */
	private $rounding_precision = 2;

	/**
	 * Get Main Singleton Instance
	 *
	 * @return   LLMS_Grades
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

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
	 * @param    array $children  list of child objects
	 * @param    obj   $student   LLMS_Student
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	private function calculate_grade_from_children( $children, $student ) {

		$grade  = null;
		$grades = array();

		// loop through all the children and compile the overall grade & points data
		foreach ( $children as $child_id ) {

			$child = llms_get_post( $child_id );
			$grade = $this->get_grade( $child_id, $student, false );

			// non numeric grade (null) hasn't been taken yet or no gradable elements exist on the child
			if ( ! is_numeric( $grade ) ) {
				continue;
			}

			$points = $child->get( 'points' );

			// if no points assigned to the child, the grade doesn't count towards the overall grade
			if ( ! $points ) {
				continue;
			}

			// add the grade & points for further processing after we have all the data
			$grades[] = array(
				'grade'  => $grade,
				'points' => $points,
			);

		}

		// if we have at least one grade
		if ( count( $grades ) ) {

			// get the total available points for all children with a numeric grade & a points value
			$total_points = array_sum( wp_list_pluck( $grades, 'points' ) );

			// if we don't have any points this element can't have an overall grade
			if ( $total_points ) {

				// sum up the adjusted grade
				$grade = 0;
				foreach ( $grades as $data ) {
					// calculate the adjusted the grade
					// grade multiplied by available points over total points
					$grade += $data['grade'] * ( $data['points'] / $total_points );
				}
			}
		}

		return $grade;

	}

	/**
	 * Calculate the grade for a course
	 *
	 * @param    obj $course   LLMS_Course
	 * @param    obj $student  LLMS_Student
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
	 * @param    obj $post     LLMS_Post_Model
	 * @param    obj $student  LLMS_Student
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function calculate_grade( $post, $student ) {

		$grade = null;

		$post_type = $post->get( 'type' );
		switch ( $post_type ) {

			case 'course':
				$grade = $this->calculate_course_grade( $post, $student );
				break;

			case 'lesson':
				$grade = $this->calculate_lesson_grade( $post, $student );
				break;

			case 'llms_quiz':
				$attempt = $student->quizzes()->get_best_attempt( $post->get( 'id' ) );
				if ( $attempt ) {
					$grade = $attempt->get( 'grade' );
				}

				break;

			// 3rd party / custom element grading
			default:
				$grade = apply_filters( 'llms_calculate_' . $post_type . '_grade', $grade, $post, $student );

		}

		// round numeric results
		if ( is_numeric( $grade ) ) {
			$grade = $this->round( $grade );
		}

		return apply_filters( 'llms_calculate_grade', $grade, $post, $student );

	}

	/**
	 * Calculates the grade for a lesson
	 *
	 * @param    obj $lesson   LLMS_Lesson
	 * @param    obj $student  LLMS_Student
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
	 * Uses caching by default and can bypass cache when requested
	 *
	 * @param    obj  $post       LLMS_Post_Model
	 * @param    obj  $student    LLMS_Student
	 * @param    bool $use_cache  when true, retrieves from cache if available
	 * @return   float|null
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function get_grade( $post, $student, $use_cache = true ) {

		$post    = llms_get_post( $post );
		$student = llms_get_student( $student );

		$grade = $use_cache ? $this->get_grade_from_cache( $post, $student ) : false;

		// grade not found in cache or we're not using the cache
		if ( false === $grade ) {

			$grade = $this->calculate_grade( $post, $student, $use_cache );

			// store in the cache
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
	 * @param    obj $post     LLMS_Post_Model
	 * @param    obj $student  LLMS_Student
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
