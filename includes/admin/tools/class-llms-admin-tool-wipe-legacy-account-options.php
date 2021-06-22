<?php
/**
 * LLMS_Admin_Tool_Wipe_Legacy_Account_Options
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin tool to wipe legacy account options
 *
 * @since [version]
 */
class LLMS_Admin_Tool_Wipe_Legacy_Account_Options extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'wipe-legacy-account-options';

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_description() {
		return __( 'Remove legacy pre LifterLMS 5.0 user information fields options.', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Wipe Legacy User Information options', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 5.0.0
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Wipe Legacy Options', 'lifterlms' );
	}

	/**
	 * Process the tool.
	 *
	 * Deletes all core reusable blocks and then recreates the core forms,
	 * which additionally recreates the core reusable blocks.
	 *
	 * @since [version]
	 *
	 * @return boolean
	 */
	protected function handle() {

		$options_to_wipe = array(
			'lifterlms_registration_generate_username',
			'lifterlms_registration_password_strength',
			'lifterlms_registration_password_min_strength',
			'lifterlms_user_info_field_names_checkout_visibility',
			'lifterlms_user_info_field_address_checkout_visibility',
			'lifterlms_user_info_field_phone_checkout_visibility',
			'lifterlms_user_info_field_email_confirmation_checkout_visibility',
			'lifterlms_user_info_field_names_registration_visibility',
			'lifterlms_user_info_field_address_registration_visibility',
			'lifterlms_user_info_field_phone_registration_visibility',
			'lifterlms_user_info_field_email_confirmation_registration_visibility',
			'lifterlms_voucher_field_registration_visibility',
			'lifterlms_user_info_field_names_account_visibility',
			'lifterlms_user_info_field_address_account_visibility',
			'lifterlms_user_info_field_phone_account_visibility',
			'lifterlms_user_info_field_email_confirmation_account_visibility',
		);

		global $wpdb;

		$sql = "
		DELETE FROM {$wpdb->options}
		WHERE option_name IN (" . implode( ', ', array_fill( 0, count( $options_to_wipe ), '%s' ) ) . ')';

		$wpdb->query(
			$wpdb->prepare(
				$sql,
				$options_to_wipe
			)
		); // db call ok; no-cache ok.

		return true;

	}

	/**
	 * Conditionally load the tool
	 *
	 * This tool should only load if there are legacy options (we only check 'lifterlms_registration_generate_username').
	 *
	 * @since [version]
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {
		return ( 'not-set' !== get_option( 'lifterlms_registration_generate_username', 'not-set' ) );

	}
}

return new LLMS_Admin_Tool_Wipe_Legacy_Account_Options();
