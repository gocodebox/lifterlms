// External deps.
import { filter } from 'lodash';

// Internal deps.
import { ACTION_TYPES } from './constants';

/**
 * Converts an array of plan objects to an object keyed by plan ID.
 *
 * @since [version]
 *
 * @param {Object[]} plans Array of plan objects.
 * @return {Object} Object of plan objects keyed by plan id.
 */
function arrayToObject( plans ) {
	return plans.reduce( ( obj, plan ) => {
		return {
			...obj,
			[ plan.id ]: plan,
		};
	}, {} );
}

/**
 * Removes an access plan by ID from the current state tree.
 *
 * @since [version]
 *
 * @param {Object} state  State tree.
 * @param {number} planId ID of the plan to remove.
 * @return {Object} Updated state tree.
 */
function deletePlan( state, planId ) {
	return {
		...state,
		plans: arrayToObject(
			filter( state.plans, ( { id } ) => planId !== id )
		),
	};
}

/**
 * Receives an error and adds it to the current state tree.
 *
 * @since [version]
 *
 * @param {Object} state State tree.
 * @param {Error}  error Error object.
 * @return {Object} Updated state tree.
 */
function receiveError( state, error ) {
	return {
		...state,
		errors: [ ...state.errors, error ],
	};
}

/**
 * Receives a list of access plans to add to the current state tree.
 *
 * @since [version]
 *
 * @param {Object}   state State tree.
 * @param {Object[]} plans Array of access plan objects.
 * @return {Object} Updated state tree.
 */
function receivePlans( state, plans ) {
	return {
		...state,
		plans: {
			...state.plans,
			...arrayToObject( plans ),
		},
	};
}

/**
 * Redux store reducer.
 *
 * @since [version]
 *
 * @param {Object}   state        The store state tree.
 * @param {Object[]} state.plans  An array of access plan objects.
 * @param {Error[]}  state.errors An array of error objects.
 * @param {Object}   action       Action object.
 * @param {string}   action.type  The action type.
 * @param {number}   action.id    An access plan resource ID. Passed during DELETE_ITEM actions.
 * @param {Object}   action.plan  An access plan object. Passed during CREATE_ITEM and UPDATE_ITEM actions.
 * @param {Object[]} action.plans An array of access plan objects. Passed during RECEIVE_ITEMS actions.
 * @param {Error}    action.error An error object. Passed during RECEIVE_ERROR actions.
 * @return {Object} The updated state tree.
 */
const reducer = (
	state = {
		plans: {},
		errors: [],
	},
	{ type, id, plan, plans, error }
) => {
	switch ( type ) {
		case ACTION_TYPES.CREATE_ITEM:
		case ACTION_TYPES.UPDATE_ITEM:
			return receivePlans( state, [ plan ] );

		case ACTION_TYPES.DELETE_ITEM:
			return deletePlan( state, id );

		case ACTION_TYPES.RECEIVE_ERROR:
			return receiveError( state, error );

		case ACTION_TYPES.RECEIVE_ITEMS:
			return receivePlans( state, plans );

		default:
			return state;
	}
};

export default reducer;
