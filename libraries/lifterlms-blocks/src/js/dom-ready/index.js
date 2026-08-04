/**
 * Handle DOM Ready Events.
 *
 * @since 1.7.0
 * @version 2.0.0
 */

// WP Deps.
import domReady from '@wordpress/dom-ready';
import { select, subscribe } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

// Internal Deps.
import formsReady from './forms/';
import { default as blocksWatcher } from './forms/blocks-watcher';
import { deregisterBlocksForForms } from '../blocks/';

/**
 * On editor DOM ready.
 *
 * @since 1.6.0
 * @since 1.7.0 Refactor for simplicity.
 * @since 1.12.0 Wait for current post to be setup before dispatching ready event.
 * @since 2.0.0 Add blocksWatcher() and add conditional loading on wp_block posts.
 *
 * @return {void}
 */
domReady( () => {
	const { getCurrentPost } = select( editorStore );

	/**
	 * The unsubscribe seems to not run "fast" enough and we end up calling the formsReady() method twice
	 * when relying solely on unsubscribe(). Adding a boolean check appears to "fix" this "problem".
	 */
	let dispatched = false;

	const unsubscribe = subscribe( () => {
		const post = getCurrentPost();

		if ( false === dispatched && 0 !== Object.keys( post ).length ) {
			dispatched = true;
			unsubscribe();

			const { type, is_llms_field: isField } = post;

			if ( 'llms_form' === type ) {
				formsReady();
				deregisterBlocksForForms();
				blocksWatcher();
			} else if ( 'wp_block' === type && 'yes' === isField ) {
				deregisterBlocksForForms();
				blocksWatcher();
			}
		}
	} );
} );
