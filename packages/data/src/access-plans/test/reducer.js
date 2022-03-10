import reducer from '../reducer';
import { ACTION_TYPES } from '../constants';

import { defaultState, mockErrors, mockPlans } from './__data__';

describe( 'AccessPlans/reducer', () => {
	test( 'DEFAULT', () => {
		const state = reducer( undefined, {} );
		expect( state ).toEqual( defaultState );
		expect( state ).not.toBe( defaultState );
	} );

	test( 'CREATE_ITEM', () => {
		const plan = {
			id: 123,
		};

		const state = reducer( defaultState, {
			type: ACTION_TYPES.CREATE_ITEM,
			plan,
		} );
		expect( state.plans ).toEqual( { 123: plan } );
		expect( state.errors.length ).toStrictEqual( 0 );
	} );

	test( 'UPDATE_ITEM', () => {
		const plan = {
			id: 123,
			price: 0.0,
		};

		const state = reducer(
			{ ...defaultState, plans: { ...mockPlans } },
			{
				type: ACTION_TYPES.UPDATE_ITEM,
				plan,
			}
		);

		// Plan updated.
		expect( state.plans[ 123 ] ).toEqual( plan );

		// Other plans remain unchanged.
		expect( state.plans[ 789 ] ).toEqual( mockPlans[ 789 ] );
		expect( Object.keys( state.plans ).length ).toStrictEqual( 2 );

		// No errors added.
		expect( state.errors.length ).toStrictEqual( 0 );
	} );

	test( 'DELETE_ITEM', () => {
		const id = 789;

		const state = reducer(
			{ ...defaultState, plans: { ...mockPlans } },
			{
				type: ACTION_TYPES.DELETE_ITEM,
				id,
			}
		);
		// Plan deleted.
		expect( state.plans[ 789 ] ).toBe( undefined );
		expect( Object.keys( state.plans ).length ).toStrictEqual( 1 );

		// Other plans remain unchanged.
		expect( state.plans[ 123 ] ).toEqual( mockPlans[ 123 ] );

		// No errors added.
		expect( state.errors.length ).toStrictEqual( 0 );
	} );

	test( 'RECEIVE_ERROR', () => {
		const error = mockErrors[ 0 ];

		const state = reducer(
			{ ...defaultState, plans: { ...mockPlans } },
			{
				type: ACTION_TYPES.RECEIVE_ERROR,
				error,
			}
		);

		// Plans remain unchanged.
		expect( state.plans ).toEqual( mockPlans );

		// Error added.
		expect( state.errors[ 0 ] ).toBe( error );
		expect( state.errors.length ).toStrictEqual( 1 );
	} );

	test( 'RECEIVE_ITEMS', () => {
		const state = reducer( defaultState, {
			type: ACTION_TYPES.RECEIVE_ITEMS,
			plans: Object.values( mockPlans ),
		} );

		// Plans remain unchanged.
		expect( state.plans ).toEqual( mockPlans );
		expect( state.plans ).not.toBe( mockPlans );

		// No errors added.
		expect( state.errors.length ).toStrictEqual( 0 );
	} );
} );
