<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification View: Course Complete
 *
 * @since    3.8.0
 * @version  3.8.2
 */
class LLMS_Notification_View_Course_Complete extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 *
	 * @var  array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it
		 */
		'auto_dismiss' => 10000,
		/**
		 * Enables manual dismissal of notifications
		 */
		'dismissible'  => true,
	);

	/**
	 * Notification Trigger ID
	 *
	 * @var  [type]
	 */
	public $trigger_id = 'course_complete';

	/**
	 * Setup body content for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return sprintf( __( 'Congratulations! %1$s completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{COURSE_TITLE}}' );
		}
		return sprintf( __( 'Congratulations! You finished %s', 'lifterlms' ), '{{COURSE_TITLE}}' );
	}

	/**
	 * Setup footer content for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'positive' );
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_merge_codes() {
		return array(
			'{{COURSE_TITLE}}' => __( 'Course Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 *
	 * @param    string $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.2
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{COURSE_TITLE}}':
				$code = $this->post->get( 'title' );
				break;

			case '{{STUDENT_NAME}}':
				$code = $this->is_for_self() ? __( 'you', 'lifterlms' ) : $this->user->get_name();
				break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subject() {
		return sprintf( __( 'Congratulations! %1$s completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{COURSE_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_title() {
		return sprintf( __( '%s Completed a Course', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
