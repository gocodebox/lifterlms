<?php
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
 * @version  3.26.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Processor_Membership_Bulk_Enroll class
 */
class LLMS_Processor_Membership_Bulk_Enroll extends LLMS_Abstract_Processor {

	/**
	 * Unique identifier for the processor
	 * @var  string
	 */
	protected $id = 'membership_bulk_enroll';

	/**
	 * WP Cron Hook for scheduling the bg process
	 * @var  string
	 */
	private $schedule_hook = 'llms_membership_bulk_enroll';

	/**
	 * Action triggered to queue all students who need to be enrolled
	 * @param    int     $membership_id  WP Post ID of the membership
	 * @param    int     $course_id      WP Post ID of the course to enroll members into
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function dispatch_enrollment( $membership_id, $course_id ) {

		$this->log( sprintf( 'membership bulk enrollment dispatched for membership %1$d into course %2$d', $membership_id, $course_id ) );

		// cancel process in case it's currently running
		$this->cancel_process();

		$args = array(
			'post_id' => $membership_id,
			'statuses' => 'enrolled',
			'page' => 1,
			'per_page' => 250,
		);

		$query = new LLMS_Student_Query( $args );

		if ( $query->found_results ) {

			while ( $args['page'] <= $query->max_pages ) {

				$this->push_to_queue( array(
					'course_id' => $course_id,
					'query_args' => $args,
					'trigger' => sprintf( 'membership_%d', $membership_id ),
				) );

				$args['page']++;

			}

			$this->save()->dispatch();

			$this->log( sprintf( 'membership bulk enrollment started for membership %1$d into course %2$d', $membership_id, $course_id ) );

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
		add_action( $this->schedule_hook, array( $this, 'dispatch_enrollment' ), 10, 2 );

		// for LifterLMS actions which trigger bulk enrollment
		$this->actions = array(
			'llms_membership_do_bulk_course_enrollment' => array(
				'arguments' => 2,
				'callback' => 'schedule_enrollment',
				'priority' => 10,
			),
		);

	}

	/**
	 * Schedule bulk enrollment
	 * This will schedule an event that will setup the queue of items for the background process
	 * @param    int     $membership_id  WP Post ID of the membership
	 * @param    int     $course_id      WP Post ID of the course to enroll members into
	 * @return   void
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function schedule_enrollment( $membership_id, $course_id ) {

		$this->log( sprintf( 'membership bulk enrollment triggered for membership %1$d into course %2$d', $membership_id, $course_id ) );

		$args = array( $membership_id, $course_id );

		if ( ! wp_next_scheduled( $this->schedule_hook, $args ) ) {

			wp_schedule_single_event( time(), $this->schedule_hook, $args );
			$this->log( sprintf( 'membership bulk enrollment scheduled for membership %1$d into course %2$d', $membership_id, $course_id ) );

		}

	}

	/**
	 * Execute calculation for each item in the queue until all students
	 * in the course have been polled
	 * Stores the data in the postmeta table to be accessilbe via LLMS_Course
	 * @param    array     $item  array of processing data
	 * @return   boolean      	  true to keep the item in the queue and process again
	 *                            false to remove the item from the queue
	 * @since    3.15.0
	 * @version  3.26.1
	 */
	public function task( $item ) {

		$this->log( sprintf( 'membership bulk enrollment task started for membership %1$d into course %2$d', $item['query_args']['post_id'], $item['course_id'] ) );
		$this->log( $item );

		// ensure the item has all the data we need to process it
		if ( ! is_array( $item ) || ! isset( $item['course_id'] ) || ! isset( $item['query_args'] ) || ! isset( $item['trigger'] ) ) {
			return false;
		}

		// turn the course data processor off
		$course_data_processor = LLMS()->processors()->get( 'course_data' );
		if ( $course_data_processor ) {
			$course_data_processor->disable();
		}

		$query = new LLMS_Student_Query( $item['query_args'] );

		if ( $query->found_results ) {
			foreach ( $query->get_students() as $student ) {
				$student->enroll( $item['course_id'], $item['trigger'] );
			}
		}

		if ( $query->is_last_page() ) {

			$this->log( sprintf( 'membership bulk enrollment completed for membership %1$d into course %2$d', $item['query_args']['post_id'], $item['course_id'] ) );

			// turn the course data processor back on
			if ( $course_data_processor ) {
				$course_data_processor->add_actions();
			}

			// process the course data
			do_action( 'llms_course_calculate_data', $item['course_id'] );

		}

		return false;

	}

}

return new LLMS_Processor_Membership_Bulk_Enroll();
