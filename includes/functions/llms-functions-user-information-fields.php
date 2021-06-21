<?php
/**
 * Functions for LifterLMS user information fields
 *
 * @package LifterLMS/Functions
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a single user information field by its ID attribute.
 *
 * @since 5.0.0
 *
 * @param string $name The field's name.
 * @return array|boolean Returns the field settings array or `false` when the field cannot be found.
 */
function llms_get_user_information_field( $name ) {

	$fields = llms_get_user_information_fields();

	$field_index = array_search( $name, array_column( $fields, 'name' ), true );
	return false === $field_index ? false : $fields[ $field_index ];

}

/**
 * Retrieve the filtered user information field schema
 *
 * @since 5.0.0
 *
 * @return array[] A list of LLMS_Form_Field settings arrays.
 */
function llms_get_user_information_fields() {

	$fields = require LLMS_PLUGIN_DIR . 'includes/schemas/llms-user-information-fields.php';

	/**
	 * Filters the user information fields schema
	 *
	 * Custom fields can be added, removed, and modified using this filter. Please note that
	 * LifterLMS relies on these fields so removal or modification of attributes (like `name`,
	 * `id`, and `data_store*`) may cause LifterLMS to break in unexpected ways.
	 *
	 * @since 5.0.0
	 *
	 * @param array[] $fields List of field definitions.
	 */
	return apply_filters( 'llms_user_information_fields', $fields );

}

/**
 * Retrieve user information fields used by the block editor
 *
 * This is used for JS localization purposes and returns a reduced set of data as used by
 * the editor for validation purposes.
 *
 * @since 5.0.0
 *
 * @return array[]
 */
function llms_get_user_information_fields_for_editor() {

	$fields = llms_get_user_information_fields();

	/**
	 * Filters the list of keys included for user information fields when localized into the block editor
	 *
	 * @since 5.0.0
	 *
	 * @param string[] $keys Array of key names.
	 */
	$keys = apply_filters(
		'llms_get_user_information_fields_for_editor_keys',
		array(
			'id',
			'name',
			'label',
			'data_store',
			'data_store_key',
		)
	);

	// Add a value so we can use array_interect_key() later.
	$keys = array_fill_keys( $keys, 1 );

	// Return a reduced list.
	return array_map(
		function( $field ) use ( $keys ) {
			return array_intersect_key( $field, $keys );
		},
		$fields
	);
}
