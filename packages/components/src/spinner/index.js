/* eslint-env jquery */

// Internal deps.
import { SIZE_DEFAULT } from './constants';
import { create, ensureElementList, find, loadStyles } from './utils';

/**
 * This module was originally included in the LifterLMS Core Javascript in the `LLMS` global object as `LLMS.Spinner`.
 *
 * It has since been relocated here as a module in the `@lifterlms/components` package. During the move it was upgraded
 * to enable usage without the requirement of passing in jQuery selectors.
 *
 * In the future, passing native jQuery selectors into any of these modules will be deprecated in favor of native/vanilla Javascript
 * Elements or selector strings that can be passed into `document.querySelector()` or `document.querySelectorAll()`.
 */

/**
 * Retrieves spinner(s) inside a given element.
 *
 * If the spinner element doesn't already exist it will be created.
 *
 * @since 1.1.0
 *
 * @param {jQuery|Element|string} selector  A selector to be parsed by `jQuery()`, an existing `jQuery()` selection, a DOM Element. The first spinner
 *                                          found within the element will be returned. If none found it will be created, appended to the element, and
 *                                          then returned.
 * @param {string}                size      Size or the spinner element. Accepts "default" (40px) or "small" (20px).
 * @param {boolean}               useJQuery If `true`, the return value will be a jQuery selection as opposed to an Element. This is the default behavior
 *                                          for backwards compatibility but will be removed in a future version.
 * @return {null|Element|jQuery} Returns `null` when the selector cannot be located, otherwise returns an `Element` or `jQuery` selection based on the value
 *                               of `useJQuery`.
 */
export function get( selector, size = SIZE_DEFAULT, useJQuery = true ) {
	loadStyles();

	const nodeList = ensureElementList( selector );
	if ( ! nodeList.length ) {
		return null;
	}

	const wrapper = nodeList[ 0 ],
		// Find an existing spinner and create it if one doesn't exist.
		spinner = find( wrapper ) || create( wrapper, size );

	// Return it.
	return useJQuery && typeof jQuery !== 'undefined'
		? jQuery( spinner )
		: spinner;
}

/**
 * Starts spinner(s) inside a given element or element list.
 *
 * If the spinner element doesn't already exist it will be created.
 *
 * @since 1.1.0
 *
 * @param {jQuery|Element|NodeList|string} selector A selector to be parsed by `jQuery()`, an existing `jQuery()` selection, a DOM Element, or an
 *                                                  array of DOM Elements. Each element in the list will have it's spinner started. If a spinner doesn't
 *                                                  exist within the element, it will be appended and then started.
 * @param {string}                         size     Size or the spinner element. Accepts "default" (40px) or "small" (20px).
 * @return {void}
 */
export function start( selector, size = SIZE_DEFAULT ) {
	ensureElementList( selector ).forEach( ( el ) => {
		const spinner = get( el, size, false );
		if ( spinner ) {
			spinner.style.display = 'block';
		}
	} );
}

/**
 * Stops spinner(s) inside a given element or element list.
 *
 * @since 1.1.0
 *
 * @param {jQuery|Element|NodeList|string} selector A selector to be parsed by `jQuery()`, an existing `jQuery()` selection, a DOM Element, or an
 *                                                  array of DOM Elements. Each element in the list will have it's spinner stopped.
 * @return {void}
 */
export function stop( selector ) {
	ensureElementList( selector ).forEach( ( el ) => {
		const spinner = get( el, SIZE_DEFAULT, false );
		if ( spinner ) {
			spinner.style.display = 'none';
		}
	} );
}
