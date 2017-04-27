<?php
/**
 * Notification View: Course Enrollment
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Notification_View_Course_Enrollment extends LLMS_Abstract_Notification_View {

	/**
	 * Settings for basic notifications
	 * @var  array
	 */
	protected $basic_options = array(
		/**
		 * Time in milliseconds to show a notification
		 * before automatically dismissing it
		 */
		'auto_dismiss' => 0,
		/**
		 * Enables manual dismissal of notifications
		 */
		'dismissible' => false,
	);

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'course_enrollment';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_body() {
		return sprintf( __( 'Congratulations! %1$s enrolled in %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{COURSE_TITLE}}' );
	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_footer() {
		return '';
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
			'{{COURSE_TITLE}}' => __( 'Course Title', 'lifterlms' ),
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

			case '{{COURSE_TITLE}}':
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
		return sprintf( __( '%1$s enrolled in %2$s', 'lifterlms' ), '{{STUDENT_NAME}}', '{{COURSE_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_title() {
		return __( 'Course enrollment success!', 'lifterlms' );
	}

}
