/**
 * BLOCK: llms/form-field-user-password
 *
 * @since 1.6.0
 * @since 1.8.0 Updated lodash imports.
 * @since 1.12.0 Add data store support.
 * @since 2.0.0 Add reusable block support.
 * @since 2.2.0 Don't define the `match` attribute.
 */

// WP Deps.
import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { select, dispatch } from '@wordpress/data';
import { store as blockEditorStore, RichText } from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';

// Internal Deps.
import { settings as baseSettings, postTypes } from './text';
import { getSettingsFromBase } from '../settings';
import { getSibling } from '../group-data';
import icon from '../../../icons/lock';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-user-password';

export const composed = true;

/**
 * Utility function to merge minlength into an existing `html_attrs` object
 *
 * @since 2.1.0
 *
 * @param {Object} html_attrs Existing object.
 * @param {number} minlength  Min length value.
 * @return {Object} Merged object.
 */
function mergeHtmlAttrs( html_attrs, minlength ) {
	return {
		html_attrs: {
			...html_attrs,
			minlength,
		},
	};
}

/**
 * Update the minlength of the confirmation field
 *
 * @since 2.1.0
 *
 * @param {number} minlength New minlength value.
 * @return {void}
 */
function updateConfirmationFieldMinLength( minlength ) {
	const { getSelectedBlockClientId } = select( blockEditorStore ),
		{ updateBlockAttributes } = dispatch( blockEditorStore ),
		controlBlock = getSelectedBlockClientId(),
		confirmBlock = getSibling( controlBlock ),
		{ attributes, clientId } = confirmBlock,
		{ html_attrs } = attributes;

	updateBlockAttributes( clientId, mergeHtmlAttrs( html_attrs, minlength ) );
}

/**
 * Shows an editable password strength meter in the main block editor area
 *
 * @since 2.0.0
 *
 * @param {Object}   attributes    Block attributes.
 * @param {Function} setAttributes Block attributes setter.
 * @return {Object} Component fragment or `null` if the meter is not enabled.
 */
const fillEditAfter = ( attributes, setAttributes ) => {
	const { meter, meter_description } = attributes;

	if ( ! meter ) {
		return null;
	}

	return (
		<Fragment>
			<div className="llms-pwd-meter">
				<div>{ __( 'Very Weak', 'lifterlms' ) }</div>
			</div>
			<RichText
				style={ { marginTop: 0 } }
				tagName="p"
				value={ meter_description }
				onChange={ ( meter_description ) =>
					setAttributes( { meter_description } )
				}
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
				aria-label={
					meter_description
						? __(
								'Password strength meter description',
								'lifterlms'
						  )
						: __(
								'Empty Password strength meter description; start writing to add a label'
						  )
				}
				placeholder={ __(
					'Enter a description for the password strength meter',
					'lifterlms'
				) }
			/>
		</Fragment>
	);
};

/**
 * Fill the controls slot with additional controls specific to this field.
 *
 * @since 2.0.0
 * @since 2.1.0 Update the minlength value of confirmation field when the control field's value changes.
 *
 * @param {Object} attributes Block attributes.
 * @param {Function} setAttributes Reference to the block's setAttributes() function.
 * @return {Fragment} Component HTML Fragment.
 */
const fillInspectorControls = ( attributes, setAttributes ) => {
	const {
			isConfirmationControlField,
			isConfirmationField,
			meter,
			min_strength,
			html_attrs,
		} = attributes,
		{ minlength } = html_attrs;

	if ( isConfirmationField ) {
		return;
	}

	/**
	 * Set the minlength on the field and match it to the confirmation field (if necessary)
	 *
	 * @since 2.1.0
	 *
	 * @param {number} minlength New minlength value.
	 * @return {void}
	 */
	const setMinLength = ( minlength ) => {
		setAttributes( mergeHtmlAttrs( html_attrs, minlength ) );
		if ( isConfirmationControlField ) {
			updateConfirmationFieldMinLength( minlength );
		}
	};

	return (
		<Fragment>
			<ToggleControl
				label={ __( 'Password strength meter', 'lifterlms' ) }
				help={
					meter
						? __(
								'Password strength meter is enabled.',
								'lifterlms'
						  )
						: __(
								'Password strength meter is disabled.',
								'lifterlms'
						  )
				}
				checked={ meter }
				onChange={ () => setAttributes( { meter: ! meter } ) }
			/>

			{ meter && (
				<SelectControl
					label={ __( 'Minimum Password Strength', 'lifterlms' ) }
					value={ min_strength }
					onChange={ ( min_strength ) =>
						setAttributes( { min_strength } )
					}
					options={ [
						{ value: 'strong', label: __( 'Strong', 'lifterlms' ) },
						{ value: 'medium', label: __( 'Medium', 'lifterlms' ) },
						{ value: 'weak', label: __( 'Weak', 'lifterlms' ) },
					] }
				/>
			) }

			<TextControl
				label={ __( 'Minimum Password Length', 'lifterlms' ) }
				value={ minlength }
				type="number"
				min="6"
				onChange={ ( val ) => setMinLength( val * 1 ) }
			/>
		</Fragment>
	);
};

export const settings = getSettingsFromBase(
	baseSettings,
	{
		title: __( 'User Password', 'lifterlms' ),
		description: __(
			"A special field used to collect a user's account password.",
			'lifterlms'
		),
		icon: icon,
		supports: {
			inserter: true,
			multiple: false, // Can only have a single user password field.
			llms_field_inspector: {
				id: false,
				name: false,
				required: false,
				storage: false,
				customFill: 'userPassAdditionalControls',
			},
			llms_edit_fill: {
				after: 'userPassStrengthMeter',
			},
		},
		attributes: {
			// Defaults.
			id: {
				__default: 'password',
			},
			field: {
				__default: 'password',
			},
			label: {
				__default: __( 'Password', 'lifterlms' ),
			},
			name: {
				__default: 'password',
			},
			required: {
				__default: true,
			},
			data_store: {
				__default: 'users',
			},
			data_store_key: {
				__default: 'user_pass',
			},

			// Extra attributes.
			meter: {
				type: 'boolean',
				__default: true,
			},
			meter_description: {
				type: 'string',
				__default: __(
					'A strong password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.',
					'lifterlms'
				),
			},
			min_strength: {
				type: 'string',
				__default: 'strong',
			},
			html_attrs: {
				__default: {
					minlength: 8,
				},
			},
		},
		fillEditAfter,
		fillInspectorControls,
	},
	[ 'transforms', 'variations' ]
);

export { postTypes };
