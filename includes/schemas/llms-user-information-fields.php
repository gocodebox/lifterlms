<?php
/**
 * User information fields schema
 *
 * A list of user information fields used by LifterLMS in various places, namely to build
 * the editable user information forms (Checkout, Registration, and Edit Account).
 *
 * Each item in this list should be an array compatible with the `LLMS_Form_Field` class'
 * settings array.
 *
 * Fields can be added and modified using the `llms_user_information_fields` filter.
 *
 * @package LifterLMS/Schemas
 *
 * @since 5.0.0
 * @version 5.0.0
 *
 * @see llms_get_user_information_fields() Retrieves the (filtered) schema.
 * @see llms_get_user_information_field() Retrieve a single field from this schema by ID.
 */

defined( 'ABSPATH' ) || exit;

return array(

	// WordPress Core.
	array(
		'id'             => 'user_login',
		'name'           => 'user_login',
		'type'           => 'text',
		'label'          => __( 'Username', 'lifterlms' ),
		'data_store'     => 'users',
		'data_store_key' => 'user_login',
	),
	array(
		'id'             => 'email_address',
		'name'           => 'email_address',
		'type'           => 'email',
		'label'          => __( 'Email Address', 'lifterlms' ),
		'data_store'     => 'users',
		'data_store_key' => 'user_email',
	),
	array(
		'id'                => 'password',
		'name'              => 'password',
		'type'              => 'password',
		'label'             => __( 'Password', 'lifterlms' ),
		'data_store'        => 'users',
		'data_store_key'    => 'user_pass',
		'meter'             => llms_parse_bool( get_option( 'lifterlms_registration_password_strength', 'yes' ) ),
		'min_strength'      => get_option( 'lifterlms_registration_password_min_strength', 'strong' ),
		'html_attrs'        => array(
			'minlength' => 8,
		),
		'meter_description' => sprintf(
			// Translators: %s = Minimum password strength.
			__(
				'A %s password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.',
				'lifterlms'
			),
			llms_get_minimum_password_strength_name( get_option( 'lifterlms_registration_password_min_strength', 'strong' ) )
		),
	),
	array(
		'id'             => 'first_name',
		'name'           => 'first_name',
		'type'           => 'text',
		'label'          => __( 'First Name', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'first_name',
	),
	array(
		'id'             => 'last_name',
		'name'           => 'last_name',
		'type'           => 'text',
		'label'          => __( 'Last Name', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'last_name',
	),
	array(
		'id'             => 'display_name',
		'name'           => 'display_name',
		'type'           => 'text',
		'label'          => __( 'Display Name', 'lifterlms' ),
		'data_store'     => 'users',
		'data_store_key' => 'display_name',
	),

	// LifterLMS core.
	array(
		'id'             => 'llms_billing_address_1',
		'name'           => 'llms_billing_address_1',
		'type'           => 'text',
		'label'          => __( 'Address', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_address_1',
	),
	array(
		'id'               => 'llms_billing_address_2',
		'name'             => 'llms_billing_address_2',
		'type'             => 'text',
		'label'            => '',
		'label_show_empty' => true,
		'data_store'       => 'usermeta',
		'data_store_key'   => 'llms_billing_address_2',
		'placeholder'      => __( 'Apartment, suite, etc...', 'lifterlms' ),
	),
	array(
		'id'             => 'llms_billing_city',
		'name'           => 'llms_billing_city',
		'type'           => 'text',
		'label'          => __( 'City', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_city',
	),
	array(
		'id'             => 'llms_billing_country',
		'name'           => 'llms_billing_country',
		'type'           => 'select',
		'label'          => __( 'Country', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_country',
		'options_preset' => 'countries',
		'placeholder'    => __( 'Select a Country', 'lifterlms' ),
		'classes'        => 'llms-select2',
	),
	array(
		'id'             => 'llms_billing_state',
		'name'           => 'llms_billing_state',
		'type'           => 'select',
		'label'          => __( 'State / Region', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_state',
		'options_preset' => 'states',
		'placeholder'    => __( 'Select a State / Region', 'lifterlms' ),
		'classes'        => 'llms-select2',
	),
	array(
		'id'             => 'llms_billing_zip',
		'name'           => 'llms_billing_zip',
		'type'           => 'text',
		'label'          => __( 'Postal / Zip Code', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_zip',
	),
	array(
		'id'             => 'llms_phone',
		'name'           => 'llms_phone',
		'type'           => 'tel',
		'label'          => __( 'Phone Number', 'lifterlms' ),
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_phone',
	),

);
