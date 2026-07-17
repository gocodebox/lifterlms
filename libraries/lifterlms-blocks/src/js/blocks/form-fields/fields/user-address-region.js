/**
 * BLOCK: llms/form-field-user-address-region
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
export const name = 'llms/form-field-user-address-region';

/**
 * Array of supported post types.
 *
 * @type {Array}
 */
export const postTypes = getDefaultPostTypes();

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
 * @since 1.12.0 Add transform support.
 * @since 2.0.0 Add reusable block support.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( getDefaultSettings( 'group' ), {
	title: __( 'User Street Address', 'lifterlms' ),
	description: __( "Collect a user's street address.", 'lifterlms' ),
	icon: {
		src: icon,
	},
	supports: {
		multiple: false,
	},
	parent: [ 'llms/form-field-user-name' ],
	llmsInnerBlocks: {
		allowed: [
			'llms/form-field-user-address-state',
			'llms/form-field-user-address-postal-code',
		],
		template: [
			[
				'llms/form-field-user-address-state',
				{ columns: 6, last_column: false },
			],
			[
				'llms/form-field-user-address-postal-code',
				{ columns: 6, last_column: true },
			],
		],
	},
} );
