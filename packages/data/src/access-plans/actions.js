import { dispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import { ENTITY_KIND, ENTITY_NAME } from './constants';

/**
 * Action triggered to delete an access plan.
 *
 * @since [version]
 *
 * @param {number} id Access plan post id.
 * @return {Object} Action object.
 */
export function* deletePlan( id ) {
	const { deleteEntityRecord } = dispatch( coreStore );
	return deleteEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Action triggered to edit an access plan.
 *
 * @since [version]
 *
 * @param {number} id    Access plan post id.
 * @param {Object} edits The edits.
 * @return {Object} Action object.
 */
export function* editPlan( id, edits = {} ) {
	const { editEntityRecord } = dispatch( coreStore );
	return editEntityRecord( ENTITY_KIND, ENTITY_NAME, id, edits );
}

/**
 * Action triggered to save a plan's edits.
 *
 * @since [version]
 *
 * @param {number} id Access plan post id.
 * @return {Object} Action object.
 */
export function* saveEditedPlan( id ) {
	const { saveEditedEntityRecord } = dispatch( coreStore );
	return saveEditedEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Action triggered to save a plan's edits.
 *
 * @since [version]
 *
 * @param {Object} plan The access plan object.
 * @return {Object} Action object.
 */
export function* savePlan( plan ) {
	const { saveEntityRecord } = dispatch( coreStore );
	return saveEntityRecord( ENTITY_KIND, ENTITY_NAME, plan );
}
