<?php
/**
 * Default form field blocks schema
 *
 * This file returns a list of the default LifterLMS form fields
 * used to build an initial set of reusable blocks used across the
 * core user information forms (checkout, registration, and account).
 *
 * Each field block is an incomplete form field definition. Each field
 * is linked to a user information form field by its name attribute which
 * will match an info field by its id attribute.
 *
 * User information fields are defined in `includes/schemas/llms-user-information-fields.php.
 *
 * @package LifterLMS/Schemas
 *
 * @since 5.0.0
 * @version 5.3.1
 */

defined( 'ABSPATH' ) || exit;

return array(
	'username'     => array(
		'title'     => _x( 'Username (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-login',
		'attrs'     => array(
			'required'        => true,
			'id'              => 'user_login',
			'llms_visibility' => 'logged_out',
		),
	),
	'email'        => array(
		'title'     => _x( 'Email Address (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-email',
		'attrs'     => array(
			'required'        => true,
			'id'              => 'email_address',
			'llms_visibility' => 'logged_out',
		),
		'confirm'   => 'email',
	),
	'password'     => array(
		'title'     => _x( 'Password (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-password',
		'attrs'     => array(
			'required'        => true,
			'id'              => 'password',
			'llms_visibility' => 'logged_out',
		),
		'confirm'   => 'password',
	),
	'name'         => array(
		'title'       => _x( 'First and Last Name (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName'   => 'llms/form-field-user-name',
		'innerBlocks' => array(
			array(
				'blockName' => 'llms/form-field-user-first-name',
				'attrs'     => array(
					'id'          => 'first_name',
					'required'    => true,
					'columns'     => 6,
					'last_column' => false,
				),
			),
			array(
				'blockName' => 'llms/form-field-user-last-name',
				'attrs'     => array(
					'id'          => 'last_name',
					'required'    => true,
					'columns'     => 6,
					'last_column' => true,
				),
			),
		),
	),
	'display_name' => array(
		'title'     => _x( 'Public Display Name (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-display-name',
		'attrs'     => array(
			'required' => true,
			'id'       => 'display_name',
		),
	),
	'address'      => array(
		'title'       => _x( 'Address (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName'   => 'llms/form-field-user-address',
		'innerBlocks' => array(
			array(
				'blockName'   => 'llms/form-field-user-address-street',
				'innerBlocks' => array(
					array(
						'blockName' => 'llms/form-field-user-address-street-primary',
						'attrs'     => array(
							'id'          => 'llms_billing_address_1',
							'required'    => true,
							'columns'     => 8,
							'last_column' => false,
						),
					),
					array(
						'blockName' => 'llms/form-field-user-address-street-secondary',
						'attrs'     => array(
							'id'          => 'llms_billing_address_2',
							'required'    => false,
							'columns'     => 4,
							'last_column' => true,
						),
					),
				),
			),
			array(
				'blockName' => 'llms/form-field-user-address-city',
				'attrs'     => array(
					'id'       => 'llms_billing_city',
					'required' => true,
				),
			),
			array(
				'blockName' => 'llms/form-field-user-address-country',
				'attrs'     => array(
					'id'       => 'llms_billing_country',
					'required' => true,
				),
			),
			array(
				'blockName'   => 'llms/form-field-user-address-region',
				'innerBlocks' => array(
					array(
						'blockName' => 'llms/form-field-user-address-state',
						'attrs'     => array(
							'id'          => 'llms_billing_state',
							'required'    => true,
							'columns'     => 6,
							'last_column' => false,
						),
					),
					array(
						'blockName' => 'llms/form-field-user-address-postal-code',
						'attrs'     => array(
							'id'          => 'llms_billing_zip',
							'required'    => true,
							'columns'     => 6,
							'last_column' => true,
						),
					),
				),
			),
		),
	),
	'phone'        => array(
		'title'     => _x( 'Phone Number (Reusable)', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-phone',
		'attrs'     => array(
			'id'       => 'llms_phone',
			'required' => false,
		),
	),
);
