/**
 * Visibility settings for all blocks
 *
 * @since 1.0.0
 * @version 2.0.0
 */

// WP Deps.
import { addFilter } from '@wordpress/hooks';

// Internal Deps.
import visibilityAttributes from './attributes';
import visibilityControls from './inspect';

addFilter(
	'blocks.registerBlockType',
	'llms/visibility-attributes',
	visibilityAttributes
);
addFilter( 'editor.BlockEdit', 'llms/visibility-controls', visibilityControls );
