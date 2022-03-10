// Internal deps.
import apiRequest from '../api-request';
import { RESOURCE_NAME as resource } from './constants';

/**
 * Processes an API Fetch call for the access plan resource.
 *
 * @since [version]
 *
 * @param {Object} args Arguments passed to `@wordpress/api-fetch`, {@link https://github.com/WordPress/gutenberg/tree/trunk/packages/api-fetch#options}.
 * @return {Object} API Response object.
 */
export default function( args ) {
	return apiRequest( {
		resource,
		...args,
	} );
}
