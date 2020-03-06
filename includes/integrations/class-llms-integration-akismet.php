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

		// Remove the "Enable/Disabled" checkbox setting.
		add_filter( 'llms_integration_akismet_get_settings', array( $this, 'mod_default_settings' ), 1 );

		if ( $this->is_available() ) {

			add_filter( 'lifterlms_user_registration_data', array( $this, 'verify_registration' ), 20, 3 );
			// add_filter( 'user_row_actions', array( $this, 'add_user_row_actions' ), 20, 2 );

			add_action( 'delete_user_form', array( $this, 'mod_delete_user_form' ), 20, 2 );
			add_action( 'llms_akismet_spam_dectected', array( $this, 'on_spam_detected' ), 10, 3 );
			add_action( 'delete_user', array( $this, 'maybe_submit_spam' ) );

		}

	}

	public function add_user_row_actions( $actions, $user ) {

		// if ( get_current_user_id() !== $user->ID && current_user_can( 'delete_user', $user->ID ) ) {
		// $actions['llms-spam'] = '<a class="danger" href="#">' . __( 'Delete & Report Spam', 'lifterlms' ) . '</a></span';
		// }

		// var_dump( $actions );

		return $actions;

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

		$settings[] = array(
			'desc'     => '<br>' . __( 'Determines the action to be taken when a spam registration is detected by Akismet.', 'lifterlms' ),
			'default'  => 'block',
			'id'       => $this->get_option_name( 'spam_action' ),
			'type'     => 'select',
			'title'    => __( 'Spam Detected Action', 'lifterlms' ),
			'disabled' => $disabled,
			'options'  => array(
				'block' => __( 'Block - Prevent account creation', 'lifterlms' ),
				'allow' => __( 'Allow - Create account and notify an admin', 'lifterlms' ),
			),
		);

		return $settings;

	}

	/**
	 * Determine if the integration had been enabled.
	 *
	 * At least one of the verification options must be enabled to consider the integration enabled.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return ( llms_parse_bool( $this->get_option( 'verify_checkout' ) ) || llms_parse_bool( $this->get_option( 'verify_registration' ) ) );
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
	 * @since [version]
	 *
	 * @link https://akismet.com/development/api/#comment-check
	 *
	 * @param array $data User-submitted user information.
	 * @return boolean `true` if the registration is spam, `false` otherwise.
	 */
	protected function is_spam( $data ) {

		$body = array(
			'user_ip'              => Akismet::get_ip_address(),
			'user_agent'           => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null,
			'referrer'             => isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null,
			'permalink'            => get_permalink(),
			'comment_author'       => $this->get_name_from_data( $data ),
			'comment_author_email' => $data['email_address'],
		);

		$res = $this->api_request( $body, 'comment-check' );

		// Spam to be moderated.
		if ( ! empty( $res ) && isset( $res[1] ) && 'true' === trim( $res[1] ) ) {

			/**
			 * Perform an action when Akismet determines a LifterLMS account registration to be spam.
			 *
			 * @since [version]
			 *
			 * @param array $res  Array of response data from the Akismet request.
			 * @param array $body Array of request data used to perform the request.
			 * @param array $data Array of user information submitted by the user.
			 */
			do_action( 'llms_akismet_spam_dectected', $res, $body, $data );

			return true;

		}

		// Not spam.
		return false;

	}

	protected function api_request( $body, $endpoint ) {

		// Add defaults.
		$body = wp_parse_args(
			$body,
			array(
				'blog'         => get_option( 'home' ),
				'blog_lang'    => get_locale(),
				'comment_type' => 'signup',
			)
		);

		/**
		 * Modify the request body passed to the Akismet API.
		 *
		 * @since [version]
		 *
		 * @param array $body     Associative array representing the request body.
		 * @param array $endpoint Request endpoint
		 */
		$body = apply_filters( 'llms_akismet_request_body', $body, $endpoint );

		// Filter the Akismet User Agent string for this next request.
		add_filter( 'akismet_ua', array( $this, 'modify_user_agent' ) );

		// Make the request.
		$res = Akismet::http_post( build_query( $body ), $endpoint );

		// Remove the User Agent filter.
		remove_filter( 'akismet_ua', array( $this, 'modify_user_agent' ) );

		return $res;

	}

	/**
	 * Add usermeta data to a user denoting that Akismet thinks it's spammy.
	 *
	 * This is called via the action `lifterlms_user_registered` which is hooked
	 * when Akismet detects spam for a registration and the `spam_action` option
	 * is set to "allow".
	 *
	 * The user is created (and the registration/checkout behavior proceeds as normal) and
	 * the admin is notified and the user is marked as spam to be moderated later.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID.
	 * @return void
	 */
	public function mark_user_as_spam( $user_id ) {

		update_user_meta( $user_id, 'llms_akismet_spam', 'yes' );
		$this->notify_admin( $user_id );

		remove_action( 'lifterlms_user_registered', array( $this, 'mark_user_as_spam' ) );

	}

	/**
	 * When a user is being deleted, report the signup as spam if the user selected to report during deletion.
	 *
	 * @since  [version]
	 *
	 * @param  int $user_id WP_User ID.
	 * @return array|false Array containing the Akismet API response or `false` if reporting wasn't requested.
	 */
	public function maybe_submit_spam( $user_id ) {

		$submit = llms_filter_input( INPUT_POST, 'llms_akismet_submit', FILTER_SANITIZE_STRING );
		if ( llms_parse_bool( $submit ) ) {

			$user = get_user_by( 'ID', $user_id );
			$body = array(
				'user_ip'              => get_user_meta( $user_id, 'llms_ip_address', true ),
				'comment_author'       => $this->get_name_from_data(
					array(
						'first_name' => $user->first_name,
						'last_name'  => $user->last_name,
					)
				),
				'comment_author_email' => $user->user_email,
			);

			return $this->api_request( $body, 'submit-spam' );

		}

		return false;

	}

	/**
	 * Modify the default settings to remove the "Enabled" option.
	 *
	 * This integration is considered "Enabled" as long as either one of the "verification" options is enabled.
	 *
	 * @since [version]
	 *
	 * @param array[] $settings Default settings array.
	 * @return array[]
	 */
	public function mod_default_settings( $settings ) {

		$ids   = wp_list_pluck( $settings, 'id' );
		$index = array_search( 'llms_integration_akismet_enabled', $ids, true );
		if ( false !== $index ) {
			unset( $settings[ $index ] );
		}

		return array_values( $settings );

	}

	/**
	 * Add option to submit a user's signup to Akismet upon user deletion.
	 *
	 * @since  [version]
	 *
	 * @param WP_User $current_user WP_User object for the current user.
	 * @param int[]   $userids      Array of IDs for users being deleted.
	 * @return void
	 */
	public function mod_delete_user_form( $current_user, $user_ids ) {

		?>
		<fieldset><p><legend><?php echo _n( 'Do you want to submit this user as a spam account?', 'Do you want to submit these users as spam accounts?', count( $user_ids ), 'lifterlms' ); ?></legend></p>
		<ul style="list-style:none;">
			<li><label><input type="radio" id="llms_akismet_option_0" name="llms_akismet_submit" value="no" checked="checked" />
				<?php _e( 'Do not submit' ); ?></label></li>
			<li><label><input type="radio" id="llms_akismet_option_1" name="llms_akismet_submit" value="yes" />
				<?php _e( 'Submit to Akismet' ); ?></label></li>
		</ul></fieldset>
		<?php

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

	public function notify_admin( $user_id ) {

		$user   = get_user_by( 'ID', $user_id );
		$mailer = LLMS()->mailer()->get_email( 'akismet_spam_notificaiton' );

		/**
		 * Modify the list of emails that are notified when Akismet detects (and allows) a spam registration.
		 *
		 * @since [version]
		 *
		 * @param string[] $emails Array of email addresses.
		 */
		$emails = apply_filters( 'llms_akismet_spam_notification_emails', array( get_option( 'admin_email' ) ) );
		array_map( array( $mailer, 'add_recipient' ), $emails );

		$subject = sprintf( esc_html__( '[%1$s] Please moderate user: "%2$s"' ), get_bloginfo( 'name' ), $user->user_email );
		$mailer->set_subject( $subject );

		$message = __( 'A new user account registration has been flagged as potential spam.', 'lifterlms' );

		$mailer->set_body( $message );

		// Log when wp_mail fails.
		if ( ! $mailer->send() ) {
			$this->log( sprintf( 'Error sending Akismet spam notification email for %s', $user->user_email ) );
		}

	}

	/**
	 * Perform actions when Akismet /comment-check returns a `true` (is spam) response.
	 *
	 * All detected spam is logged to the "akismet" log file.
	 *
	 * This will trigger spam information to be recorded to the user account after registration
	 * if the `spam_action` is "allow" and the admin will be notified via email to moderate the
	 * user account.
	 *
	 * @since [version]
	 *
	 * @param array $res  Array of response data from the Akismet request.
	 * @param array $body Array of request data used to perform the request.
	 * @param array $data Array of user information submitted by the user.
	 * @return void
	 */
	public function on_spam_detected( $res, $body, $data ) {

		$action = $this->get_option( 'spam_action' );

		llms_log( sprintf( 'Spam %sed:', $action ), 'akismet' );
		llms_log( sprintf( '        $res: %s', wp_json_encode( $res ) ), 'akismet' );
		llms_log( sprintf( '       $body: %s', wp_json_encode( $body ) ), 'akismet' );

		if ( 'allow' === $action ) {
			add_action( 'lifterlms_user_registered', array( $this, 'mark_user_as_spam' ) );
		}

	}

	/**
	 * Determine if verification should be attempted.
	 *
	 * Checks if verification options are r for the given screen.
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

			// Not a screen we're verifying.
		} elseif ( ! $this->should_verify( $screen ) ) {
			return $valid;

			// Not spam.
		} elseif ( ! $this->is_spam( $data ) ) {
			return $valid;
		}

		if ( 'allow' === $this->get_option( 'spam_action' ) ) {
			return $valid;
		}

		return new WP_Error( 'llms-akismet-user-reg-spam-detected', $this->get_error_message() );

	}

}
