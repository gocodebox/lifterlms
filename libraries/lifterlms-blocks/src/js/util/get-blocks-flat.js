/**
 * Block List Flattening Utilities.
 *
 * Recursively loops through all nested blocks and returns
 * a flat array of every block in the layout.
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// External Deps.
import { select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';

/**
 * Recursively pulls inner/nested blocks to return a flat array of blocks.
 *
 * @since 1.6.0
 * @since 2.0.0 Automatically, load innerBlocks of a reusable blocks.
 *
 * @param {Array} blocks Array of WP Blocks.
 * @return {Array} Array of WP Blocks.
 */
export const flattenBlocks = ( blocks ) => {
	let flat = [];

	blocks.forEach( ( block ) => {
		if ( 'core/block' === block.name ) {
			const { getBlocks } = select( blockEditorStore );
			flat = flat.concat( flattenBlocks( getBlocks( block.clientId ) ) );
		} else if ( block.innerBlocks.length ) {
			flat = flat.concat( flattenBlocks( block.innerBlocks ) );
		} else {
			flat.push( block );
		}
	} );

	return flat;
};

/**
 * Retrieve an array of flattened blocks from the block editor.
 *
 * @since 1.6.0
 * @since 1.7.0 Backwards compat fix: fallback to `core/editor` if `core/block-editor` isn't available.
 *
 * @return {Array} Flattened array of blocks.
 */
export default () => {
	const { getBlocks } = select( blockEditorStore );
	return flattenBlocks( getBlocks() );
};
