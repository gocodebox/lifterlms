/**
 * BLOCK: llms/form-field-textarea
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { createBlock } from '@wordpress/blocks';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './text';
import { getSettingsFromBase } from '../settings';
import icon from '../../../icons/paragraph';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-textarea';

/**
 * Is this a default or composed field?
 *
 * @type {string}
 */
export const composed = false;

/**
 * Fill the controls slot with additional controls specific to this field.
 *
 * @since 1.12.0
 * @since 2.0.0 Update to use `html_attrs.rows` in favor of `attributes.rows`.
 *
 * @param {Object}   attributes    Block attributes.
 * @param {Function} setAttributes Reference to the block's setAttributes() function.
 * @return {TextControl} Component html fragment.
 */
const fillInspectorControls = ( attributes, setAttributes ) => {
	const { html_attrs } = attributes,
		{ rows } = html_attrs;

	return (
		<TextControl
			label={ __( 'Rows', 'lifterlms' ) }
			help={ __(
				'Specify the number of text rows for the textarea input.',
				'lifterlms'
			) }
			value={ rows }
			type="number"
			onChange={ ( rows ) =>
				setAttributes( { html_attrs: { ...html_attrs, rows } } )
			}
			min="2"
			step="1"
		/>
	);
};

/**
 * Block settings
 *
 * @since 1.6.0
 * @since 2.0.0 Refactor for 2.0.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase(
	baseSettings,
	{
		title: __( 'Textarea', 'lifterlms' ),
		description: __(
			'A text field accepting multiple lines of user information.',
			'lifterlms'
		),
		icon: icon,
		category: 'llms-custom-fields',
		supports: {
			inserter: true,
			llms_field_inspector: {
				customFill: 'fieldTextarea',
			},
		},
		attributes: {
			field: {
				__default: 'textarea',
			},
			html_attrs: {
				__default: {
					rows: 4,
				},
			},
		},
		fillInspectorControls,
		transforms: {
			from: [
				{
					type: 'block',
					blocks: [ 'llms/form-field-text' ],
					transform: ( attributes ) =>
						createBlock( name, {
							...attributes,
							html_attrs: {
								...attributes.html_attrs,
								rows: 4,
							},
							field: 'textarea',
						} ),
				},
			],
			to: [
				{
					type: 'block',
					blocks: [ 'llms/form-field-text' ],
					transform: ( attributes ) =>
						createBlock( 'llms/form-field-text', {
							...attributes,
							field: 'text',
						} ),
				},
			],
		},
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
