// WP Deps.
import { addFilter } from '@wordpress/hooks';

/**
 * Modifies the registration of the core/columns block.
 *
 * I cannot find a way to disable the toggle in the block's inspector panel, nor
 * can I determine the proper way to simply define the block's default attribute value
 * as `false`. By setting the variations to all have the default value it will
 * ensure that any new columns added to a certificate will have mobile stacking disabled.
 * Users will still be able to enable this via the admin UI but since there's no way
 * to disable the toggle we'll have to accept that. Realistically it won't have much impact
 * anyway but it would be good to be able to disable it.
 *
 * @since [version]
 *
 * @see {@link https://github.com/gocodebox/lifterlms/issues/1972}
 *
 * @param {Object} settings  Block registration settings.
 * @param {string} blockName The block's name.
 * @return {Object} Block registration settings.
 */
function modifyColumnsBlock( settings, blockName ) {
	if ( 'core/columns' === blockName ) {
		// Force all the existing columns block variation to have mobile stacking disabled by default.
		settings.variations = settings.variations.map( ( variation ) => {
			const { attributes = {} } = variation;
			variation.attributes = {
				...attributes,
				isStackedOnMobile: false,
			};
			return variation;
		} );
	}

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'llms/certificate-editor/columns-block',
	modifyColumnsBlock,
);
