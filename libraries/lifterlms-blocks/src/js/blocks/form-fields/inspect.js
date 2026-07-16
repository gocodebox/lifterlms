/**
 * Inspector settings for the Course Information Block.
 *
 * @since 1.6.0
 * @version 2.0.0
 */

// WP Deps.
import {
	InspectorControls,
	InspectorAdvancedControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	Slot,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { select, dispatch } from '@wordpress/data';
import { Component, Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import {
	switchToBlockType,
	getPossibleBlockTransformations,
	getBlockType,
} from '@wordpress/blocks';
import { store as editorStore } from '@wordpress/editor';

// Internal Deps.
import InspectorFieldOptions from './inspect-field-options';
import getBlocksFlat from '../../util/get-blocks-flat';
import { store as fieldsStore } from '../../data/fields';
import { isUnique } from './checks';

/**
 * Block Inspector for Field blocks
 *
 * @since 1.6.0
 */
export default class Inspector extends Component {
	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 *
	 * @param {Object} props Component properties.
	 * @return {void}
	 */
	constructor( props ) {
		super( props );
		this.state = {
			validationErrors: {},
		};
	}

	/**
	 * Retrieve a specific block by it's ID attribute.
	 *
	 * @since 1.6.0
	 *
	 * @param {string} fieldId The field ID.
	 * @return {Object | boolean} A block object or false if not found.
	 */
	getBlockByFieldId( fieldId ) {
		const blocks = getBlocksFlat().filter(
			( block ) => fieldId === block.attributes.id
		);
		if ( blocks ) {
			return blocks[ 0 ];
		}

		return false;
	}

	getColumnsOptions( context ) {
		let options = [];

		// Full width is allowed inside stacked groups or when the field is being used solo.
		if (
			! context ||
			( context &&
				( ! context[ 'llms/fieldGroup/fieldLayout' ] ||
					'stacked' === context[ 'llms/fieldGroup/fieldLayout' ] ) )
		) {
			options.push( {
				value: 12,
				label: __( '100%', 'lifterlms' ),
			} );
		}

		options = options.concat( [
			{
				value: 9,
				label: __( '75%', 'lifterlms' ),
			},
			{
				value: 8,
				label: __( '66.66%', 'lifterlms' ),
			},
			{
				value: 6,
				label: __( '50%', 'lifterlms' ),
			},
			{
				value: 4,
				label: __( '33.33%', 'lifterlms' ),
			},
			{
				value: 3,
				label: __( '25%', 'lifterlms' ),
			},
		] );

		return options;
	}

	/**
	 * Retrieve an array of objects to be used in the Matching Field select control.
	 *
	 * @since 1.6.0
	 *
	 * @return {Object[]} Array of objects for the select control options list.
	 */
	getMatchFieldOptions() {
		const { clientId, name } = this.props;

		const opts = [
			{
				value: '',
				label: __( 'Select a field', 'lifterlms' ),
			},
		];

		return opts.concat(
			getBlocksFlat()
				.filter(
					( block ) =>
						block.clientId !== clientId &&
						-1 !== name.indexOf( 'llms/form-field-' )
				)
				.map( ( block ) => {
					const { id, label } = block.attributes;

					return {
						value: id,
						label: `${ label } (${ id })`,
					};
				} )
		);
	}

	/**
	 * Determine if the block has inspector support
	 *
	 * The block must have support for at least one inspector control.
	 *
	 * @since 1.6.0
	 *
	 * @return {boolean} Returns `true` if the block has inspector support.
	 */
	hasInspectorSupport() {
		const { inspectorSupports } = this.props;
		return (
			Object.keys( inspectorSupports ).filter(
				( key ) => inspectorSupports[ key ]
			).length >= 1
		);
	}

	/**
	 * Determine if the block has inspector support for a specific control
	 *
	 * @since 1.6.0
	 *
	 * @param {string} control Control ID.
	 * @return {boolean} Returns `true` if the block has support for inspector controls.
	 */
	hasInspectorControlSupport( control ) {
		const { inspectorSupports } = this.props;
		return inspectorSupports[ control ];
	}

	canTransformToGroup( block ) {
		// If the block is already within a confirmation block then we can't add a confirmation group.
		if ( ! block || this.isInAConfirmGroup( block ) ) {
			return false;
		}
		return getPossibleBlockTransformations( [ block ] )
			.map( ( { name } ) => name )
			.includes( 'llms/form-field-confirm-group' );
	}

	isInAConfirmGroup( block ) {
		return this.getParentGroupClientId( block ) ? true : false;
	}

	getParentGroupClientId( block ) {
		if ( ! block ) {
			return false;
		}

		const { clientId } = block,
			{ getBlockParentsByBlockName } = select( blockEditorStore ),
			parents = getBlockParentsByBlockName(
				clientId,
				'llms/form-field-confirm-group'
			);

		return parents.length ? parents[ 0 ] : false;
	}

	getBlockSiblings( block ) {
		const parentClientId = this.getParentGroupClientId( block );

		if ( ! parentClientId ) {
			return [];
		}

		const { getBlock } = select( blockEditorStore ),
			parentBlock = getBlock( parentClientId );

		return parentBlock.innerBlocks.filter(
			( { clientId } ) => clientId !== block.clientId
		);
	}

	/**
	 * Retrieve an error message for use in error notices thrown by updateValueWithValidation()
	 *
	 * @since 2.0.0
	 *
	 * @param {string} key Field key.
	 * @return {string} Error message for the given validation issue.
	 */
	getValidationErrText( key ) {
		let msg = '';

		const val = this.state.validationErrors[ key ];

		if ( ! val ) {
			msg = __( 'The value cannot be blank.', 'lifterlms' );
		} else if ( this.containsInvalidCharacters( val ) ) {
			// Translators: %s = user-submitted value.
			msg = __(
				'The value "%s" contains invalid characters. Only letters, numbers, underscores, and hyphens are allowed.',
				'lifterlms'
			);
		} else {
			switch ( key ) {
				case 'data_store_key':
					// Translators: %s = user-submitted value.
					msg = __(
						'The user meta key "%s" is not unique. Please choose a unique value.',
						'lifterlms'
					);
					break;

				case 'id':
					// Translators: %s = user-submitted value.
					msg = __(
						'The ID "%s" is not unique. Please choose a unique field ID.',
						'lifterlms'
					);
					break;

				case 'name':
					// Translators: %s = user-submitted value.
					msg = __(
						'The name "%s" is not unique. Please choose a globally unique field name.',
						'lifterlms'
					);
					break;

				default:
					// Translators: %s = user-submitted value.
					msg = __(
						'The chosen value "%s" is invalid.',
						'lifterlms'
					);
			}
		}

		return sprintf( msg, val );
	}

	/**
	 * Determine if invalid characters are present in an attribute
	 *
	 * This validates the data_store_key, name, and ID attributes only.
	 *
	 * This is a pretty accurate version of characters allowed in HTML name/id attributes
	 * on the HTML 4 spec. HTML 5 allows a tremendous number of characters but I'd rather
	 * not validate strictly against that because I want to ensure we can use the same
	 * characters for usermeta keys.
	 *
	 * For future Thomas who will have forgetten how to read the regex: allows alphanumeric chars, hyphens, and underscores.
	 *
	 * @since 2.0.0
	 *
	 * @param {string} val Attribute value.
	 * @return {boolean} Returns `true` if the value contains invalid chars and `false` otherwise.
	 */
	containsInvalidCharacters( val ) {
		return val.match( /[^A-Za-z0-9\-\_]/g ) ? true : false;
	}

	/**
	 * Sets (or remove) a validation error for given key
	 *
	 * Passing a null value removes an existing validation error.
	 *
	 * @since 2.0.0
	 *
	 * @param {string}  key Attribute key.
	 * @param {?string} val Content of the invalid value, an empty string, or `null` to remove an existing validation error.
	 * @return {void}
	 */
	setValidationError( key, val = null ) {
		this.setState( {
			validationErrors: {
				...this.state.validationErrors,
				[ key ]: val,
			},
		} );
	}

	/**
	 * Determines if a block attribute has a validation error.
	 *
	 * @since 2.0.0
	 *
	 * @param {string} key Attribute key.
	 * @return {boolean} Returns `true` when a validation error is present and `false`` if not.
	 */
	hasValidationErr( key ) {
		return 'string' === typeof this.state.validationErrors[ key ];
	}

	/**
	 * Component to render a TextControl with built-in data validation and error display
	 *
	 * @since 2.0.0
	 *
	 * @param {Object}    props
	 * @param {Inspector} props.parent  Reference to the parent Inspector component.
	 * @param {string}    props.attrKey Attribute key name for the control input.
	 * @param {string}    props.label   Label property passed to TextControl.
	 * @param {string}    props.help    Help property passed to TextControl.
	 * @return {Object} Component HTML fragment.
	 */
	ValidatedTextControl( { parent, attrKey, label, help } ) {
		const { attributes } = parent.props,
			value = attributes[ attrKey ],
			hasError = parent.hasValidationErr( attrKey ),
			className = hasError ? 'llms-invalid-control' : '';

		return (
			<div className={ className }>
				<TextControl
					label={ label }
					help={ help }
					value={ value }
					onChange={ ( newValue ) =>
						parent.updateValueWithValidation(
							attrKey,
							newValue,
							'name' === attrKey ? 'global' : 'local'
						)
					}
				/>
				{ hasError && (
					<p className="llms-invalid-control--msg">
						{ parent.getValidationErrText( attrKey ) }
					</p>
				) }
			</div>
		);
	}

	/**
	 * Set attributes with built-in validation
	 *
	 * Validates fields for uniqueness against field data in the llms/user-info-fields data
	 * store. Prevents storage of invalid data and throws an error notice when validation issues
	 * are encountered.
	 *
	 * @since 2.0.0
	 *
	 * @param {string} key      Field key.
	 * @param {string} newValue Field value.
	 * @param {string} context  Validation context. Accepts "global" to validate against all known fields
	 *                          or "local" to validate against loaded fields in the current form.
	 * @return {void}
	 */
	updateValueWithValidation( key, newValue, context = 'local' ) {
		const { clientId, attributes, setAttributes } = this.props,
			{ name } = attributes,
			currentValue = attributes[ key ],
			{ editField, renameField } = dispatch( fieldsStore ),
			{ lockPostSaving, unlockPostSaving } = dispatch( editorStore ),
			errorId = `llms-${ key }-validation-err-${ clientId }-${ name }`;

		// No change.
		if ( newValue === currentValue ) {
			return;
		}

		const isEmpty = ! newValue,
			hasInvalidChars = this.containsInvalidCharacters( newValue ),
			hasUniqueVal = isUnique( newValue, key, context ),
			isValid = ! isEmpty && ! hasInvalidChars && hasUniqueVal;

		this.setValidationError( key );
		unlockPostSaving( errorId );
		if ( ! isValid ) {
			this.setValidationError( key, newValue );

			/**
			 * When a field is "emptied" we'll throw an error but don't save the empty value.
			 * This results in an error being displayed and the initial (pre deletion) value being
			 * restored. If someone highlights the whole field and deletes it the whole field name
			 * stays and an error displays. If the backspace one at a time the first character will
			 * remain.
			 *
			 * This isn't ideal but I can't find a way to let them completely empty the data
			 * without rewriting the duplicate detection logic in the main `edit()` component.
			 */
			if ( isEmpty ) {
				return;
			}
			lockPostSaving( errorId );
		}

		if ( 'name' === key ) {
			// Renaming a duplicate will cause overwrites of the original field.
			if ( ! hasUniqueVal ) {
				newValue = newValue.slice( 0, -1 );
			}
			renameField( attributes.name, newValue );
		} else {
			editField( attributes.name, { [ key ]: newValue } );
		}

		setAttributes( {
			[ key ]: newValue,
		} );
	}

	/**
	 * Render the Block Inspector
	 *
	 * @since 1.6.0
	 * @since 1.12.0 Add inspector controls for data store mapping.
	 *
	 * @return {Fragment} Component HTML fragment.
	 */
	render() {
		// Return early if there's no inspector options to display.
		if ( ! this.hasInspectorSupport() ) {
			return '';
		}

		const { attributes, setAttributes, clientId, context } = this.props,
			block = select( blockEditorStore ).getBlock( clientId ),
			{
				required,
				placeholder,
				columns,
				isConfirmationField,
				isConfirmationControlField,
			} = attributes;

		const canTransformToGroup = this.canTransformToGroup( block ),
			isInAConfirmGroup = this.isInAConfirmGroup( block );

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody>
						{ ! isConfirmationField &&
							this.hasInspectorControlSupport( 'required' ) && (
								<ToggleControl
									className="llms-required-field-toggle"
									label={ __( 'Required', 'lifterlms' ) }
									checked={ !! required }
									onChange={ () =>
										setAttributes( {
											required: ! required,
										} )
									}
									help={
										!! required
											? __(
													'Field is required.',
													'lifterlms'
											  )
											: __(
													'Field is optional.',
													'lifterlms'
											  )
									}
								/>
							) }

						<SelectControl
							className="llms-field-width-select"
							label={ __( 'Field Width', 'lifterlms' ) }
							onChange={ ( columns ) => {
								columns = parseInt( columns, 10 );
								setAttributes( { columns } );

								const sibling = this.getBlockSiblings( block );

								if (
									sibling.length &&
									columns + sibling[ 0 ].attributes.columns >
										12
								) {
									const { updateBlockAttributes } = dispatch(
										blockEditorStore
									);
									updateBlockAttributes(
										sibling[ 0 ].clientId,
										{
											columns: 12 - columns,
										}
									);
								}
							} }
							help={ __(
								'Determines the width of the form field.',
								'lifterlms'
							) }
							value={ columns }
							options={ this.getColumnsOptions( context ) }
						/>

						{ this.hasInspectorControlSupport( 'options' ) && (
							<InspectorFieldOptions
								attributes={ attributes }
								setAttributes={ setAttributes }
							/>
						) }

						{ this.hasInspectorControlSupport( 'placeholder' ) && (
							<TextControl
								label={ __( 'Placeholder', 'lifterlms' ) }
								value={ placeholder }
								onChange={ ( placeholder ) =>
									setAttributes( { placeholder } )
								}
								help={ __(
									'Displays a placeholder option as the selected instead of a default value.',
									'lifterlms'
								) }
							/>
						) }

						{ ( canTransformToGroup ||
							( isConfirmationControlField &&
								isInAConfirmGroup ) ) && (
							<ToggleControl
								className="llms-confirmation-field-toggle"
								label={ __(
									'Confirmation Field',
									'lifterlms'
								) }
								checked={ isInAConfirmGroup }
								onChange={ () => {
									const {
											replaceBlock,
											selectBlock,
										} = dispatch( blockEditorStore ),
										{
											findControllerBlockIndex,
										} = getBlockType(
											'llms/form-field-confirm-group'
										),
										{ getBlock } = select(
											blockEditorStore
										);

									let replaceClientId = clientId,
										newBlockType =
											'llms/form-field-confirm-group',
										blockToSwitch = block,
										selectBlockId = null;

									if ( isInAConfirmGroup ) {
										replaceClientId = this.getParentGroupClientId(
											block
										);
										blockToSwitch = getBlock(
											replaceClientId
										);
										newBlockType = block.name;
									}

									const switched = switchToBlockType(
										blockToSwitch,
										newBlockType
									);

									replaceBlock( replaceClientId, switched );

									if ( ! isInAConfirmGroup ) {
										const { innerBlocks } = switched[ 0 ],
											controllerIndex = findControllerBlockIndex(
												innerBlocks
											);

										selectBlockId =
											innerBlocks[ controllerIndex ]
												.clientId;
									} else {
										selectBlockId = switched[ 0 ].clientId;
									}

									selectBlock( selectBlockId );
								} }
								help={
									isInAConfirmGroup
										? __(
												'A Confirmation field is active.',
												'lifterlms'
										  )
										: __(
												'No confirmation field.',
												'lifterlms'
										  )
								}
							/>
						) }

						{ this.hasInspectorControlSupport( 'customFill' ) && (
							<Slot
								name={ `llmsInspectorControlsFill.${ this.hasInspectorControlSupport(
									'customFill'
								) }.${ clientId }` }
							/>
						) }
					</PanelBody>

					{ ! isConfirmationField &&
						this.hasInspectorControlSupport( 'storage' ) && (
							<PanelBody
								title={ __( 'Data Storage', 'lifterlms' ) }
							>
								<this.ValidatedTextControl
									parent={ this }
									attrKey="data_store_key"
									label={ __( 'Usermeta Key', 'lifterlms' ) }
									help={ __(
										'Database field key name. Only accepts alphanumeric characters, hyphens, and underscores.',
										'lifterlms'
									) }
								/>
							</PanelBody>
						) }
				</InspectorControls>

				<InspectorAdvancedControls>
					{ ! isConfirmationField &&
						this.hasInspectorControlSupport( 'name' ) && (
							<this.ValidatedTextControl
								parent={ this }
								attrKey="name"
								label={ __( 'Field Name', 'lifterlms' ) }
								help={ __(
									"The field's HTML name attribute.",
									'lifterlms'
								) }
							/>
						) }

					{ ! isConfirmationField &&
						this.hasInspectorControlSupport( 'id' ) && (
							<this.ValidatedTextControl
								parent={ this }
								attrKey="id"
								label={ __( 'Field ID', 'lifterlms' ) }
								help={ __(
									"The field's HTML id attribute.",
									'lifterlms'
								) }
							/>
						) }
				</InspectorAdvancedControls>
			</Fragment>
		);
	}
}
