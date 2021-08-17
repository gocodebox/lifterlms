<?php
/**
 * Notification View: Course Track Complete
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Course Track Complete
 *
 * @since 3.8.0
 */
class LLMS_Notification_View_Course_Track_Complete extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 *
	 * @var array
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
	 * @var string
	 */
	public $trigger_id = 'course_track_complete';

	/**
	 * Setup body content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			// Translators: %1$s = {{STUDENT_NAME}} merge code; %2$s = {{TRACK_TITLE}} merge code.
			return sprintf( __( 'Congratulations! %1$s completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{TRACK_TITLE}}' );
		}
		// Translators: %s = {{TRACK_TITLE}} merge code.
		return sprintf( __( 'Congratulations! You finished %s', 'lifterlms' ), '{{TRACK_TITLE}}' );
	}

	/**
	 * Setup footer content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'positive' );
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{TRACK_TITLE}}'  => __( 'Track Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 *
	 * @since 3.8.0
	 * @since 3.8.2 Unknown.
	 * @since [version] Remove output of "you" when displaying notification to the receiving student.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{TRACK_TITLE}}':
				$track = new LLMS_Track( $this->notification->get( 'post_id' ) );
				$code  = $track->get_title();
				break;

			case '{{STUDENT_NAME}}':
				$code = $this->user->get_name();
				break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_subject() {
		// Translators: %1$s = {{STUDENT_NAME}} merge code; %2$s = {{TRACK_TITLE}} merge code.
		return sprintf( __( 'Congratulations! %1$s completed %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{TRACK_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_title() {
		// Translators: %s = {{STUDENT_NAME}} merge code.
		return sprintf( __( '%s Completed a Track', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
