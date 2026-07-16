/**
 * Form Field related checks
 *
 * @since Unknown
 * @version 2.4.1
 */

// External Deps.
import { forEach } from 'lodash';

// WP Deps.
import { select } from '@wordpress/data';

// Internal deps.
import { store as fieldsStore } from '../../data/fields';

/**
 * Determines if the block is (or contains) field block/s
 *
 * If the block contains innerBlocks (or is a reusable block) it
 * passes the list of contained to filterBlocks.
 *
 * @since Unknown
 * @since 2.0.0 Adds support for reusable blocks.
 *
 * @param {Object} block A block object.
 * @return {Object[]} An array of field blocks.
 */
function filterBlock( block ) {
	// when running `wp.blocks.parse()` for a reusable block `false` gets passed in and I don't understand it.
	if ( ! block ) {
		return [ block ];
	}

	// Check the block's inner blocks.
	if ( block.innerBlocks.length ) {
		return filterBlocks( block.innerBlocks );
	}

	// Reusable blocks don't have inner blocks defined so we need to look them up this way.
	if ( 'core/block' === block.name ) {
		const { blocks } = select( 'core' ).getEditedEntityRecord(
			'postType',
			'wp_block',
			block.attributes.ref
		);
		return filterBlocks( blocks );
	}

	// Only return form field blocks.
	if ( -1 === block.name.indexOf( 'llms/form-field' ) ) {
		return [];
	}

	return [ block ];
}

/**
 * Recursively filter blocks (checking inner blocks)
 *
 * Returns only a (flattened) list of field blocks.
 *
 * @since 2.0.0
 *
 * @param {Object[]} blocks An array of blocks to filter.
 * @return {Object[]} An array of field blocks.
 */
function filterBlocks( blocks = [] ) {
	let fieldBlocks = [];

	forEach( blocks, ( block ) => {
		const filtered = filterBlock( block );
		if ( filtered.length ) {
			fieldBlocks = fieldBlocks.concat( filtered );
		}
	} );

	return fieldBlocks;
}

/**
 * Retrieve a "flattened" list of all form fields from a list of blocks
 *
 * Recursively checks each block for it's inner blocks to determine if there's
 * fields in the block.
 *
 * @since Unknown
 *
 * @param {Object[]} blocks An array of blocks to filter.
 * @return {Object[]} An array of field blocks.
 */
export const getFieldBlocks = ( blocks = [] ) => {
	if ( ! Array.isArray( blocks ) ) {
		blocks = [ blocks ];
	}
	return filterBlocks( blocks );
};

/**
 * Ensure field attributes are unique in the requested context.
 *
 * @since Unknown
 * @since 2.0.0 Added `context` parameter and use data from the llms/user-info-fields store.
 * @since 2.4.1 Fixed order of `getFieldBy()` parameters.
 *
 * @param {string} key     Attribute key name.
 * @param {string} val     String to check for uniqueness.
 * @param {string} context Field context to look within. Accepts "global" to check against all fields or "local"
 *                         to check only against loaded fields in the current form.
 * @return {boolean} Returns `true` when the string is unique across the form and `false` if it's not.
 */
export const isUnique = ( key, val, context = 'global' ) => {
	const { getFieldBy } = select( fieldsStore );
	return getFieldBy( val, key, context ) ? false : true;
};
