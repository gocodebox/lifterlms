<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Handle background processing of average progress & average grade for LifterLMS Courses
 * This triggers a bg process which gets the current progress
 * of all students in a course
 *
 * Progress is queued for recalulation when
 * 		students enroll
 * 		students unenroll
 * 		sutendts complete lessons
 * @since    3.15.0
 * @version  3.15.0
 */
class LLMS_Processor_Course_Data extends LLMS_Abstract_Processor {

	/**
	 * Unique identifier for the processor
	 * @var  string
	 */
	protected $id = 'course_data';

	/**
	 * WP Cron Hook for scheduling the bg process
	 * @var  string
	 */
	private $schedule_hook = 'llms_calculate_course_data';

	/**
	 * Maximum number of students allowed in a course
	 * when enrollment is higher than this number
	 * throttling the calculations will be delayed
	 * @var  int
	 */
	private $throttle_max_students;

	/**
	 * When a calculation is throttled based on the number of students
	 * this will determine the frequency with which the query can run
	 * Should be time in seconds
	 * default is HOUR_IN_SECONDS * 4
	 * @var  int
	 */
	private $throttle_frequency;

	/**
	 * Called when queue is emptied and process is complete
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function complete() {

		parent::complete();
		$this->set_data( 'last_run', time() );

	}

	/**
	 * Action triggered to queue queries needed to make the calculation
	 * @param    int     $course_id  WP Post ID of the course
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function dispatch_calc( $course_id ) {

		$this->log( sprintf( 'course data calculation dispatched for course %d', $course_id ) );

		// cancel process in case it's currently running
		$this->cancel_process();

		$args = array(
			'post_id' => $course_id,
			'statuses' => array( 'enrolled' ),
			'page' => 1,
			'per_page' => 100,
		);

		// get total number of pages
		$query = new LLMS_Student_Query( $args );

		// only queue if we have students in the course
		if ( $query->found_results ) {

			// throttle dispatch?
			if ( $this->maybe_throttle( $query->found_results ) ) {

				// schedule to run again in the future
				$last_run = $this->get_data( 'last_run', 0 );
				$this->schedule_calculation( $course_id, $last_run + $this->throttle_frequency );

				$this->log( sprintf( 'course data calculation throttled for course %d', $course_id ) );
				return;

			}

			// add each page to the queue
			while ( $args['page'] <= $query->max_pages ) {

				$this->push_to_queue( $args );
				$args['page']++;

			}

			// save queue and dispatch the process
			$this->save()->dispatch();

			$this->log( sprintf( 'course data calculation started for course %d', $course_id ) );

		}

	}

	/**
	 * Initializer
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function init() {

		// for the cron
		add_action( $this->schedule_hook, array( $this, 'dispatch_calc' ), 10, 1 );

		// for LifterLMS actions which trigger recalculation
		$this->actions = array(
			'llms_course_calculate_data' => array(
				'arguments' => 1,
				'callback' => 'schedule_calculation',
				'priority' => 10,
			),
			'llms_user_enrolled_in_course' => array(
				'arguments' => 2,
				'callback' => 'schedule_from_course',
				'priority' => 10,
			),
			'llms_user_removed_from_course' => array(
				'arguments' => 2,
				'callback' => 'schedule_from_course',
				'priority' => 10,
			),
			'lifterlms_lesson_completed' => array(
				'arguments' => 2,
				'callback' => 'schedule_from_lesson',
				'priority' => 10,
			),
			'lifterlms_quiz_completed' => array(
				'arguments' => 3,
				'callback' => 'schedule_from_quiz',
				'priority' => 10,
			),
		);

		// setup throttle vars
		$this->throttle_max_students = apply_filters( 'llms_data_processor_' . $this->id . '_throttle_count', 2500, $this );
		$this->throttle_frequency = apply_filters( 'llms_data_processor_' . $this->id . '_throttle_frequency', HOUR_IN_SECONDS * 4, $this );

	}

	/**
	 * For large courses, only recalculate once every 4 hours
	 * @param    int    $num_students  number of students in the current course
	 * @return   boolean               true = throttle the current dispatch
	 *                                 false = run the current dispatch
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function maybe_throttle( $num_students = 0 ) {

		// if we have more students in the course than the max allowed
		// we will only process this query once every four hours
		if ( $num_students >= $this->throttle_max_students ) {

			$last_run = $this->get_data( 'last_run' );

			return ( ( time() - $last_run ) <= $this->throttle_frequency );

		}

		return false;

	}

	/**
	 * Schedule recalculation from actions triggered against a course
	 * @param    int     $user_id    WP user id of the student
	 * @param    int     $course_id  WP Post ID of the course
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function schedule_from_course( $user_id, $course_id ) {
		$this->schedule_calculation( $course_id );
	}

	/**
	 * Schedule recalculation from actions triggered against a lesson
	 * @param    int     $user_id    WP user id of the student
	 * @param    int     $lesson_id  WP Post ID of the lesson
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function schedule_from_lesson( $user_id, $lesson_id ) {
		$lesson = llms_get_post( $lesson_id );
		$this->schedule_calculation( $lesson->get( 'parent_course' ) );
	}

	/**
	 * Schedule recalculation from actions triggered against a quiz
	 * @param    int     $user_id  WP user id of the student
	 * @param    int     $quiz_id  WP Post ID of the quiz
	 * @param    obj     $attempt  LLMS_Quiz_Attempt object
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function schedule_from_quiz( $user_id, $quiz_id, $attempt ) {
		$this->schedule_from_lesson( $user_id, $attempt->get( 'lesson_id' ) );
	}

	/**
	 * Schedule a calculation to execute
	 * This will schedule an event that will setup the queue of items for the background process
	 * @param    int     $course_id  WP Post ID of the course
	 * @param    int     $time       optionally pass a timestamp for when the event should be run
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function schedule_calculation( $course_id, $time = null ) {

		$this->log( sprintf( 'course data calculation triggered for course %d', $course_id ) );

		$args = array( $course_id );

		if ( ! wp_next_scheduled( $this->schedule_hook, $args ) ) {

			$time = ! $time ? time() : $time;

			wp_schedule_single_event( $time, $this->schedule_hook, $args );
			$this->log( sprintf( 'course data calculation scheduled for course %d', $course_id ) );

		}

	}


	/**
	 * Execute calculation for each item in the queue until all students
	 * in the course have been polled
	 * Stores the data in the postmeta table to be accessilbe via LLMS_Course
	 * @param    array     $args  query arguments passed to LLMS_Student_Query
	 * @return   boolean      	  true to keep the item in the queue and process again
	 *                            false to remove the item from the queue
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function task( $args ) {

		$course_id = $args['post_id'];

		$this->log( sprintf( 'course data calculation task called for course %d (args below)', $course_id ) );
		$this->log( $args );

		$course = llms_get_post( $course_id );

		// get saved data or empty array when on first page
		$data = ( 1 !== $args['page'] ) ? $course->get( 'temp_calc_data' ) : array();

		// merge with the defaults
		$data = wp_parse_args( $data, array(
			'students' => 0,
			'progress' => 0,
			'quizzes' => 0,
			'grade' => 0,
		) );

		$query = new LLMS_Student_Query( $args );

		foreach ( $query->get_students() as $student ) {

			// progress, all students counted here
			$data['students']++;
			$data['progress'] = $data['progress'] + $student->get_progress( $course_id );

			// grades only counted when a student has taken a quiz
			// if a student hasn't taken it, we don't count it as a 0 on the quiz
			$grade = $student->get_grade( $course_id );

			// only check actual quiz grades
			if ( is_numeric( $grade ) ) {
				$data['quizzes']++;
				$data['grade'] = $data['grade'] + $grade;
			}
		}

		$this->log( $data );

		// save our work on the last run
		if ( $query->max_pages === $query->get( 'page' ) ) {

			// calculate
			$grade = $data['quizzes'] ? round( $data['grade'] / $data['quizzes'], 2 ) : 0;
			$progress = $data['students'] ? round( $data['progress'] / $data['students'], 2 ) : 0;

			// save the data to the course
			$course->set( 'average_grade', $grade );
			$course->set( 'average_progress', $progress );

			// delete the temporary data so its fresh for next time
			delete_post_meta( $query->get( 'post_id' ), '_llms_temp_calc_data' );

			$this->log( sprintf( 'course data calculation completed for course %d', $course_id ) );

		} // End if().
		else {

			$course->set( 'temp_calc_data', $data );

		}

		return false;

	}

}

return new LLMS_Processor_Course_Data();
