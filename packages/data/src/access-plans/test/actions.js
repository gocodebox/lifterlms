jest.mock( '../request' );

import {
	createPlan,
	deletePlan,
	receivePlans,
	receiveError,
	updatePlan,
} from '../actions';
import { ACTION_TYPES, ERRORS } from '../constants';
import { mockErrors, mockPlans, mockApiError } from './__data__';

import request from '../request';

let mockRequestResponse;

request.mockImplementation( () => {
	if ( mockRequestResponse.err ) {
		throw mockRequestResponse;
	}
	return mockRequestResponse;
} );

/**
 */
function mockApiWithError() {
	mockRequestResponse = { ...mockApiError };
}

describe( 'AccessPlans/actions', () => {
	beforeEach( () => {
		mockRequestResponse = null;
	} );

	describe( 'createPlan()', () => {
		it( 'should error when the supplied object already contains an id', function* () {
			const data = { id: 123 },
				expected = {
					type: ACTION_TYPES.RECEIVE_ERROR,
					error: {
						...ERRORS.CREATE_ITEM_WITH_ID,
						data,
					},
				};
			expect( yield createPlan( data ) ).toEqual( expected );
		} );

		it( 'should error when an API error is encountered', function* () {
			mockApiWithError();

			const data = { title: 'A title' },
				expected = {
					type: ACTION_TYPES.RECEIVE_ERROR,
					error: mockApiError,
				};
			expect( yield createPlan( data ) ).toEqual( expected );
		} );

		it( 'should create a new plan', function* () {
			mockRequestResponse = { id: 123, title: 'A title' };

			const data = { title: mockRequestResponse.title },
				expected = {
					type: ACTION_TYPES.CREATE_ITEM,
					plan: mockRequestResponse,
				};
			expect( yield createPlan( data ) ).toEqual( expected );
		} );
	} );

	describe( 'deletePlan()', () => {
		it( 'should error when an API error is encountered', function* () {
			mockApiWithError();

			const expected = {
				type: ACTION_TYPES.RECEIVE_ERROR,
				error: mockApiError,
			};
			expect( yield deletePlan( 1 ) ).toEqual( expected );
		} );

		it( 'should delete the plan', function* () {
			mockRequestResponse = {};

			const id = 123,
				expected = {
					type: ACTION_TYPES.DELETE_ITEM,
					id,
				};
			expect( yield deletePlan( id ) ).toEqual( expected );
		} );
	} );

	test( 'receivePlans()', () => {
		const plans = Object.values( mockPlans ),
			expected = {
				type: ACTION_TYPES.RECEIVE_ITEMS,
				plans,
			};

		expect( receivePlans( plans ) ).toEqual( expected );
	} );

	test( 'receiveError()', () => {
		const error = mockErrors[ 1 ],
			expected = {
				type: ACTION_TYPES.RECEIVE_ERROR,
				error,
			};

		expect( receiveError( error ) ).toEqual( expected );
	} );

	describe( 'updatePlan()', () => {
		it( 'should error when the supplied object is missing a plan id', function* () {
			const edits = { title: 'A title' },
				expected = {
					type: ACTION_TYPES.RECEIVE_ERROR,
					error: {
						...ERRORS.UPDATE_ITEM_MISSING_ID,
						edits,
					},
				};
			expect( yield updatePlan( edits ) ).toEqual( expected );
		} );

		it( 'should error when the supplied object is empty', function* () {
			const edits = { id: 123 },
				expected = {
					type: ACTION_TYPES.RECEIVE_ERROR,
					error: {
						...ERRORS.UPDATE_ITEM_MISSING_DATA,
						edits,
					},
				};
			expect( yield updatePlan( edits ) ).toEqual( expected );
		} );

		it( 'should error when an API error is encountered', function* () {
			mockApiWithError();

			const edits = { id: 123, title: 'A title' },
				expected = {
					type: ACTION_TYPES.RECEIVE_ERROR,
					error: mockApiError,
				};
			expect( yield updatePlan( edits ) ).toEqual( expected );
		} );

		it( 'should update the plan', function* () {
			mockRequestResponse = {
				id: 123,
				title: 'New Title',
				otherProp: true,
			};

			const edits = { id: 123, title: 'Original Title' },
				expected = {
					type: ACTION_TYPES.UPDATE_ITEM,
					plan: mockRequestResponse,
				};
			expect( yield updatePlan( edits ) ).toEqual( expected );
		} );
	} );
} );
