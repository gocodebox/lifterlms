<?php
/**
 * Handle extra profile fields for users in admin
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [verson]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle extra profile fields for users in admin
 *
 * Applies to edit-user.php, & profile.php.
 *
 * @since [version]
 */
class LLMS_Admin_Profile {

	/**
	 * Array of user profile fields
	 *
	 * @var array
	 */
	private $fields;

	/**
	 * Submission error
	 *
	 * @var null|WP_Error
	 */
	private $errors;

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'show_user_profile', array( $this, 'add_customer_meta_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_customer_meta_fields' ) );

		add_action( 'personal_options_update', array( $this, 'save_customer_meta_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields' ) );

		// Allow errors to be output.
		add_action( 'user_profile_update_errors', array( $this, 'add_errors' ), 10, 3 );

	}

	/**
	 * Add customer meta fields to the profile screens
	 *
	 * @since [version]
	 *
	 * @param WP_User $user Instance of WP_User for the user being updated.
	 * @return void
	 */
	public function add_customer_meta_fields( $user ) {

		if ( ! $this->current_user_can_manage_user_custom_fields( $user ) ) {
			return;
		}

		$fields = $this->get_fields();

		include_once LLMS_PLUGIN_DIR . 'includes/admin/views/user-edit-fields.php';

	}

	/**
	 * Undocumented function
	 *
	 * Only who can 'manage_lifterlms' will be able to edit own and other profiles.
	 *
	 * @param WP_User $user Instance of WP_User for the user being updated.
	 * @return boolean
	 */
	private function current_user_can_manage_user_custom_fields( $user ) {

		return current_user_can( 'manage_lifterlms' ) && current_user_can( 'edit_users', $user );

	}

	/**
	 * Maybe print validation errors
	 *
	 * @since [version]
	 *
	 * @param obj     $errors Instance of WP_Error, passed by reference.
	 * @param bool    $update `true` if updating a profile, `false` if a new user.
	 * @param WP_User $user   Instance of WP_User for the user being updated.
	 * @return void
	 */
	public function add_errors( &$errors, $update, $user ) {

		if ( is_wp_error( $this->errors ) && $this->errors->has_errors() ) {
			foreach ( $this->errors->get_error_messages() as $message ) {
				$errors->add(
					'',
					sprintf(
						// Translators: %1$s = Opening strong tag; %2$s = Closing strong tag; %3$s = The error message.
						esc_html__( '%1$sError%2$s: %3$s', 'lifterlms' ),
						'<strong>',
						'</strong>',
						$message
					)
				);
			}
		}

	}

	/**
	 * Maybe save customer meta fields
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID for the user being updated.
	 * @return void
	 */
	public function save_customer_meta_fields( $user_id ) {

		if ( ! $this->current_user_can_manage_user_custom_fields( get_user_by( 'ID', $user_id ) ) ) {
			return;
		}

		$fields      = $this->get_fields();
		$posted_data = array();

		foreach ( $this->fields as $field ) {
			//phpcs:disable WordPress.Security.NonceVerification.Missing  -- nonce is verified prior to reaching this method.
			if ( isset( $_POST[ $field['data_store_key'] ] ) &&
					isset( $field['data_store_key'] ) &&
						$field['data_store'] && 'usermeta' === $field['data_store'] ) {
				//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitization and unslashing happens in `LLMS_Form_Handler::instance()->submit_form_fields()` below.
				$posted_data[ $field['data_store_key'] ] = $_POST[ $field['data_store_key'] ];
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
	 * Get fields to be added in the profile screen
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_fields() {

		if ( ! isset( $this->fields ) ) {

			$this->fields = apply_filters(
				/**
				 * Fields to be added in the profile screen
				 *
				 * @since [version]
				 *
				 * @param array $fields Array of fields.
				 */
				'llms_admin_profile_fields',
				array(
					array(
						'type'           => 'text',
						'label'          => __( 'Address', 'lifterlms' ),
						'name'           => 'llms_billing_address_1',
						'id'             => 'llms_billing_address_1',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_billing_address_1',
						'columns'        => 6,
					),
					array(
						'type'           => 'text',
						'label'          => __( 'Additional address information', 'lifterlms' ), // It's used in the error messages.
						'placeholder'    => __( 'Apartment, suite, etc...', 'lifterlms' ),
						'name'           => 'llms_billing_address_2',
						'id'             => 'llms_billing_address_2',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_billing_address_2',
						'columns'        => 6,
					),
					array(
						'type'           => 'text',
						'label'          => __( 'City', 'lifterlms' ),
						'name'           => 'llms_billing_city',
						'id'             => 'llms_billing_city',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_billing_city',
						'columns'        => 6,
					),
					array(
						'type'           => 'select',
						'label'          => __( 'Country', 'lifterlms' ),
						'name'           => 'llms_billing_country',
						'id'             => 'llms_billing_country',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_billing_country',
						'options_preset' => 'countries',
						'placeholder'    => __( 'Select a Country', 'lifterlms' ),
						'columns'        => 6,
					),
					array(
						'type'           => 'select',
						'label'          => __( 'State / Region', 'lifterlms' ),
						'options_preset' => 'states',
						'placeholder'    => __( 'Select a State / Region', 'lifterlms' ),
						'name'           => 'llms_billing_state',
						'id'             => 'llms_billing_state',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_billing_state',
						'columns'        => 6,
					),
					array(
						'type'           => 'text',
						'label'          => __( 'Postal / Zip Code', 'lifterlms' ),
						'name'           => 'llms_billing_zip',
						'id'             => 'llms_billing_zip',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_billing_zip',
						'columns'        => 6,
					),
					array(
						'type'           => 'tel',
						'label'          => __( 'Phone Number', 'lifterlms' ),
						'name'           => 'llms_phone',
						'id'             => 'llms_phone',
						'data_store'     => 'usermeta',
						'data_store_key' => 'llms_phone',
						'columns'        => 6,
					),
				)
			);

		}

		return $this->fields;
	}

}

return new LLMS_Admin_Profile();
