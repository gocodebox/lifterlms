/**
 * Redux data store utility functions
 *
 * @since 2.0.0
 * @version 2.0.0
 */

/**
 * Convert an array of user information fields to an object keyed by globally unique `name` attribute.
 *
 * @since 2.0.0
 *
 * @param {Object[]} fields Array of field objects.
 * @return {Object} Object of field objects.
 */
export function fieldsArrayToObject( fields ) {
	return fields.reduce(
		( obj, currentField ) => ( {
			...obj,
			[ currentField.name ]: currentField,
		} ),
		{}
	);
}

/**
 * Convert an object of field objects into an array
 *
 * Useful for running array functions like filter.
 *
 * @since 2.0.0
 *
 * @param {Object} fields Object of user information field objects.
 * @return {Object[]} Array of user information field objects.
 */
export function fieldsObjectToArray( fields ) {
	return Object.values( fields );
}
