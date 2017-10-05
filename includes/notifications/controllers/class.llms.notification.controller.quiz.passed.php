<?php
/**
 * Notification Controller: Quiz Passed
 * @since    3.8.0
 * @version  3.13.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Controller_Quiz_Passed extends LLMS_Abstract_Notification_Controller {

	/**
	 * Trigger Identifier
	 * @var  [type]
	 */
	public $id = 'quiz_passed';

	/**
	 * Number of accepted arguments passed to the callback function
	 * @var  integer
	 */
	protected $action_accepted_args = 2;

	/**
	 * Action hooks used to trigger sending of the notification
	 * @var  array
	 */
	protected $action_hooks = array( 'lifterlms_quiz_passed' );

	/**
	 * Callback function called when a quiz is passed by a student
	 * @param    int     $student_id  WP User ID of a LifterLMS Student
	 * @param    array   $quiz_data   WP Post ID of a LifterLMS quiz
	 * @return   void
	 * @since    3.8.0
	 * @version  3.13.1
	 */
	public function action_callback( $student_id = null, $quiz_data = null ) {

		$this->user_id = $student_id;
		$this->post_id = $quiz_data['id'];
		$this->quiz = new LLMS_Quiz( $quiz_data['id'] );
		$this->course = $this->quiz->get_course();

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
				if ( $this->course ) {
					$uid = $this->course->get( 'author' );
				} else {
					$uid = $this->quiz->post->post_author;
				}
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
		return __( 'Quiz Passed', 'lifterlms' );
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
				$options[] = $this->get_subscriber_option_array( 'course_author', 'no' );
				$options[] = $this->get_subscriber_option_array( 'custom', 'no' );
			break;

		}

		return $options;

	}

}

return LLMS_Notification_Controller_Quiz_Passed::instance();
