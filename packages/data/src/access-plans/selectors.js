/**
 * Retrieves errors on the current state tree.
 *
 * @since [version]
 *
 * @param {Object}   state        Current state tree.
 * @param {Object[]} state.errors Array of error objects.
 * @return {Object[]} Array of error objects.
 */
export function getErrors( { errors } ) {
	return errors;
}

/**
 * Determines if the current state tree contains any errors.
 *
 * @since [version]
 *
 * @param {Object}   state        Current state tree.
 * @param {Object[]} state.errors Array of error objects.
 * @return {boolean} Returns `true` if there are errors, otherwise `false`.
 */
export function hasErrors( { errors } ) {
	return errors.length > 0;
}

/**
 * Retrieves plans from the current state tree, optionally filtered by the plan's parent post ID.
 *
 * @since [version]
 *
 * @param {Object} state       Current state tree.
 * @param {Object} state.plans Plan objects in the current state.
 * @param {number} postId      Optional parent post ID, used to filter down the list of plans to only those associated with the specified parent post id.
 * @return {Object[]} Array of plan objects.
 */
export function getPlans( { plans }, postId ) {
	plans = Object.values( plans );
	if ( postId ) {
		plans = plans.filter( ( { post_id } ) => post_id === postId );
	}
	return plans;
}

/**
 * Retrieves a single plan by ID.
 *
 * @since [version]
 *
 * @param {Object} state       Current state tree.
 * @param {Object} state.plans Plan objects in the current state.
 * @param {number} id          Plan ID.
 * @return {Object|undefined} Returns the plan object or `undefined` if it could not be found.
 */
export function getPlan( { plans }, id ) {
	return plans[ id ];
}
