/**
 * Redux store responsible for client-side state management of LifterLMS User Information field data
 *
 * The store is populated using information derived from the `window.llms.userInfoFields` variable.
 *
 * This data is added to the window via PHP javascript localization. At some point in the future we plan
 * to connect this to the server via REST endpoints.
 *
 * @since 2.0.0
 * @version 2.0.0
 */

/**
 * WP dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './name';
import reducer from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

const storeConfig = {
	reducer,
	actions: { ...actions },
	selectors: { ...selectors },
};

/**
 * Redux store definition
 *
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/data/README.md#createReduxStore
 *
 * @type {Object}
 */
export const store = createReduxStore( STORE_NAME, storeConfig );

register( store );
