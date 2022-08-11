// External deps.
import styled from '@emotion/styled';

// WP deps.
import { BaseControl, Button, Dropdown } from '@wordpress/components';

// Internal deps.
import { getMetadata } from '../';
import Icon from './icon';
import IconList from './icon-list';

/**
 * A <Dropdown> component with styles targeting the sub-components within it.
 *
 * @since [version]
 */
const StyledDropdown = styled( Dropdown )`
	display: block;
	width: 100%;
	> .components-button {
		border: 1px solid rgba( 0, 0, 0, 0.1 );
		border-radius: 2px;
		padding-bottom: 24px;
		padding-top: 24px;
		width: 100%;
	}
`;

/**
 * Renders an icon picker component, intended to be used within the WordPress block editor.
 *
 * @since [version]
 *
 * @param {Object}   props              Component properties.
 * @param {string}   props.icon         The Icon ID.
 * @param {string}   props.iconStyle    The icon style, enum: "solid", "regular", or "brands".
 * @param {string}   props.iconPrefix   The project's icon prefix.
 * @param {Object}   props.controlProps Properties to pass through to the <BaseControl> component.
 * @param {Function} props.onChange     Function called when an icon is selected from the picker. The function is passed three properties:
 *                                      The icon ID, the currently selected style, and the icon's predefined label.
 * @return {BaseControl} A BaseControl containing the icon picker component.
 */
export default function( { icon, iconStyle, controlProps = {}, onChange = () => {}, iconPrefix = 'llms-fa-' } ) {
	const { label } = getMetadata( icon );

	return (
		<BaseControl { ...controlProps }>
			<StyledDropdown
				// position="bottom left"
				className="llms-fa-icon-picker--dropdown"
				contentClassName="llms-fa-icon-picker--content"
				popoverProps={ {
					style: {
						marginTop: '-50px',
						marginLeft: '-180px',
					},
					// placement: 'left-start',
					// offset: 200,
				} }
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Button
						onClick={ onToggle }
						aria-expanded={ isOpen }
						style={ isOpen ? { background: '#f0f0f0' } : {} }
					>
						<Icon { ...{ icon, iconStyle, iconPrefix, style: { fontSize: '18px', marginRight: '8px' } } } />
						<span>{ label }</span>
					</Button>
				) }
				renderContent={ () => (
					<div style={ { width: '380px', maxWidth: '100%' } }>
						<IconList { ...{ iconPrefix, onChange } } />
					</div>
				) }
			/>
		</BaseControl>
	);
}
