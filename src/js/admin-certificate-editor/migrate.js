import { subscribe, select, dispatch } from '@wordpress/data';
import { rawHandler, serialize } from '@wordpress/blocks';
import { store as editorStore } from '@wordpress/editor';
import { store as blockEditorStore } from '@wordpress/block-editor';

/**
 * Wait for the current post type to load and unsubscribe as soon as we have a post type.
 *
 * This subscription "translates" the "Move to trash" button to "Delete permanently".
 *
 * @since [version]
 */
const unsubscribe = subscribe( () => {
	const search = new URLSearchParams( window.location.search ),
		doMigration = 1 === parseInt( search.get( 'llms-migrate-legacy-template' ) );

	if ( ! doMigration ) {
		return doUnsubscribe( false );
	}

	const blocks = getAllBlocks();

	if ( 0 !== blocks.length ) {
		doUnsubscribe( true );
	}
} );

/**
 * Performs unsubscribe on the subscription and optionally migrates classic editor blocks.
 *
 * @since [version]
 *
 * @param {boolean} withMigration Whether or not to perform the classic editor migration.
 * @return {void}
 */
function doUnsubscribe( withMigration ) {
	unsubscribe();

	if ( ! withMigration ) {
		return;
	}

	migrateClassicBlock();
}

/**
 * Helper to retrieve a list of all blocks.
 *
 * @since [version]
 *
 * @return {WPBlock[]} Array of blocks.
 */
function getAllBlocks() {
	const { getBlocks } = select( blockEditorStore );
	return getBlocks();
}

/**
 * Performs a migration on all classic editor blocks.
 *
 * This performs logic largely similar to the classic blocks "Convert to Blocks"
 * button. Also forces a post update after migrating.
 *
 * @since [version]
 *
 * @see {@link https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/freeform/convert-to-blocks-button.js}
 *
 * @return {void}
 */
function migrateClassicBlock() {
	const classics = getAllBlocks().filter( ( { name } ) => 'core/freeform' === name );

	if ( 0 === classics.length ) {
		return;
	}

	const { replaceBlocks } = dispatch( blockEditorStore ),
		{ savePost } = dispatch( editorStore );

	classics.forEach( ( block ) => {
		replaceBlocks(
			block.clientId,
			rawHandler( { HTML: serialize( block ) } )
		);
	} );

	savePost();
}

