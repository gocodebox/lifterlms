<?php
/**
 * Notification View: Lesson Complete
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_View_Lesson_Complete extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 * @var  array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it
		 */
		'auto_dismiss' => 5000,
		/**
		 * Enables manual dismissal of notifications
		 */
		'dismissible' => true,
	);

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'lesson_complete';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return sprintf( __( 'Congratulations! %1$s completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{LESSON_TITLE}}' );
		}
		return  sprintf( __( 'Congratulations! You finished %s', 'lifterlms' ), '{{LESSON_TITLE}}' );
	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_footer() {
		return 'This is a Footer';
	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_icon() {
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_merge_codes() {
		return array(
			'{{LESSON_TITLE}}' => __( 'Lesson Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{LESSON_TITLE}}':
				$code = $this->post->get( 'title' );
			break;

			case '{{STUDENT_NAME}}':
				$code = $this->is_for_self() ? 'you' : $this->user->get_name();
			break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_subject() {
		return sprintf( __( 'Congratulations! %1$s completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{LESSON_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_title() {
		return  sprintf( __( '%s Completed a Lesson', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
