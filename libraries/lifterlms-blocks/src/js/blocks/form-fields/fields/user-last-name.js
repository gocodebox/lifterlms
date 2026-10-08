/**
 * BLOCK: llms/form-field-user-last-name
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './user-first-name';
import { getSettingsFromBase } from '../settings';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-last-name';

/**
 * Is this a default or composed field?
 *
 * @type {string}
 */
export const composed = true;

/**
 * Block settings
 *
 * @since 1.6.0
 * @since 1.8.0 Updated lodash imports.
 * @since 1.12.0 Add data store support.
 * @since 2.0.0 Add reusable block support.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( baseSettings, {
	title: __( 'Last Name', 'lifterlms' ),
	description: __(
		"A special field used to collect a user's last name.",
		'lifterlms'
	),
	attributes: {
		id: {
			__default: 'last_name',
		},
		label: {
			__default: __( 'Last Name', 'lifterlms' ),
		},
		name: {
			__default: 'last_name',
		},
		data_store_key: {
			__default: 'last_name',
		},
	},
} );

export { postTypes };
