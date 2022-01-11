// WP Deps.
import { CustomSelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

// Internal Deps.
import { getFonts } from './fonts-store';

/**
 * Retrieves a list of font objects in a format sufficient to pass into a CustomSelectControl.
 *
 * @since [version]
 *
 * @return {Object[]} Array of font objects.
 */
function getFontOptions() {
	const fonts = getFonts();

	return Object.keys( fonts ).map( ( key ) => {
		return {
			key,
			name: fonts[ key ].name,
			style: {
				fontFamily: fonts[ key ].css,
			},
		};
	} );
}

/**
 * A CustomSelectControl used for displaying font family options.
 *
 * @since [version]
 *
 * @param {Object}   args               Arguments object.
 * @param {string}   args.fontFamily    Currently selected font family id.
 * @param {Function} args.setAttributes Function used to update the blocks attributes.
 * @return {CustomSelectControl} The select control.
 */
export default function FontSelectControl( { fontFamily, setAttributes } ) {
	const [ currFontFamily, setFontFamily ] = useState( fontFamily ),
		options = getFontOptions();

	return (
		<CustomSelectControl
			label={ __( 'Font Family', 'lifterlms' ) }
			options={ options }
			onChange={ ( { selectedItem } ) => {
				const { key } = selectedItem;
				setAttributes( { fontFamily: key } );
				setFontFamily( key );
			} }
			value={ options.find( ( { key } ) => key === currFontFamily ) }
		/>
	);
}
