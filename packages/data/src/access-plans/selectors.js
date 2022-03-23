import { select } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import { ENTITY_KIND, ENTITY_NAME } from './constants';

/**
 * Retrieves an edited access plan record, merged with its edits.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {Object?} The plan object.
 */
export function getEditedPlan( state, id ) {
	const { getEditedEntityRecord } = select( coreStore );
	return getEditedEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Retrieves a plan by id.
 *
 * @since [version]
 *
 * @param {Object}  state State tree.
 * @param {number}  id    Access plan post id.
 * @param {Object?} query Optional query.
 * @return {Object?|undefined} The plan object, `null` if the plan isn't received, and `undefined` if it's
 *                             known to not exist.
 */
export function getPlan( state, id, query ) {
	const { getEntityRecord } = select( coreStore );
	return getEntityRecord( ENTITY_KIND, ENTITY_NAME, id, query );
}

/**
 * Retrieves the edits to the specified plan.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {Object?} The plan's edits.
 */
export function getPlanEdits( state, id ) {
	const { getEntityRecordEdits } = select( coreStore );
	return getEntityRecordEdits( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Retrieves access plans.
 *
 * @since [version]
 *
 * @param {Object}  state State tree.
 * @param {Object?} query Optional query.
 * @return {?Array<Object>} Array of plan objects.
 */
export function getPlans( state, query ) {
	const { getEntityRecords } = select( coreStore );
	return getEntityRecords( ENTITY_KIND, ENTITY_NAME, query );
}

/**
 * Retrieves plans for the specified query with each plan merged with its edits.
 *
 * @since [version]
 *
 * @param {Object}  state State tree.
 * @param {Object?} query Optional query.
 * @return {?Array<Object>} Array of plan objects.
 */
export function getEditedPlans( state, query ) {
	const plans = getPlans( state, query );
	return plans && plans.length ? plans.map( ( { id } ) => getEditedPlan( state, id ) ) : null;
}

/**
 * Retrieves the specified plan's last delete error.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {Object?} The access plan delete error.
 */
export function getLastPlanDeleteError( state, id ) {
	const { getLastEntityDeleteError } = select( coreStore );
	return getLastEntityDeleteError( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Retrieves the specified plan's last save error.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {Object?} The access plan delete error.
 */
export function getLastPlanSaveError( state, id ) {
	const { getLastEntitySaveError } = select( coreStore );
	return getLastEntitySaveError( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Retrieves a plan's entity records with attributes mapped to their raw values.
 *
 * See ENTITY_CONFIG.rawAttributes (in the ./contstants.js file) for a list of the raw attribute keys.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {Object?} The plan object.
 */
export function getRawPlan( state, id ) {
	const { getRawEntityRecord } = select( coreStore );
	return getRawEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Determines if the plan has edits.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {boolean} Returns `true` if the plan has edits and `false` if it does not.
 */
export function hasEditsForPlan( state, id ) {
	const { hasEditsForEntityRecord } = select( coreStore );
	return hasEditsForEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Determines if plans have been received for the given query.
 *
 * @since [version]
 *
 * @param {Object}  state State tree.
 * @param {Object?} query Optional query.
 * @return {boolean} Returns `true` if plans have been received, otherwise returns `false`.
 */
export function hasPlans( state, query ) {
	const { hasEntityRecords } = select( coreStore );
	return hasEntityRecords( ENTITY_KIND, ENTITY_NAME, query );
}

/**
 * Determines if the specified plan is being autosaved.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {boolean} Returns `true` if the plan is autosaving and `false` otherwise.
 */
export function isAutosavingPlan( state, id ) {
	const { isAutosavingEntityRecord } = select( coreStore );
	return isAutosavingEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Determines if the specified plan is being deleted.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {boolean} Returns `true` if the plan is being deleted and `false` otherwise.
 */
export function isDeletingPlan( state, id ) {
	const { isDeletingEntityRecord } = select( coreStore );
	return isDeletingEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Determines if the specified plan is being saved.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {number} id    Access plan post id.
 * @return {boolean} Returns `true` if the plan is being saved and `false` otherwise.
 */
export function isSavingPlan( state, id ) {
	const { isSavingEntityRecord } = select( coreStore );
	return isSavingEntityRecord( ENTITY_KIND, ENTITY_NAME, id );
}

/**
 * Determines if plans are being resolved for the specified query
 *
 * @since [version]
 *
 * @param {Object}  state State tree.
 * @param {Object?} query Optional query.
 * @return {boolean} Returns `true` if plans are resolving, otherwise returns `false`.
 */
export function isLoading( state, query ) {
	const { isResolving } = select( coreStore );
	return isResolving( 'getEntityRecords', [ ENTITY_KIND, ENTITY_NAME, query ] );
}
