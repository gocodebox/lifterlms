/**
 * BLOCK: llms/form-field-confirm-group
 *
 * @since 2.0.0
 * @version 2.2.0
 */

// WP Deps.
import { createBlock } from '@wordpress/blocks';
import { dispatch, select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __, isRTL, sprintf } from '@wordpress/i18n';

// Internal Deps.
import {
	default as getDefaultSettings,
	getSettingsFromBase,
	getDefaultPostTypes,
} from '../settings';
import icon from "../../../icons/grip-lines";
import { store as fieldsStore } from '../../../data/fields';

/**
 * Block Name
 *
 * @type {string}
 */
export const name = 'llms/form-field-confirm-group';

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
 * Retrieve block attributes for a controller block
 *
 * @since 2.0.0
 * @since 2.2.0 Setup match attribute.
 *
 * @param {Object} attributes Existing attributes to merge defaults into.
 * @return {Object} Updated block attributes.
 */
function getControllerBlockAttrs( attributes = {} ) {
	const { id } = attributes;
	let { match } = attributes;
	if ( id && ! match ) {
		match = `${ id }_confirm`;
	}

	return {
		...attributes,
		match,
		columns: 6,
		last_column: false,
		isConfirmationControlField: true,
		llms_visibility: 'off',
	};
}

/**
 * Retrieve block attributes for a controlled (confirmation) block
 *
 * @since 2.0.0
 * @since 2.2.0 Setup id, name, and match attributes.
 *
 * @param {Object} attributes Existing attributes to merge defaults into.
 * @return {Object} Updated block attributes.
 */
function getControlledBlockAttrs( attributes = {} ) {
	let { id, name, match } = attributes;
	if ( id && ! match ) {
		match = id;
		id = `${ id }_confirm`;
		name = `${ name }_confirm`;
	}

	return {
		...attributes,
		id,
		name,
		match,
		label: attributes.label
			? // Translators: %s label of the controller field.
			  sprintf( __( 'Confirm %s', 'lifterlms' ), attributes.label )
			: '',
		columns: 6,
		last_column: true,
		data_store: false,
		data_store_key: false,
		isConfirmationField: true,
		llms_visibility: 'off',
	};
}

/**
 * Revert the confirmation group to a single field
 *
 * Replaces the group block with the controller block (first inner child) of the group.
 *
 * @since 2.0.0
 *
 * @param {string} clientId Client ID of the group block.
 * @return {void}
 */
function revertToSingle( clientId ) {
	const { getBlock } = select( blockEditorStore ),
		{ replaceBlock } = dispatch( blockEditorStore ),
		block = getBlock( clientId ),
		{ innerBlocks } = block,
		{ llms_visibility } = block.attributes,
		{ name, attributes } = innerBlocks[
			findControllerBlockIndex( innerBlocks )
		];

	doFieldUnload( attributes.name );

	replaceBlock(
		clientId,
		createBlock( name, {
			...attributes,
			columns: 12,
			last_column: true,
			isConfirmationControlField: false,
			match: '',
			llms_visibility,
		} )
	);
}

/**
 * Unloads field immediately prior to running block transforms
 *
 * During transformations fields will show up as duplicates if we rely
 * on the blocks-watcher to unload fields, to combat this
 * we'll unload the fields manually before the transformations are
 * run.
 *
 * @since 2.0.0
 *
 * @param {string} name Field name attribute.
 * @return {void}
 */
function doFieldUnload( name ) {
	const { unloadField } = dispatch( fieldsStore );
	unloadField( name );
}

/**
 * Allowed blocks list.
 *
 * @type {string[]}
 */
const allowed = [
	'llms/form-field-text',
	'llms/form-field-user-email',
	'llms/form-field-user-login',
	'llms/form-field-user-password',
];

/**
 * Block transforms.
 *
 * @type {Object}
 */
const transforms = {
	from: [],
	to: [],
};

/**
 * Create a block transform for each of the allowed blocks
 *
 * When creating a confirm group from a single block the first child
 * should be a direct copy of the block itself (EG: user-email -> user->email).
 *
 * The second child should be a simple text block with the type of the first block
 * (eg: user-email to text with a field type email).
 *
 * Therefore each transform is identical except for the block type of the first child
 * block.
 *
 * @since 2.0.0
 *
 * @param {string} blockName Name of the block being transformed into a group.
 * @return {void}
 */
allowed.forEach( ( blockName ) => {
	/**
	 * Transform a single block to a confirmation group
	 *
	 * @since 2.0.0
	 * @since 2.2.0 Determine the attributes of both confirm group children before creating any blocks.
	 */
	transforms.from.push( {
		type: 'block',
		blocks: [ blockName ],
		transform: ( attributes ) => {
			doFieldUnload( attributes.name );

			const { llms_visibility } = attributes,
				controllerAttrs = getControllerBlockAttrs( attributes ),
				controlledAttrs = getControlledBlockAttrs( attributes );

			const children = [
				createBlock( blockName, controllerAttrs ),
				createBlock( 'llms/form-field-text', controlledAttrs ),
			];

			return createBlock(
				name,
				{ llms_visibility },
				isRTL() ? children.reverse() : children
			);
		},
	} );

	/**
	 * Transform a confirm group to a single block
	 *
	 * @since 2.0.0
	 */
	transforms.to.push( {
		type: 'block',
		blocks: [ blockName ],
		isMatch: () => {
			const { getSelectedBlock, getBlockParentsByBlockName } = select( blockEditorStore ),
            selectedBlock = getSelectedBlock();

			// Case 1: Confirm group itself is selected.
			if (selectedBlock.name === name) {
				const controllerBlock = selectedBlock.innerBlocks[findControllerBlockIndex(selectedBlock.innerBlocks)];
				return controllerBlock?.name === blockName;
			}

			// Case 2: Inner block is selected.
			const confirmGroupParents = getBlockParentsByBlockName(selectedBlock.clientId, name);
			return confirmGroupParents.length > 0 && selectedBlock.name === blockName;
		},
		transform: ( groupAttributes, innerBlocks ) => {
			const { llms_visibility } = groupAttributes,
				controllerBlock =
					innerBlocks[ findControllerBlockIndex( innerBlocks ) ],
				{ name, attributes } = controllerBlock;

			doFieldUnload( attributes.name );

			return createBlock( name, {
				...attributes,
				columns: 12,
				last_column: true,
				isConfirmationControlField: false,
				match: '',
				llms_visibility,
			} );
		},
	} );
} );

/**
 * Fill the controls slot with additional controls specific to this field.
 *
 * @since 2.0.0
 * @param {Object}   attributes    Block attributes.
 * @param {Function} setAttributes Reference to the block's setAttributes() function.
 * @param {Object}   props         Block properties.
 * @return {Button} Component HTML Fragment.
 */
const fillInspectorControls = ( attributes, setAttributes, props ) => {
	const { clientId } = props;

	return (
		<Button isDestructive onClick={ () => revertToSingle( clientId ) }>
			{ __( 'Remove confirmation field', 'lifterlms' ) }
		</Button>
	);
};

/**
 * Finds the index of the primary (controller) field in the group
 *
 * @since 2.0.0
 *
 * @param {Object[]} innerBlocks ) Inner blocks array.
 * @return {number} Array index of the primary block within the inner blocks array.
 */
const findControllerBlockIndex = ( innerBlocks ) =>
	innerBlocks.findIndex(
		( { attributes } ) => attributes.isConfirmationControlField
	);

/**
 * Block settings
 *
 * @type {Object}
 */
export const settings = getSettingsFromBase( getDefaultSettings( 'group' ), {
	title: __( 'Input Confirmation Group', 'lifterlms' ),
	description: __(
		'Adds a required confirmation field to an input field.',
		'lifterlms'
	),
	icon: icon,
	category: 'llms-custom-fields',
	transforms,
	fillInspectorControls,
	findControllerBlockIndex,
	supports: {
		llms_field_inspector: {
			customFill: 'confirmGroupAdditionalControls',
		},
		inserter: false,
	},
	llmsInnerBlocks: {
		allowed,

		/**
		 * Create block template depending on it's innerBlocks
		 *
		 * If the block has no children, setup matching text fields (type can be changed later).
		 *
		 * Otherwise pass in `null` which will allow various composed fields to be setup through
		 * transformation. They'll be locked by template locking even though no template is provided.
		 *
		 * @since 2.0.0
		 *
		 * @param {Object}  options
		 * @param {?Object} options.block Block object.
		 * @return {?Array} An InnerBlocks template array.
		 */
		template: ( { block } ) => {
			let template = null;

			if ( ! block || ! block.innerBlocks.length ) {
				template = [
					[ 'llms/form-field-text', getControllerBlockAttrs() ],
					[ 'llms/form-field-text', getControlledBlockAttrs() ],
				];
			}

			return template;
		},
	},
} );
