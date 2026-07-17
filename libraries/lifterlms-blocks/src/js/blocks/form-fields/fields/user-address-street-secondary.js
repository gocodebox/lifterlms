/**
 * BLOCK: llms/form-field-user-address-street-secondary
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import {
	settings as baseSettings,
	postTypes,
} from './user-address-street-primary';
import { getSettingsFromBase } from '../settings';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-address-street-secondary';

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
		title: __( 'User Street Address Additional Information', 'lifterlms' ),
		description: __(
			"A special field used to collect a user's street address.",
			'lifterlms'
		),
		attributes: {
			id: {
				__default: 'llms_billing_address_2',
			},
			label: {
				__default: '',
			},
			placeholder: {
				__default: __( 'Apartment, suite, etc…', 'lifterlms' ),
			},
			name: {
				__default: 'llms_billing_address_2',
			},
			required: {
				__default: false,
			},
			data_store_key: {
				__default: 'llms_billing_address_2',
			},
			label_show_empty: {
				__default: true,
			},
		},
		usesContext: [ 'llms/fieldGroup/fieldLayout' ],
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
