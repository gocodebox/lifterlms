import { RadioControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { dispatch } from '@wordpress/data';

/**
 * On Change callback for the radio control element
 *
 * Updates the block's attributes and sets the child blocks column attributes
 * depending on the newly selected field layout option.
 *
 * If the selected layout is "stacked" makes children full width and sets them all as the
 * last column.
 *
 * If the selected layout is "columns" makes children 50/50 width, sets the last field as the
 * last column and marks the other fields as not being the last column.
 *
 * @since 2.0.0
 *
 * @param {Object}   options
 * @param {string}   options.fieldLayout   The newly selected field layout option.
 * @param {Function} options.setAttributes Function to set attributes on the current block (group).
 * @param {Object[]} options.innerBlocks   Array of the current block's innerBlocks.
 * @return {void}
 */
function onChange( { fieldLayout, setAttributes, innerBlocks } ) {
	const { updateBlockAttributes } = dispatch( blockEditorStore );

	// Update the field layout on the group block.
	setAttributes( { fieldLayout } );

	// Update inner blocks.
	const columns = 'columns' === fieldLayout ? 6 : 12;
	innerBlocks.forEach( ( { clientId }, index ) => {
		let last_column = 1 === index;
		if ( 0 === index && 'stacked' === fieldLayout ) {
			last_column = true;
		}

		updateBlockAttributes( clientId, { columns, last_column } );
	} );
}

export default function ( props ) {
	const { attributes, block, setAttributes } = props,
		{ fieldLayout } = attributes,
		{ innerBlocks } = block;

	return (
		<RadioControl
			label={ __( 'Field Layout', 'lifterlms' ) }
			selected={ fieldLayout }
			onChange={ ( fieldLayout ) =>
				onChange( { fieldLayout, setAttributes, innerBlocks } )
			}
			options={ [
				{ value: 'columns', label: __( 'Columns', 'lifterlms' ) },
				{ value: 'stacked', label: __( 'Stacked', 'lifterlms' ) },
			] }
		/>
	);
}
