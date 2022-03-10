import { getErrors, hasErrors, getPlans, getPlan } from '../selectors';

import { defaultState, mockErrors, mockPlans } from './__data__';

describe( 'AccessPlans/selectors', () => {
	test( 'getErrors()', () => {
		const noErrors = getErrors( defaultState );
		expect( noErrors ).toEqual( [] );

		const errors = getErrors( {
			...defaultState,
			errors: [ ...mockErrors ],
		} );
		expect( errors ).toEqual( mockErrors );
	} );

	test( 'hasErrors()', () => {
		const noErrors = hasErrors( defaultState );
		expect( noErrors ).toStrictEqual( false );

		const errors = hasErrors( {
			...defaultState,
			errors: [ ...mockErrors ],
		} );
		expect( errors ).toStrictEqual( true );
	} );

	test( 'getPlans()', () => {
		const state = { ...defaultState, plans: { ...mockPlans } };

		// Empty.
		expect( getPlans( defaultState ) ).toEqual( [] );

		// Has results.
		expect( getPlans( state ) ).toEqual( Object.values( mockPlans ) );

		// By parent.
		expect( getPlans( state, 987 ) ).toEqual( [ mockPlans[ 789 ] ] );

		// None found for parent.
		expect( getPlans( state, 10101 ) ).toEqual( [] );
	} );

	test( 'getPlan()', () => {
		// Not found.
		expect( getPlan( defaultState, 123456 ) ).toBeUndefined();

		// Exists.
		expect(
			getPlan( { ...defaultState, plans: { ...mockPlans } }, 123 )
		).toEqual( mockPlans[ 123 ] );
	} );
} );
