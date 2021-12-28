import { store as coreStore } from '@wordpress/core-data';
import { dispatch, select, subscribe } from '@wordpress/data';
import { rawHandler } from '@wordpress/blocks';
import domReady from '@wordpress/dom-ready';
import { store as editorStore } from '@wordpress/editor';
import { store as blockEditorStore } from '@wordpress/block-editor';

/**
 * Flag used by maybeRefreshContent() to determine of the current post has been saved.
 *
 * @type {boolean}
 */
let hasSaved = false;

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
 * Determines whether or not the current post has a certificate title block.
 *
 * @since [version]
 *
 * @see {@link https://github.com/WordPress/gutenberg/issues/37540}
 *
 * @return {boolean} Returns `true` if the post has the block and `false` if it doesn't.
 */
function hasCertificateTitle() {
	const { getInserterItems } = select( blockEditorStore );

	// Using this method in favor of `canInsertBlockType()` due to this: https://github.com/WordPress/gutenberg/issues/37540.
	const { isDisabled } = getInserterItems().find( ( { name } ) => 'llms/certificate-title' === name );
	return isDisabled;
}

/**
 * Sync saved content with displayed content.
 *
 * We process merge and shortcodes server-side when a published `llms_my_certificate` is updated.
 * When the content is returned from the server, it is not updated in the block editor (it's assumed that
 * the content doesn't change).
 *
 * This function waits until a save has been processed and then determines if the editor's content should
 * be updated by looking for merge or shortcodes in the editor's content. If any are found in the editor's
 * content and none are found in the post's content as returned from the server it will update the editor's
 * content with the content returned from the server.
 *
 * @since [version]
 *
 * @see {@link https://github.com/WordPress/gutenberg/issues/26763}
 *
 * @param {string} content       Post content as returned from the server.
 * @param {string} editedContent Post content currently found in the editor.
 * @return {void}
 */
function maybeRefreshContent( content, editedContent ) {
	const { isSavingPost } = select( editorStore ),
		isSaving = isSavingPost();

	// @see {@link https://github.com/WordPress/gutenberg/issues/17632#issuecomment-583772895}
	if ( isSaving ) {
		hasSaved = true;
	} else if ( ! isSaving && hasSaved ) {
		hasSaved = false;

		const REGEX = /(\{[A-Za-z_].*\})|(\[llms-user .+])/g,
			actualMatch = content.match( REGEX ),
			editedMatch = editedContent.match( REGEX );

		if ( editedMatch?.length && ! actualMatch?.length ) {
			refreshContent( content );
		}
	}
}

/**
 * Replace the content in the editor with the specified content.
 *
 * @since [version]
 *
 * @param {string} content HTML/Block markup string.
 * @return {void}
 */
function refreshContent( content ) {
	const { replaceBlocks } = dispatch( blockEditorStore ),
		{ savePost } = dispatch( editorStore ),
		{ getBlocks } = select( blockEditorStore );

	replaceBlocks(
		getBlocks().map( ( { clientId } ) => clientId ),
		rawHandler( { HTML: content } )
	);

	savePost();
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
	const { getCurrentPostAttribute, getEditedPostAttribute, getCurrentPostType } = select( editorStore ),
		bg = getEditedPostAttribute( 'certificate_background' ),
		margins = getEditedPostAttribute( 'certificate_margins' ),
		width = getEditedPostAttribute( 'certificate_width' ),
		height = getEditedPostAttribute( 'certificate_height' ),
		unit = getEditedPostAttribute( 'certificate_unit' ),
		orientation = getEditedPostAttribute( 'certificate_orientation' ),
		content = getCurrentPostAttribute( 'content' ),
		editedContent = getEditedPostAttribute( 'content' );

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

	if ( 'llms_my_certificate' === getCurrentPostType() ) {
		maybeRefreshContent( content, editedContent );

		// Visually hide the post title element based on the presence of the cert title block.
		const title = document.querySelector( '.edit-post-visual-editor__post-title-wrapper' );
		if ( title ) {
			title.style.display = hasCertificateTitle() ? 'none' : 'initial';
		}
	}
}

domReady( () => {
	applyBlockVisualFixes();

	subscribe( updateDOM );
} );
