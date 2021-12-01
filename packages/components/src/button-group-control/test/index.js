/**
 * External dependencies
 */
import { render } from '@testing-library/react';

/**
 * Internal dependencies
 */
import ButtonGroupControl from '../';

describe( 'ButtonGroupControl', () => {
	it( 'should render a <ButtonGroupControl> component', () => {
		const args = {
				id: 'group-id',
				className: 'extra-class',
				label: 'Label',
				options: [
					{
						label: 'Button 1',
						value: 'button-1',
					},
					{
						label: 'Button 2',
						value: 'button-2',
					},
				],
				selected: 'button-1',
			},
			{ container } = render( <ButtonGroupControl { ...args } /> ),
			control = container.firstChild,
			label = control.querySelector( 'label' ),
			buttons = control.querySelectorAll( 'button' );

		expect( control.classList.contains( 'llms-button-group-control' ) ).toBe( true );
		expect( control.classList.contains( 'components-base-control' ) ).toBe( true );
		expect( control.classList.contains( 'extra-class' ) ).toBe( true );

		expect( label.getAttribute( 'for' ) ).toBe( args.id );
		expect( label.textContent ).toBe( args.label );

		expect( buttons.length ).toBe( args.options.length );
		expect( buttons[ 0 ].textContent ).toBe( args.options[ 0 ].label );
		expect( buttons[ 0 ].classList.contains( 'is-primary' ) ).toBe( true );
		expect( buttons[ 1 ].textContent ).toBe( args.options[ 1 ].label );
		expect( buttons[ 1 ].classList.contains( 'is-secondary' ) ).toBe( true );
	} );
} );
