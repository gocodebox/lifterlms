/**
 * BLOCK: llms/form-field-user-address
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';

// Internal Deps.
import {
	default as getDefaultSettings,
	getSettingsFromBase,
	getDefaultPostTypes,
} from '../settings';
import icon from '../../../icons/address-card';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-address';

/**
 * Array of supported post types.
 *
 * @type {Array}
 */
export const postTypes = getDefaultPostTypes();

/**
 * Is this a default or composed field?
 *
 * @type {boolean}
 */
export const composed = true;

/**
 * Block settings
 *
 * @since 1.6.0
 * @since 1.12.0 Add transform support.
 * @since 2.0.0 Add reusable block support.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase(
	getDefaultSettings( 'group' ),
	{
		title: __( 'User Address', 'lifterlms' ),
		description: __(
			"A group of fields used to collect a user's full address.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			inserter: true,
			multiple: false,
		},
		llmsInnerBlocks: {
			allowed: [
				'llms/form-field-user-address-street',
				'llms/form-field-user-address-city',
				'llms/form-field-user-address-country',
				'llms/form-field-user-address-region',
			],
			template: [
				[ 'llms/form-field-user-address-street' ],
				[ 'llms/form-field-user-address-city' ],
				[ 'llms/form-field-user-address-country' ],
				[ 'llms/form-field-user-address-region' ],
			],
		},
	},
	[ 'providesContext' ]
);
