// Internal deps.
import { mockCoreStore } from './__utils__';
import { ENTITY_KIND, ENTITY_NAME } from '../constants';
import {
	deletePlan,
	editPlan,
	saveEditedPlan,
	savePlan,
} from '../actions';

describe( 'AccessPlans/actions', () => {
	test( 'deletePlan()', function* () {
		const deleteEntityRecord = jest.fn().mockReturnValue( { type: 'MOCK_ACTION' } );
		mockCoreStore( 'actions', { deleteEntityRecord } );

		yield deletePlan( 123 );
		expect( deleteEntityRecord ).toHaveBeenCalledWith( ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'editPlan()', function* () {
		const editEntityRecord = jest.fn().mockReturnValue( { type: 'MOCK_ACTION' } );
		mockCoreStore( 'actions', { editEntityRecord } );

		yield editPlan( 123, { edit: true } );
		expect( editEntityRecord ).toHaveBeenCalledWith( ENTITY_KIND, ENTITY_NAME, 123, { edit: true } );
	} );

	test( 'saveEditedPlan()', function* () {
		const saveEditedEntityRecord = jest.fn().mockReturnValue( { type: 'MOCK_ACTION' } );
		mockCoreStore( 'actions', { saveEditedEntityRecord } );

		yield saveEditedPlan( 123 );
		expect( saveEditedEntityRecord ).toHaveBeenCalledWith( ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'savePlan()', function* () {
		const saveEntityRecord = jest.fn().mockReturnValue( { type: 'MOCK_ACTION' } );
		mockCoreStore( 'actions', { saveEntityRecord } );

		yield savePlan( { id: 123 } );
		expect( saveEntityRecord ).toHaveBeenCalledWith( ENTITY_KIND, ENTITY_NAME, { id: 123 } );
	} );
} );
