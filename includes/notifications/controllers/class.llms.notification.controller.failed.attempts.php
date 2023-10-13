<?php
/**
 * Notification Controller: Failed Attempts
 *
 * @package LifterLMS/Notifications/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification Controller: Failed Attempts.
 */
class LLMS_Notification_Controller_Failed_Attempts extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier.
	 *
	 * @since [version]
	 * @var [type]
	 */
	public $id = 'failed_attempts';

	/**
	 * Number of accepted arguments passed to the callback function.
	 *
	 * @since [version]
	 * @var int
	 */
	protected $action_accepted_args = 2;

	/**
	 * Action hooks used to trigger sending of the notification.
	 *
	 * @since [version]
	 * @var array
	 */
	protected $action_hooks = array( 'lifterlms_failed_attempts' );

	/**
	 * Callback function called when a student failed in all quiz attempts.
	 *
	 * @param int $student_id WP User ID of a LifterLMS Student
	 * @param int $course_id  WP Post ID of a LifterLMS Course
	 * @param int $quiz_id    WP Post ID of a LifterLMS Quiz
	 *
	 * @since [version]
	 * @return void
	 */
	public function action_callback( $student_id = null, $course_id = null, $quiz_id = null ) {

		$this->user_id = $student_id;
		$this->post_id = $quiz_id;
		$this->course  = llms_get_post( $course_id );

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID.
	 *
	 * @since [version]
	 *
	 * @param string $subscriber Subscriber type string.
	 * @return int|false
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'course_author':
				$uid = $this->course->get( 'author' );
				break;

			case 'student':
				$uid = $this->user_id;
				break;

			default:
				$uid = false;

		}

		return $uid;

	}

	/**
	 * Get the translatable title for the notification used on settings screens.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Failed Attempts', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification.
	 *
	 * @since [version]
	 *
	 * @param string $type Notification type ID.
	 * @return array
	 */
	protected function set_subscriber_options( $type ) {

		$options = array();

		switch ( $type ) {

			case 'basic':
				$options[] = $this->get_subscriber_option_array( 'course_author', 'yes' );
				break;

			case 'email':
				$options[] = $this->get_subscriber_option_array( 'course_author', 'yes' );
				break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Failed_Attempts::instance();
