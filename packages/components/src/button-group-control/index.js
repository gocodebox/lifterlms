// WP deps.
import { BaseControl, Button, ButtonGroup } from '@wordpress/components';
import { useState } from '@wordpress/element';

/**
 * Button Group Control component
 *
 * Similar to the experimental `<RadioGroup>` component from @wordpress/components but it allows
 * passing in an array of options.
 *
 * @since [version]
 *
 * @see BaseControl https://github.com/WordPress/gutenberg/tree/trunk/packages/components/src/base-control
 *
 * @param {Object}   props           Component properties object.
 * @param {string}   props.label     Control label text.
 * @param {string}   props.className Control element css class name attribute.
 * @param {string}   props.id        Control element ID attribute.
 * @param {Function} props.onClick   Callback function when a button in the group is clicked.
 * @param {string}   props.selected  The value of the currently selected option.
 * @param {Object[]} props.options   An array of objects used to create the buttons in the group.
 *                                   Each object should contain at least a "label" and "value" property and
 *                                   can optionally include an "icon" property.
 * @return {BaseControl} The rendered component.
 */
export default function( { label, onClick = () => {}, className = null, id = null, selected = '', options = [] } ) {
	const [ selectedValue, setSelectedValue ] = useState( selected );

	className = className ? ` ${ className }` : '';

	return (
		<BaseControl
			label={ label }
			className={ `llms-button-group-control${ className }` }
			id={ id }
		>
			<ButtonGroup style={ { display: 'flex' } }>
				{ options.map( ( { label: buttonLabel, value, icon = null } ) =>
					( <Button style={ { padding: '6px 8px' } }
						key={ value }
						isPrimary={ value === selectedValue }
						isSecondary={ value !== selectedValue }
						icon={ icon }
						onClick={ () => {
							setSelectedValue( value );
							onClick( value );
						} }
					>
						{ buttonLabel }
					</Button> )
				) }
			</ButtonGroup>
		</BaseControl>
	);
}
