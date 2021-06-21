<?php
/**
 * Default LifterLMS Form location definitions
 *
 * This file returns a list of the default LifterLMS form locations required
 * by LifterLMS for allowing users to create and manage accounts on the website.
 *
 * @package LifterLMS/Schemas
 *
 * @since 5.0.0
 * @version 5.0.0
 *
 * @see LLMS_Forms::get_locations() The core method used to retrieve the form locations schema.
 * @see llms_forms_get_locations Filter the form locations schema.
 */

defined( 'ABSPATH' ) || exit;

return array(
	'checkout'     => array(
		'name'        => __( 'Checkout', 'lifterlms' ),
		'description' => __( 'Handles new user registration and existing user information updates during checkout and enrollment.', 'lifterlms' ),
		'title'       => __( 'Billing Information', 'lifterlms' ),
		'template'    => LLMS_Form_Templates::get_template( 'checkout' ),
		'required'    => array(
			array(
				'fieldName' => 'email_address',
				'blockName' => 'llms/form-field-user-email',
			),
			array(
				'fieldName' => 'password',
				'blockName' => 'llms/form-field-user-password',
			),
		),
		'meta'        => array(
			'_llms_form_location'   => 'checkout',
			'_llms_form_show_title' => 'yes',
			'_llms_form_is_core'    => 'yes',
		),
	),
	'registration' => array(
		'name'        => __( 'Registration', 'lifterlms' ),
		'description' => __( 'Handles new user registration and existing user information updates for open registration on the student dashboard and wherever the [lifterlms_registration] shortcode is used.', 'lifterlms' ),
		'title'       => __( 'Register', 'lifterlms' ),
		'template'    => LLMS_Form_Templates::get_template( 'registration' ),
		'required'    => array(
			array(
				'fieldName' => 'email_address',
				'blockName' => 'llms/form-field-user-email',
			),
			array(
				'fieldName' => 'password',
				'blockName' => 'llms/form-field-user-password',
			),
		),
		'meta'        => array(
			'_llms_form_location'   => 'registration',
			'_llms_form_show_title' => 'yes',
			'_llms_form_is_core'    => 'yes',
		),
	),
	'account'      => array(
		'name'        => __( 'Account', 'lifterlms' ),
		'description' => __( 'Handles user account information updates on the edit account area of the student dashboard.', 'lifterlms' ),
		'title'       => __( 'Edit Account Information', 'lifterlms' ),
		'template'    => LLMS_Form_Templates::get_template( 'account' ),
		'required'    => array(
			array(
				'fieldName' => 'email_address',
				'blockName' => 'llms/form-field-user-email',
			),
			array(
				'fieldName' => 'password',
				'blockName' => 'llms/form-field-user-password',
			),
			array(
				'fieldName' => 'display_name',
				'blockName' => 'llms/form-field-user-display-name',
			),
		),
		'meta'        => array(
			'_llms_form_location'   => 'account',
			'_llms_form_show_title' => 'no',
			'_llms_form_is_core'    => 'yes',
		),
	),
);
