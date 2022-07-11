import $ from 'jquery';

import { WRAPPER_CLASSNAME, CLASSNAME, SIZE_DEFAULT } from './constants';

import { STYLES } from './styles';

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
 * Loads CSS styles and appends them to the document's <head>.
 *
 * Attaching CSS directly to the `get()` method means that we don't have to worry about loading CSS files (or relying on CSS) included
 * by the LifterLMS core plugin in order to use this styled component.
 * 
 * @since [version]
 *
 * @return {void}
 */
function loadStyles() {

	const STYLE_ID = 'llms-spinner-styles';

	if ( ! document.getElementById( STYLE_ID ) ) {
		
		const style = document.createElement( 'style' );
		style.textContent = STYLES.replace( /\n/g, '' ).replace( /\t/g, ' ' ).replace( /\s\s+/g, ' ' );
		style.id = STYLE_ID;
		document.head.appendChild( style );

	}

}

/**
 * Retrieves spinner(s) inside a given element or element list.
 * 
 * If the spinner element doesn't already exist it will be created.
 *
 * @since 3.0.0
 * @since [version] Adjusted the `selector` parameter to support additional input types beyond a `jQuery()` selection.
 *               Added the `vanilla` parameter for forwards compatibility.
 * 
 * @param {jQuery|Element|string} selector A selector to be parsed by `jQuery()`, an existing `jQuery()` selection, a DOM Element. The first spinner
 *                                         found within the element will be returned. If none found it will be created, appended to the element, and
 *                                         then returned.
 * @param {string}                size     Size or the spinner element. Accepts "default" (40px) or "small" (20px).
 * @param {Boolean}               vanilla  If `true`, the return value will be an `Element` as opposed to a `jQuery()` selection. This is added for
 *                                         forwards compatibility.
 * @return {jQuery|Element} A jQuery selection or DOM Element of the spinner element.
 */
export function get( selector, size = SIZE_DEFAULT, vanilla = false ) {

	loadStyles();

	const $el = $( selector ).first();

	// Look for an existing spinner.
	let $spinner = $el.find( `.${ WRAPPER_CLASSNAME }` ).first();

	// No spinner found.
	if ( ! $spinner.length ) {

		// Create the spinner.
		$spinner = $( `<div class="${ WRAPPER_CLASSNAME }"><i class="${ CLASSNAME } ${ size }"></i></div>` );

		// Add it to the DOM.
		$el.append( $spinner );

	}

	// Return it.
	return vanilla ? $spinner[ 0 ] : $spinner;

}

/**
 * Starts spinner(s) inside a given element or element list.
 * 
 * If the spinner element doesn't already exist it will be created.
 *
 * @since 3.0.0
 * @since [version] Adjusted the `selector` parameter to support additional input types beyond a `jQuery()` selection.
 * 
 * @param {jQuery|Element|Element[]|string} selector A selector to be parsed by `jQuery()`, an existing `jQuery()` selection, a DOM Element, or an
 *                                                   array of DOM Elements. Each element in the list will have it's spinner started. If a spinner doesn't
 *                                                   exist within the element, it will be appended and then started.
 * @param {string}                          size     Size or the spinner element. Accepts "default" (40px) or "small" (20px).
 * @return {void}
 */
export function start( selector, size = SIZE_DEFAULT ) {
	$( selector ).each( function() {
		get( $( this ), size ).show();
	} );
}

/**
 * Stops spinner(s) inside a given element or element list.
 *
 * @since 3.0.0
 * @since [version] Adjusted the `selector` parameter to support additional input types beyond a `jQuery()` selection.
 * 
 * @param {jQuery|Element|Element[]|string} selector A selector to be parsed by `jQuery()`, an existing `jQuery()` selection, a DOM Element, or an
 *                                                   array of DOM Elements. Each element in the list will have it's spinner stopped.
 * @return {void}
 */
export function stop( selector ) {
	$( selector ).each( function() {
		get( $( this ) ).hide();
	} );
}
