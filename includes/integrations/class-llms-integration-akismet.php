<?php
/**
 * Akismet Integration
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Akismet Integration
 *
 * @since [version]
 */
class LLMS_Integration_Akismet extends LLMS_Abstract_Integration {

	/**
	 * Integration ID
	 *
	 * @var string
	 */
	public $id = 'akismet';

	/**
	 * Display order on Integrations tab
	 *
	 * @var integer
	 */
	protected $priority = 5;

	protected $always_on = true;

	/**
	 * Configure the integration
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function configure() {

		$this->title               = __( 'Akismet', 'lifterlms' );
		$this->description         = sprintf( __( 'Add Akismet\'s powerful spam protection to LifterLMS checkout and registration forms.', 'lifterlms' ), '<a href="" target="_blank">', '</a>' );
		$this->description_missing = sprintf( __( 'To use this integration, the %1$sAkismet%2$s plugin must be installed, activated, and have a valid API key.', 'lifterlms' ), '<a href="https://wordpress.org/plugins/akismet/" target="_blank">', '</a>' );

		if ( $this->is_available() ) {

			add_filter( 'lifterlms_user_registration_data', array( $this, 'verify_registration' ), 20, 3 );

		}

	}

	/**
	 * Retrieve the stored/default error message displayed to users when Spam is detected.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_error_message() {

		return $this->get_option( 'error_message', __( 'There was an error while creating your account. Please try again later.', 'lifterlms' ) );

	}

	/**
	 * Retrieve a full name from user submitted data.
	 *
	 * @since  [version]
	 *
	 * @param  array $data User submitted registration data.
	 * @return string
	 */
	protected function get_name_from_data( $data ) {

		$name = '';
		if ( isset( $data['first_name'] ) ) {
			$name .= $data['first_name'];
		}
		if ( isset( $data['last_name'] ) ) {
			$name .= ' ' . $data['last_name'];
		}

		return trim( $name );

	}

	/**
	 * Retrieve integration settings.
	 *
	 * @since  [version]
	 *
	 * @return array
	 */
	protected function get_integration_settings() {

		$settings = array();

		$disabled = ! $this->is_available();

		$settings[] = array(
			'desc'     => __( 'Verify user information with Akismet during checkout for new users.', 'lifterlms' ),
			'default'  => 'no',
			'id'       => $this->get_option_name( 'verify_checkout' ),
			'type'     => 'checkbox',
			'title'    => __( 'Verify Checkout', 'lifterlms' ),
			'disabled' => $disabled,
		);

		$settings[] = array(
			'desc'     => __( 'Verify user information with Akismet during open registration.', 'lifterlms' ),
			'default'  => 'no',
			'id'       => $this->get_option_name( 'verify_registration' ),
			'type'     => 'checkbox',
			'title'    => __( 'Verify Registration', 'lifterlms' ),
			'disabled' => $disabled,
		);

		$settings[] = array(
			'desc'     => '<br>' . __( 'This message is displayed when Akismet determines the registration is spam.', 'lifterlms' ),
			'id'       => $this->get_option_name( 'error_message' ),
			'type'     => 'textarea',
			'title'    => __( 'Spam Message', 'lifterlms' ),
			'disabled' => $disabled,
			'value'    => $this->get_error_message(),
		);

		return $settings;

	}

	/**
	 * Determine if Akismet is installed and activated.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_installed() {

		// Not installed or activated.
		if ( ! is_callable( array( 'Akismet', 'get_api_key' ) ) ) {
			return false;
		}

		// No Akismet API key.
		$akismet_key = Akismet::get_api_key();
		if ( empty( $akismet_key ) ) {
			return false;
		}

		// Key is valid.
		return 'valid' === Akismet::verify_key( $akismet_key );

	}

	/**
	 * Makes an API Request to the Akisment "comment-check" API to determine if the registration is spam.
	 *
	 * @since  [version]
	 * @link https://akismet.com/development/api/#comment-check
	 *
	 * @param array $data User-submitted user information.
	 * @return boolean `true` if the registration is spam, `false` otherwise.
	 */
	protected function is_spam( $data ) {

		$body = array(
			'blog'                 => get_option( 'home' ),
			'user_ip'              => Akismet::get_ip_address(),
			'user_agent'           => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null,
			'referrer'             => isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null,
			'permalink'            => get_permalink(),
			'comment_type'         => 'signup',
			'comment_author'       => $this->get_name_from_data( $data ),
			'comment_author_email' => $data['email_address'],
			'blog_lang'            => get_locale(),
		);

		/**
		 * Customize the request body passed to Akismet to verify a user registration.
		 *
		 * @since [version]
		 *
		 * @param array $body Associative array representing the request body.
		 * @param array $data Associative array of user information submitted for the registration attempt..
		 */
		$body = apply_filters( 'llms_akismet_request_body', $body, $data );

		// Filter the Akismet User Agent string for this next request.
		add_filter( 'akismet_ua', array( $this, 'modify_user_agent' ) );

		// Make the request.
		$res = Akismet::http_post( build_query( $body ), 'comment-check' );

		// Remove the User Agent filter.
		remove_filter( 'akismet_ua', array( $this, 'modify_user_agent' ) );

		// Is spam.
		if ( ! empty( $res ) && isset( $res[1] ) && 'true' === trim( $res[1] ) ) {
			return true;
		}

		// Not spam.
		return false;

	}

	/**
	 * Modify the User Agent string used when submitting API requests to Akismet.
	 *
	 * @since [version]
	 *
	 * @param string $user_agent Default string.
	 * @return string
	 */
	public function modify_user_agent( $user_agent ) {

		global $wp_version;
		return sprintf( 'WordPress/%s | LifterLMS/%s', $wp_version, LLMS_VERSION );

	}

	/**
	 * Determine if verification should be attempted.
	 *
	 * Checks if verification options are enabled for the given screen.
	 *
	 * @since [version]
	 *
	 * @param string $screen Registration form screen from the LifterLMS Core.
	 * @return bool
	 */
	protected function should_verify( $screen ) {

		return llms_parse_bool( $this->get_option( sprintf( 'verify_%s', $screen ) ) );

	}

	/**
	 * Verify user-submitted information with Akismet.
	 *
	 * Hooked to `lifterlms_user_registration_data`.
	 *
	 * @since [version]
	 *
	 * @param  bool|WP_Error $valid  Whether or not the form is valid based on previous validations.
	 * @param  array         $data   User submitted registration information.
	 * @param  string        $screen Form location.
	 * @return bool|WP_Error
	 */
	public function verify_registration( $valid, $data, $screen ) {

		// Data is already invalid.
		if ( true !== $valid ) {
			return $valid;
		} elseif ( ! $this->should_verify( $screen ) ) {
			return $valid;
		} elseif ( ! $this->is_spam( $data ) ) {
			return $valid;
		}

		return new WP_Error( 'llms-akismet-user-reg-spam-detected', $this->get_error_message() );

	}

}
