/**
 * Block save functions
 *
 * @since 2.0.0
 */

// WP deps.
import { InnerBlocks } from '@wordpress/block-editor';

/**
 * Save function for a standard field
 *
 * @since 2.0.0
 *
 * @param {Object} props Block properties.
 * @return {Object} Block attributes object.
 */
export function SaveField( props ) {
	const { attributes } = props;
	return attributes;
}

/**
 * Save function for a standard field
 *
 * @since 2.0.0
 *
 * @return {InnerBlocks.Content} Inner blocks.
 */
export function SaveGroup() {
	return <InnerBlocks.Content />;
}
