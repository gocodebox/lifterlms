<?php
defined( 'ABSPATH' ) || exit;

/**
* Admin Settings Page, Accounts Tab
* @since    1.0.0
* @version  3.24.0
*/
class LLMS_Settings_Accounts extends LLMS_Settings_Page {

	/**
	 * Allow settings page to determine if a rewrite flush is required
	 * @var      boolean
	 * @since    3.0.4
	 * @version  3.0.4
	 */
	protected $flush = true;

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
	 * @return  array
	 * @since   1.0.0
	 * @version 3.24.0
	 */
	public function get_settings() {

		$field_options = array(
			'required' => __( 'Required', 'lifterlms' ),
			'optional' => __( 'Optional', 'lifterlms' ),
			'hidden' => __( 'Hidden', 'lifterlms' ),
		);

		return apply_filters( 'lifterlms_' . $this->id . '_settings', array(

			array(
				'class' => 'top',
				'id' => 'course_account_options',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'account_page_options_start',
				'title' => __( 'Student Dashboard', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'title' => __( 'Dashboard Page', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Page where students can view and manage their current enrollments, earned certificates and acheivements, account information, and purchase history.', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_page_id',
				'default'	=> '',
				'desc_tip'	=> true,
				'class'		=> 'llms-select2-post',
				'type' 		=> 'select',
				'custom_attributes' => array(
					'data-post-type' => 'page',
				),
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_myaccount_page_id', '' ) ),
			),

			array(
				'title' => __( 'Courses Sorting', 'lifterlms' ),
				'default'	=> 'order,ASC',
				'desc' 		=> '<br>' . __( 'Determines the order of the courses in-progress listed on the student dashboard.', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_courses_in_progress_sorting',
				'type' 		=> 'select',
				'options'     => array(
					'title,ASC' => __( 'Course Title (A to Z)', 'lifterlms' ),
					'title,DESC' => __( 'Course Title (Z to A)', 'lifterlms' ),
					'date,DESC' => __( 'Enrollment Date (Most Recent to Least Recent)', 'lifterlms' ),
					'order,ASC' => __( 'Order (Low to High)', 'lifterlms' ),
					'order,DESC' => __( 'Order (High to Low)', 'lifterlms' ),
				),
			),

			array(
				'id' => 'course_account_options_end',
				'type' => 'sectionend',
			),

			array(
				'class' => 'top',
				'id' => 'course_account_endpoint_options_start',
				'type' => 'sectionstart',
			),

			array(
				'id' => 'account_page_endpoint_options_title',
				'title' => __( 'Student Dashboard Endpoints', 'lifterlms' ),
				'desc' => __( 'Each endpoint allows students to view more information or manage parts of their account. Each endpoint should be unique, URL-safe, and can be left blank to disable the endpoint completely.', 'lifterlms' ),
				'type' => 'title',
			),

			array(
				'title' => __( 'View Grades', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Student grade and progress reporting', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_grades_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-grades',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'View Courses', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'List of all the student\'s courses', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_courses_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-courses',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'View Memberships', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'List of all the student\'s memberships', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_memberships_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-memberships',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'View Achievements', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'List of all the student\'s achievements', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_achievements_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-achievements',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'View Certificates', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'List of all the student\'s certificates', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_certificates_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-certificates',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'Notifications', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'View Notifications and adjust notification settings', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_notifications_endpoint',
				'type' 		=> 'text',
				'default'	=> 'notifications',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'Edit Account', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Edit Account page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_edit_account_endpoint',
				'type' 		=> 'text',
				'default'	=> 'edit-account',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'Lost Password', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Lost Password page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_lost_password_endpoint',
				'type' 		=> 'text',
				'default'	=> 'lost-password',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'Redeem Vouchers', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Redeem vouchers page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_redeem_vouchers_endpoint',
				'type' 		=> 'text',
				'default'	=> 'redeem-voucher',
				'sanitize'  => 'slug',
			),

			array(
				'title' => __( 'Orders History', 'lifterlms' ),
				'desc' 		=> '<br>' . __( 'Students can review order history on this page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_orders_endpoint',
				'type' 		=> 'text',
				'default'	=> 'orders',
				'sanitize'  => 'slug',
			),

			array(
				'id' => 'course_account_endpoint_options_end',
				'type' => 'sectionend',
			),

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
				'title' => __( 'Terms and Conditions', 'lifterlms' ),
				'type' => 'subtitle',
			),
			array(
				'autoload' => false,
				'default' => 'no',
				'id' => 'lifterlms_registration_require_agree_to_terms',
				'desc' => __( 'When enabled users must agree to your site\'s Terms and Conditions to register for an account.', 'lifterlms' ),
				'title' => __( 'Enable / Disable', 'lifterlms' ),
				'type' => 'checkbox',
				'custom_attributes' => array(
					'class' => 'llms-conditional-controller',
					'data-controls' => '#lifterlms_terms_page_id,#llms_terms_notice',
				),
			),
			array(
				'autoload' => false,
				'desc' => '<br>' . __( 'Select a page where your site\'s Terms and Conditions are described.', 'lifterlms' ),
				'id' => 'lifterlms_terms_page_id',
				'default' => '',
				'desc_tip' => true,
				'class' => 'llms-select2-post',
				'title' => __( 'Terms and Conditions Page', 'lifterlms' ),
				'type' => 'select',
				'custom_attributes' => array(
					'data-post-type' => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'options' => llms_make_select2_post_array( get_option( 'lifterlms_terms_page_id', '' ) ),
			),
			array(
				'autoload' => false,
				'default' => llms_get_terms_notice(),
				'id' => 'llms_terms_notice',
				'desc' => '<br>' . __( 'Customize the text used to display the Terms and Conditions checkbox that students must accept.', 'lifterlms' ),
				'title' => __( 'Terms and Conditions Notice', 'lifterlms' ),
				'type' => 'textarea',
				'value' => llms_get_terms_notice(),
			),

			array(
				'title' => __( 'Privacy Policy', 'lifterlms' ),
				'type' => 'subtitle',
			),
			array(
				'autoload' => false,
				'desc' => '<br>' . sprintf(
					__( 'Select a page where your site\'s Privacy Policy is described. See %1$sWordPress Privacy Settings%2$s for more information', 'lifterlms' ),
					'<a href="' . esc_url( admin_url( 'privacy.php' ) ) . '">',
					'</a>'
				),
				'id' => 'wp_page_for_privacy_policy',
				'class' => 'llms-select2-post',
				'title' => __( 'Privacy Policy Page', 'lifterlms' ),
				'type' => 'select',
				'custom_attributes' => array(
					'data-post-type' => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'options' => llms_make_select2_post_array( get_option( 'wp_page_for_privacy_policy' ) ),
			),
			array(
				'autoload' => false,
				'default' => llms_get_privacy_notice(),
				'id' => 'llms_privacy_notice',
				'desc' => '<br>' . __( 'Optionally display a privacy policy notice during registration and checkout.', 'lifterlms' ),
				'title' => __( 'Privacy Policy Notice', 'lifterlms' ),
				'type' => 'textarea',
			),

			array(
				'title' => __( 'Account Erasure Requests', 'lifterlms' ),
				/* Translators: %$1s = opening anchor to account erasure screen; %2$s closing anchor */
				'desc' => sprintf( __( 'Customize data retention during %1$saccount erasure requests%2$s.', 'lifterlms' ), '<a href="' . esc_url( admin_url( 'tools.php?page=remove_personal_data' ) ) . '">', '</a>' ),
				'type' => 'subtitle',
			),
			array(
				'autoload' => false,
				'default' => 'no',
				'id' => 'llms_erasure_request_removes_order_data',
				'desc' => __( 'When enabled orders will be anonymized during a personal data erasure.', 'lifterlms' ),
				'title' => __( 'Remove Order Data', 'lifterlms' ),
				'type' => 'checkbox',
			),
			array(
				'autoload' => false,
				'default' => 'no',
				'id' => 'llms_erasure_request_removes_lms_data',
				'desc' => __( 'When enabled all student data related to course and membership activities will be removed.', 'lifterlms' ),
				'title' => __( 'Remove Student LMS Data', 'lifterlms' ),
				'type' => 'checkbox',
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
				'autoload'      => false,
				'default'       => 'optional',
				'desc'          => '<br>' . __( 'If required, users can only use open registration with a voucher.', 'lifterlms' ) .
								   '<br>' . __( 'If optional, users may redeem a voucher during open registration.', 'lifterlms' ) .
								   '<br>' . __( 'If hidden, users can only redeem vouchers on their dashboard.', 'lifterlms' ),
				'id'            => 'lifterlms_voucher_field_registration_visibility',
				'title'         => __( 'Voucher', 'lifterlms' ),
				'type'          => 'select',
				'options'       => $field_options,
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
