// Internal deps.
import { mockCoreStore } from './__utils__';
import { ENTITY_CONFIG } from '../constants';
import {
	registerEntity,
} from '../entity';

describe( 'AccessPlans/entity', () => {
	test( 'registerEntity()', function* () {
		const addEntities = jest.fn().mockReturnValue( { type: 'MOCK_ACTION' } );
		mockCoreStore( 'actions', { addEntities } );

		registerEntity();
		expect( addEntities ).toHaveBeenCalledWith( ENTITY_CONFIG );
	} );
} );
