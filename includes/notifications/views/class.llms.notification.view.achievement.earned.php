<?php
/**
 * Notification View: Achievement Earned
 *
 * @since 3.8.0
 * @version 3.17.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Achievement Earned
 *
 * @since 3.8.0
 * @since 3.30.3 Fixed spelling errors.
 */
class LLMS_Notification_View_Achievement_Earned extends LLMS_Abstract_Notification_View {

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
	public $trigger_id = 'achievement_earned';

	/**
	 * Setup body content for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_body() {
		ob_start();
		?>
		<p style="text-align: center;">{{ACHIEVEMENT_IMAGE}}</p>
		<h2 style="text-align: center;"><strong>{{ACHIEVEMENT_TITLE}}</strong></h2>
		<p style="text-align: center;">{{ACHIEVEMENT_CONTENT}}</p>
		<?php
		return ob_get_clean();
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
		return '';
	}

	/**
	 * Setup merge codes that can be used with the notification
	 *
	 * @since 3.8.0
	 * @since 3.30.3 Fixed spelling errors.
	 *
	 * @return array
	 */
	protected function set_merge_codes() {
		return array(
			'{{ACHIEVEMENT_CONTENT}}'   => __( 'Achievement Content', 'lifterlms' ),
			'{{ACHIEVEMENT_IMAGE}}'     => __( 'Achievement Image', 'lifterlms' ),
			'{{ACHIEVEMENT_IMAGE_URL}}' => __( 'Achievement Image URL', 'lifterlms' ),
			'{{ACHIEVEMENT_TITLE}}'     => __( 'Achievement Title', 'lifterlms' ),
			'{{STUDENT_NAME}}'          => __( 'Student Name', 'lifterlms' ),
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

		$achievement = new LLMS_User_Achievement( $this->post );

		switch ( $code ) {

			case '{{ACHIEVEMENT_CONTENT}}':
				$code = $achievement->get( 'content' );
				break;

			case '{{ACHIEVEMENT_IMAGE}}':
				$title = $this->set_merge_data( '{{ACHIEVEMENT_TITLE}}' );
				$url   = $this->set_merge_data( '{{ACHIEVEMENT_IMAGE_URL}}' );
				$code  = '<img alt="' . sprintf( _x( '%s Icon', 'Achievement icon alt text', 'lifterlms' ), $title ) . '" src="' . $url . '">';
				break;

			case '{{ACHIEVEMENT_IMAGE_URL}}':
				$code = $achievement->get_image( 'medium', 'achievement_image' );
				if ( ! $code ) {
					$code = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_achievement.png' );
				}
				break;

			case '{{ACHIEVEMENT_TITLE}}':
				$code = $achievement->get( 'title' );
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
		return '';
	}

	/**
	 * Setup notification title for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_title() {
		return __( 'You\'ve been awarded an achievement!', 'lifterlms' );
	}

	/**
	 * Define field support for the view
	 * Extending classes can override this
	 * 3rd parties should filter $this->get_supported_fields()
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_supported_fields() {
		return array(
			'basic' => array(
				'body'  => true,
				'title' => true,
				'icon'  => false,
			),
		);
	}

}
