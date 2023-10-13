<?php
/**
 * Notification View: Failed Attempts
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Failed Attempts.
 *
 * @since [version]
 */
class LLMS_Notification_View_Failed_Attempts extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications.
	 *
	 * @since [version]
	 * @var array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it.
		 */
		'auto_dismiss' => 10000,
		/**
		 * Enables manual dismissal of notifications.
		 */
		'dismissible'  => true,
	);

	/**
	 * Notification Trigger ID.
	 *
	 * @since [version]
	 * @var [type]
	 */
	public $trigger_id = 'failed_attempts';

	/**
	 * Setup body content for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_body() {
		return sprintf( __( 'Student %1$s has failed all attempts in %2$s.', 'lifterlms' ), '{{STUDENT_NAME}}', '{{QUIZ_TITLE}}' );
	}

	/**
	 * Setup footer content for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'warning' );
	}

	/**
	 * Setup merge codes that can be used with the notification.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{QUIZ_TITLE}}'   => __( 'Quiz Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values.
	 *
	 * @since [version]
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{QUIZ_TITLE}}':
				$code = $this->post->get( 'title' );
				break;

			case '{{STUDENT_NAME}}':
				$code = $this->user->get_name();
				break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_subject() {
		return sprintf( __( 'Student %1$s has failed all attempts in %2$s.', 'lifterlms' ), '{{STUDENT_NAME}}', '{{QUIZ_TITLE}}' );
	}

	/**
	 * Setup notification title for output.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_title() {
		return sprintf( __( '%s failed a Quiz.', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
