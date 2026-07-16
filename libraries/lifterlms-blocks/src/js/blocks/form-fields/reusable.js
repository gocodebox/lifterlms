/**
 * Manage reusable block meta data
 *
 * @since Unknown
 * @version 2.0.0
 */

// WP Deps.
import { select, dispatch, subscribe } from '@wordpress/data';
import { addFilter } from '@wordpress/hooks';

// External Deps.
import { some } from 'lodash';

// Internal Deps.
import { getFieldBlocks } from './checks';
import { getCurrentPostType } from '../../util/';

/**
 * Handle setting is_llms_field when editing a wp_block directly
 *
 * This subscribes to changes in the post_content and stores the meta
 * value depending on the presence of llms form field blocks.
 *
 * This handles setting the meta value when editing a reusable block
 * directly on a reusable block edit screen.
 *
 * @since 2.0.0
 */
if ( 'wp_block' === getCurrentPostType() ) {
	let lastContent = '';

	subscribe( () => {
		const content = select( 'core/editor' ).getEditedPostContent();

		if ( 'undefined' === typeof content || content === lastContent ) {
			return;
		}

		lastContent = content;

		const val = content.includes( '<!-- wp:llms/form-field' )
			? 'yes'
			: 'no';

		dispatch( 'core/editor' ).editPost( {
			is_llms_field: val,
		} );
	} );
}

/**
 * Locate a core/block by it's ref ID
 *
 * @since 2.0.0
 *
 * @param {number} ref The WP_Post ID of the reusable block.
 * @return {Object} A block editor block object.
 */
function getBlockByRef( ref ) {
	let refBlock = false;

	some( select( 'core/block-editor' ).getBlocks(), ( block ) => {
		const match = block.attributes.ref === ref;
		if ( match ) {
			refBlock = block;
		}
		return match;
	} );

	return refBlock;
}

/**
 * Handle storing the is_llms_field meta value when editing a block from another post
 *
 * This ensures that any reusable block created on a form page will have the meta data
 * set properly depending on the presence of llms form fields within the reusable block.
 *
 * @since 2.0.0
 */
addFilter(
	'blocks.getSaveElement',
	'llms/core-block/save',
	( el, block, attributes ) => {
		if ( 'core/block' !== block.name ) {
			return el;
		}

		const { ref } = attributes;

		// Wait for block resolution.
		const hasResolvedBlock = select(
			'core'
		).hasFinishedResolution( 'getEntityRecord', [
			'postType',
			'wp_block',
			ref,
		] );

		if ( hasResolvedBlock ) {
			// Get field blocks from the block.
			const fields = getFieldBlocks( getBlockByRef( ref ) );

			if ( fields.length ) {
				/**
				 * Update block metadata.
				 *
				 * The setTimeout is bad but fixes no-op/memory leak.
				 *
				 * @see https://github.com/WordPress/gutenberg/issues/21049#issuecomment-632134201
				 */
				setTimeout( () => {
					dispatch( 'core' ).editEntityRecord(
						'postType',
						'wp_block',
						attributes.ref,
						{
							is_llms_field: fields.length > 0 ? 'yes' : 'no',
						}
					);
				} );
			}
		}

		return el;
	}
);
