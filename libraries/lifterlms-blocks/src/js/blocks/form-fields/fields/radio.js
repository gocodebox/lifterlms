/**
 * BLOCK: llms/form-field-radio
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { __ } from '@wordpress/i18n';
import { createBlock } from '@wordpress/blocks';

// Internal Deps.
import {
	getSettingsFromBase,
	getDefaultPostTypes,
	getDefaultOptionsArray,
} from '../settings';
import icon from '../../../icons/circle-dot';
import { settings as baseSettings } from './checkboxes';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-radio';

/**
 * Array of supported post types.
 *
 * @type {Array}
 */
export const postTypes = getDefaultPostTypes();

/**
 * Field composition type.
 *
 * @type {string}
 */
export const composed = false;

/**
 * Block settings
 *
 * @since 1.12.0 Add transform support and default options.
 * @since 2.0.0 Add reusable block support.
 * @since 2.0.0 Add default keys.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( baseSettings, {
	title: __( 'Radio', 'lifterlms' ),
	description: __(
		'A group of radio inputs which can be populated with any number of options.',
		'lifterlms'
	),
	icon: icon,
	attributes: {
		field: {
			__default: 'radio',
		},
		options: {
			__default: getDefaultOptionsArray( 2, 1 ),
		},
	},
	transforms: {
		from: [
			{
				type: 'block',
				blocks: [
					'llms/form-field-checkboxes',
					'llms/form-field-select',
				],
				transform: ( attributes ) =>
					createBlock( name, {
						...attributes,
						field: settings.attributes.field.__default,
					} ),
			},
		],
	},
} );
