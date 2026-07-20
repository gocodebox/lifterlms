/**
 * Edit components
 *
 * @since 2.0.0
 * @version 2.5.2
 */

// External deps.
import { snakeCase, kebabCase, uniqueId } from 'lodash';

// WP deps.
import { getBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { dispatch, select } from '@wordpress/data';
import { PanelBody, Slot, Fill } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

// Internal Deps.
import Field from './field';
import Inspector from './inspect';
import { isUnique } from './checks';
import manageFieldGroupAttributes from './group-data';
import GroupLayoutControl from './group-layout-control';
import { store as fieldsStore } from '../../data/fields';

/**
 * Generate a unique "name" attribute.
 *
 * @since 1.6.0
 * @since 2.0.0 Removed '_field' and added the current post id to ensure uniqueness across multiple forms.
 *
 * @param {string} name Base name, generally the field's "field" attribute. EG: "text".
 * @return {string} A unique name, in snake case, suitable to be used as a field's "name" attribute.
 */
const generateName = ( name ) => {
	const { getCurrentPostId } = select( 'core/editor' );
	return snakeCase( uniqueId( `${ name }_${ getCurrentPostId() }_` ) );
};

/**
 * Generate a unique "id" attribute.
 *
 * @since 1.6.0
 *
 * @param {string} name Base name, generally the field's "name" attribute. EG: "text_field_1".
 * @return {string} A unique name, in kebab case, suitable to be used as a field's "id" attribute.
 */
const generateId = ( name ) => {
	return kebabCase( name );
};

/**
 * Sets up block attributes, filling defaults and generating unique values.
 *
 * @since 1.6.0
 * @since 1.12.0 Add data_store_key generation.
 *
 * @param {Object}  atts        Default block attributes object.
 * @param {Object}  blockAtts   Actual WP_Block attributes object.
 * @param {boolean} addingField Determines if the field is new and should clear certain generated attributes.
 * @return {Object} Attribute object suitable for use when registering the block.
 */
const setupAtts = ( atts, blockAtts, addingField ) => {
	// Merge configured defaults into the block attributes.
	Object.keys( blockAtts ).forEach( ( key ) => {
		const defaultValue = blockAtts[ key ].__default;
		if (
			'undefined' !== typeof defaultValue &&
			'undefined' === typeof atts[ key ]
		) {
			atts[ key ] = defaultValue;
		}
	} );

	if ( ! atts.name || ( addingField && ! atts.isConfirmationField ) ) {
		let name = generateName( atts.field );
		while ( ! isUnique( 'name', name ) ) {
			name = generateName( atts.field );
		}
		atts.name = name;
	}

	if ( ! atts.id || ( addingField && ! atts.isConfirmationField ) ) {
		let id = generateId( atts.name );
		while ( ! isUnique( 'id', id, 'local' ) ) {
			id = generateId( uniqueId( `${ atts.field }-field-` ) );
		}
		atts.id = id;
	}

	if (
		'' === atts.data_store_key ||
		( addingField && ! atts.isConfirmationField )
	) {
		atts.data_store_key = atts.name;
	}

	return atts;
};

/**
 * Edit action for a field.
 *
 * @since 2.0.0
 * @since 2.0.1 Use non-unique error notice IDs for reusable multiple error notice.
 * @since 2.2.0 Remove reusable multiple error notice.
 * @since 2.5.2 Exclude LifterLMS preset fields when computing `addingField`.
 *
 * @param {Object} props Component properties.
 * @return {Object} HTML component fragment.
 */
export function EditField( props ) {
	let { attributes } = props,
		shouldSetupAtts = true;

	const { name } = props,
		block = getBlockType( name ),
		{ clientId, context, setAttributes } = props,
		inspectorSupports = block.supports.llms_field_inspector,
		editFills = block.supports.llms_edit_fill,
		{ fillEditAfter, fillInspectorControls } = block,
		{ getSelectedBlockClientId } = select( blockEditorStore ),
		{ isDuplicate } = select( fieldsStore ),
		inFieldGroup = context[ 'llms/fieldGroup/fieldLayout' ] ? true : false,
		addingField =
			attributes.name &&
			isDuplicate( attributes.name, clientId ) &&
			/**
			 * Allow LifterLMS preset fields to be duplicated without altering their id and name attributes.
			 *
			 * @see {@link https://github.com/gocodebox/lifterlms-blocks/issues/169}
			 */
			0 !== name.indexOf( 'llms/form-field-user-' ),
		/**
		 * Prevent confirmation fields from being copied/pasted into the editor out of their intended context.
		 *
		 * @see {@link https://github.com/gocodebox/lifterlms-blocks/issues/106}
		 */
		isConfirmWhichHasBeenCopied =
			! inFieldGroup && attributes.isConfirmationField;

	if ( isConfirmWhichHasBeenCopied ) {
		shouldSetupAtts = false;
	}

	attributes = shouldSetupAtts
		? setupAtts( attributes, block.attributes, addingField )
		: attributes;

	// Manage field data for blocks in field groups.
	if ( inFieldGroup && shouldSetupAtts ) {
		manageFieldGroupAttributes( props );
	}

	// Add columns CSS class.
	const blockProps = useBlockProps( {
		className: `llms-fields llms-cols-${ attributes.columns }`,
	} );

	// We can't disable the variation transformer by context so we'll do it this way which is gross but works.
	useEffect( () => {
		if (
			block.variations &&
			block.variations.length &&
			clientId === getSelectedBlockClientId()
		) {
			const interval = setInterval( () => {
				const el = document.querySelector(
					'.block-editor-block-inspector .block-editor-block-variation-transforms'
				);
				if ( el ) {
					el.style.display = attributes.isConfirmationField
						? 'none'
						: 'inline-block';
					clearInterval( interval );
				}

				return () => {
					clearInterval( interval );
				};
			}, 10 );
		}
	} );

	if ( isConfirmWhichHasBeenCopied ) {
		setTimeout( () => {
			dispatch( blockEditorStore ).removeBlock( clientId );
		}, 10 );

		return null;
	}

	return (
		<div { ...blockProps }>
			<Inspector
				{ ...{
					attributes,
					clientId,
					name,
					setAttributes,
					inspectorSupports,
					context,
				} }
			/>

			<Field
				{ ...{ attributes, setAttributes, block, clientId, context } }
			/>

			{ inspectorSupports.customFill && (
				<Fill
					name={ `llmsInspectorControlsFill.${ inspectorSupports.customFill }.${ clientId }` }
				>
					{ fillInspectorControls(
						attributes,
						setAttributes,
						props
					) }
				</Fill>
			) }

			{ editFills.after && (
				<Fill
					name={ `llmsEditFill.after.${ editFills.after }.${ clientId }` }
				>
					{ fillEditAfter( attributes, setAttributes, props ) }
				</Fill>
			) }
		</div>
	);
}

/**
 * Edit action for a field group.
 *
 * @since 2.0.0
 *
 * @param {Object} props Component properties.
 * @return {Object} HTML component fragment.
 */
export function EditGroup( props ) {
	const { attributes, clientId, name, setAttributes } = props,
		{ fieldLayout } = attributes,
		{ getBlock } = select( blockEditorStore ),
		block = getBlock( clientId ),
		blockType = getBlockType( name ),
		{ allowed, template, lock } = blockType.llmsInnerBlocks,
		primaryBlock =
			block &&
			block.innerBlocks.length &&
			'llms/form-field-confirm-group' === block.name
				? block.innerBlocks[
						blockType.findControllerBlockIndex( block.innerBlocks )
				  ]
				: null,
		primaryBlockType = primaryBlock
			? getBlockType( primaryBlock.name )
			: null,
		editFills = primaryBlockType
			? primaryBlockType.supports.llms_edit_fill
			: { after: false },
		inspectorSupports = blockType.supports.llms_field_inspector,
		hasLayout =
			blockType.providesContext &&
			blockType.providesContext[ 'llms/fieldGroup/fieldLayout' ];

	let orientation = 'columns' === fieldLayout ? 'horizontal' : 'vertical';
	if ( ! hasLayout ) {
		orientation = 'vertical';
	}

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody>
					{ hasLayout && (
						<GroupLayoutControl { ...{ ...props, block } } />
					) }

					{ inspectorSupports.customFill &&
						blockType.fillInspectorControls(
							attributes,
							setAttributes,
							props
						) }
				</PanelBody>
			</InspectorControls>

			<div
				className="llms-field-group"
				data-field-layout={ hasLayout ? fieldLayout : 'stacked' }
			>
				<InnerBlocks
					allowedBlocks={ allowed }
					template={
						'function' === typeof template
							? template( {
									attributes,
									clientId,
									block,
									blockType,
							  } )
							: template
					}
					templateLock={ lock }
					orientation={ orientation }
				/>
			</div>

			{ editFills.after && (
				<Slot
					name={ `llmsEditFill.after.${ editFills.after }.${ primaryBlock.clientId }` }
				/>
			) }
		</div>
	);
}
