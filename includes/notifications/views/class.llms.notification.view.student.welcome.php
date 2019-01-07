<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notification View: Student Welcome
 * @since    3.8.0
 * @version  3.8.2
 */
class LLMS_Notification_View_Student_Welcome extends LLMS_Abstract_Notification_View {

	/**
	 * Notification Trigger ID
	 * @var  [type]
	 */
	public $trigger_id = 'student_welcome';

	/**
	 * Setup body content for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_body() {

		ob_start();
		?><p><?php printf( __( 'Hello %s,', 'lifterlms' ), '{{STUDENT_NAME}}' ); ?></p>
		<p><?php printf( __( 'Here\'s some helpful information to help you get started at %s.', 'lifterlms' ), '{{SITE_TITLE}}' ); ?></p>
		<p><b><?php _e( 'Your Login', 'lifterlms' ); ?></b>: {{STUDENT_LOGIN}}</p>
		<p><b><?php _e( 'Your Dashboard', 'lifterlms' ); ?></b>: <a href="{{DASHBOARD_URL}}">{{DASHBOARD_URL}}</a></p>
		<p><?php _e( 'If you forgot or don\'t have a password you can reset it now so you can login and get started:', 'lifterlms' ); ?> <a href="{{PASSWORD_RESET_URL}}">{{PASSWORD_RESET_URL}}</a></p>
		<?php
		return ob_get_clean();

	}

	/**
	 * Setup footer content for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_footer() {
		return '';
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
	 * Setup merge codes that can be used with the notification
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_merge_codes() {
		return array(
			'{{DASHBOARD_URL}}' => __( 'Dashboard URL', 'lifterlms' ),
			'{{PASSWORD_RESET_URL}}' => __( 'Password Reset URL', 'lifterlms' ),
			'{{SITE_TITLE}}' => __( 'Site Title', 'lifterlms' ),
			'{{STUDENT_NAME}}' => __( 'Student Name', 'lifterlms' ),
			'{{STUDENT_LOGIN}}' => __( 'Student Login', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 * @param    string   $code  the merge code to ge merged data for
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.2
	 */
	protected function set_merge_data( $code ) {

		switch ( $code ) {

			case '{{DASHBOARD_URL}}':
				$code = llms_get_page_url( 'myaccount' );
			break;

			case '{{PASSWORD_RESET_URL}}':
				$code = llms_lostpassword_url();
			break;

			case '{{SITE_TITLE}}':
				$code = get_bloginfo( 'name', 'display' );
			break;

			case '{{STUDENT_NAME}}':
				$code = $this->user->get_name();
			break;

			case '{{STUDENT_LOGIN}}':
				$field = ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) ) ? 'user_email' : 'user_login';
				$code = $this->user->get( $field );
			break;

		}

		return $code;

	}

	/**
	 * Setup notification subject for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_subject() {
		return sprintf( __( 'Welcome to %s', 'lifterlms' ), '{{SITE_TITLE}}' );
	}

	/**
	 * Setup notification title for output
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_title() {
		return sprintf( __( 'Let\'s get started %s', 'lifterlms' ), '{{STUDENT_NAME}}' );
	}

}
