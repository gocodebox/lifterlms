/**
 * A Redux Store powering state management for LifterLMS Access Plans.
 *
 * This store utilizes the `llms/v1/access-plans/` REST endpoint to resolve
 * CRUD operations for access plan posts.
 */

// WP Deps.
import { createReduxStore, register } from '@wordpress/data';

// Internal Deps.
import { STORE_NAME } from './constants';
import * as actions from './actions';
import * as selectors from './selectors';
import { registerEntity } from './entity';

/**
 * Data store configuration.
 *
 * @type {Object}
 */
export const storeConfig = {
	reducer: ( state ) => state,
	actions,
	selectors,
};

// Create the store.
export const store = createReduxStore( STORE_NAME, storeConfig );

registerEntity();

// Register it with @wordpress/data.
register( store );

