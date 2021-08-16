<?php
/**
 * Notification View: Certificate Earned
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Certificate Earned
 *
 * @since 3.8.0
 */
class LLMS_Notification_View_Certificate_Earned extends LLMS_Abstract_Notification_View {

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
	public $trigger_id = 'certificate_earned';

	/**
	 * Retrieve the HTML for a mini cert.
	 *
	 * @since Unknown
	 *
	 * @param string $title   Certificate title.
	 * @param string $content Certificate content.
	 * @return string
	 */
	private function get_mini_html( $title, $content ) {
		$attrs   = array(
			'class' => array(),
			'id'    => array(),
			'style' => array(),
		);
		$allowed = array(
			'h1'     => $attrs,
			'h2'     => $attrs,
			'h3'     => $attrs,
			'h4'     => $attrs,
			'h5'     => $attrs,
			'h6'     => $attrs,
			'p'      => $attrs,
			'ul'     => $attrs,
			'ol'     => $attrs,
			'li'     => $attrs,
			'strong' => $attrs,
			'em'     => $attrs,
			'i'      => $attrs,
			'b'      => $attrs,
		);
		ob_start();
		?>
		<div class="llms-mini-cert">
			<h2 class="llms-mini-cert-title"><?php echo $title; ?></h2>
			<?php echo wp_kses( $content, $allowed ); ?>
		</div>
		<?php
		return ob_get_clean();

	}

	/**
	 * Setup body content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_body() {
		return '{{MINI_CERTIFICATE}}';
	}

	/**
	 * Setup footer content for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_footer() {
		$url = $this->set_merge_data( '{{CERTIFICATE_URL}}' );
		return '<a href="' . esc_url( $url ) . '">' . __( 'View Full Certificate', 'lifterlms' ) . '</a>';
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
			'{{CERTIFICATE_CONTENT}}' => __( 'Certificate Content', 'lifterlms' ),
			'{{CERTIFICATE_TITLE}}'   => __( 'Certificate Title', 'lifterlms' ),
			'{{CERTIFICATE_URL}}'     => __( 'Certificate URL', 'lifterlms' ),
			'{{STUDENT_NAME}}'        => __( 'Student Name', 'lifterlms' ),
			'{{MINI_CERTIFICATE}}'    => __( 'Mini Certificate', 'lifterlms' ),
		);
	}

	/**
	 * Replace merge codes with actual values
	 *
	 * @since 3.8.0
	 * @since 3.16.6 Unknown.
	 * @since [version] Remove output of "you" when displaying notification to the receiving student.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string
	 */
	protected function set_merge_data( $code ) {

		$cert = new LLMS_User_Certificate( $this->notification->post_id );

		switch ( $code ) {

			case '{{CERTIFICATE_CONTENT}}':
				$code = $cert->get( 'content' );
				break;

			case '{{CERTIFICATE_TITLE}}':
				$code = $cert->get( 'certificate_title' );
				break;

			case '{{CERTIFICATE_URL}}':
				$code = get_permalink( $cert->get( 'id' ) );
				break;

			case '{{MINI_CERTIFICATE}}':
				$code = $this->get_mini_html( $this->set_merge_data( '{{CERTIFICATE_TITLE}}' ), $this->set_merge_data( '{{CERTIFICATE_CONTENT}}' ) );
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
		return '';
	}

	/**
	 * Setup notification title for output
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'You\'ve earned a certificate!', 'lifterlms' );
	}

	/**
	 * Define field support for the view
	 *
	 * @since 3.8.0
	 *
	 * @return array
	 */
	protected function set_supported_fields() {
		return array(
			'basic' => array(
				'body'  => true,
				'title' => true,
				'icon'  => true,
			),
		);
	}

}
