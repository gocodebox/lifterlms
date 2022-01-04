<?php
/**
 * Notification View: Certificate Earned
 *
 * @package LifterLMS/Notifications/Views/Classes
 *
 * @since 3.8.0
 * @version 3.17.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification View: Certificate Earned
 *
 * @since 3.8.0
 * @since 3.17.6 Unknown.
 */
class LLMS_Notification_View_Certificate_Earned extends LLMS_Abstract_Notification_View {

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
	public $trigger_id = 'certificate_earned';

	/**
	 * Get the HTML for the mini certificate preview.
	 *
	 * @since Unknown
	 *
	 * @param string $title   The (merged) certificate title.
	 * @param string $content The (merged) certificate body/content.
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
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_body() {
		return '{{MINI_CERTIFICATE}}';
	}

	/**
	 * Setup footer content for output
	 *
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_footer() {
		$url = $this->set_merge_data( '{{CERTIFICATE_URL}}' );
		return '<a href="' . esc_url( $url ) . '">' . __( 'View Full Certificate', 'lifterlms' ) . '</a>';
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
	 * @since [version] Refactor to give each merge code it's own method.
	 *
	 * @param string $code The merge code to get merged data for.
	 * @return string The merged string or the original code for invalid merge codes.
	 */
	protected function set_merge_data( $code ) {

		if ( in_array( $code, array_keys( $this->set_merge_codes() ), true ) ) {
			$method = 'set_merge_data_' . strtolower( str_replace( array( '{{', '}}' ), '', $code ) );
			$code   = method_exists( $this, $method ) ? $this->$method( new LLMS_User_Certificate( $this->notification->post_id ) ) : $code;
		}

		return $code;

	}

	/**
	 * Get merge data for the {{CERTIFICATE_CONTENT}} merge code.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $cert Earned certificate object.
	 * @return string
	 */
	private function set_merge_data_certificate_content( $cert ) {
		return $cert->get( 'content' );
	}

	/**
	 * Get merge data for the {{CERTIFICATE_TITLE}} merge code.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $cert Earned certificate object.
	 * @return string
	 */
	private function set_merge_data_certificate_title( $cert ) {
		return $cert->get( 'title' );
	}

	/**
	 * Get merge data for the {{CERTIFICATE_URL}} merge code.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $cert Earned certificate object.
	 * @return string
	 */
	private function set_merge_data_certificate_url( $cert ) {
		return get_permalink( $cert->get( 'id' ) );
	}

	/**
	 * Get merge data for the {{MINI_CERTIFICATE}} merge code.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $cert Earned certificate object.
	 * @return string
	 */
	private function set_merge_data_mini_certificate( $cert ) {
		return $this->get_mini_html( $this->set_merge_data( '{{CERTIFICATE_TITLE}}' ), $this->set_merge_data( '{{CERTIFICATE_CONTENT}}' ) );
	}

	/**
	 * Get merge data for the {{STUDENT_NAME}} merge code.
	 *
	 * @since [version]
	 *
	 * @param LLMS_User_Certificate $cert Earned certificate object.
	 * @return string
	 */
	private function set_merge_data_student_name( $cert ) {
		return $this->is_for_self() ? __( 'you', 'lifterlms' ) : $this->user->get_name();
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
		return __( 'You\'ve earned a certificate!', 'lifterlms' );
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
				'icon'  => true,
			),
		);
	}

}
