import { useSetting } from '@wordpress/block-editor';
import { BaseControl, ColorPalette } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import editCertificate from '../edit-certificate';

/**
 * Retrieves a color palette for use in the background control component.
 *
 * Attempts to use the theme's color palette (if available) and falls back
 * to the color palette provided by the LifterLMS plugin.
 *
 * Additionally converts all hexcodes to lowercase to enforce consistency
 * across themes which may store hexcodes in upper or lower case.
 *
 * @see {@link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#block-color-palettes}
 *
 * @since [version]
 *
 * @return {Object[]} Array of color palette objects.
 */
function usePalette() {
	let palette = useSetting( 'color.palette' );

	// Use default LifterLMS colors if there's none specified by the theme.
	if ( ! palette.length ) {
		palette = window.llms.certificates.colors;
	}

	return palette.map( ( item ) => {
		const { color } = item;
		return {
			...item,
			color: color.startsWith( '#' ) ? color.toLowerCase() : color,
		};
	} );
}

/**
 * Certificate background color control.
 *
 * @since [version]
 *
 * @param {Object} args            Function arguments object.
 * @param {string} args.background Value of the background color.
 * @return {BaseControl} The background control component.
 */
export default function BackgroundControl( { background } ) {
	const [ color, setColor ] = useState( background );
	return (
		<BaseControl
			label={ __( 'Background Color', 'lifterlms' ) }
			id="llms-certificate-control--background-color"
		>
			<ColorPalette
				colors={ usePalette() }
				onChange={ ( val ) => {
					setColor( val );
					editCertificate( 'background', val );
				} }
				value={ color }
				clearable={ false }
			/>
		</BaseControl>
	);
}
