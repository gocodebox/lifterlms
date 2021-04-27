<?php



return array(
	'username'     => array(
		'title'     => _x( 'Default Field: Username', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-login',
		'attrs'     => array(
			'field'           => 'text',
			'required'        => true,
			'label'           => __( 'Username', 'lifterlms' ),
			'name'            => 'user_login',
			'id'              => 'user_login',
			'data_store'      => 'users',
			'data_store_key'  => 'user_login',
			'llms_visibility' => 'logged_out',
		),
	),
	'email'        => array(
		'title'     => _x( 'Default Field: Email Address', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-email',
		'attrs'     => array(
			'field'           => 'email',
			'required'        => true,
			'label'           => __( 'Email Address', 'lifterlms' ),
			'name'            => 'email_address',
			'id'              => 'email_address',
			'data_store'      => 'users',
			'data_store_key'  => 'user_email',
			'llms_visibility' => 'logged_out',
		),
		'confirm'   => 'email',
	),
	'password'     => array(
		'title'     => _x( 'Default Field: Password', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-password',
		'attrs'     => array(
			'field'             => 'text',
			'required'          => true,
			'label'             => __( 'Password', 'lifterlms' ),
			'name'              => 'password',
			'id'                => 'password',
			'data_store'        => 'users',
			'data_store_key'    => 'user_pass',
			'llms_visibility'   => 'logged_out',
			'meter'             => llms_parse_bool( get_option( 'lifterlms_registration_password_strength', 'yes' ) ),
			'min_strength'      => get_option( 'lifterlms_registration_password_min_strength', 'strong' ),
			'html_attrs'        => array(
				'minlength' => 8,
			),
			'meter_description' => sprintf(
				// Translators: %1$s = Min strength merge code; %2$s = min length merge code.
				__(
					'A %1$s password is required with at least %2$s characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.',
					'lifterlms'
				),
				'{min_strength}',
				'{min_length}'
			),
		),
		'confirm'   => 'password',
	),
	'name'         => array(
		'title'       => _x( 'Default Field: First and Last Name', 'Default form field reusable block title', 'lifterlms' ),
		'blockName'   => 'llms/form-field-user-name',
		'innerBlocks' => array(
			array(
				'blockName' => 'llms/form-field-user-last-name',
				'attrs'     => array(
					'field'          => 'text',
					'label'          => __( 'First Name', 'lifterlms' ),
					'name'           => 'first_name',
					'id'             => 'first_name',
					'data_store'     => 'usermeta',
					'data_store_key' => 'first_name',
					'columns'        => 6,
					'last_column'    => false,
				),
			),
			array(
				'blockName' => 'llms/form-field-user-last-name',
				'attrs'     => array(
					'field'          => 'text',
					'label'          => __( 'Last Name', 'lifterlms' ),
					'name'           => 'last_name',
					'id'             => 'last_name',
					'data_store'     => 'usermeta',
					'data_store_key' => 'last_name',
					'columns'        => 6,
					'last_column'    => true,
				),
			),
		),
	),
	'display_name' => array(
		'title'     => _x( 'Default Field: Public Display Name', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-display-name',
		'attrs'     => array(
			'field'          => 'text',
			'required'       => true,
			'label'          => __( 'Display Name', 'lifterlms' ),
			'name'           => 'display_name',
			'id'             => 'display_name',
			'data_store'     => 'users',
			'data_store_key' => 'display_name',
		),
	),
	'address'      => array(
		'title'       => _x( 'Default Field: Address', 'Default form field reusable block title', 'lifterlms' ),
		'blockName'   => 'llms/form-field-user-address',
		'innerBlocks' => array(
			array(
				'blockName'   => 'llms/form-field-user-address-street',
				'innerBlocks' => array(
					array(
						'blockName' => 'llms/form-field-user-address-street-primary',
						'attrs'     => array(
							'field'          => 'text',
							'label'          => __( 'Address', 'lifterlms' ),
							'name'           => 'llms_billing_address_1',
							'id'             => 'llms_billing_address_1',
							'data_store'     => 'usermeta',
							'data_store_key' => 'llms_billing_address_1',
							'columns'        => 8,
							'last_column'    => false,
						),
					),
					array(
						'blockName' => 'llms/form-field-user-address-street-secondary',
						'attrs'     => array(
							'field'            => 'text',
							'label'            => '',
							'label_show_empty' => true,
							'placeholder'      => __( 'Apartment, suite, etc...', 'lifterlms' ),
							'name'             => 'llms_billing_address_2',
							'id'               => 'llms_billing_address_2',
							'data_store'       => 'usermeta',
							'data_store_key'   => 'llms_billing_address_2',
							'columns'          => 4,
							'last_column'      => true,
						),
					),
				),
			),
			array(
				'blockName' => 'llms/form-field-user-address-city',
				'attrs'     => array(
					'field'          => 'text',
					'label'          => __( 'City', 'lifterlms' ),
					'name'           => 'llms_billing_city',
					'id'             => 'llms_billing_city',
					'data_store'     => 'usermeta',
					'data_store_key' => 'llms_billing_city',
				),
			),
			array(
				'blockName' => 'llms/form-field-user-address-country',
				'attrs'     => array(
					'field'          => 'select',
					'label'          => __( 'Country', 'lifterlms' ),
					'name'           => 'llms_billing_country',
					'id'             => 'llms_billing_country',
					'data_store'     => 'usermeta',
					'data_store_key' => 'llms_billing_country',
					'options_preset' => 'countries',
					'placeholder'    => __( 'Select a Country', 'lifterlms' ),
				),
			),
			array(
				'blockName'   => 'llms/form-field-user-address-region',
				'innerBlocks' => array(
					array(
						'blockName' => 'llms/form-field-user-address-state',
						'attrs'     => array(
							'field'          => 'select',
							'label'          => __( 'State / Region', 'lifterlms' ),
							'placeholder'    => __( 'Select a State / Region', 'lifterlms' ),
							'name'           => 'llms_billing_state',
							'id'             => 'llms_billing_state',
							'data_store'     => 'usermeta',
							'data_store_key' => 'llms_billing_state',
							'columns'        => 6,
							'last_column'    => false,
						),
					),
					array(
						'blockName' => 'llms/form-field-user-address-postal-code',
						'attrs'     => array(
							'field'          => 'text',
							'label'          => __( 'Postal / Zip Code', 'lifterlms' ),
							'name'           => 'llms_billing_zip',
							'id'             => 'llms_billing_zip',
							'data_store'     => 'usermeta',
							'data_store_key' => 'llms_billing_zip',
							'columns'        => 6,
							'last_column'    => true,
						),
					),
				),
			),
		),
	),
	'phone'        => array(
		'title'     => _x( 'Default Field: Phone Number', 'Default form field reusable block title', 'lifterlms' ),
		'blockName' => 'llms/form-field-user-phone',
		'attrs'     => array(
			'field'          => 'tel',
			'label'          => __( 'Phone Number', 'lifterlms' ),
			'name'           => 'llms_phone',
			'id'             => 'llms_phone',
			'data_store'     => 'usermeta',
			'data_store_key' => 'llms_phone',
		),
	),
);
