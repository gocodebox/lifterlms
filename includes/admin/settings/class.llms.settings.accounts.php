<?php
/**
 * Admin Settings Page, Accounts Tab
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, Accounts Tab class
 *
 * @since 1.0.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.37.3 Renamed setting field IDs to be unique.
 *               Removed redundant functions defined in the `LLMS_Settings_Page` class.
 *               Removed constructor and added `get_label()` method to be compatible with changes in `LLMS_Settings_Page`.
 * @since 3.37.4 Revert $id to "account".
 */
class LLMS_Settings_Accounts extends LLMS_Settings_Page {

	/**
	 * Settings identifier
	 *
	 * @var string
	 */
	public $id = 'account';

	/**
	 * Should permalinks be flushed on save?
	 *
	 * @var boolean
	 */
	protected $flush = true;

	/**
	 * Get settings array
	 *
	 * @since 1.0.0
	 * @since 3.30.3 Fixed spelling errors.
	 * @since 3.37.3 Renamed duplicate field id for section close (`user_info_field_options` to `user_info_field_options_end`)
	 * @since 5.0.0 Removed field display settings.
	 *              Reorganized open registration setting.
	 *              Renamed "User Information Options" to "User Privacy Options".
	 * @since 5.6.0 Added options to disable concurrent logins.
	 * @since 7.5.0 Added settings for favorites endpoint.
	 *
	 * @return array
	 */
	public function get_settings() {
		$account_settings = array(
			array(
				'class' => 'top',
				'id'    => 'course_account_options',
				'type'  => 'sectionstart',
			),
			array(
				'id'    => 'account_page_options_start',
				'title' => __( 'Student Dashboard', 'lifterlms' ),
				'type'  => 'title',
			),
			array(
				'title'             => __( 'Dashboard Page', 'lifterlms' ),
				'desc'              => __( 'Page where students can view and manage their current enrollments, earned certificates and achievements, account information, and purchase history.', 'lifterlms' ) . ' ' . sprintf( __( 'Requires the %1$s[lifterlms_my_account]%2$s shortcode or the "My Account" block.', 'lifterlms' ), '<code>', '</code>' ),
				'id'                => 'lifterlms_myaccount_page_id',
				'default'           => '',
				'desc_tip'          => true,
				'class'             => 'llms-select2-post',
				'type'              => 'select',
				'custom_attributes' => array(
					'data-post-type'   => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'options'           => llms_make_select2_post_array( get_option( 'lifterlms_myaccount_page_id', '' ) ),
			),
			array(
				'title'   => __( 'Courses Sorting', 'lifterlms' ),
				'default' => 'order,ASC',
				'desc'    => __( 'Determines the order of the courses in-progress listed on the student dashboard.', 'lifterlms' ),
				'id'      => 'lifterlms_myaccount_courses_in_progress_sorting',
				'type'    => 'select',
				'options' => array(
					'title,ASC'  => __( 'Course Title (A to Z)', 'lifterlms' ),
					'title,DESC' => __( 'Course Title (Z to A)', 'lifterlms' ),
					'date,DESC'  => __( 'Enrollment Date (Most Recent to Least Recent)', 'lifterlms' ),
					'order,ASC'  => __( 'Order (Low to High)', 'lifterlms' ),
					'order,DESC' => __( 'Order (High to Low)', 'lifterlms' ),
				),
			),
			array(
				'default' => 'no',
				'desc'    => sprintf(
					// Translators: %1$s = opening anchor tag; %2$s = closing anchor tag.
					__( 'Enable new user registration on the Student Dashboard. %1$sLearn More%2$s.', 'lifterlms' ),
					'<a href="https://lifterlms.com/docs/open-registration/" target="_blank">',
					'</a>'
				),
				'id'      => 'lifterlms_enable_myaccount_registration',
				'title'   => __( 'Open Registration', 'lifterlms' ),
				'type'    => 'checkbox',
			),
			array(
				'default'           => 'no',
				'desc'              => __( 'Only allow the most recent login for each user account.', 'lifterlms' ),
				'id'                => 'lifterlms_prevent_concurrent_logins',
				'title'             => __( 'Prevent concurrent logins', 'lifterlms' ),
				'type'              => 'checkbox',
				'custom_attributes' => array(
					'class'         => 'llms-conditional-controller',
					'data-controls' => '#lifterlms_prevent_concurrent_logins_roles',
				),
			),
			array(
				'class'             => 'llms-select2',
				'default'           => array( 'student' ),
				'desc'              => __( 'Prevent concurrent logins for users with the selected user roles.', 'lifterlms' ),
				'id'                => 'lifterlms_prevent_concurrent_logins_roles',
				'options'           => LLMS_Roles::get_all_role_names(),
				'title'             => '',
				'type'              => 'multiselect',
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select user roles', 'lifterlms' ),
				),
			),
			array(
				'id'   => 'course_account_options_end',
				'type' => 'sectionend',
			),
			array(
				'class' => 'top',
				'id'    => 'course_account_endpoint_options_start',
				'type'  => 'sectionstart',
			),
			array(
				'id'    => 'account_page_endpoint_options_title',
				'title' => __( 'Student Dashboard Endpoints', 'lifterlms' ),
				'desc'  => __( 'Each endpoint allows students to view more information or manage parts of their account. Each endpoint should be unique, URL-safe, and can be left blank to disable the endpoint completely.', 'lifterlms' ),
				'type'  => 'title',
			),
			array(
				'title'    => __( 'View Grades', 'lifterlms' ),
				'desc'     => __( 'Student grade and progress reporting', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_grades_endpoint',
				'type'     => 'text',
				'default'  => 'my-grades',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'View Courses', 'lifterlms' ),
				'desc'     => __( 'List of all the student\'s courses', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_courses_endpoint',
				'type'     => 'text',
				'default'  => 'my-courses',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'View Memberships', 'lifterlms' ),
				'desc'     => __( 'List of all the student\'s memberships', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_memberships_endpoint',
				'type'     => 'text',
				'default'  => 'my-memberships',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'View Achievements', 'lifterlms' ),
				'desc'     => __( 'List of all the student\'s achievements', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_achievements_endpoint',
				'type'     => 'text',
				'default'  => 'my-achievements',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'View Certificates', 'lifterlms' ),
				'desc'     => __( 'List of all the student\'s certificates', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_certificates_endpoint',
				'type'     => 'text',
				'default'  => 'my-certificates',
				'sanitize' => 'slug',
			),

			array(
				'title'    => __( 'Notifications', 'lifterlms' ),
				'desc'     => __( 'View Notifications and adjust notification settings', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_notifications_endpoint',
				'type'     => 'text',
				'default'  => 'notifications',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'Edit Account', 'lifterlms' ),
				'desc'     => __( 'Edit Account page', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_edit_account_endpoint',
				'type'     => 'text',
				'default'  => 'edit-account',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'Lost Password', 'lifterlms' ),
				'desc'     => __( 'Lost Password page', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_lost_password_endpoint',
				'type'     => 'text',
				'default'  => 'lost-password',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'Redeem Vouchers', 'lifterlms' ),
				'desc'     => __( 'Redeem vouchers page', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_redeem_vouchers_endpoint',
				'type'     => 'text',
				'default'  => 'redeem-voucher',
				'sanitize' => 'slug',
			),
			array(
				'title'    => __( 'Orders History', 'lifterlms' ),
				'desc'     => __( 'Students can review order history on this page', 'lifterlms' ),
				'id'       => 'lifterlms_myaccount_orders_endpoint',
				'type'     => 'text',
				'default'  => 'orders',
				'sanitize' => 'slug',
			),
			array(
				'id'   => 'course_account_endpoint_options_end',
				'type' => 'sectionend',
			),

			// Start user info fields options.
			array(
				'id'   => 'user_info_field_options',
				'type' => 'sectionstart',
			),
			array(
				'title' => __( 'User Information & Privacy Options', 'lifterlms' ),
				'type'  => 'title',
				'id'    => 'user_info_field_options_title',
			),

			array(
				'title' => __( 'User Information Field Settings', 'lifterlms' ),
				'type'  => 'subtitle',
				'desc'  => __( 'Since version 5.0, all user information fields are customized using the form editor.', 'lifterlms' ),
			),
			array(
				'type'  => 'custom-html',
				'value' => '<p><a class="button-primary" href="' . admin_url( 'edit.php?post_type=llms_form' ) . '">' . __( 'Edit Forms', 'lifterlms' ) . '</a></p>',
			),

			array(
				'title' => __( 'Terms and Conditions', 'lifterlms' ),
				'type'  => 'subtitle',
			),
			array(
				'autoload'          => false,
				'default'           => 'no',
				'id'                => 'lifterlms_registration_require_agree_to_terms',
				'desc'              => __( 'When enabled users must agree to your site\'s Terms and Conditions to register for an account.', 'lifterlms' ),
				'title'             => __( 'Enable / Disable', 'lifterlms' ),
				'type'              => 'checkbox',
				'custom_attributes' => array(
					'class'         => 'llms-conditional-controller',
					'data-controls' => '#lifterlms_terms_page_id,#llms_terms_notice',
				),
			),
			array(
				'autoload'          => false,
				'desc'              => __( 'Select a page where your site\'s Terms and Conditions are described.', 'lifterlms' ),
				'id'                => 'lifterlms_terms_page_id',
				'default'           => '',
				'desc_tip'          => true,
				'class'             => 'llms-select2-post',
				'title'             => __( 'Terms and Conditions Page', 'lifterlms' ),
				'type'              => 'select',
				'custom_attributes' => array(
					'data-post-type'   => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'options'           => llms_make_select2_post_array( get_option( 'lifterlms_terms_page_id', '' ) ),
			),
			array(
				'autoload' => false,
				'default'  => llms_get_terms_notice(),
				'id'       => 'llms_terms_notice',
				'desc'     => __( 'Customize the text used to display the Terms and Conditions checkbox that students must accept.', 'lifterlms' ),
				'title'    => __( 'Terms and Conditions Notice', 'lifterlms' ),
				'type'     => 'textarea',
				'value'    => llms_get_terms_notice(),
			),
			array(
				'title' => __( 'Privacy Policy', 'lifterlms' ),
				'type'  => 'subtitle',
			),
			array(
				'autoload'          => false,
				'desc'              => sprintf(
					__( 'Select a page where your site\'s Privacy Policy is described. See %1$sWordPress Privacy Settings%2$s for more information', 'lifterlms' ),
					'<a href="' . esc_url( admin_url( 'privacy.php' ) ) . '">',
					'</a>'
				),
				'id'                => 'wp_page_for_privacy_policy',
				'class'             => 'llms-select2-post',
				'title'             => __( 'Privacy Policy Page', 'lifterlms' ),
				'type'              => 'select',
				'custom_attributes' => array(
					'data-post-type'   => 'page',
					'data-placeholder' => __( 'Select a page', 'lifterlms' ),
				),
				'options'           => llms_make_select2_post_array( get_option( 'wp_page_for_privacy_policy' ) ),
			),
			array(
				'autoload' => false,
				'default'  => llms_get_privacy_notice(),
				'id'       => 'llms_privacy_notice',
				'desc'     => __( 'Optionally display a privacy policy notice during registration and checkout.', 'lifterlms' ),
				'title'    => __( 'Privacy Policy Notice', 'lifterlms' ),
				'type'     => 'textarea',
			),
			array(
				'title' => __( 'Account Erasure Requests', 'lifterlms' ),
				/* Translators: %$1s = opening anchor to account erasure screen; %2$s closing anchor */
				'desc'  => sprintf( __( 'Customize data retention during %1$saccount erasure requests%2$s.', 'lifterlms' ), '<a href="' . esc_url( admin_url( 'tools.php?page=remove_personal_data' ) ) . '">', '</a>' ),
				'type'  => 'subtitle',
			),
			array(
				'autoload' => false,
				'default'  => 'no',
				'id'       => 'llms_erasure_request_removes_order_data',
				'desc'     => __( 'When enabled orders will be anonymized during a personal data erasure.', 'lifterlms' ),
				'title'    => __( 'Remove Order Data', 'lifterlms' ),
				'type'     => 'checkbox',
			),
			array(
				'autoload' => false,
				'default'  => 'no',
				'id'       => 'llms_erasure_request_removes_lms_data',
				'desc'     => __( 'When enabled all student data related to course and membership activities will be removed.', 'lifterlms' ),
				'title'    => __( 'Remove Student LMS Data', 'lifterlms' ),
				'type'     => 'checkbox',
			),
			array(
				'id'   => 'user_info_field_options_end',
				'type' => 'sectionend',
			),

		);

		if ( llms_is_favorites_enabled() ) {
			array_splice(
				$account_settings,
				15,
				0,
				array(
					array(
						'title'    => __( 'View Favorites', 'lifterlms' ),
						'desc'     => __( 'List of all the student\'s favorites', 'lifterlms' ),
						'id'       => 'lifterlms_myaccount_favorites_endpoint',
						'type'     => 'text',
						'default'  => 'my-favorites',
						'sanitize' => 'slug',
					),
				)
			);
		}

		/**
		 * Filters the account settings.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the settings page.
		 *
		 * @since Unknown
		 *
		 * @param array $account_settings The account page settings.
		 */
		return apply_filters( "lifterlms_{$this->id}_settings", $account_settings );
	}

	/**
	 * Retrieve the page label.
	 *
	 * @since 3.37.3
	 *
	 * @return string
	 */
	protected function set_label() {
		return __( 'Accounts', 'lifterlms' );
	}
}

return new LLMS_Settings_Accounts();
