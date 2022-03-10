// WP Deps.
import { addQueryArgs } from '@wordpress/url';
import { apiFetch } from '@wordpress/data-controls';

// LLMS Deps.
import { untrailingSlashIt } from '@lifterlms/utils';

/**
 * Processes an API Fetch call for the access plan resource.
 *
 * @since [version]
 *
 * @param {Object} args           Fetch arguments.
 * @param {string} args.namespace API namespace.
 * @param {string} args.resource  The API resource, eg "access-plans".
 * @param {string} args.path      The API resource path, eg "123" for (a resource ID).
 * @param {string} args.query     Object of query arguments to be added to the request URL.
 * @param {Object} args.fetchArgs Arguments passed to `@wordpress/api-fetch`, {@link https://github.com/WordPress/gutenberg/tree/trunk/packages/api-fetch#options}.
 * @return {Object} API Response object.
 */
export default function( {
	namespace = 'llms/v1',
	resource,
	path = '',
	query = {},
	...fetchArgs
} ) {
	if ( ! query.context ) {
		query.context = 'edit';
	}
	fetchArgs.path = addQueryArgs(
		untrailingSlashIt( `${ namespace }/${ resource }/${ path }` ),
		query
	);
	return apiFetch( fetchArgs );
}
