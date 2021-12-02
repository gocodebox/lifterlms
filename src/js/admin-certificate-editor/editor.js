import { store as coreStore } from '@wordpress/core-data';
import { select, subscribe } from '@wordpress/data';
import domReady from '@wordpress/dom-ready';
import { store as editorStore } from '@wordpress/editor';

/**
 * Retrieves the current media object for the certificate's featured image.
 *
 * @since [version]
 *
 * @return {Object} The featured image object or an empty object if no featured image is set.
 */
function getFeaturedMedia() {
	const { getEditedPostAttribute } = select( editorStore ),
		{ getMedia } = select( coreStore ),
		imageId = getEditedPostAttribute( 'featured_media' );

	return imageId ? getMedia( imageId ) : {};
}

/**
 * Retrieve the source url for the certificate background image.
 *
 * Utilizes the current featured image if set otherwise falls back to the global
 * default certificate background image.
 *
 * @since [version]
 *
 * @return {string} The background image source url.
 */
function getBackgroundImage() {
	const mediaRes = getFeaturedMedia(),
		{ default_image: defaultSrc } = window.llms.certificates;

	// Wait until the API is ready.
	if ( undefined === mediaRes ) {
		return null;
	}

	const { source_url: src } = mediaRes;

	return src ? src : defaultSrc;
}

/**
 * Add inline styles to fix visual issues for certificate building.
 *
 * Forces blocks to take up the actual full-width of the certificate "canvas" and
 * removes margins and spacing between blocks so that what is displayed in the editor
 * more closely resembles what will be displayed on the frontend.
 *
 * @since [version]
 *
 * @return {void}
 */
function applyBlockVisualFixes() {
	const style = document.createElement( 'style' );
	style.type = 'text/css';
	// Force editor style to show blocks as full width.
	style.appendChild( document.createTextNode( '.editor-styles-wrapper .wp-block { max-width: 100% !important; }' ) );
	// Force editor block spacing to more closely resemble rendering on the frontend.
	style.appendChild( document.createTextNode( '.editor-styles-wrapper [data-block], .wp-block { margin-top: 0 !important; margin-bottom: 0 !important }' ) );
	document.head.appendChild( style );
}

/**
 * Updates to the the editor "canvas" to reflect certificate settings.
 *
 * Sets the width, margins, background image, color, etc...
 *
 * @since [version]
 *
 * @return {void}
 */
function updateDOM() {
	const { getEditedPostAttribute } = select( editorStore ),
		bg = getEditedPostAttribute( 'certificate_background' ),
		margins = getEditedPostAttribute( 'certificate_margins' ),
		width = getEditedPostAttribute( 'certificate_width' ),
		height = getEditedPostAttribute( 'certificate_height' ),
		unit = getEditedPostAttribute( 'certificate_unit' ),
		orientation = getEditedPostAttribute( 'certificate_orientation' );

	const list = document.querySelector( '.block-editor-block-list__layout.is-root-container' );
	if ( list ) {
		const displayWidth = 'portrait' === orientation ? width : height,
			displayHeight = 'portrait' === orientation ? height : width,
			padding = margins.map( ( margin ) => `${ margin }%` ).join( ' ' );

		list.style.backgroundImage = `url( '${ getBackgroundImage() }' )`;
		list.style.backgroundSize = `${ displayWidth }${ unit } ${ displayHeight }${ unit }`;
		list.style.backgroundRepeat = 'no-repeat';
		list.style.marginLeft = 'auto';
		list.style.marginRight = 'auto';
		list.style.padding = padding;
		list.style.width = `${ displayWidth }${ unit }`;
		list.style.minHeight = `${ displayHeight }${ unit }`;
		list.style.boxSizing = 'border-box';
	}

	const styles = document.querySelector( '.editor-styles-wrapper' );
	if ( styles ) {
		styles.style.backgroundColor = bg;
	}
}

domReady( () => {
	applyBlockVisualFixes();

	subscribe( updateDOM );
} );
