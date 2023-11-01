<?php
/**
 * Test LLMS_Settings_Accounts
 *
 * @package LifterLMS/Tests
 *
 * @group admin
 * @group settings_page
 * @group settings_page_accounts
 *
 * @since 3.37.3
 * @since 3.37.4 The ID is "account" not "accounts".
 */
class LLMS_Test_Settings_Accounts extends LLMS_Settings_Page_Test_Case {

	/**
	 * Classname.
	 *
	 * @var string
	 */
	protected $classname = 'LLMS_Settings_Accounts';

	/**
	 * Expected class $id property.
	 *
	 * @var string
	 */
	protected $class_id = 'account';

	/**
	 * Expected class $label property.
	 *
	 * @var string
	 */
	protected $class_label = 'Accounts';

	/**
	 * Return an array of mock settings and possible values.
	 *
	 * @since 3.37.3
	 *
	 * @return void
	 */
	protected function get_mock_settings() {

		$pages = array(
			$this->factory->post->create( array( 'post_type' => 'page' ) ),
			$this->factory->post->create( array( 'post_type' => 'page' ) ),
		);


		$settings = array(
			'lifterlms_myaccount_page_id' => $pages,
			'lifterlms_myaccount_courses_in_progress_sorting' => array(
				'title,ASC',
				'title,DESC',
				'date,DESC',
				'order,ASC',
				'order,DESC',
			),
			'lifterlms_enable_myaccount_registration' => array(
				'yes',
			),
			'lifterlms_prevent_concurrent_logins' => array(
				'yes',
			),
			'lifterlms_prevent_concurrent_logins_roles' => array(
				array( '' ),
				array( 'student' ),
			),
			'lifterlms_myaccount_grades_endpoint' => array(
				'my-grades',
				'custom-endpoint-grades',
			),
			'lifterlms_myaccount_courses_endpoint' => array(
				'my-courses',
				'custom-endpoint-courses',
			),
			'lifterlms_myaccount_memberships_endpoint' => array(
				'my-memberships',
				'custom-endpoint-memberships',
			),
			'lifterlms_myaccount_achievements_endpoint' => array(
				'my-achievements',
				'custom-endpoint-achievements',
			),
			'lifterlms_myaccount_certificates_endpoint' => array(
				'my-certificates',
				'custom-endpoint-certificates',
			),
			'lifterlms_myaccount_favorites_endpoint' => array(
				'my-favorites',
				'custom-endpoint-favorites',
			),
			'lifterlms_myaccount_notifications_endpoint' => array(
				'notifications',
				'custom-endpoint-notifications',
			),
			'lifterlms_myaccount_edit_account_endpoint' => array(
				'edit-account',
				'custom-endpoint-account',
			),
			'lifterlms_myaccount_lost_password_endpoint' => array(
				'lost-password',
				'custom-endpoint-reset-pass',
			),
			'lifterlms_myaccount_redeem_vouchers_endpoint' => array(
				'redeem-voucher',
				'custom-redemption-code'
			),
			'lifterlms_myaccount_orders_endpoint' => array(
				'orders',
				'custom-order-history',
			),
			'lifterlms_registration_require_agree_to_terms' => array(
				'yes',
			),
			'lifterlms_terms_page_id' => $pages,
			'llms_terms_notice' => array(
				llms_get_terms_notice(),
				'mock terms notice',
			),
			'wp_page_for_privacy_policy' => $pages,
			'llms_privacy_notice' => array(
				llms_get_privacy_notice(),
				'mock privacy notice',
			),
			'llms_erasure_request_removes_order_data' => array(
				'yes',
			),
			'llms_erasure_request_removes_lms_data' => array(
				'yes',
			),
		);

		if ( ! llms_is_favorites_enabled() ) {
			unset( $settings['lifterlms_myaccount_favorites_endpoint'] );
		}

		return $settings;
	}

}
