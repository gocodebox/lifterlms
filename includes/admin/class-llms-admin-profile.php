<?php
/**
 * Handle extra profile fields for users in admin
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [verson]
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle extra profile fields for users in admin
 *
 * Applies to edit-user.php & profile.php.
 *
 * @since 5.0.0
 */
class LLMS_Admin_Profile {

	/**
	 * Array of user profile fields
	 *
	 * @var array
	 */
	private $fields;

	/**
	 * Submission errors
	 *
	 * @var null|WP_Error
	 */
	private $errors;

	/**
	 * Constructor
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'show_user_profile', array( $this, 'add_user_meta_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_user_meta_fields' ) );

		add_action( 'personal_options_update', array( $this, 'save_user_meta_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_meta_fields' ) );

		// Allow errors to be output.
		add_action( 'user_profile_update_errors', array( $this, 'add_errors' ) );
	}

	/**
	 * Add user meta fields to the profile screens
	 *
	 * @since 5.0.0
	 *
	 * @param WP_User $user Instance of WP_User for the user being updated.
	 * @return bool `true` if fields were added, `false` otherwise.
	 */
	public function add_user_meta_fields( $user ) {

		if ( ! $this->current_user_can_edit_admin_custom_fields() ) {
			return false;
		}

		$fields = $this->get_fields();

		if ( empty( $fields ) ) {
			return false;
		}

		/**
		 * Enqueue select2 scripts and styles.
		 */
		wp_enqueue_script( 'llms-metaboxes' );
		wp_enqueue_script( 'llms-select2' );
		llms()->assets->enqueue_style( 'llms-select2-styles' );
		wp_add_inline_script(
			'llms',
			"window.llms.address_info = '" . wp_json_encode( llms_get_countries_address_info() ) . "';"
		);

		include_once LLMS_PLUGIN_DIR . 'includes/admin/views/user-edit-fields.php';

		return true;
	}

	/**
	 * Maybe save user meta fields
	 *
	 * @since 5.0.0
	 *
	 * @param int $user_id WP_User ID for the user being updated.
	 * @return void
	 */
	public function save_user_meta_fields( $user_id ) {

		if ( ! $this->current_user_can_edit_admin_custom_fields() ) {
			return;
		}

		$fields      = $this->get_fields();
		$posted_data = array();

		foreach ( $this->fields as $field ) {
			if ( in_array( $field['name'], LLMS_CONFIRMATION_FIELDS, true ) ) {
				continue;
			}

			//phpcs:disable WordPress.Security.NonceVerification.Missing  -- nonce is verified prior to reaching this method.
			if ( isset( $_POST[ $field['name'] ] ) &&
					isset( $field['data_store_key'] ) &&
						$field['data_store'] && 'usermeta' === $field['data_store'] ) {
				//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitization and unslashing happens in `LLMS_Form_Handler::instance()->submit_form_fields()` below.
				$posted_data[ $field['name'] ] = $_POST[ $field['name'] ];
			}
			//phpcs:disable WordPress.Security.NonceVerification.Missing
		}

		if ( empty( $posted_data ) ) {
			return;
		}

		$posted_data['user_id'] = $user_id;

		$submit = LLMS_Form_Handler::instance()->submit_fields( $posted_data, 'admin-profile', $fields, 'update' );

		if ( is_wp_error( $submit ) ) {
			$this->errors = $submit;
		}
	}

	/**
	 * Maybe print validation errors
	 *
	 * @since 5.0.0
	 *
	 * @param WP_Error $errors Instance of WP_Error, passed by reference.
	 * @return void
	 */
	public function add_errors( &$errors ) {

		if ( is_wp_error( $this->errors ) && $this->errors->has_errors() ) {
			$this->merge_llms_fields_errors( $errors );
		}
	}

	/**
	 * Check whether the current user can edit users custom fields
	 *
	 * @since 5.0.0
	 *
	 * @return boolean
	 */
	private function current_user_can_edit_admin_custom_fields() {
		return current_user_can( 'manage_lifterlms' ) && current_user_can( 'edit_users' );
	}

	/**
	 * Merge llms fields errors into the passed WP_Error
	 *
	 * @since 5.0.0
	 * @todo Remove the fallback when minimum required WP version will be 5.6+.
	 *
	 * @param WP_Error $errors Instance of WP_Error, passed by reference.
	 * @return void
	 */
	private function merge_llms_fields_errors( &$errors ) {

		foreach ( $this->errors->get_error_codes() as $code ) {
			foreach ( $this->errors->get_error_messages( $code ) as $error_message ) {
				$errors->add(
					$code,
					sprintf(
						// Translators: %1$s = Opening strong tag; %2$s = Closing strong tag; %3$s = The error message.
						esc_html__( '%1$sError%2$s: %3$s', 'lifterlms' ),
						'<strong>',
						'</strong>',
						$error_message
					)
				);
			}

			// `WP_Error::get_all_error_data()` has been introduced in WP 5.6.0.
			$error_data = method_exists( $this->errors, 'get_all_error_data' ) ?
					$this->errors->get_all_error_data( $code ) : $this->errors->get_error_data( $code );

			foreach ( $error_data as $data ) {
				$errors->add_data( $data, $code );
			}
		}
	}

	/**
	 * Get fields to be added in the profile screen
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	private function get_fields() {

		if ( ! isset( $this->fields ) ) {
			$this->fields = $this->prepare_fields();
		}

		return $this->fields;
	}

	/**
	 * Setup fields to be added to the profile screen
	 *
	 * @since 5.0.0
	 *
	 * @return array
	 */
	private function prepare_fields() {

		$fields   = llms_get_user_information_fields();
		$prepared = array();

		/**
		 * Filters the list of user information fields which are excluded from the admin profile.
		 *
		 * By default WP core fields are excluded as they are automatically rendered on the screen
		 * by the WP core.
		 *
		 * @since 5.0.0
		 *
		 * @param string[] $fields A list of field ids to be excluded.
		 */
		$excluded = apply_filters(
			'llms_admin_profile_excluded_fields',
			array_merge(
				array(
					'user_login',
					'email_address',
					'password',
					'first_name',
					'last_name',
					'display_name',
				),
				LLMS_CONFIRMATION_FIELDS
			)
		);

		foreach ( $fields as $field ) {

			// Skip excluded fields.
			if ( in_array( $field['name'], $excluded, true ) ) {
				continue;
			}

			// For display purposes.
			$field['columns'] = 6;

			// Handle weird exception.
			$field['label'] = ( 'llms_billing_address_2' === $field['name'] ) ? __( 'Address line 2', 'lifterlms' ) : $field['label'];

			$prepared[] = $field;

		}

		/**
		 * Fields to be added in the profile screen
		 *
		 * @since 5.0.0
		 *
		 * @param array[] $fields Array of fields.
		 */
		return apply_filters( 'llms_admin_profile_fields', $prepared );
	}
}

return new LLMS_Admin_Profile();
