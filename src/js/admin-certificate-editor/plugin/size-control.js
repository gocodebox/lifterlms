import { __, _x, sprintf } from '@wordpress/i18n';
import { SelectControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

import editCertificate from '../edit-certificate';

/**
 * Format the label for a size in the SizeControl.
 *
 * @since [version]
 *
 * @param {Object} args        Component args.
 * @param {string} args.name   Name of the size.
 * @param {number} args.width  Size width.
 * @param {number} args.height Size height.
 * @param {string} args.unit   Size unit ID.
 * @return {string} Label for the size.
 */
function formatSizeLabel( { name, width, height, unit } ) {
	const { units } = window.llms.certificates,
		{ symbol } = units[ unit ] || {};
	return sprintf( '%1$s (%2$s%4$s x %3$s%4$s)', name, width, height, symbol );
}

/**
 * Control component group for defining a custom size.
 *
 * @since [version]
 *
 * @param {Object} args        Component args.
 * @param {number} args.width  Current width.
 * @param {number} args.height Current height.
 * @param {string} args.unit   Current unit ID.
 * @return {WPElement} The component.
 */
function CustomSizeControl( { width, height, unit } ) {
	const [ currWidth, setWidth ] = useState( width ),
		[ currHeight, setHeight ] = useState( height ),
		[ currUnit, setUnit ] = useState( unit );

	return (
		<div style={ { display: 'flex' } }>
			<div style={ { flex: 1 } }>
				<TextControl
					id="llms-certificate-control--size--custom-width"
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
					id="llms-certificate-control--size--custom-height"
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
					id="llms-certificate-control--size--custom-unit"
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

/**
 * Size control selector.
 *
 * @since [version]
 *
 * @param {Object} args        Component args.
 * @param {string} args.size   Selected size ID.
 * @param {number} args.width  Current width.
 * @param {number} args.height Current height.
 * @param {string} args.unit   Current unit.
 * @return {WPElement} Component.
 */
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
				id="llms-certificate-control--size"
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
