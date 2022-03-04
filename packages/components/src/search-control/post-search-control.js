import { __, _x, sprintf } from '@wordpress/i18n';

import BaseSearchControl from './base-search-control';

/**
 * Searchable <select> element powered by a WordPress REST API users endpoint.
 *
 * This component is a wrapper around the <BaseSearchControl> component. It is configured
 * to search users via the WordPress user REST API endpoint.
 *
 * @since [version]
 *
 * @param {Object}    args                         Component arguments.
 * @param {string}    args.postType                Post type endpoint.
 * @param {string}    args.baseSearchPath          Base search path used to create the searchPath.
 * @param {?string}   args.searchPath              API path used to perform the search. If passed, will be used instead of the
 *                                                 path generated from `args.postType` and `args.baseSearchPath`.
 * @param {string}    args.placeholder             The placeholder displayed within an empty search control.
 * @param {string}    args.className               HTML class attribute added to the select control.
 * @param {?Function} args.formatSearchResultLabel Function invoked to format the display label for a result. The function is passed
 * @param {Object}    args.additionalSearchArgs    An object representing a single result and should return a string.
 * @param {...*}      args.baseProps               Any remaining properties are passed to the <BaseSearchControl> component.
 * @return {BaseSearchControl} The component.
 */
export default function PostSearchControl( {
	postType = 'posts',
	baseSearchPath = '/wp/v2/',
	searchPath = null,
	className = 'llms-post-search-control',
	placeholder = __( 'Search for posts…', 'lifterlms' ),
	formatSearchResultLabel = null,
	additionalSearchArgs = {},
	...baseProps
} ) {
	// Default result label.
	formatSearchResultLabel = formatSearchResultLabel ? formatSearchResultLabel : ( { title, id } ) => sprintf(
		// Translators: %1$s = Post title; %2$s = Post id.
		_x( '%1$s (ID# %2$d)', 'Post search result label', 'lifterlms' ),
		title.rendered,
		id
	);

	return (
		<BaseSearchControl { ...{
			searchPath: searchPath || `${ baseSearchPath }${ postType }`,
			className,
			placeholder,
			formatSearchResultLabel,
			additionalSearchArgs,
			...baseProps,
		} } />
	);
}
