/**
 * Redux data store reducer
 *
 * @since 2.0.0
 * @version 2.0.0
 */

// WP deps.
import { combineReducers } from '@wordpress/data';

// Internal Deps.
import { DEFAULT_FIELDS } from './default-fields';
import { fieldsObjectToArray, fieldsArrayToObject } from './util';

/**
 * Create a new field
 *
 * @since 2.0.0
 *
 * @param {Object} fields Current fields object derived from the state tree.
 * @param {Object} field  Field to add.
 * @return {Object} Updated fields object.
 */
function addField( fields, field ) {
	return {
		...fields,
		[ field.name ]: {
			...field,
		},
	};
}

/**
 * Delete a field
 *
 * Note: persisted fields (those stored in the database) should be unloaded instead of deleted.
 *
 * @since 2.0.0
 *
 * @param {Object} fields       Current fields object derived from the state tree.
 * @param {string} nameToDelete Name of the field to delete.
 * @return {Object} Updated fields object.
 */
function deleteField( fields, nameToDelete ) {
	fields = fieldsObjectToArray( fields ).filter(
		( { name } ) => name !== nameToDelete
	);
	return fieldsArrayToObject( fields );
}

/**
 * Edit an existing field
 *
 * Note: the `name` attribute should not be edited, instead use `renameField()` and then `editField()`.
 *
 * @since 2.0.0
 *
 * @param {Object} fields Current fields object derived from the state tree.
 * @param {string} name   Name of the field to be edited.
 * @param {Object} edits  Full or partial object of edits to make to the field.
 * @return {Object} Updated fields object.
 */
function editField( fields, name, edits ) {
	return {
		...fields,
		[ name ]: {
			...fields[ name ],
			...edits,
		},
	};
}

/**
 * Rename a field
 *
 * @since 2.0.0
 *
 * @param {Object} fields  Current fields object derived from the state tree.
 * @param {string} oldName Current/old name of the field.
 * @param {string} newName New name of the field.
 * @return {Object} Updated fields object.
 */
function renameField( fields, oldName, newName ) {
	const copy = {
		...fields[ oldName ],
	};
	fields = deleteField( fields, oldName );
	return addField( fields, {
		...copy,
		name: newName,
	} );
}

/**
 * Reset fields to the default state.
 *
 * @since 2.0.0
 *
 * @return {Object} Object of field objects.
 */
function resetFields() {
	return DEFAULT_FIELDS;
}

/**
 * Fields reducer
 *
 * @since 2.0.0
 *
 * @param {Object} state  Current fields object derived from the state tree.
 * @param {Object} action Dispached action object.
 * @return {Object} Updated state.
 */
export function fields( state = DEFAULT_FIELDS, action ) {
	const { type } = action;

	switch ( type ) {
		case 'ADD_FIELD':
			return addField( state, action.field );

		case 'DELETE_FIELD':
			return deleteField( state, action.name );

		case 'EDIT_FIELD':
			return editField( state, action.name, action.edits );

		case 'RECEIVE_FIELDS':
			return fieldsArrayToObject( action.fields );

		case 'RENAME_FIELD':
			return renameField( state, action.oldName, action.newName );

		case 'RESET_FIELDS':
			return resetFields();

		default:
			return state;
	}
}

export default combineReducers( {
	fields,
} );
