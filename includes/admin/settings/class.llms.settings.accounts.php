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

		return apply_filters( 'lifterlms_' . $this->id . '_settings', array(

			array( 'type' => 'sectionstart', 'id' => 'course_account_options', 'class' => 'top' ),

			array( 'title' => __( 'Account Settings', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Customize your users account and sign up experience.', 'lifterlms' ), 'id' => 'account_page_options' ),

			array(
				'title' => __( 'Account Access Page', 'lifterlms' ),
				'desc' 		=> __( 'Page used for login / my account access. You can also place this shortcode on any page: ', 'lifterlms' ) . ' [' . apply_filters( 'lifterlms_my_account_shortcode_tag', 'lifterlms_my_account' ) . ']',
				'id' 		=> 'lifterlms_myaccount_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
				'desc_tip'	=> true,
			),

			array(
				'desc'          => __( 'Display Student Memberships on Account Page', 'lifterlms' ),
				'id'            => 'lifterlms_enable_myaccount_memberships_list',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),

			array( 'type' => 'sectionend', 'id' => 'course_account_options' ),

			array( 'type' => 'sectionstart', 'id' => 'account_page_options' ),

			array( 'title' => __( 'Account Page Slugs', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'These slugs represent different actions in the user account.', 'lifterlms' ), 'id' => 'account_endpoint_options' ),

			array(
				'title' => __( 'My Courses', 'lifterlms' ),
				'desc' 		=> __( 'Purchased courses page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_my_courses_endpoint',
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

			array( 'type' => 'sectionstart', 'id' => 'account_registration_options' ),

			array( 'title' => __( 'User Registration Options', 'lifterlms' ), 'type' => 'title', 'id' => 'Customize the registration experience for users.' ),

			array(
				'desc'          => __( 'Enable user registration on login page.', 'lifterlms' ),
				'id'            => 'lifterlms_enable_myaccount_registration',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false,
			),

			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Use email addresses for usernames.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_generate_username',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
				'autoload'      => false,
			),

			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Require users to enter their email address twice for accuracy', 'lifterlms' ),
				'id'            => 'lifterlms_registration_confirm_email',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
				'autoload'      => false,
			),

			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Require First and Last name on user registration.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_require_name',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
				'autoload'      => false,
			),
			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Require Billing Address on user registration.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_require_address',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
				'autoload'      => false,
			),
			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Add phone number field to user registration.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_add_phone',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
				'autoload'      => false,
			),
			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Add a required "I Agree to the Terms and Conditions" checkbox on user registration', 'lifterlms' ),
				'id'            => 'lifterlms_registration_require_agree_to_terms',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'middle',
				'autoload'      => false,
			),
			array(
				'title' => __( 'Terms and Conditions Page', 'lifterlms' ),
				'desc' 		=> __( 'Select the page where your Terms and Conditions are described.', 'lifterlms' ),
				'id' 		=> 'lifterlms_terms_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> 0,
				'class'		=> 'chosen_select_nostd',
				'css' 		=> 'min-width:200px;',
				'desc_tip'	=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'account_registration_options' ),

		)); // End pages settings
	}

}

return new LLMS_Settings_Accounts();
