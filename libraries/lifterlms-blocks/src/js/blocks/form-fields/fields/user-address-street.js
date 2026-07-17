/**
 * BLOCK: llms/form-field-user-address-street
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
 * Block Namer
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-address-street';

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
 * Block Settings
 *
 * @since 1.6.0
 * @since 2.0.0 Add reusable block support.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( getDefaultSettings( 'group' ), {
	title: __( 'User Street Address', 'lifterlms' ),
	description: __( "Collect a user's street address.", 'lifterlms' ),
	icon: icon,
	supports: {
		multiple: false,
	},
	llmsInnerBlocks: {
		allowed: [
			'llms/form-field-user-address-street-primary',
			'llms/form-field-user-address-street-secondary',
		],
		template: [
			[
				'llms/form-field-user-address-street-primary',
				{ columns: 8, last_column: false },
			],
			[
				'llms/form-field-user-address-street-secondary',
				{ columns: 4, last_column: true },
			],
		],
	},
	parent: [ 'llms/form-field-user-name' ],
} );
