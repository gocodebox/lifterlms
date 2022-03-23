/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';

/**
 * Internal dependencies
 */
import ConfirmButton from '../';

describe( 'ConfirmButton', () => {
	test( 'default properties', () => {
		const onClick = jest.fn(),
			{ container } = render(
				<ConfirmButton onClick={ onClick }>Button Text</ConfirmButton>
			),
			button = container.firstChild;

		expect( button.textContent ).toBe( 'Button Text' );
		expect( button.classList.value ).toBe( 'components-button' );
		expect( onClick ).not.toHaveBeenCalled();

		// Advance to confirmation state.
		fireEvent.click( button );
		expect( button.textContent ).toBe( 'Are you sure?' );
		expect( button.classList.value ).toBe(
			'components-button is-destructive'
		);
		expect( onClick ).not.toHaveBeenCalled();

		// Confirm the action.
		fireEvent.click( button );
		expect( button.textContent ).toBe( 'Processing…' );
		expect( button.classList.value ).toBe( 'components-button is-busy' );
		expect( onClick ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'cancel button', () => {
		const onClick = jest.fn(),
			{ container } = render(
				<ConfirmButton onClick={ onClick }>Button Text</ConfirmButton>
			),
			button = container.firstChild;

		// Advance to confirmation state.
		fireEvent.click( button );

		// Check the cancel button exists.
		const cancel = container.lastChild;
		expect( cancel.textContent ).toBe( 'Cancel' );
		fireEvent.click( cancel );

		// Button state restored and onClick not called.
		expect( button.textContent ).toBe( 'Button Text' );
		expect( onClick ).not.toHaveBeenCalled();
	} );
} );
