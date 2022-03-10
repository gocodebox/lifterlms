/**
 * Redux data store resolvers
 *
 * @since [version]
 * @version [version]
 */

// Internal Deps.
import { receiveError, receivePlans } from './actions';
import request from './request';

/**
 * Retrieves an access plan by plan ID.
 *
 * @since [version]
 *
 * @param {number} id WP_Post ID of the access plan.
 * @yield {Object} Yields an action object signaling the successful retrieval of the plan or an error.
 */
export function* getPlan( id ) {
	try {
		const plan = yield request( { path: id } );
		yield receivePlans( [ plan ] );
	} catch ( error ) {
		yield receiveError( error );
	}
}

/**
 * Retrieves an access plan by plan ID, optionally filtered by the parent course or membership.
 *
 * @since [version]
 *
 * @param {number} postId WP_Post ID of the access plan's parent course or membership.
 * @yield {Object} Yields an action object signaling the successful retrieval of the plans or an error.
 */
export function* getPlans( postId = null ) {
	const query = postId ? {} : { post_id: postId };
	try {
		const plans = yield request( { query } );
		yield receivePlans( plans );
	} catch ( error ) {
		yield receiveError( error );
	}
}
