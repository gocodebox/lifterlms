<?php
/**
 * Admin Settings Page, Security Tab
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 9.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Settings Page, Security Tab class
 *
 * @since 9.0.0
 */
class LLMS_Settings_Security extends LLMS_Settings_Page {

	/**
	 * Settings identifier
	 *
	 * @var string
	 */
	public $id = 'security';

	/**
	 * Get settings array
	 *
	 * @since 9.0.0
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
				'title' => __( 'Website Security & Spam Prevention', 'lifterlms' ),
				'type'  => 'title',
				'id'    => 'security_and_spam_options_title',
			),
			array(
				'autoload'          => false,
				'default'           => '',
				'id'                => 'lifterlms_captcha',
				'desc'              => __( 'Choose a captcha service to require at checkout.', 'lifterlms' ),
				'title'             => __( 'Captcha', 'lifterlms' ),
				'type'              => 'select',
				'options'           => array(
					''          => __( 'None', 'lifterlms' ),
					'recaptcha' => __( 'reCAPTCHA', 'lifterlms' ),
					'turnstile' => __( 'Turnstile', 'lifterlms' ),
				),
				'class'             => 'llms-conditional-controller',
				'custom_attributes' => array(
					'data-controls-recaptcha' => '#lifterlms_recaptcha_site_key,#lifterlms_recaptcha_secret_key,#lifterlms_recaptcha_min_score',
					'data-controls-turnstile' => '#lifterlms_turnstile_site_key,#lifterlms_turnstile_secret_key',
				),
			),
			array(
				'autoload' => false,
				'default'  => '',
				'id'       => 'lifterlms_recaptcha_site_key',
				'desc'     => 'Requires reCAPTCHA v3 keys. <a href="https://lifterlms.com/docs/recaptcha" target="_blank">Learn More</a>.',
				'title'    => __( 'reCAPTCHA v3 Site Key', 'lifterlms' ),
				'type'     => 'text',
			),
			array(
				'autoload' => false,
				'default'  => '',
				'id'       => 'lifterlms_recaptcha_secret_key',
				'desc'     => '',
				'title'    => __( 'reCAPTCHA v3 Secret Key', 'lifterlms' ),
				'type'     => 'text',
			),
			array(
				'autoload'          => false,
				'default'           => '5',
				'id'                => 'lifterlms_recaptcha_min_score',
				'desc'              => 'The minimum score required for reCAPTCHA validation, from 1-10. Google recommends 5 as a default. <a href="https://lifterlms.com/docs/recaptcha" target="_blank">Learn More</a>.',
				'title'             => __( 'reCAPTCHA Minimum Score', 'lifterlms' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min' => '1',
					'max' => '10',
				),
			),
			array(
				'autoload' => false,
				'default'  => '',
				'id'       => 'lifterlms_turnstile_site_key',
				'desc'     => 'Requires Cloudflare Turnstile keys. <a href="https://lifterlms.com/docs/turnstile" target="_blank">Learn More</a>.',
				'title'    => __( 'Turnstile Site Key', 'lifterlms' ),
				'type'     => 'text',
			),
			array(
				'autoload' => false,
				'default'  => '',
				'id'       => 'lifterlms_turnstile_secret_key',
				'desc'     => '',
				'title'    => __( 'Turnstile Secret Key', 'lifterlms' ),
				'type'     => 'text',
			),
			array(
				'autoload' => false,
				'default'  => 'yes',
				'id'       => 'lifterlms_spam_protection',
				'desc'     => __( 'Block IPs from checkout if there are more than 10 failures within 15 minutes.', 'lifterlms' ),
				'title'    => __( 'Spam Protection', 'lifterlms' ),
				'type'     => 'checkbox',
			),
		);

		if ( LLMS_Akismet::instance()->is_available() ) {
			$account_settings[] = array(
				'autoload' => false,
				'default'  => 'no',
				'id'       => 'lifterlms_akismet_enabled',
				'desc'     => __( 'Enable Akismet spam protection.', 'lifterlms' ),
				'title'    => __( 'Akismet Spam Protection', 'lifterlms' ),
				'type'     => 'checkbox',
			);
		}

		$account_settings[] =
			array(
				'id'   => 'security_and_spam_options_end',
				'type' => 'sectionend',
			);

		/**
		 * Filters the account settings.
		 *
		 * The dynamic portion of this filter `{$this->id}` refers to the unique ID for the settings page.
		 *
		 * @since 9.0.0
		 *
		 * @param array $account_settings The account page settings.
		 */
		return apply_filters( "lifterlms_{$this->id}_settings", $account_settings );
	}

	/**
	 * Retrieve the page label.
	 *
	 * @since 9.0.0
	 *
	 * @return string
	 */
	protected function set_label() {
		return __( 'Security', 'lifterlms' );
	}
}

return new LLMS_Settings_Security();
