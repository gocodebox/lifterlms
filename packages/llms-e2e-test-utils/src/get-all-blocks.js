import { getAllBlocks as realGetAllBlocks } from '@wordpress/e2e-test-utils';
 
 /**
  * Removes clientIds from a block and its innerBlocks.
  *
  * @since [version]
  *
  * @param {Object}   options
  * @param {string}   options.clientId The block's clientId.
  * @param {...mixed} options.block    The remaining block properties.
  * @return {Object} The original block without the clientId property.
  */
function removeClientId( { clientId, ...block } ) {

	block.innerBlocks = removeClientIds( block.innerBlocks );
	return block;

}

/**
 * Removes clientIds from a list of blocks.
 *
 * @since [version]
 *
 * @param {Object[]} blocks Array of WP_Block objects.
 * @return {Object[]} Original array with clientIds removed.
 */
function removeClientIds( blocks ) {
	return blocks.map( ( block ) => removeClientId( block ) )
}

/**
 * Retrieves a list of blocks in the editor, with or without client IDs.
 *
 * Specifying `withClientIds=false` allows using the resulting array of block
 * objects in snapshots without having to specify a snapshot matcher
 * that excludes (possibly nested) blocks with clientIds that will not
 * match future test runs.
 *
 * @since [version]
 *
 * @param {Boolean} withClientIds Whether or not to exclude clientIds.
 * @return {Object[]} Array of block objects.
 */
export async function getAllBlocks( withClientIds = true ) {

	const blocks = await realGetAllBlocks();

	if ( withClientIds ) {
		return blocks;
	}

	return removeClientIds( blocks );

}
