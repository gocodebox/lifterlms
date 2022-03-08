import { __, _x, sprintf } from '@wordpress/i18n';

import BaseSearchControl from './base-search-control';

/**
 * Searchable <select> element powered by a WordPress REST API users endpoint.
 *
 * This component is a wrapper around the <BaseSearchControl> component. It is configured
 * to search users via the WordPress user REST API endpoint.
 *
 * @since 1.0.0
 *
 * @param {Object}    args                         Component arguments.
 * @param {string}    args.searchPath              Required. API path used to perform the search.
 * @param {string}    args.placeholder             The placeholder displayed within an empty search control.
 * @param {string}    args.className               HTML class attribute added to the select control.
 * @param {?Function} args.formatSearchResultLabel Function invoked to format the display label for a result. The function is passed
 * @param {Object}    args.additionalSearchArgs    An object representing a single result and should return a string.
 * @param {...*}      args.baseProps               Any remaining properties are passed to the <BaseSearchControl> component.
 * @return {BaseSearchControl} The component.
 */
export default function UserSearchControl( {
	searchPath = '/wp/v2/users',
	className = 'llms-user-search-control',
	placeholder = __( 'Search users by email or name…', 'lifterlms' ),
	formatSearchResultLabel = null,
	additionalSearchArgs = {},
	...baseProps
} ) {
	// Default result label.
	formatSearchResultLabel = formatSearchResultLabel
		? formatSearchResultLabel
		: ( { name, id } ) =>
				sprintf(
					// Translators: %1$s = User's name; %2$s = User's id.
					_x(
						'%1$s (ID# %2$d)',
						'User search result label',
						'lifterlms'
					),
					name,
					id
				);

	return (
		<BaseSearchControl
			{ ...{
				searchPath,
				className,
				placeholder,
				formatSearchResultLabel,
				additionalSearchArgs,
				...baseProps,
			} }
		/>
	);
}
