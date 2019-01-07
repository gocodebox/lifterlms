<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification Controller: Lesson Complete
 * @since    3.8.0
 * @version  3.8.0
 */
class LLMS_Notification_Controller_Lesson_Complete extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 * @var  [type]
	 */
	public $id = 'lesson_complete';

	/**
	 * Number of accepted arguments passed to the callback function
	 * @var  integer
	 */
	protected $action_accepted_args = 2;

	/**
	 * Action hooks used to trigger sending of the notification
	 * @var  array
	 */
	protected $action_hooks = array( 'lifterlms_lesson_completed' );

	/**
	 * Callback function called when a lesson is completed by a student
	 * @param    int     $student_id  WP User ID of a LifterLMS Student
	 * @param    int     $lesson_id   WP Post ID of a LifterLMS Lesson
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function action_callback( $student_id = null, $lesson_id = null ) {

		$this->user_id = $student_id;
		$this->post_id = $lesson_id;
		$this->lesson = llms_get_post( $lesson_id );
		$this->course = $this->lesson->get_course();

		$this->send();

	}

	/**
	 * Takes a subscriber type (student, author, etc) and retrieves a User ID
	 * @param    string     $subscriber  subscriber type string
	 * @return   int|false
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_subscriber( $subscriber ) {

		switch ( $subscriber ) {

			case 'course_author':
				$uid = $this->course->get( 'author' );
			break;

			case 'lesson_author':
				$uid = $this->lesson->get( 'author' );
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
	 * Get the translateable title for the notification
	 * used on settings screens
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_title() {
		return __( 'Lesson Complete', 'lifterlms' );
	}

	/**
	 * Setup the subscriber options for the notification
	 * @param    string     $type  notification type id
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
				$options[] = $this->get_subscriber_option_array( 'lesson_author', 'no' );
				$options[] = $this->get_subscriber_option_array( 'course_author', 'no' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
			break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Lesson_Complete::instance();
