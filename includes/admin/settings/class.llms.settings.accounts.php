<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin Settings Page, Accounts Tab
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Settings_Accounts extends LLMS_Settings_Page {

	/**
	* Constructor
	*
	* executes settings tab actions
	*/
	public function __construct() {
		$this->id    = 'account';
		$this->label = __( 'Accounts', 'lifterlms' );

		add_filter( 'lifterlms_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'lifterlms_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'lifterlms_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		$field_options = array(
			'required' => __( 'Required', 'lifterlms' ),
			'optional' => __( 'Optional', 'lifterlms' ),
			'hidden' => __( 'Hidden', 'lifterlms' ),
		);

		return apply_filters( 'lifterlms_' . $this->id . '_settings', array(

			array( 'type' => 'sectionstart', 'id' => 'course_account_options', 'class' => 'top' ),

			array( 'title' => __( 'Account Settings', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Customize your users account and sign up experience.', 'lifterlms' ), 'id' => 'account_page_options' ),

			array(
				'title' => __( 'Account Access Page', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Page used for login / my account access. You can also place this shortcode on any page: ', 'lifterlms' ) . ' [' . apply_filters( 'lifterlms_my_account_shortcode_tag', 'lifterlms_my_account' ) . ']',
				'id' 		=> 'lifterlms_myaccount_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
				'desc_tip'	=> true,
			),

			array(
				'desc'          => __( 'Display Student Memberships on Account Page', 'lifterlms' ),
				'id'            => 'lifterlms_enable_myaccount_memberships_list',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),

			array( 'type' => 'sectionend', 'id' => 'course_account_options' ),

			array( 'type' => 'sectionstart', 'id' => 'account_page_options' ),

			array( 'title' => __( 'Account Page Slugs', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'These slugs represent different actions in the user account.', 'lifterlms' ), 'id' => 'account_endpoint_options' ),

			array(
				'title' => __( 'Dashboard', 'lifterlms' ),
				'desc' 		=> __( 'Purchased courses page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_my_courses_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-account',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'My Courses', 'lifterlms' ),
				'desc' 		=> __( 'Purchased courses page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_courses_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-courses',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Edit Account Info', 'lifterlms' ),
				'desc' 		=> __( 'Edit Account page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_edit_account_endpoint',
				'type' 		=> 'text',
				'default'	=> 'edit-account',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Lost Password', 'lifterlms' ),
				'desc' 		=> __( 'Lost Password page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_lost_password_endpoint',
				'type' 		=> 'text',
				'default'	=> 'lost-password',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Redeem Vouchers', 'lifterlms' ),
				'desc' 		=> __( 'Redeem vouchers page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_redeem_vouchers_endpoint',
				'type' 		=> 'text',
				'default'	=> 'redeem-voucher',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'Logout', 'lifterlms' ),
				'desc' 		=> __( 'Custom Link: yoursite.com/?person-logout=true', 'lifterlms' ),
				'id' 		=> 'lifterlms_logout_endpoint',
				'type' 		=> 'text',
				'default'	=> 'logout',
				'desc_tip'	=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'account_page_options' ),

			// start user info fields options
			array(
				'id' => 'user_info_field_options',
				'type' => 'sectionstart',
			),
			array(
				'title' => __( 'User Information Options', 'lifterlms' ),
				'type' => 'title',
				'id' => 'user_info_field_options_title',
			),

			array(
				'desc' => __( 'These settings apply to all user information screens.', 'lifterlms' ),
				'title' => __( 'General Information Field Settings', 'lifterlms' ),
				'type' => 'subtitle',
			),
			array(
				'title'         => __( 'Disable Usernames', 'lifterlms' ),
				'desc'          => __( 'Automatically generate student usernames and enable email address login.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_generate_username',
				'default'       => 'yes',
				'type'          => 'checkbox',
			),
			array(
				'title'         => __( 'Password Strength', 'lifterlms' ),
				'desc'          => __( 'Add a password strength meter', 'lifterlms' ),
				'id'            => 'lifterlms_registration_password_strength',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'autoload'      => false,
			),
			array(
				'desc' 		=> '<br>' . __( 'Select the minimum password strength allowed when students create a new password.', 'lifterlms' ),
				'id' 		=> 'lifterlms_registration_password_min_strength',
				'type' 		=> 'select',
				'default'	=> 'strong',
				'options'     => array(
					'weak' => _x( 'Weak', 'password strength meter', 'lifterlms' ),
					'medium' => _x( 'Medium', 'password strength meter', 'lifterlms' ),
					'strong' => _x( 'Strong', 'password strength meter', 'lifterlms' ),
				),
			),
			array(
				'title'         => __( 'Terms and Conditions', 'lifterlms' ),
				'desc'          => __( 'Add a required "I Agree to the Terms and Conditions" checkbox. When enabled, displays only on checkout and registration screens.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_require_agree_to_terms',
				'default'       => 'no',
				'type'          => 'checkbox',
				'autoload'      => false,
			),
			array(
				'desc' 		=> '<br>' . __( 'Select the page where your Terms and Conditions are described.', 'lifterlms' ),
				'id' 		=> 'lifterlms_terms_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> 0,
				'class'		=> 'chosen_select_nostd',
				'css' 		=> 'min-width:200px;',
				'desc_tip'	=> true,
			),

			array(
				'desc' => __( 'Customize the user information fields available on the checkout screen.', 'lifterlms' ),
				'title' => __( 'Checkout Fields', 'lifterlms' ),
				'type' => 'subtitle',
			),
			array(
				'autoload'      => false,
				'default'       => 'required',
				'id'            => 'lifterlms_user_info_field_names_checkout_visibility',
				'title'         => __( 'First & Last Name', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'required',
				'id'            => 'lifterlms_user_info_field_address_checkout_visibility',
				'title'         => __( 'Address', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'optional',
				'id'            => 'lifterlms_user_info_field_phone_checkout_visibility',
				'title'         => __( 'Phone Number', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'yes',
				'desc'         => __( 'Add an email confirmation field', 'lifterlms' ),
				'id'            => 'lifterlms_user_info_field_email_confirmation_checkout_visibility',
				'title'         => __( 'Email Confirmation', 'lifterlms' ),
				'type'          => 'checkbox',
			),

			array(
				'desc' => __( 'Customize the user information fields available on the open registration screen.', 'lifterlms' ),
				'title' => __( 'Open Registration Fields', 'lifterlms' ),
				'type' => 'subtitle',
			),
			array(
				'default'       => 'no',
				'desc'          => __( 'Allow registration on the Account Access Page without enrolling in a course or membership.', 'lifterlms' ),
				'id'            => 'lifterlms_enable_myaccount_registration',
				'title'         => __( 'Enable / Disable', 'lifterlms' ),
				'type'          => 'checkbox',
			),
			array(
				'autoload'      => false,
				'default'       => 'required',
				'id'            => 'lifterlms_user_info_field_names_registration_visibility',
				'title'         => __( 'First & Last Name', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'optional',
				'id'            => 'lifterlms_user_info_field_address_registration_visibility',
				'title'         => __( 'Address', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'hidden',
				'id'            => 'lifterlms_user_info_field_phone_registration_visibility',
				'title'         => __( 'Phone Number', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'no',
				'desc'         => __( 'Add an email confirmation field', 'lifterlms' ),
				'id'            => 'lifterlms_user_info_field_email_confirmation_registration_visibility',
				'title'         => __( 'Email Confirmation', 'lifterlms' ),
				'type'          => 'checkbox',
			),

			array(
				'desc' => __( 'Customize the user information fields available on the account update screen.', 'lifterlms' ),
				'title' => __( 'Account Update Fields', 'lifterlms' ),
				'type' => 'subtitle',
			),
			array(
				'autoload'      => false,
				'default'       => 'required',
				'id'            => 'lifterlms_user_info_field_names_account_visibility',
				'title'         => __( 'First & Last Name', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'required',
				'id'            => 'lifterlms_user_info_field_address_account_visibility',
				'title'         => __( 'Address', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'optional',
				'id'            => 'lifterlms_user_info_field_phone_account_visibility',
				'title'         => __( 'Phone Number', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
			),
			array(
				'autoload'      => false,
				'default'       => 'yes',
				'desc'         => __( 'Add an email confirmation field', 'lifterlms' ),
				'id'            => 'lifterlms_user_info_field_email_confirmation_account_visibility',
				'title'         => __( 'Email Confirmation', 'lifterlms' ),
				'type'          => 'checkbox',
			),

			array(
				'id' => 'user_info_field_options',
				'type' => 'sectionend',
			),
			// end user info field options

		)); // End pages settings
	}

}

return new LLMS_Settings_Accounts();
