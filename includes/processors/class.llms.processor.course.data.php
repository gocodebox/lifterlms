<?php
/**
 * Processor: Course Data
 *
 * @package LifterLMS/Processors/Classes
 *
 * @since 3.15.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Processor_Course_Data
 *
 * Handle background processing of average progress & average grade for courses.
 *
 * The background process calculates "expensive" aggregate course data and stores them
 * on the `wp_postmeta` table so the data can be access later with a single
 * database read.
 *
 * The process is queued for recalculation when:
 *
 *   + Students enroll.
 *   + Students unenroll.
 *   + Students complete lessons.
 *   + Students complete quizzes.
 *
 * Upon completion, the following values can be accessed via the `LLMS_Course` model
 * to retrieve the aggregate data for the course:
 *
 *   + Average grade: `LLMS_Course::get( 'average_grade' )`
 *   + Average progress: `LLMS_Course::get( 'average_progress' )`
 *   + Number of currently enrolled students: `LLMS_Course::get( 'enrolled_students' )`
 *
 * @since 3.15.0
 * @since 4.12.0 Remove (protected) method `LLMS_Processor_Course_Data::complete()`, the override of the parent method is no longer needed.
 */
class LLMS_Processor_Course_Data extends LLMS_Abstract_Processor {

	/**
	 * Unique identifier for the processor
	 *
	 * @var string
	 */
	protected $id = 'course_data';

	/**
	 * WP Cron Hook for scheduling the bg process
	 *
	 * @var string
	 */
	private $schedule_hook = 'llms_calculate_course_data';

	/**
	 * Maximum number of students allowed in a course
	 *
	 * When enrollment is higher than this number
	 * throttling the calculations will be delayed.
	 *
	 * @var int
	 */
	private $throttle_max_students;

	/**
	 * Frequency of calculation process when the process is throttled.
	 *
	 * @var int
	 */
	private $throttle_frequency;

	/**
	 * Action triggered to queue queries needed to make the calculation
	 *
	 * @since 3.15.0
	 * @since 4.12.0 Add throttling by course in progress and adjust last_run calculation to be specific to the course.
	 *               Improve performance of the student query by removing unneeded sort columns.
	 * @since 4.21.0 When there's no students found in the course, run the `task_complete()` method to ensure data
	 *               from a previous calculation is cleared.
	 * @since 6.0.0 Don't access `LLMS_Student_Query` properties directly.
	 *
	 * @param int $course_id WP Post ID of the course.
	 * @return void|null
	 */
	public function dispatch_calc( $course_id ) {

		$this->log( sprintf( 'Course data calculation dispatched for course %d.', $course_id ) );

		// Make sure we have a course.
		$course = llms_get_post( $course_id );
		if ( ! $course instanceof LLMS_Course ) {
			return null;
		}

		// Return early if we're already processing data for the given course.
		if ( $this->is_already_processing_course( $course_id ) ) {
			return $this->dispatch_calc_throttled( $course_id );
		}

		// Retrieve args.
		$args = $this->get_student_query_args( $course_id );

		// Get total number of pages.
		$query = new LLMS_Student_Query( $args );

		// No students in the course, run task completion.
		if ( ! $query->get_found_results() ) {
			return $this->task_complete( $course, $this->get_task_data(), true );
		}

		// Store the total number of students right away.
		$course->set( 'enrolled_students', $query->get_found_results() );

		// Throttle processing.
		if ( $this->maybe_throttle( $query->get_found_results(), $course_id ) ) {
			return $this->dispatch_calc_throttled( $course_id );
		}

		// Add each page to the queue.
		while ( $args['page'] <= $query->get_max_pages() ) {
			$this->push_to_queue( $args );
			$args['page']++;
		}

		// Save queue and dispatch the process.
		$this->save()->dispatch();

	}

	/**
	 * Schedule data calculation for the future
	 *
	 * This method is called when data processing is triggered for a course that is currently being processed
	 * or for a course that qualifies for process throttling based on the number of students in the course.
	 *
	 * @since 4.12.0
	 *
	 * @param int $course_id WP_Post ID of the course.
	 * @return void
	 */
	protected function dispatch_calc_throttled( $course_id ) {

		$this->schedule_calculation( $course_id, $this->get_last_run( $course_id ) + $this->throttle_frequency );
		$this->log( sprintf( 'Course data calculation throttled for course %d.', $course_id ) );

	}

	/**
	 * Retrieve arguments used to perform an LLMS_Student_Query for background data processing
	 *
	 * @since 4.12.0
	 *
	 * @param int $course_id WP_Post ID of the course.
	 * @return array Array of arguments passed to an LLMS_Student_Query.
	 */
	protected function get_student_query_args( $course_id ) {

		/**
		 * Filter the query arguments used when calculating course data
		 *
		 * @since 4.12.0
		 *
		 * @param array                      $args      Query arguments passed to LLMS_Student_Query.
		 * @param LLMS_Processor_Course_Data $processor Instance of the data processor class.
		 */
		return apply_filters(
			'llms_data_processor_course_data_student_query_args',
			array(
				'post_id'  => $course_id,
				'statuses' => array( 'enrolled' ),
				'page'     => 1,
				'per_page' => 100,
				'sort'     => array(
					'id' => 'ASC',
				),
			),
			$this
		);

	}

	/**
	 * Retrieve a timestamp for the last time data calculation was completed for a given course
	 *
	 * @since 4.12.0
	 *
	 * @param int $course_id WP_Post ID of the course.
	 * @return int The timestamp of the last run. Returns `0` when no data recorded.
	 */
	protected function get_last_run( $course_id ) {
		return absint( get_post_meta( $course_id, '_llms_last_data_calc_run', true ) );
	}

	/**
	 * Retrieve structured task data array.
	 *
	 * Ensures the expected required array keys are found on the task array
	 * and optionally merges in an existing array of day with the (empty) defaults.
	 *
	 * @since 4.21.0
	 *
	 * @param array $data Existing array of day (from a previous task).
	 * @return array
	 */
	protected function get_task_data( $data = array() ) {
		return wp_parse_args(
			$data,
			array(
				'students' => 0,
				'progress' => 0,
				'quizzes'  => 0,
				'grade'    => 0,
			)
		);
	}

	/**
	 * Initializer
	 *
	 * @since 3.15.0
	 *
	 * @return void
	 */
	protected function init() {

		// For the cron.
		add_action( $this->schedule_hook, array( $this, 'dispatch_calc' ), 10, 1 );

		// For LifterLMS actions which trigger recalculation.
		$this->actions = array(
			'llms_course_calculate_data'    => array(
				'arguments' => 1,
				'callback'  => 'schedule_calculation',
				'priority'  => 10,
			),
			'llms_user_enrolled_in_course'  => array(
				'arguments' => 2,
				'callback'  => 'schedule_from_course',
				'priority'  => 10,
			),
			'llms_user_removed_from_course' => array(
				'arguments' => 2,
				'callback'  => 'schedule_from_course',
				'priority'  => 10,
			),
			'lifterlms_lesson_completed'    => array(
				'arguments' => 2,
				'callback'  => 'schedule_from_lesson',
				'priority'  => 10,
			),
			'lifterlms_quiz_completed'      => array(
				'arguments' => 3,
				'callback'  => 'schedule_from_quiz',
				'priority'  => 10,
			),
		);

		/**
		 * Throttles course data processing based on the number of a students in a course.
		 *
		 * If the number of students in a course is greater than or equal to this number, the background
		 * process will be throttled to run only once every N hours where N is equal to the number of hours
		 * defined by the `llms_data_processor_course_data_throttle_frequency` filter.
		 *
		 * @since 3.15.0
		 * @since 4.12.0 Reduced default value of `$number_students` from 2500 to 500.
		 *
		 * @see llms_data_processor_course_data_throttle_frequency
		 *
		 * @param int                        $number_students The number of students. Default is `500`.
		 * @param LLMS_Processor_Course_Data $processor       Instance of the data processor class.
		 */
		$this->throttle_max_students = apply_filters( 'llms_data_processor_course_data_throttle_count', 500, $this );

		/**
		 * Frequency to run the processor for a given course when processing is throttled
		 *
		 * @since 3.15.0
		 *
		 * @see llms_data_processor_course_data_throttle_count
		 *
		 * @param int                        $frequency Frequency of the calculation process in seconds. Default `HOUR_IN_SECONDS * 4`.
		 * @param LLMS_Processor_Course_Data $processor Instance of the data processor class.
		 */
		$this->throttle_frequency = apply_filters( 'llms_data_processor_course_data_throttle_frequency', HOUR_IN_SECONDS * 4, $this );

	}

	/**
	 * Determines if the supplied course is already being processed.
	 *
	 * If it's already being processed we'll throttle the processing so we'll wait until the course
	 * completes its current data processing and start again later.
	 *
	 * @since 4.12.0
	 *
	 * @param int $course_id WP_Post ID of the course.
	 * @return boolean
	 */
	protected function is_already_processing_course( $course_id ) {
		return llms_parse_bool( get_post_meta( $course_id, '_llms_temp_calc_data_lock', true ) );
	}

	/**
	 * For large courses, only recalculate once every 4 hours
	 *
	 * @since 3.15.0
	 * @since 4.12.0 Adjusted access from private to protected.
	 *               Pull last run data on a per-course basis.
	 *               Added parameter `$course_id`.
	 *
	 * @param int $num_students Number of students in the current course.
	 * @param int $course_id    WP_Post ID of the course.
	 * @return boolean When `true` the dispatch is throttled and when `false` it will run.
	 */
	protected function maybe_throttle( $num_students, $course_id ) {

		$throttled = false;

		if ( $num_students >= $this->throttle_max_students ) {

			$throttled = ( time() - $this->get_last_run( $course_id ) <= $this->throttle_frequency );

		}

		/**
		 * Filters whether or not data processing is throttled for a request
		 *
		 * @since 4.12.0
		 *
		 * @param boolean $throttled    If `true`, the processing for the current request is throttled, otherwise data processing will begin.
		 * @param int     $num_students Number of students in the current course.
		 * @param int     $course_id    WP_Post ID of the course.
		 * $param int     $max_students Maximum number of students in the course before processing is throttled.
		 */
		return apply_filters( 'llms_data_processor_course_data_throttled', $throttled, $num_students, $course_id, $this->throttle_max_students );

	}

	/**
	 * Schedule recalculation from actions triggered against a course
	 *
	 * @since 3.15.0
	 *
	 * @param int $user_id   WP user id of the student.
	 * @param int $course_id WP Post ID of the course.
	 * @return void
	 */
	public function schedule_from_course( $user_id, $course_id ) {
		$this->schedule_calculation( $course_id );
	}

	/**
	 * Schedule recalculation from actions triggered against a lesson
	 *
	 * @since 3.15.0
	 *
	 * @param int $user_id   WP user id of the student.
	 * @param int $lesson_id WP Post ID of the lesson.
	 * @return void
	 */
	public function schedule_from_lesson( $user_id, $lesson_id ) {
		$lesson = llms_get_post( $lesson_id );
		$this->schedule_calculation( $lesson->get( 'parent_course' ) );
	}

	/**
	 * Schedule recalculation from actions triggered against a quiz
	 *
	 * @since 3.15.0
	 *
	 * @param int               $user_id WP user id of the student.
	 * @param int               $quiz_id WP Post ID of the quiz.
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt object.
	 * @return void
	 */
	public function schedule_from_quiz( $user_id, $quiz_id, $attempt ) {
		$this->schedule_from_lesson( $user_id, $attempt->get( 'lesson_id' ) );
	}

	/**
	 * Schedule a calculation to execute
	 *
	 * This will schedule an event that will setup the queue of items for the background process.
	 *
	 * @since 3.15.0
	 * @since 4.21.0 Force `$course_id` to an absolute integer to avoid duplicate scheduling resulting from loose variable typing.
	 *
	 * @param int $course_id WP Post ID of the course.
	 * @param int $time      Optionally pass a timestamp for when the event should be run.
	 * @return void
	 */
	public function schedule_calculation( $course_id, $time = null ) {

		$course_id = absint( $course_id );

		$this->log( sprintf( 'Course data calculation triggered for course %d.', $course_id ) );

		$args = array( $course_id );

		if ( ! wp_next_scheduled( $this->schedule_hook, $args ) ) {

			$time = ! $time ? time() : $time;

			wp_schedule_single_event( $time, $this->schedule_hook, $args );
			$this->log( sprintf( 'Course data calculation scheduled for course %d.', $course_id ) );

		}

	}


	/**
	 * Execute calculation for each item in the queue until all students in the course have been polled
	 *
	 * Stores the data in the postmeta table to be accessible via LLMS_Course.
	 *
	 * @since 3.15.0
	 * @since 4.12.0 Moved task completion logic to `task_complete()`.
	 * @since 4.16.0 Fix log string to properly record the post_id.
	 * @since 4.21.0 Use `get_task_data()` to merge/retrieve aggregate task data.
	 *               Return early for non-courses.
	 *
	 * @param array $args Query arguments passed to LLMS_Student_Query.
	 * @return boolean Always returns `false` to remove the item from the queue when processing is complete.
	 */
	public function task( $args ) {

		$this->log( sprintf( 'Course data calculation task called for course %1$d with args: %2$s', $args['post_id'], wp_json_encode( $args ) ) );

		$course = llms_get_post( $args['post_id'] );

		// Only process existing courses.
		if ( ! $course instanceof LLMS_Course ) {
			$this->log( sprintf( 'Course data calculation task skipped for course %1$d.', $args['post_id'] ) );
			return false;
		}

		// Lock the course against duplicate processing.
		$course->set( 'temp_calc_data_lock', 'yes' );

		// Get saved data or empty array when on first page.
		$data = ( 1 !== $args['page'] ) ? $course->get( 'temp_calc_data' ) : array();

		// Merge with the defaults.
		$data = $this->get_task_data( $data );

		// Perform the query.
		$query = new LLMS_Student_Query( $args );

		foreach ( $query->get_students() as $student ) {

			// Progress, all students counted here.
			$data['students']++;
			$data['progress'] = $data['progress'] + $student->get_progress( $args['post_id'] );

			// Grades only counted when a student has taken a quiz.
			// If a student hasn't taken it, we don't count it as a 0 on the quiz.
			$grade = $student->get_grade( $args['post_id'] );

			// Only check actual quiz grades.
			if ( is_numeric( $grade ) ) {
				$data['quizzes']++;
				$data['grade'] = $data['grade'] + $grade;
			}
		}

		return $this->task_complete( $course, $data, $query->is_last_page() );

	}

	/**
	 * Complete a task
	 *
	 * Stores the current (incomplete) array of course data on the postmeta table for use
	 * by the next task in the queue.
	 *
	 * Upon completion, uses the data array to calculate the final aggregate values and store
	 * them on the postmeta table for the course for quick retrieval later.
	 *
	 * @since 4.12.0
	 * @since 4.16.0 Fix log string to properly log the course id.
	 *
	 * @param LLMS_Course $course    Course object.
	 * @param array       $data      Aggregate calculation data array.
	 * @param boolean     $last_page Whether or not this is the last page set of students for the process.
	 * @return boolean Always returns false.
	 */
	protected function task_complete( $course, $data, $last_page ) {

		$this->log( sprintf( 'Course data calculation task completed for course %1$d with data: %2$s', $course->get( 'id' ), wp_json_encode( $data ) ) );

		// Save our work on the last run.
		if ( $last_page ) {

			// Calculate.
			$grade    = $data['quizzes'] ? round( $data['grade'] / $data['quizzes'], 2 ) : 0;
			$progress = $data['students'] ? round( $data['progress'] / $data['students'], 2 ) : 0;

			// Save the data to the course.
			$course->set( 'average_grade', $grade );
			$course->set( 'average_progress', $progress );
			$course->set( 'enrolled_students', $data['students'] );
			$course->set( 'last_data_calc_run', time() );

			// Delete the temporary data so its fresh for next time.
			delete_post_meta( $course->get( 'id' ), '_llms_temp_calc_data' );

			// Unlock the course.
			delete_post_meta( $course->get( 'id' ), '_llms_temp_calc_data_lock' );

			$this->log( sprintf( 'Course data calculation completed for course %d.', $course->get( 'id' ) ) );

		} else {

			// Save temporary data so it can be used by the next run in the process.
			$course->set( 'temp_calc_data', $data );

		}

		return false;

	}

}

return new LLMS_Processor_Course_Data();
