// WP deps.
import { registerStore } from '@wordpress/data';

/**
 * Mocks the WP core store with the specified mock actions, selectors, etc...
 *
 * @since [version]
 *
 * @param {string} key   Option key for adding mock functions, eg: "selectors" or "actions".
 * @param {Object} funcs Object of mock functions to register to the store.
 * @return {void}
 */
export function mockCoreStore( key, funcs = {} ) {
	registerStore( 'core', {
		reducer: () => ( {} ),
		[ key ]: funcs,
	} );
}
