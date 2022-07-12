// WP deps.
import { __ } from '@wordpress/i18n';

// Internal deps.
import { WRAPPER_CLASSNAME, CLASSNAME, SIZE_DEFAULT } from './constants';
import { STYLES } from './styles';

/**
 * Creates a spinner element inside the specified wrapper Element.
 *
 * @since [version]
 *
 * @param {Element} wrapper DOM node to append the created spinner element to.
 * @param {string}  size    Spinner element size.
 * @return {Element} Returns the created spinner node.
 */
export function create( wrapper, size = SIZE_DEFAULT ) {
	const spinner = document.createElement( 'div' ),
		loadingMsg = __( 'Loading…', 'lifterlms' );

	spinner.innerHTML = `<i class="${ CLASSNAME } ${ size }" role="alert" aria-live="assertive"><span class="screen-reader-text">${ loadingMsg }</span></i>`;
	spinner.classList.add( WRAPPER_CLASSNAME );

	wrapper.appendChild( spinner );

	return spinner;
}

/**
 * Normalizes accepted selector inputs and returns a `NodeList`.
 *
 * When jQuery selection is detected, adds a console deprecation warning as well.
 *
 * @since [version]
 *
 * @param {NodeList|Element|string|jQuery} selector The input selector.
 * @return {Element[]} An array of `Element` objects derived from the selector input.
 */
export function ensureElementList( selector ) {
	selector =
		typeof selector === 'string'
			? document.querySelectorAll( selector )
			: selector;

	// Already a NodeList.
	if ( selector instanceof NodeList ) {
		return Array.from( selector );
	}

	const list = [];
	if ( selector instanceof Element ) {
		list.push( selector );
	} else if ( typeof jQuery !== 'undefined' && selector instanceof jQuery ) {
		selector.toArray().forEach( ( el ) => list.push( el ) );
	}

	return list;
}

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
export function loadStyles() {
	const STYLE_ID = 'llms-spinner-styles';

	if ( ! document.getElementById( STYLE_ID ) ) {
		const style = document.createElement( 'style' );
		style.textContent = STYLES.replace( /\n/g, '' )
			.replace( /\t/g, ' ' )
			.replace( /\s\s+/g, ' ' );
		style.id = STYLE_ID;
		document.head.appendChild( style );
	}
}
