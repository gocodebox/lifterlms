import { BaseControl, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import editCertificate from '../edit-certificate';

/**
 * Retrieve a description for the margin based on it's index in the margins array.
 *
 * @since [version]
 *
 * @param {number} index Index of the margin.
 * @return {string} Margin description.
 */
function getDesc( index ) {
	const vals = [
		__( 'Top', 'lifterlms' ),
		__( 'Right', 'lifterlms' ),
		__( 'Bottom', 'lifterlms' ),
		__( 'Left', 'lifterlms' ),
	];

	return vals[ index ];
}

/**
 * Single margin control component.
 *
 * @since [version]
 *
 * @param {Object}   args             Component arguments.
 * @param {number}   args.margin      Current value of the margin.
 * @param {number}   args.index       Index of the margin.
 * @param {Function} args.editMargins Function used to update the margins.
 * @return {WPElement} Component.
 */
function MarginControl( { margin, index, editMargins } ) {
	const [ currMargin, setMargin ] = useState( margin );

	return (
		<div style={ { flex: 1 } }>
			<TextControl
				value={ currMargin }
				type="number"
				onChange={ ( val ) => {
					editMargins( val, index, setMargin );
				} }
			/>
			<em style={ { display: 'block', marginTop: '-8px' } }>{ getDesc( index ) }</em>
		</div>
	);
}

/**
 * Certificate margins control.
 *
 * @since [version]
 *
 * @param {Object}   args         Function arguments object.
 * @param {number[]} args.margins Array of numbers representing the certificate's margins.
 * @return {BaseControl} The background control component.
 */
export default function MarginsControl( { margins } ) {
	const editMargins = ( val, index, setState ) => {
		const newMargins = [ ...margins ];
		newMargins[ index ] = val;

		setState( val );
		editCertificate( 'margins', newMargins );
	};

	return (
		<BaseControl
			label={ __( 'Inner Margins', 'lifterlms' ) }
			id="llms-certificate-margins-control"
		>
			<div style={ { display: 'flex' } }>
				{ margins.map( ( margin, index ) => ( <MarginControl key={ index } { ...{ margin, index, editMargins } } /> ) ) }
			</div>
		</BaseControl>
	);
}
