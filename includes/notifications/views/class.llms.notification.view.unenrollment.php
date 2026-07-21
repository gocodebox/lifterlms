<?php
/**
 * Notification View: Course/Membership Unenrollment
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Course/Membership Unenrollment
 *
 * @since [version]
 */
class LLMS_Notification_View_Unenrollment extends LLMS_Abstract_Notification_View {

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
	public $trigger_id = 'unenrollment';

	/**
	 * Setup body content for output
	 *
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	protected function set_body() {
		if ( 'email' === $this->notification->get( 'type' ) ) {
			return sprintf( __( 'You have been unenrolled from %s.', 'lifterlms' ), '{{TITLE}}' );
		}
		return sprintf( __( '%1$s has been unenrolled from %2$s.', 'lifterlms' ), '{{STUDENT_NAME}}', '{{TITLE}}' );
	}

	/**
	 * Setup footer content for output
	 *
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	protected function set_footer() {
		return '';
	}

	/**
	 * Setup notification icon for output
	 *
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	protected function set_icon() {
		return $this->get_icon_default( 'negative' );
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @return array
	 * @since [version]
	 * @version [version]
	 */
	protected function set_merge_codes() {
		return array(
			'{{TITLE}}'        => __( 'Title', 'lifterlms' ),
			'{{TYPE}}'         => __( 'Type (Course or Membership)', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{TITLE}}':
				$code = $this->post->get( 'title' );
				break;

			case '{{TYPE}}':
				$code = $this->post->get_post_type_label();
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
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	protected function set_subject() {
		if ( $this->is_for_self() ) {
			return sprintf( __( 'You have been unenrolled from %s', 'lifterlms' ), '{{TITLE}}' );
		}
		return sprintf( __( '%1$s unenrolled from %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @return string
	 * @since [version]
	 * @version [version]
	 */
	protected function set_title() {
		return sprintf( __( '%1$s unenrollment', 'lifterlms' ), '{{TYPE}}' );
	}

}
