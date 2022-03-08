// External deps.
import { isPlainObject } from 'lodash';

// WP deps.
import apiFetch from '@wordpress/api-fetch';

/**
 * Default function used to hydrate stored numeric IDs to the equivalent object.
 *
 * @since [version]
 *
 * @param {Array}    values        Array of values.
 * @param {string}   path          API request path.
 * @param {Object[]} loadedResults Array of already-hydrated API results.
 * @return {Object[]} Hydrated result array.
 */
export async function defaultHydrateValues( values, path, loadedResults ) {
	const isResultHydrated = ( result ) =>
		isPlainObject( result ) && result.label && result.value;

	return Promise.all(
		values.map( async ( value ) => {
			if ( ! isResultHydrated( value ) && Number.isInteger( value ) ) {
				value =
					loadedResults.find( ( { id } ) => id === value ) ||
					( await apiFetch( { path: `${ path }/${ value }` } ) );
			}

			return value;
		} )
	);
}

/**
 * Default styles object passed to the underlying <Select> component.
 *
 * @type {Object}
 */
export const defaultStyles = {
	control: ( control ) => ( {
		...control,
		borderColor: '#8d96a0',
		'&:hover': {
			...control[ '&:hover' ],
			borderColor: '#8d96a0',
		},
	} ),
};

/**
 * Default <Select> component theme callback function.
 *
 * Customizes the theme of the component to better match the WordPress editor UI.
 *
 * Uses the default UI color from the current admin theme. The theme doesn't work that well
 * with the other provided theme colors (which are darker than the Select component options which
 * are lighter highlights). So if you're using a non-default admin color scheme the select will probably
 * look a bit weird. I'm sorry.
 *
 * @since [version]
 *
 * @see https://react-select.com/styles#overriding-the-theme
 *
 * @param {Object} theme Theme object.
 * @return {Object} Theme object.
 */
export function defaultTheme( theme ) {
	return {
		...theme,
		colors: {
			...theme.colors,
			primary: 'var( --wp-admin-theme-color )',
			// primary25: '#ccf2ff',
			// primary50: '#b3ecff',
			// primary75: '#4dd2ff',
		},
		spacing: {
			...theme.spacing,
			baseUnit: 2,
			controlHeight: 28,
			menuGutter: 4,
		},
	};
}

/**
 * Default format search results function.
 *
 * Accepts an array of raw API results adds a label and value for use by the <Select>
 * component.
 *
 * @since [version]
 *
 * @param {Object[]} results                 API result array.
 * @param {Function} formatSearchResultLabel Label formatting function.
 * @param {Function} formatSearchResultValue Value formatting function.
 * @return {Object[]} Formatted results.
 */
export function defaultFormatSearchResults(
	results,
	formatSearchResultLabel,
	formatSearchResultValue
) {
	return results.map( ( result ) => ( {
		...result,
		label: formatSearchResultLabel( result ),
		value: formatSearchResultValue( result ),
	} ) );
}
