<?php

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
	protected $action_accepted_arguments = 2;

	/**
	 * Action hook used to trigger sending of the notification
	 * @var  string
	 */
	protected $action_hook = 'lifterlms_lesson_completed';

	/**
	 * Array of supported notification types
	 * @var  array
	 */
	protected $supported_types = array( 'basic', 'email' );

	/**
	 * Callback function called when a lesson is completed by a student
	 * @param    int     $student_id  WP User ID of a LifterLMS Student
	 * @param    int     $lesson_id   WP Post ID of a LifterLMS Lesson
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function action_callback( $student_id = null, $lesson_id = null ) {

		$this->user_id = $student_id;
		$this->post_id = $lesson_id;
		$this->lesson = llms_get_post( $lesson_id );

		// add a basic subscription
		$this->subscribe( $student_id, 'basic' );

		// add email subscription for the lesson author
		$this->subscribe( $this->lesson->get( 'author' ), 'email' );

		$this->send();

	}

	/**
	 * Get the translateable title for the notification
	 * used on settings screens
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_title() {
		return __( 'Lesson Complete', 'lifterlms' );
	}

}

return LLMS_Notification_Controller_Lesson_Complete::instance();
