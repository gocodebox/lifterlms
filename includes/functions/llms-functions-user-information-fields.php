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

/**
 * Retrieve a single user information field by its ID attribute.
 *
 * @since [version]
 *
 * @param string $id The field's id
 * @return array|boolean Returns the field settings array or `false` when the field cannot be found.
 */
function llms_get_user_information_field( $id ) {

	$fields = llms_get_user_information_fields();

	foreach ( $fields as $field ) {

		if ( isset( $field['id'] ) && $id === $field['id'] ) {
			return $field;
		}
	}

	return false;

}

/**
 * Retrieve the filtered user information field schema
 *
 * @since [version]
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
	 * @since [version]
	 *
	 * @param array[] $fields List of field definitions.
	 */
	return apply_filters( 'llms_user_information_fields', $fields );

}

/**
 * Retrieve user infor fields used by the block editor
 *
 * This is used for JS localization purposes and returns a reduced set of data as used by
 * the editor for validation purposes.
 *
 * @since [version]
 *
 * @return array[]
 */
function llms_get_user_information_fields_for_editor() {

	$fields = llms_get_user_information_fields();

	/**
	 * Filters the list of keys included for user information fields when localized into the block editor
	 *
	 * @since [version]
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

/**
 * Returns a list of fields belonging to the specified group.
 *
 * @since [version]
 *
 * @param string $group_id A field group id.
 * @return array[] Returns a list of LLMS_Form_Field settings arrays for groups belonging to the specified group.
 */
function llms_get_user_information_fields_for_group( $group_id ) {

	return array_values(
		array_filter(
			llms_get_user_information_fields(),
			function( $field ) use ( $group_id ) {
				return $field['group'][0] === $group_id;
			}
		)
	);

}
