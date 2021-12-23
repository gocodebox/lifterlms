
import { createHigherOrderComponent } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { useSelect, dispatch, select } from '@wordpress/data';
import { store as blocksStore } from '@wordpress/blocks';
import { store as editorStore } from '@wordpress/editor';
import { PanelRow, PanelBody, SelectControl } from '@wordpress/components';
import { InspectorControls, useSetting } from '@wordpress/block-editor';
import { addFilter } from '@wordpress/hooks';

import metadata from './block.json';
import FontSelectControl from './font-select-control';

const { name } = metadata;

const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {

		const { name: blockName, attributes, setAttributes } = props,
			{ fontFamily } = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				{ ( blockName === name ) && (
					<InspectorControls>
						<PanelBody title={ __( 'Font Family', 'lifterlms' ) } initialOpen={ true }>
							<PanelRow>
								<FontSelectControl { ...{ fontFamily, setAttributes } }/>
							</PanelRow>
						</PanelBody>
					</InspectorControls>
				) }
			</>
		);
	};
}, 'withInspectorControl' );
addFilter( 'editor.BlockEdit', 'lifterlms/certificate-title-controls', withInspectorControls );

function saveContent( content ) {

	const { getCurrentPostType } = select( editorStore ),
		{ editPost } = dispatch( editorStore ),
		postType = getCurrentPostType(),
		edits = {};

	if ( 'llms_certificate' === postType ) {
		edits.certificate_title = content;
	} else if ( 'llms_my_certificate' === postType ) {
		edits.title = content;
	}

	if ( Object.keys( edits ).length ) {
		editPost( edits );
	}

}

export default function edit( {
	attributes,
	setAttributes: origSetAttributes,
	mergeBlocks,
	onReplace,
	style,
	clientId,
} ) {

	const { getBlockType } = useSelect( blocksStore ),
		{ getEditedPostAttribute, getCurrentPostType } = useSelect( editorStore ),
		{ edit: Edit } = getBlockType( 'core/heading' ),
		titleAttribute = 'llms_certificate' === getCurrentPostType() ? 'certificate_title' : 'title';

	attributes.placeholder = attributes.placeholder || __( 'Certificate of Achievement', 'lifterlms' );
	attributes.content     = attributes.content || getEditedPostAttribute( titleAttribute );

	const setAttributes = ( attrs ) => {

		const { content } = attrs;

		if ( undefined !== content ) {
			saveContent( content );
		}

		return origSetAttributes( attrs );

	};

	return (
		<>
			<Edit { ...{
				attributes,
				setAttributes,
				mergeBlocks,
				onReplace,
				style,
				clientId,
			} } />
		</>
	);
}
