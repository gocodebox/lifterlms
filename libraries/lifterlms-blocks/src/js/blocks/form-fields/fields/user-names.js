/**
 * BLOCK: llms/form-field-passwords
 *
 * @since 2.0.0
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
import icon from '../../../icons/user';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-name';

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
 * @since 2.0.0
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( getDefaultSettings( 'group' ), {
	title: __( 'User name', 'lifterlms' ),
	description: __(
		"A special field used to collect a user's first and last name.",
		'lifterlms'
	),
	icon: icon,
	supports: {
		inserter: true,
		multiple: false,
	},
	llmsInnerBlocks: {
		allowed: [
			'llms/form-field-user-first-name',
			'llms/form-field-user-last-name',
		],
		template: [
			[
				'llms/form-field-user-first-name',
				{ columns: 6, last_column: false },
			],
			[
				'llms/form-field-user-last-name',
				{ columns: 6, last_column: true },
			],
		],
	},
} );
