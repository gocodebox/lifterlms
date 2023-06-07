// WordPress dependencies.
import { registerBlockType } from '@wordpress/blocks';

// Internal dependencies.
import blockJson from './block.json';
import Edit from './edit.jsx';
import Save from './save.jsx';
import Icon from './icon.jsx';

/**
 * Register the Certificate Title block.
 *
 * @since 6.0.0
 */
registerBlockType(
	blockJson,
	{
		icon: Icon,
		edit: Edit,
		save: Save,
	}
);
