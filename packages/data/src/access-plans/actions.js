// Internal deps.
import { ACTION_TYPES, ERRORS } from './constants';
import request from './request';

/**
 * Creates a plan via a REST API request and returns an action signaling the creation of the new plan.
 *
 * @since [version]
 *
 * @param {Object} data Access plan object data, {@link https://developer.lifterlms.com/rest-api/#tag/Access-Plans/paths/~1access-plans/post}.
 * @yield {Object} Yields an action object signaling the successful creation of the access plan or an error.
 * @return {void}
 */
export function* createPlan( data ) {
	try {
		if ( data.id ) {
			throw { ...ERRORS.CREATE_ITEM_WITH_ID, data };
		}

		const plan = yield request( {
			data,
			method: 'POST',
		} );
		return {
			type: ACTION_TYPES.CREATE_ITEM,
			plan,
		};
	} catch ( error ) {
		return receiveError( error );
	}
}

/**
 * Deletes a plan via a REST API request and returns an action signaling the deletion of the plan.
 *
 * @since [version]
 *
 * @param {number} id WP_Post ID of the access plan.
 * @yield {Object} Yields an action object signaling the successful deletion of the access plan or an error.
 * @return {void}
 */
export function* deletePlan( id ) {
	try {
		yield request( {
			path: id,
			method: 'DELETE',
		} );
		return {
			type: ACTION_TYPES.DELETE_ITEM,
			id,
		};
	} catch ( error ) {
		return receiveError( error );
	}
}

/**
 * Receive an array of access plans and returns an action object signaling the successful retrieval.
 *
 * @since [version]
 *
 * @param {Object[]} plans Array of access plan objects.
 * @return {Object} The action object.
 */
export function receivePlans( plans ) {
	return {
		type: ACTION_TYPES.RECEIVE_ITEMS,
		plans,
	};
}

/**
 * Receives an error object and returns an action object signaling an error was received.
 *
 * @since [version]
 *
 * @param {Error|Object} error An error or error object.
 * @return {Object} The action object.
 */
export function receiveError( error ) {
	return {
		type: ACTION_TYPES.RECEIVE_ERROR,
		error,
	};
}

/**
 * Updates a plan via a REST API request and returns an action signaling the creation of the new plan.
 *
 * @since [version]
 *
 * @param {Object} edits Access plan object data, {@link https://developer.lifterlms.com/rest-api/#tag/Access-Plans/paths/~1access-plans~1{id}/post}.
 * @yield {Object} Yields an action object signaling the successful creation of the access plan or an error.
 * @return {void}
 */
export function* updatePlan( edits ) {
	try {
		const { id, ...data } = edits;
		if ( ! id ) {
			throw { ...ERRORS.UPDATE_ITEM_MISSING_ID, edits };
		}

		if ( ! Object.keys( data ).length ) {
			throw { ...ERRORS.UPDATE_ITEM_MISSING_DATA, edits };
		}

		const plan = yield request( {
			path: id,
			method: 'POST',
			data,
		} );
		return {
			type: ACTION_TYPES.UPDATE_ITEM,
			plan,
		};
	} catch ( error ) {
		return receiveError( error );
	}
}
