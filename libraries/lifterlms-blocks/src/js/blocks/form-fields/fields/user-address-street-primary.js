/**
 * BLOCK: llms/form-field-user-address-street-primary
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './text';
import { getSettingsFromBase } from '../settings';
import icon from '../../../icons/house';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-address-street-primary';

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
		title: __( 'User Street Address', 'lifterlms' ),
		description: __(
			"A special field used to collect a user's street address.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			multiple: false,
			llms_field_inspector: {
				id: false,
				name: false,
				required: true,
				storage: false,
			},
		},
		attributes: {
			id: {
				__default: 'llms_billing_address_1',
			},
			label: {
				__default: __( 'Address', 'lifterlms' ),
			},
			name: {
				__default: 'llms_billing_address_1',
			},
			required: {
				__default: true,
			},
			data_store: {
				__default: 'usermeta',
			},
			data_store_key: {
				__default: 'llms_billing_address_1',
			},
		},
		parent: [ 'llms/form-field-user-address-street' ],
		usesContext: [ 'llms/fieldGroup/fieldLayout' ],
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
