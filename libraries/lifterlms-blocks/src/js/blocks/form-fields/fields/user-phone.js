/**
 * BLOCK: llms/form-field-user-email
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './text';
import { getSettingsFromBase } from '../settings';
import icon from '../../../icons/phone';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-phone';

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
 * @since 1.12.0 Add data store support.
 * @since 2.0.0 Add reusable block support.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase(
	baseSettings,
	{
		title: __( 'User Phone', 'lifterlms' ),
		description: __(
			"A field used to collect a user's phone number.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			inserter: true,
			multiple: false,
			llms_field_inspector: {
				id: false,
				name: false,
				storage: false,
			},
		},
		attributes: {
			id: {
				__default: 'llms_phone',
			},
			field: {
				__default: 'tel',
			},
			label: {
				__default: __( 'Phone Number', 'lifterlms' ),
			},
			name: {
				__default: 'llms_phone',
			},
			data_store: {
				__default: 'usermeta',
			},
			data_store_key: {
				__default: 'llms_phone',
			},
		},
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
