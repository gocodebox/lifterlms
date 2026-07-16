/**
 * BLOCK: llms/form-field-redeem-voucher
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import { ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Internal Deps.
import { settings as baseSettings, postTypes } from './text';
import { getSettingsFromBase } from '../settings';
import icon from "../../../icons/ticket";

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-redeem-voucher';

/**
 * Is this a default or composed field?
 *
 * @type {string}
 */
export const composed = true;

/**
 * Fill the controls slot with additional controls specific to this field.
 *
 * @since 1.6.0
 *
 * @param {Object}   attributes    Block attributes.
 * @param {Function} setAttributes Reference to the block's setAttributes() function.
 * @return {ToggleControl} Component HTML fragment.
 */
const fillInspectorControls = ( attributes, setAttributes ) => {
	const { toggleable, required } = attributes;

	if ( required ) {
		return null;
	}

	return (
		<ToggleControl
			label={ __( 'Toggleable', 'lifterlms' ) }
			checked={ !! toggleable }
			onChange={ () => setAttributes( { toggleable: ! toggleable } ) }
			help={
				!! toggleable
					? __(
							'Field is revealed when the toggle is clicked.',
							'lifterlms'
					  )
					: __( 'Field is always visible.', 'lifterlms' )
			}
		/>
	);
};

/**
 * Block settings
 *
 * @since 1.6.0
 * @since 1.8.0 Updated lodash imports.
 * @since 2.0.0 Add reusable block support.
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase(
	baseSettings,
	{
		title: __( 'Voucher Code Redemption', 'lifterlms' ),
		description: __(
			'Allows user to redeem a voucher code during account registration.',
			'lifterlms'
		),
		icon: icon,
		supports: {
			inserter: true,
			multiple: false,
			llms_field_inspector: {
				id: false,
				name: false,
				storage: false,
				customFill: 'redeemVoucher',
			},
		},
		attributes: {
			id: {
				__default: 'llms_voucher',
			},
			field: {
				__default: 'text',
			},
			label: {
				__default: __( 'Have a voucher?', 'lifterlms' ),
			},
			name: {
				__default: 'llms_voucher',
			},
			placeholder: {
				__default: __( 'Voucher Code', 'lifterlms' ),
			},
			data_store: {
				__default: false,
			},
			data_store_key: {
				__default: false,
			},

			toggleable: {
				__default: false,
			},
		},
		fillInspectorControls,
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
