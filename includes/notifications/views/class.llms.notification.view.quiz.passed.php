<?php
defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Quiz Passed
 * @since    3.8.0
 * @version  3.24.0
 */
class LLMS_Notification_View_Quiz_Passed extends LLMS_Abstract_Notification_View_Quiz_Completion {

	/**
	 * Notification Trigger ID
	 * @var  string
	 */
	public $trigger_id = 'quiz_passed';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.24.0
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return $this->set_body_email();
		}
		$content = sprintf( __( 'Congratulations! You passed %s!', 'lifterlms' ), '{{QUIZ_TITLE}}' );
		$content .= "\r\n\r\n{{GRADE_BAR}}";
		return $content;
	}

	/**
	 * Setup notification icon for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'positive' );
	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subject() {
		return sprintf( __( 'Congratulations! %1$s passed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{QUIZ_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_title() {
		return  sprintf( __( '%s passed a quiz', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
