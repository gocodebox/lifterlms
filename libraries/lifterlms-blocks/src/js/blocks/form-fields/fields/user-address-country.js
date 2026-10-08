/**
 * BLOCK: llms/form-field-user-address-country
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './select';
import { getSettingsFromBase } from '../settings';
import icon from "../../../icons/earth-americas";

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-address-country';

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
		title: __( 'User Country', 'lifterlms' ),
		description: __(
			"A special field used to collect a user's billing country.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			multiple: false,
			llms_field_inspector: {
				id: false,
				name: false,
				required: true,
				match: false,
				storage: false,
				options: false,
			},
		},
		attributes: {
			id: {
				__default: 'llms_billing_country',
			},
			label: {
				__default: __( 'Country', 'lifterlms' ),
			},
			name: {
				__default: 'llms_billing_country',
			},
			required: {
				__default: true,
			},
			data_store: {
				__default: 'usermeta',
			},
			data_store_key: {
				__default: 'llms_billing_country',
			},
			options_preset: {
				__default: 'countries',
			},
			placeholder: {
				__default: __( 'Select a Country', 'lifterlms' ),
			},
		},
		parent: [ 'llms/form-field-user-address' ],
	},
	[ 'transforms' ]
);

export { postTypes };
