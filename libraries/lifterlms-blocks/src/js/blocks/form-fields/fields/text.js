/**
 * BLOCK: llms/form-field-text
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

// Internal Deps.
import {
	default as getDefaultSettings,
	getSettingsFromBase,
	getDefaultPostTypes,
} from '../settings';
import defaultIcon from '../../../icons/text';
import hashtagIcon from '../../../icons/hashtag';
import envelopeIcon from '../../../icons/envelope';
import lockIcon from '../../../icons/lock';
import phoneIcon from '../../../icons/phone';
import linkIcon from '../../../icons/link';

const baseSettings = getDefaultSettings();

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-text';

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
 * Block Variations
 *
 * @type {Object[]}
 */
const variations = [
	{
		name: 'text',
		title: __( 'Text', 'lifterlms' ),
		description: __(
			'An input field which accepts any form of text.',
			'lifterlms'
		),
		isDefault: true,
		icon: defaultIcon,
	},
	{
		name: 'email',
		title: __( 'Email', 'lifterlms' ),
		description: __(
			'A text input field which only accepts an email address.',
			'lifterlms'
		),
		icon: envelopeIcon,
	},
	{
		name: 'password',
		title: __( 'Password', 'lifterlms' ),
		description: __( 'User password confirmation field.', 'lifterlms' ),
		icon: lockIcon,
		scope: [],
	},
	{
		name: 'number',
		title: __( 'Number', 'lifterlms' ),
		description: __(
			'An input field which only accepts numeric input.',
			'lifterlms'
		),
		icon: hashtagIcon,
		attributes: {
			html_attrs: {
				min: '',
				max: '',
			},
		},
	},
	{
		name: 'tel',
		title: __( 'Phone Number', 'lifterlms' ),
		description: __(
			'An input field which only accepts phone numbers.',
			'lifterlms'
		),
		icon: phoneIcon,
	},
	{
		name: 'url',
		title: __( 'Website Address / URL', 'lifterlms' ),
		description: __(
			'An input field which only accepts a website address or URL.',
			'lifterlms'
		),
		icon: linkIcon,
	},
];

/**
 * Add information to each variation
 *
 * @since 2.0.0
 * @since 2.4.3 Temporary skip merging foreground color for certain block editor versions.
 *
 * @param {Object} variation A block variation object.
 * @return {Object[]} Update block variations array.
 */
variations.forEach( ( variation ) => {
	// Setup scope.
	variation.scope = variation.scope || [ 'block', 'inserter', 'transform' ];

	/**
	 * Temporary skip merging foreground color for certain block editor versions.
	 * See https://github.com/gocodebox/lifterlms-blocks/issues/170
	 */
	if ( window.llmsBlocks.variationIconCanBeObject ) {
		// Update the icon (add the default foreground color.
		variation.icon = {
			...baseSettings.icon,
			src: variation.icon,
		};
	}

	// Add a "field" attribute based off the variation name.
	if ( ! variation.attributes ) {
		variation.attributes = {};
	}
	variation.attributes.field = variation.name;

	// Add an isActive function.
	variation.isActive = ( blockAttributes, variationAttributes ) =>
		blockAttributes.field === variationAttributes.field;
} );

/**
 * Fill the controls slot with additional controls specific to this field.
 *
 * @since 2.0.0
 *
 * @param {Object} attributes Block attributes.
 * @param {Function} setAttributes Reference to the block's setAttributes() function.
 * @return {Fragment} Component HTML Fragment.
 */
const fillInspectorControls = ( attributes, setAttributes ) => {
	// We only add extra controls to the number variation.
	if ( attributes.isConfirmationField || 'number' !== attributes.field ) {
		return;
	}

	// Add min/max options to a number field.
	const { html_attrs } = attributes,
		{ min, max } = html_attrs;

	return (
		<Fragment>
			<TextControl
				label={ __( 'Minimum Value', 'lifterlms' ) }
				help={ __(
					'Specify the minimum allowed value. Leave blank for no minimum.',
					'lifterlms'
				) }
				value={ min }
				type="number"
				onChange={ ( min ) =>
					setAttributes( { html_attrs: { ...html_attrs, min } } )
				}
			/>

			<TextControl
				label={ __( 'Maximum Value', 'lifterlms' ) }
				help={ __(
					'Specify the maximum allowed value. Leave blank for no maximum.',
					'lifterlms'
				) }
				value={ max }
				type="number"
				onChange={ ( max ) =>
					setAttributes( { html_attrs: { ...html_attrs, max } } )
				}
			/>
		</Fragment>
	);
};

/**
 * Block settings
 *
 * @since 2.0.0
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( baseSettings, {
	title: __( 'Text', 'lifterlms' ),
	description: __( 'A simple text input field.', 'lifterlms' ),
	icon: defaultIcon,
	usesContext: [ 'llms/fieldGroup/fieldLayout' ],
	supports: {
		inserter: false,
		llms_field_inspector: {
			customFill: 'fieldTextAdditionalControls',
		},
	},
	variations,
	fillInspectorControls,
} );
