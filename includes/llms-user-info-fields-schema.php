<?php
/**
 * User information fields schema
 *
 * @package LifterLMS/Schemas
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

return array(

	// WordPress Core.
	array(
		'type'           => 'text',
		'label'          => __( 'Username', 'lifterlms' ),
		'id'             => 'user_login',
		'data_store'     => 'users',
		'data_store_key' => 'user_login',
		'group'          => array( 'wp', __( 'WordPress', 'lifterlms' ) ),
	),
	array(
		'type'           => 'email',
		'label'           => __( 'Email Address', 'lifterlms' ),
		'id'              => 'email_address',
		'data_store'      => 'users',
		'data_store_key'  => 'user_email',
		'group'          => array( 'wp', __( 'WordPress', 'lifterlms' ) ),
	),
	array(
		'type'           => 'password',
		'label'          => __( 'Password', 'lifterlms' ),
		'id'             => 'password',
		'data_store'     => 'users',
		'data_store_key' => 'user_pass',
		'group'          => array( 'wp', __( 'WordPress', 'lifterlms' ) ),
	),
	array(
		'type'          => 'text',
		'label'          => __( 'First Name', 'lifterlms' ),
		'id'             => 'first_name',
		'data_store'     => 'usermeta',
		'data_store_key' => 'first_name',
		'group'          => array( 'wp', __( 'WordPress', 'lifterlms' ) ),
	),
	array(
		'type'          => 'text',
		'label'          => __( 'Last Name', 'lifterlms' ),
		'id'             => 'last_name',
		'data_store'     => 'usermeta',
		'data_store_key' => 'last_name',
		'group'          => array( 'wp', __( 'WordPress', 'lifterlms' ) ),
	),
	array(
		'type'          => 'text',
		'label'          => __( 'Display Name', 'lifterlms' ),
		'id'             => 'display_name',
		'data_store'     => 'users',
		'data_store_key' => 'display_name',
		'group'          => array( 'wp', __( 'WordPress', 'lifterlms' ) ),
	),

	// LifterLMS core.
	array(
		'type'           => 'text',
		'label'          => __( 'Address', 'lifterlms' ),
		'id'             => 'llms_billing_address_1',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_address_1',
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),
	array(
		'type'           => 'text',
		'label'          => '',
		'id'             => 'llms_billing_address_2',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_address_2',
		'placeholder'    => __( 'Apartment, suite, etc...', 'lifterlms' ),
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),
	array(
		'type'           => 'text',
		'label'          => __( 'City', 'lifterlms' ),
		'id'             => 'llms_billing_city',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_city',
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),
	array(
		'type'           => 'select',
		'label'          => __( 'Country', 'lifterlms' ),
		'id'             => 'llms_billing_country',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_country',
		'options_preset' => 'countries',
		'placeholder'    => __( 'Select a Country', 'lifterlms' ),
		'classes'        => 'llms-select2',
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),
	array(
		'type'           => 'select',
		'label'          => __( 'State / Region', 'lifterlms' ),
		'id'             => 'llms_billing_state',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_state',
		'options_preset' => 'states',
		'placeholder'    => __( 'Select a State / Region', 'lifterlms' ),
		'classes'        => 'llms-select2',
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),
	array(
		'type'           => 'text',
		'label'          => __( 'Postal / Zip Code', 'lifterlms' ),
		'id'             => 'llms_billing_zip',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_billing_zip',
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),
	array(
		'type'           => 'tel',
		'label'          => __( 'Phone Number', 'lifterlms' ),
		'id'             => 'llms_phone',
		'data_store'     => 'usermeta',
		'data_store_key' => 'llms_phone',
		'group'          => array( 'llms', __( 'LifterLMS', 'lifterlms' ) ),
	),

);
