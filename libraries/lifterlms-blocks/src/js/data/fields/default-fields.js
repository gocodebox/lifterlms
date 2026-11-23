// Internal dependencies.
import { fieldsArrayToObject } from './util';

/**
 * Default fields object
 *
 * @since 2.0.0
 *
 * @type {Object}
 */
export const DEFAULT_FIELDS = fieldsArrayToObject(
	window.llms.userInfoFields.map( ( field ) => ( {
		...field,
		isPersisted: true,
	} ) )
);
