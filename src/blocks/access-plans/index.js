// WP Deps.
import { registerBlockType } from '@wordpress/blocks';

// Internal Deps.
import metadata from './block.json';
import edit from './edit';
import save from './save';

import { icon } from './constants';

const { name } = metadata;

/**
 * Register the Certificate Title block.
 *
 * @since [version]
 */
registerBlockType(
	name,
	{
		icon,
		edit,
		save,
	}
);
