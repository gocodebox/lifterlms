/**
 * Inspector settings component for managing a field's options
 *
 * Used on Radios, Checkboxes, and Selects
 *
 * @since 1.6.0
 * @version 2.1.0
 */

// WP Deps.
import {
	Button,
	BaseControl,
	Dashicon,
	TextControl,
	Tooltip,
	RadioControl,
	CheckboxControl,
} from '@wordpress/components';
import { Component } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

// External Deps.
import { v4 as uuid } from 'uuid';

// Internal Deps.
import { SortableList, SortableDragHandle } from '../../components';

/**
 * Render a single option within the sortable list.
 *
 * @since 2.1.0
 *
 * @param {Object}   props
 * @param {string}   props.id          Option UUID.
 * @param {Object}   props.item        Full option object.
 * @param {Object}   props.extraProps  Object of extra properties.
 * @param {Object}   props.manageState State management object used to CRUD options.
 * @param {Object}   props.listeners   UI event listeners (used for the drag handle).
 * @param {Function} props.setNodeRef  Function used to set the reference node (for the drag handle).
 * @return {Object} Component.
 */
function ListItem( {
	id,
	item,
	extraProps,
	manageState,
	listeners,
	setNodeRef,
} ) {
	const { showKeys, type, optionCount } = extraProps,
		{ updateItem, deleteItem } = manageState;

	const CheckboxOptionControl = () => (
		<CheckboxControl
			className="llms-field-opt-default"
			checked={ 'yes' === item.default }
			onChange={ ( val ) => {
				updateItem( id, {
					...item,
					default: true === val ? 'yes' : 'no',
				} );
			} }
			tabIndex="-1"
		/>
	);

	const RadioOptionControl = () => (
		<RadioControl
			className="llms-field-opt-default"
			selected={ item.default }
			onChange={ ( val ) => {
				updateItem( id, {
					...item,
					default: val,
				} );
			} }
			options={ [ { label: '', value: 'yes' } ] }
			tabIndex="-1"
		/>
	);

	return (
		<>
			<SortableDragHandle
				label={ __( 'Reorder option', 'lifterlms' ) }
				setNodeRef={ setNodeRef }
				listeners={ listeners }
			/>
			<Tooltip text={ __( 'Make default', 'lifterlms' ) }>
				<div className="llms-field-opt-default-wrap">
					{ 'checkbox' === type && <CheckboxOptionControl /> }
					{ 'checkbox' !== type && <RadioOptionControl /> }
				</div>
			</Tooltip>
			<div className="llms-field-opt-text-wrap">
				<TextControl
					className="llms-field-opt-text"
					value={ item.text }
					onChange={ ( text ) => updateItem( id, { ...item, text } ) }
					placeholder={ __( 'Option label', 'lifterlms' ) }
				/>
				{ showKeys && (
					<div className="llms-field-opt-db-key">
						<Tooltip
							text={ __( 'Database key value', 'lifterlms' ) }
						>
							<Dashicon icon="database" />
						</Tooltip>
						<TextControl
							className="llms-field-opt-text "
							value={ item.key }
							onChange={ ( key ) =>
								updateItem( id, { ...item, key } )
							}
							placeholder={ __(
								'Database key value',
								'lifterlms'
							) }
						/>
					</div>
				) }
			</div>
			{ optionCount > 1 && (
				<div className="llms-del-field-opt-wrap">
					<Button
						style={ { flex: 1 } }
						icon="trash"
						label={ __( 'Delete Option', 'lifterlms' ) }
						onClick={ () => deleteItem( id ) }
						tabIndex="-1"
						isSmall
					/>
				</div>
			) }
		</>
	);
}

/**
 * Inspector "Options" component
 *
 * Used to CRUD options for select, checkboxes, and radio field blocks.
 *
 * @since 1.6.0
 */
export default class InspectorFieldOptions extends Component {
	/**
	 * Constructor
	 *
	 * @since 1.6.0
	 * @since 2.1.0 Rename state.items to state.options & load a unique ID into
	 *               each option object for compatibility with the SortableList component.
	 *
	 * @return {void}
	 */
	constructor() {
		super( ...arguments );

		const { options } = this.props.attributes;
		this.state = {
			showKeys: false,
			// Add an ID to each option for dndkit to work.
			options: options.map( ( opt ) => {
				return {
					...opt,
					id: uuid(),
				};
			} ),
		};
	}

	/**
	 * Add a new empty option with default values
	 *
	 * @since 2.1.0
	 *
	 * @return {void}
	 */
	addOption = () => {
		const { options } = this.state,
			{ length } = options;

		const [ key, optionNumber ] = this.getUniqueKeyNumber( length + 1 ),
			option = {
				key,
				id: uuid(),
				// Translators: %d = Option index in the list of options.
				text: sprintf( __( 'Option %d', 'lifterlms' ), optionNumber ),
				default: 'no',
			};

		options.push( option );

		this.updateOptions( options );
	};

	/**
	 * Retrieve the manageState object passed to the SortableList component
	 *
	 * @since 2.1.0
	 *
	 * @return {Object} An options state management object.
	 */
	getManageState = () => ( {
		createItem: this.addOption,
		deleteItem: this.removeOption,
		updateItem: this.updateOption,
		updateItems: this.updateOptions,
	} );

	/**
	 * Retrieves a unique key used when creating a new option
	 *
	 * When creating a new option we start with option_{options.length + 1} and
	 * keep counting up until we've found a unique key. The number used to generate
	 * the key is also returned to be used when generating the new default label.
	 *
	 * @since 2.1.0
	 *
	 * @param {number} optionNum A number.
	 * @return {Array} Array containing a new option key (string) and the number used to generate the key.
	 */
	getUniqueKeyNumber = ( optionNum ) => {
		/**
		 * Test a key against the existing option list for uniqueness
		 *
		 * @since 2.1.0
		 *
		 * @param {string} newKey Proposed new key to use.
		 * @return {boolean} Returns `true` when newKey is unique and `false` otherwise.
		 */
		const isUnique = ( newKey ) => {
			const { options } = this.state;
			return -1 === options.findIndex( ( { key } ) => key === newKey );
		};

		// Translators: %d = Option index in the list of options.
		let key = sprintf( __( 'option_%d', 'lifterlms' ), optionNum );

		// Continue until we get a unique key.
		while ( ! isUnique( key ) ) {
			[ key, optionNum ] = this.getUniqueKeyNumber( ++optionNum );
		}

		return [ key, optionNum ];
	};

	/**
	 * Update a single options
	 *
	 * @since 2.1.0
	 *
	 * @param {string} id   Option ID.
	 * @param {Object} data Option properties to be updated.
	 * @return {void}
	 */
	updateOption = ( id, data ) => {
		const { options } = this.state,
			{ field } = this.props.attributes,
			newDefault = 'yes' === data.default && 'checkbox' !== field,
			newOptions = options.map( ( option ) => {
				if ( id === option.id ) {
					option = {
						...option,
						...data,
					};
				} else if ( newDefault ) {
					option = {
						...option,
						default: 'no',
					};
				}

				return option;
			} );

		this.updateOptions( newOptions );
	};

	/**
	 * Update options
	 *
	 * @since 1.6.0
	 * @since 2.1.0 Refactored.
	 *
	 * @param {Object[]} options Options array.
	 * @return {void}
	 */
	updateOptions = ( options ) => {
		const { setAttributes } = this.props;

		// Set to the state.
		this.setState( { options } );

		// Pass back to the block (and remove the ID so it isn't stored.
		setAttributes( {
			options: options.map( ( { id, ...rest } ) => rest ), // eslint-disable-line no-unused-vars
		} );
	};

	removeOption = ( deleteId ) => {
		const { options } = this.state,
			{ field } = this.props.attributes;

		let newDefault = null;

		// If the deleting option was the default force default back to the first item in the list.
		if ( 'checkbox' !== field ) {
			const deletingOption = options.find(
				( { id } ) => id === deleteId
			);
			newDefault = 'yes' === deletingOption.default;
		}

		this.updateOptions(
			options
				.filter( ( { id } ) => id !== deleteId )
				.map( ( opt, index ) => {
					if ( newDefault && 0 === index ) {
						opt = {
							...opt,
							default: 'yes',
						};
					}
					return opt;
				} )
		);
	};

	/**
	 * Component render
	 *
	 * @since 1.6.0
	 * @since 2.1.0 Refactored to utilize @dndkit in favor of react-sortable-hoc.
	 *
	 * @return {BaseControl} Component HTML fragment.
	 */
	render() {
		const { props, state } = this,
			{ attributes } = props,
			{ id, field } = attributes,
			{ options, showKeys } = state,
			{ length: optionCount } = options;

		return (
			<BaseControl id={ id } label={ __( 'Options', 'lifterlms' ) }>
				<SortableList
					ListItem={ ListItem }
					items={ options }
					itemClassName="llms-field-option"
					manageState={ this.getManageState() }
					extraProps={ { type: field, showKeys, optionCount } }
				/>

				<div className="llms-field-options--footer">
					<Button isSecondary onClick={ this.addOption }>
						{ __( 'Add option', 'lifterlms' ) }
					</Button>

					<Button
						isTertiary
						onClick={ () =>
							this.setState( { showKeys: ! showKeys } )
						}
					>
						{ showKeys
							? __( 'Hide keys', 'lifterlms' )
							: __( 'Show keys', 'lifterlms' ) }
					</Button>
				</div>
			</BaseControl>
		);
	}
}
