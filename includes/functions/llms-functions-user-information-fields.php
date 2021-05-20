<?php
/**
 * Functions for LifterLMS user information fields
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

function llms_get_user_information_fields() {

	$fields = require LLMS_PLUGIN_DIR . '/includes/llms-user-info-fields-schema.php';

	/**
	 * Filters the user information fields schema
	 *
	 * @since [version]
	 *
	 * @param array[] $fields Lift of field definitions.
	 */
	return apply_filters( 'llms_user_information_fields', $fields );

}

function llms_get_user_information_fields_for_group( $group_id ) {

	return array_values( array_filter( llms_get_user_information_fields(), function( $field ) use( $group_id ) {
		return $field['group'][0] === $group_id;
	} ) );

}
