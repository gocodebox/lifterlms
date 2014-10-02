<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'LLMS_Settings_Accounts' ) ) :

/**
* Admin Settings Page, Accounts Tab
*
* @version 1.0
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

			array( 'title' => __( 'Account Settings', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'Customize your users account and sign up experience.', 'lifterlms' ), 'id' => 'account_page_options' ),

			array(
				'title' => __( 'Account Access Page', 'lifterlms' ),
				'desc' 		=> __( 'Page used for login / my account access. You can also place this shortcode on any page: ', 'lifterlms' ) . ' [' . apply_filters( 'lifterlms_my_account_shortcode_tag', 'lifterlms_my_account' ) . ']',
				'id' 		=> 'lifterlms_myaccount_page_id',
				'type' 		=> 'single_select_page',
				'default'	=> '',
				'class'		=> 'chosen_select_nostd',
				'css' 		=> 'min-width:300px;',
				'desc_tip'	=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'account_page_options' ),

			array( 'title' => __( 'My Account Page Slugs', 'lifterlms' ), 'type' => 'title', 'desc' => __( 'These slugs represent different actions in the user account.', 'lifterlms' ), 'id' => 'account_endpoint_options' ),

			array(
				'title' => __( 'My Courses', 'lifterlms' ),
				'desc' 		=> __( 'Purchased courses page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_my_courses_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-courses',
				'desc_tip'	=> true,
			),

			array(
				'title' => __( 'My Badges', 'lifterlms' ),
				'desc' 		=> __( 'Earned badges page', 'lifterlms' ),
				'id' 		=> 'lifterlms_myaccount_my_badges_endpoint',
				'type' 		=> 'text',
				'default'	=> 'my-badges',
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
				'title' => __( 'Logout', 'lifterlms' ),
				'desc' 		=> __( 'Custom Link: yoursite.com/?person-logout=true', 'lifterlms' ),
				'id' 		=> 'lifterlms_logout_endpoint',
				'type' 		=> 'text',
				'default'	=> 'logout',
				'desc_tip'	=> true,
			),

			array( 'type' => 'sectionend', 'id' => 'account_endpoint_options' ),

			array(	'title' => __( 'User Registration Options', 'lifterlms' ), 'type' => 'title', 'id' => 'Customize the registration experience for users.' ),

			array(
				'title'         => __( 'Enable Registration', 'lifterlms' ),
				'desc'          => __( 'Create an account for someone when they purchase a course (we\'ll send them an email with credentials.)', 'lifterlms' ),
				'id'            => 'lifterlms_enable_signup_and_login_from_checkout',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false
			),

			array(
				'desc'          => __( 'Allow users sign up for an account without purchasing a course.', 'lifterlms' ),
				'id'            => 'lifterlms_enable_myaccount_registration',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'autoload'      => false
			),

			array(
				'title'         => __( 'Account Creation', 'lifterlms' ),
				'desc'          => __( 'Use email addresses for usernames.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_generate_username',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false
			),

			array(
				'desc'          => __( 'Auto-generate passwords when users sign up.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_generate_password',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'autoload'      => false
			),

			array(
				'title'         => __( 'Course Access', 'lifterlms' ),
				'desc'          => __( 'Require user signup for free courses.', 'lifterlms' ),
				'id'            => 'lifterlms_registration_require_signup',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'autoload'      => false
			),

			array( 'type' => 'sectionend', 'id' => 'account_registration_options'),

		)); // End pages settings
	}

}

endif;

return new LLMS_Settings_Accounts();
