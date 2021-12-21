import { CustomSelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';


import { getFonts } from './fonts-store';

function getFontOptions() {

	const fonts = getFonts();

	return Object.keys( fonts ).map( ( key ) => {
		return {
			key,
			name: fonts[ key ].name,
			style: {
				fontFamily: fonts[ key ].css
			},
		};
	} );

}

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

};
