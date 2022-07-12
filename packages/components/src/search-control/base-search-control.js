// External Deps.
import Select from 'react-select/async';
import { debounce } from 'throttle-debounce';
import { uniqueId, differenceBy, uniqBy } from 'lodash';

// WP Deps.
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

// Internal Deps.
import { StyledBaseControl } from './styled-base-control';
import {
	defaultHydrateValues,
	defaultStyles,
	defaultTheme,
	defaultFormatSearchResults,
} from './defaults';

/**
 * Searchable <select> element powered by a WordPress REST API endpoint.
 *
 * @since 1.0.0
 *
 * @param {Object}    args                         Component arguments.
 * @param {string}    args.searchPath              Required. API path used to perform the search.
 * @param {Function}  args.onUpdate                Callback function invoked when the value of the select changes.
 *                                                 The callback function is passed a single parameter, the new selected
 *                                                 value object(s). For multiselects it will be an array of objects.
 *                                                 If the select is clearable, the value will be `null` when the select
 *                                                 is cleared.
 * @param {Array}     args.selectedValue           The currently selected value(s). If an object is passed, it should contain at least
 *                                                 a `label` and `value` key. Can pass IDs as integers and the values will be automatically
 *                                                 hydrated.
 * @param {string}    args.placeholder             The placeholder displayed within an empty search control.
 * @param {string}    args.className               HTML class attribute added to the select control.
 * @param {string}    args.classNamePrefix         Prefix added to select control subcomponent classnames. In most circumstances this should not
 *                                                 be changed as it is used to style the compontents.
 * @param {number}    args.searchDebounceDelay     Search debounce delay, in milliseconds.
 * @param {Object}    args.additionalSearchArgs    Object of additional query string arguments to use with the API request.
 * @param {?string}   args.label                   Search control label, passed to <BaseControl>.
 * @param {?string}   args.id                      Search control HTML ID attribute, passed to <BaseControl>.
 * @param {?Object[]} args.defaultOptions          Array of hydrated objects to preload into the select as options.
 * @param {?Function} args.getSearchArgs           Function invoked to generate the query string arguments used when fetching
 *                                                 results from the API. The callback function is passed the search string.
 * @param {?Function} args.getSearchURL            Function invoked to create the search URL used to fetch results. The function
 *                                                 is passed the `searchPath` and generated query string arguments from `getSearchArgs()`.
 * @param {?Function} args.hydrateValues           Function invoked to hydrate integer values. The function is passed the currently selected values,
 *                                                 the `searchPath`, and an array of cached (and hydrated) objects previously loaded from the server.
 * @param {?Function} args.formatSearchResults     Function invoked to format results retrieved from the server. The function is passed an array
 *                                                 of objects from the server. It should return an array of objects, each containing at least a
 *                                                 value and label property.
 * @param {?Function} args.formatSearchResultLabel Function invoked to format the display label for a result. The function is passed
 *                                                 an object representing a single result and should return a string.
 * @param {?Function} args.formatSearchResultValue Function invoked to format the saved value for a result. The function is passed
 *                                                 an object representing a single result and should the value to be stored.
 * @param {...*}      args.selectProps             Any remaining properties are passed to the <Select> component, {@link https://react-select.com/props#select-props}.
 * @return {StyledBaseControl} The component.
 */
export default function BaseSearchControl( {
	searchPath,
	onUpdate = () => {},
	selectedValue = [],
	additionalSearchArgs = {},
	defaultOptions = null,
	getSearchArgs = null,
	label = null,
	getSearchURL = null,
	hydrateValues = null,
	formatSearchResults = null,
	formatSearchResultLabel = null,
	formatSearchResultValue = null,
	id = null,
	placeholder = __( 'Search…', 'lifterlms' ),
	className = 'llms-base-search-control',
	classNamePrefix = 'llms-search-control',
	searchDebounceDelay = 300,
	...selectProps
} ) {
	// Setup state variables.
	const [ loadedResults, setLoadedResults ] = useState( [] ),
		addLoadedResults = ( newResults ) =>
			setLoadedResults( loadedResults.concat( newResults ) ),
		[ value, setValue ] = useState(
			Array.isArray( selectedValue ) ? selectedValue : [ selectedValue ]
		);

	// If an ID is stored and passed into component as the selectedValue, hydrate the value from cached results or the API.
	useEffect( () => {
		// Nothing to hydrate.
		if ( ! selectedValue.length ) {
			return;
		}
		hydrateValues = hydrateValues || defaultHydrateValues;
		hydrateValues( value, searchPath, loadedResults ).then(
			( newValues ) => {
				newValues = formatSearchResults(
					newValues,
					formatSearchResultLabel,
					formatSearchResultValue
				);
				const toAdd = differenceBy( newValues, loadedResults, 'id' );
				if ( toAdd.length ) {
					addLoadedResults( toAdd );
				}
				setValue( newValues );
				return newValues;
			}
		);
	}, [ selectedValue ] );

	/**
	 * On change function callback.
	 *
	 * Updates the current value's state and calls the `onUpdate()` user function.
	 *
	 * @since 1.0.0
	 *
	 * @param {?Object[]} newValues Newly selected values.
	 * @return {void}
	 */
	const onChange = ( newValues ) => {
		setValue( Array.isArray( newValues ) ? newValues : [ newValues ] );
		onUpdate( newValues );
	};

	/**
	 * Load options from the server.
	 *
	 * On search term update callback function.
	 *
	 * @since 1.0.0
	 */
	const loadOptions = debounce(
		searchDebounceDelay,
		( searchQuery, callback ) => {
			apiFetch( {
				path: getSearchURL( searchPath, getSearchArgs( searchQuery ) ),
			} ).then( ( results ) => {
				const formatted = formatSearchResults(
					results,
					formatSearchResultLabel,
					formatSearchResultValue
				);
				addLoadedResults( formatted );
				callback( formatted );
			} );
		}
	);

	// Setup defaults.
	id = id || uniqueId( `${ className }--` );

	formatSearchResults = formatSearchResults || defaultFormatSearchResults;
	formatSearchResultLabel = formatSearchResultLabel
		? formatSearchResultLabel
		: ( res ) => res?.id;
	formatSearchResultValue = formatSearchResultValue
		? formatSearchResultValue
		: ( res ) => res?.id;

	getSearchArgs = getSearchArgs
		? getSearchArgs
		: ( searchQuery ) => ( {
			per_page: 10,
			search: searchQuery,
			...additionalSearchArgs,
		} );

	getSearchURL = getSearchURL
		? getSearchURL
		: ( path, args ) => addQueryArgs( path, args );

	selectProps.styles = selectProps.styles || defaultStyles;
	selectProps.theme = selectProps.theme || defaultTheme;

	if ( null === defaultOptions && value.length ) {
		defaultOptions = loadedResults.length
			? uniqBy( loadedResults, 'id' )
			: true;
	}

	return (
		<StyledBaseControl { ...{ id, label } }>
			<Select
				{ ...{
					className,
					classNamePrefix,
					value,
					placeholder,
					loadOptions,
					defaultOptions,
					onChange,
					...selectProps,
				} }
			/>
		</StyledBaseControl>
	);
}
