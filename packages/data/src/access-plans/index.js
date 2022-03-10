/**
 * A Redux Store powering state management for LifterLMS Access Plans.
 *
 * This store utilizes the `llms/v1/access-plans/` REST endpoint to resolve
 * CRUD operations for access plan posts.
 */

// WP Deps.
import { createReduxStore, register } from '@wordpress/data';
import { controls } from '@wordpress/data-controls';

// Internal Deps.
import { STORE_NAME } from './constants';
import * as actions from './actions';
import * as resolvers from './resolvers';
import * as selectors from './selectors';
import reducer from './reducer';

/**
 * Data store configuration.
 *
 * @type {Object}
 */
export const storeConfig = {
	reducer,
	actions,
	controls,
	selectors,
	resolvers,
};

// Create the store.
export const store = createReduxStore( STORE_NAME, storeConfig );

// Register it with @wordpress/data.
register( store );
