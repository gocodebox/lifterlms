<?php
/**
 * Notification Controller: Course Track Complete
 *
 * @since 3.8.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Course Track Complete
 *
 * @since 3.8.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Notification_Controller_Course_Track_Complete extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 *
	 * @var  [type]
	 */
	public $id = 'course_track_complete';

	/**
	 * Number of accepted arguments passed to the callback function
	 *
	 * @var  integer
	 */
	protected $action_accepted_args = 2;

	/**
	 * Action hooks used to trigger sending of the notification
	 *
	 * @var  array
	 */
	protected $action_hooks = array( 'lifterlms_course_track_completed' );

	/**
	 * @var LLMS_Track
	 * @since 3.8.0
	 */
	public $track;

	/**
	 * Callback function called when a course track is completed by a student
	 *
	 * @param    int $student_id  WP User ID of a LifterLMS Student
	 * @param    int $course_track_id   WP Post ID of a LifterLMS Course
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function action_callback( $student_id = null, $course_track_id = null ) {

		$this->user_id = $student_id;
		$this->post_id = $course_track_id;
		$this->track   = new LLMS_Track( $course_track_id );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 *
	 * @param    string $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'student':
				$uid = $this->user_id;
				break;

			default:
				$uid = false;

		}

		return $uid;

	}

	/**
	 * Get the translatable title for the notification
	 * used on settings screens
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_title() {
		return __( 'Course Track Complete', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 *
	 * @param    string $type  notification type id
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'basic':
				$options[] = $this->get_subscriber_option_array( 'student', 'yes' );
				break;

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
				break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Course_Track_Complete::instance();
