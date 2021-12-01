import { __, _x, sprintf } from '@wordpress/i18n';
import { SelectControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

import editCertificate from '../edit-certificate';


function formatSizeLabel( { name, width, height, unit } ) {
	return sprintf( '%1$s (%2$s%4$s x %3$s%4$s)', name, width, height, unit );
}


function CustomSizeControl( { width, height, unit } ) {
	const [ currWidth, setWidth ] = useState( width ),
		[ currHeight, setHeight ] = useState( height ),
		[ currUnit, setUnit ] = useState( unit );

	return (
		<div style={ { display: 'flex' } }>
			<div style={ { flex: 1 } }>
				<TextControl
					label={ __( 'Custom Size Width', 'lifterlms' ) }
					placeholder={ __( 'Width', 'lifterlms' ) }
					type="number"
					value={ currWidth }
					hideLabelFromVision
					onChange={ ( val ) => {
						setWidth( val );
						editCertificate( 'width', val );
					} }
				/>
			</div>
			<div style={ { flex: 1 } }>
				<TextControl
					label={ __( 'Custom Size Height', 'lifterlms' ) }
					placeholder={ __( 'Height', 'lifterlms' ) }
					type="number"
					value={ currHeight }
					hideLabelFromVision
					onChange={ ( val ) => {
						setHeight( val );
						editCertificate( 'height', val );
					} }
				/>
			</div>
			<div style={ { flex: 2 } }>
				<SelectControl
					label={ __( 'Custom Size Dimension', 'lifterlms' ) }
					hideLabelFromVision
					value={ currUnit }
					onChange={ ( val ) => {
						setUnit( val );
						editCertificate( 'unit', val );
					} }
					options={ [
						{ value: 'in', label: __( 'in (Inches)', 'lifterlms' ) },
						{ value: 'mm', label: __( 'mm (Millimeters)', 'lifterlms' ) },
					] }
				/>
			</div>
		</div>
	);
}


export default function SizeControl( { size: selected, width, height, unit } ) {
	const { sizes } = window.llms.certificates,
		options = Object.entries( sizes ).map( ( [ value, sizeData ] ) => ( { value, label: formatSizeLabel( sizeData ) } ) ),
		[ size, setSize ] = useState( selected );

	options.push( {
		value: 'CUSTOM',
		label: _x( 'Custom', 'certificate sizing option', 'lifterlms' ),
	} );

	return (
		<>
			<SelectControl
				label={ __( 'Size', 'lifterlms' ) }
				value={ size }
				options={ options }
				onChange={ ( val ) => {
					setSize( val );
					editCertificate( 'size', val );

					// Update other fields so that when switching to custom it always shows the previously selected size data.
					if ( 'CUSTOM' !== val ) {
						const newSize = sizes[ val ];
						editCertificate( 'unit', newSize.unit );
						editCertificate( 'width', newSize.width );
						editCertificate( 'height', newSize.height );
					}
				} }
			/>

			{ 'CUSTOM' === size && ( <CustomSizeControl { ...{ editCertificate, width, height, unit } } /> ) }

		</>
	);
}
