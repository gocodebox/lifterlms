// WP Deps.
import { dispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

import { ENTITY_CONFIG } from './constants';

/**
 * Registers the access plan entity with the WordPress entity store.
 *
 * @since [version]
 *
 * @return {Object} Action object.
 */
export function registerEntity() {
	const { addEntities } = dispatch( coreStore );
	return addEntities( [ ENTITY_CONFIG ] );
}
