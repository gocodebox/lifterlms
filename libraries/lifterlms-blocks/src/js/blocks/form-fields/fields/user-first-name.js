/**
 * BLOCK: llms/form-field-user-first-name
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './text';
import { getSettingsFromBase } from '../settings';
import icon from '../../../icons/user';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-first-name';

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
		title: __( 'First Name', 'lifterlms' ),
		description: __(
			"A special field used to collect a user's first name.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			multiple: false, // Can only have a single email address field.
			llms_field_inspector: {
				id: false,
				name: false,
				required: true,
				storage: false,
			},
		},
		attributes: {
			id: {
				__default: 'first_name',
			},
			field: {
				__default: 'text',
			},
			label: {
				__default: __( 'First Name', 'lifterlms' ),
			},
			name: {
				__default: 'first_name',
			},
			required: {
				__default: true,
			},
			data_store: {
				__default: 'usermeta',
			},
			data_store_key: {
				__default: 'first_name',
			},
		},
		parent: [ 'llms/form-field-user-name' ],
		usesContext: [ 'llms/fieldGroup/fieldLayout' ],
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
