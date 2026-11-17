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
import icon from '../../../icons/envelope';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-email';

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
export const settings = getSettingsFromBase(
	baseSettings,
	{
		title: __( 'User Email', 'lifterlms' ),
		description: __(
			"A special field used to collect a user's account email address.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			inserter: true,
			multiple: false,
			llms_field_inspector: {
				id: false,
				name: false,
				required: false,
				storage: false,
			},
		},
		attributes: {
			id: {
				__default: 'email_address',
			},
			field: {
				__default: 'email',
			},
			label: {
				__default: __( 'Email Address', 'lifterlms' ),
			},
			name: {
				__default: 'email_address',
			},
			required: {
				__default: true,
			},
			data_store: {
				__default: 'users',
			},
			data_store_key: {
				__default: 'user_email',
			},
		},
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
