jest.mock( '../request' );

import { getPlan, getPlans } from '../resolvers';
import { ACTION_TYPES, ERRORS } from '../constants';
import { defaultState, mockErrors, mockPlans, mockApiError } from './__data__';

import request from '../request';

let mockRequestResponse;

request.mockImplementation( () => {
	if ( mockRequestResponse.err ) {
		throw mockRequestResponse;
	}
	return mockRequestResponse;
} );

function mockApiWithError() {
	mockRequestResponse = { ...mockApiError };
}

describe( 'AccessPlans/resolvers', () => {

	beforeEach( () => {
		mockRequestResponse = null;
	} );

	describe( 'getPlan()', () => {

		it ( 'should error when an API error is encountered', () => {

			mockApiWithError();
			expect( getPlan( 123 ).next().value ).toEqual( { type: ACTION_TYPES.RECEIVE_ERROR, error: mockApiError } );

		} );


		it ( 'should retrieve the plan', () => {

			mockRequestResponse = { id: 123, title: 'A title' };
			expect( getPlan( 123 ).next().value ).toEqual( mockRequestResponse );

		} );

	} );

	describe( 'getPlans()', () => {

		it ( 'should error when an API error is encountered', () => {

			mockApiWithError();
			expect( getPlans().next().value ).toEqual( { type: ACTION_TYPES.RECEIVE_ERROR, error: mockApiError } );

		} );


		it ( 'should retrieve the plans', () => {

			mockRequestResponse = [ { id: 123, title: 'A title' } ];
			expect( getPlans().next().value ).toEqual( mockRequestResponse );

		} );

	} );

} );
