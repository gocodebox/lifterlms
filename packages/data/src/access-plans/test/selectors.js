// Internal deps.
import { mockCoreStore } from './__utils__';
import { ENTITY_KIND, ENTITY_NAME } from '../constants';
import {
	getEditedPlan,
	getPlan,
	getPlanEdits,
	getPlans,
	getEditedPlans,
	getLastPlanDeleteError,
	getLastPlanSaveError,
	getRawPlan,
	hasEditsForPlan,
	hasPlans,
	isAutosavingPlan,
	isDeletingPlan,
	isSavingPlan,
	isLoading,
} from '../selectors';

describe( 'AccessPlans/selectors', () => {
	test( 'getEditedPlan()', () => {
		const getEditedEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { getEditedEntityRecord } );

		getEditedPlan( undefined, 123 );
		expect( getEditedEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'getPlan()', () => {
		const getEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { getEntityRecord } );

		getPlan( undefined, 123 );
		expect( getEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123, undefined );

		// With a query.
		getPlan( undefined, 123, { query: 'var' } );
		expect( getEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123, { query: 'var' } );
	} );

	test( 'getPlanEdits()', () => {
		const getEntityRecordEdits = jest.fn();
		mockCoreStore( 'selectors', { getEntityRecordEdits } );

		getPlanEdits( undefined, 123 );
		expect( getEntityRecordEdits ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'getPlans()', () => {
		const getEntityRecords = jest.fn();
		mockCoreStore( 'selectors', { getEntityRecords } );

		// Defaults.
		getPlans();
		expect( getEntityRecords ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, undefined );

		// Query is passed on.
		getPlans( undefined, { query: 'var' } );
		expect( getEntityRecords ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, { query: 'var' } );
	} );

	test( 'getEditedPlans()', () => {
		const getEntityRecords = jest.fn().mockReturnValue( [ { id: 123 }, { id: 456 } ] );
		const getEditedEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { getEntityRecords, getEditedEntityRecord } );

		// Defaults.
		getEditedPlans();
		expect( getEntityRecords ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, undefined );
		expect( getEditedEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
		expect( getEditedEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 456 );

		getEntityRecords.mockClear();
		getEditedEntityRecord.mockClear();

		// Query is passed on.
		getEditedPlans( undefined, { query: 'var' } );
		expect( getEntityRecords ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, { query: 'var' } );
		expect( getEditedEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
		expect( getEditedEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 456 );
	} );

	test( 'getLastPlanDeleteError()', () => {
		const getLastEntityDeleteError = jest.fn();
		mockCoreStore( 'selectors', { getLastEntityDeleteError } );

		getLastPlanDeleteError( undefined, 123 );
		expect( getLastEntityDeleteError ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'getLastPlanSaveError()', () => {
		const getLastEntitySaveError = jest.fn();
		mockCoreStore( 'selectors', { getLastEntitySaveError } );

		getLastPlanSaveError( undefined, 123 );
		expect( getLastEntitySaveError ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'getRawPlan()', () => {
		const getRawEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { getRawEntityRecord } );

		getRawPlan( undefined, 123 );
		expect( getRawEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'hasEditsForPlan()', () => {
		const hasEditsForEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { hasEditsForEntityRecord } );

		hasEditsForPlan( undefined, 123 );
		expect( hasEditsForEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'hasPlans()', () => {
		const hasEntityRecords = jest.fn();
		mockCoreStore( 'selectors', { hasEntityRecords } );

		// Defaults.
		hasPlans();
		expect( hasEntityRecords ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, undefined );

		// Query is passed on.
		hasPlans( undefined, { query: 'var' } );
		expect( hasEntityRecords ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, { query: 'var' } );
	} );

	test( 'isAutosavingPlan()', () => {
		const isAutosavingEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { isAutosavingEntityRecord } );

		isAutosavingPlan( undefined, 123 );
		expect( isAutosavingEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'isDeletingPlan()', () => {
		const isDeletingEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { isDeletingEntityRecord } );

		isDeletingPlan( undefined, 123 );
		expect( isDeletingEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'isSavingPlan()', () => {
		const isSavingEntityRecord = jest.fn();
		mockCoreStore( 'selectors', { isSavingEntityRecord } );

		isSavingPlan( undefined, 123 );
		expect( isSavingEntityRecord ).toHaveBeenCalledWith( {}, ENTITY_KIND, ENTITY_NAME, 123 );
	} );

	test( 'isLoading()', () => {
		const isResolving = jest.fn();
		mockCoreStore( 'selectors', { isResolving } );

		isLoading();
		expect( isResolving ).toHaveBeenCalledWith( {}, 'getEntityRecords', [ ENTITY_KIND, ENTITY_NAME, undefined ] );

		isLoading( undefined, { query: 'var' } );
		expect( isResolving ).toHaveBeenCalledWith( {}, 'getEntityRecords', [ ENTITY_KIND, ENTITY_NAME, { query: 'var' } ] );
	} );
} );
