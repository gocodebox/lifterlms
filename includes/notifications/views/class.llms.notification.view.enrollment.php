<?php
/**
 * Notification View: Course/Membership Enrollment
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Course/Membership Enrollment
 *
 * @since 3.8.0
 */
class LLMS_Notification_View_Enrollment extends LLMS_Abstract_Notification_View {

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
	public $trigger_id = 'enrollment';

	/**
	 * Setup body content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_body() {
		return sprintf( __( 'Congratulations! %1$s enrolled in %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{TITLE}}' );
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
			'{{TITLE}}'        => __( 'Title', 'lifterlms' ),
			'{{TYPE}}'         => __( 'Type (Course or Membership)', 'lifterlms' ),
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

			case '{{TITLE}}':
				$code = $this->post->get( 'title' );
				break;

			case '{{TYPE}}':
				$code = $this->post->get_post_type_label();
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
		return sprintf( __( '%1$s enrolled in %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return sprintf( __( '%1$s enrollment success!', 'lifterlms' ), '{{TYPE}}' );
	}

}
