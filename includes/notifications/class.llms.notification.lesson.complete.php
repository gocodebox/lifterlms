<?php
/**
 * Notification for lesson completion
 * @since    ??
 * @version  ??
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_Lesson_Complete extends LLMS_Notification {

	/**
	 * Notification ID
	 * @var  string
	 */
	public $id = 'lesson_complete';

	/**
	 * Accepted numeber of arguments that the action callback will recieve
	 * @var  integer
	 */
	protected $accepted_args = 2;

	/**
	 * Action called to trigger this notification
	 * should be something triggered by do_action()
	 * @var  string
	 */
	protected $action = 'lifterlms_lesson_completed';

	/**
	 * Callback function for notifications
	 * Depending on the action that triggers this callback there will be a variable number of parameters
	 * @return   void
	 * @since    ??
	 * @version  ??
	 */
	public function callback( $student_id = null, $lesson_id = null ) {

		$this->student = new LLMS_Student( $student_id );
		$this->lesson = llms_get_post( $lesson_id );

		// add a basic subscription for the student
		$this->add_subscription( $student_id, 'basic' );

		$this->handle();

	}

	/**
	 * Determine if the subscriber is the user the notification is about
	 * @param    int      $subscriber_id  WP User ID of the subscriber
	 * @return   boolean                    [description]
	 * @since    [version]
	 * @version  [version]
	 */
	private function is_subscriber_self( $subscriber_id ) {
		return $subscriber_id === $this->student->get_id();
	}

	/**
	 * Replaces a given merge code with real information
	 * @param    string     $code  unprepared merge code
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	protected function merge_code( $code, $subscriber_id = null ) {

		switch ( $code ) {

			case 'LESSON_TITLE':
				return $this->lesson->get( 'title' );
			break;

			case 'STUDENT_NAME':
				if ( $this->is_subscriber_self( $subscriber_id ) ) {
					return __( 'you', 'lifterlms' );
				}
				return $this->student->get_name();
			break;
		}

		return $code;

	}

	/**
	 * Set the content of the notification's body
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @param    string     $type           id of the LifterLMS Notification Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_body( $subscriber_id = null, $type = null ) {

		return sprintf( __( 'wooh00t %s', 'lifterlms' ), '{{STUDENT_NAME}}' );

	}

	/**
	 * Define the LifterLMS Notification Handlers that will handle the notification
	 * core handlers are 'basic' and 'email'
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_handlers() {
		return array(
			'basic',
			'email',
		);
	}

	/**
	 * Set the url of the notification's icon
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @param    string     $type           id of the LifterLMS Notification Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_icon( $subscriber_id = null, $type = null ) {

		$img = $this->lesson->get_image( $this->get_icon_dimensions() );
		if ( ! $img ) {
			$course = $this->lesson->get_course();
			$img = $course->get_image( $this->get_icon_dimensions() );
		}

		return $img;

	}

	/**
	 * Determine merge codes that can be used with this notification
	 * the merge codes should be returned without the merge prefix & suffix
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_merge_codes() {
		return array(
			'STUDENT_NAME',
			'LESSON_TITLE',
		);
	}

	/**
	 * Set the content of the notification's title
	 * @param    int        $subscriber_id  WP User ID of the subscriber
	 * @param    string     $type           id of the LifterLMS Notification Handler
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_title( $subscriber_id = null, $type = null ) {

		if ( 'email' === $type ) {
			return sprintf( __( 'Congratulations, %1$s Completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{LESSON_TITLE}}' );
		}

		return __( 'Lesson Completed', 'lifterlms' );

	}

}

return new LLMS_Notification_Lesson_Complete();
