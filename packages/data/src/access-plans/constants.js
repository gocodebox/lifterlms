// WP Deps.
import { __ } from '@wordpress/i18n';

/**
 * Action types.
 *
 * @type {Object}
 */
export const ACTION_TYPES = {
	CREATE_ITEM: 'CREATE_PLAN',
	EDIT_ITEM: 'EDIT_PLAN',
	DELETE_ITEM: 'DELETE_ITEM',
	RECEIVE_ERROR: 'RECEIVE_ERROR',
	RECEIVE_ITEMS: 'RECEIVE_PLANS',
	UPDATE_ITEM: 'UPDATE_ITEM',
};

/**
 * Error objects.
 *
 * @type {Object}
 */
export const ERRORS = {
	CREATE_ITEM_WITH_ID: {
		name: 'create-resource-with-id',
		message: __(
			'The supplied access plan object contains a resource ID and cannot be created.',
			'lifterlms'
		),
	},

	UPDATE_ITEM_MISSING_DATA: {
		name: 'missing-update-data',
		message: __(
			'The supplied access plan object contains no data to update.',
			'lifterlms'
		),
	},
	UPDATE_ITEM_MISSING_ID: {
		name: 'missing-resource-id',
		message: __(
			'The supplied access plan object is missing the required id key.',
			'lifterlms'
		),
	},
};

/**
 * API path Resource name.
 *
 * @type {string}
 */
export const RESOURCE_NAME = 'access-plans';

/**
 * Data store reducer key
 *
 * @type {string}
 */
export const STORE_NAME = 'llms/access-plans';
