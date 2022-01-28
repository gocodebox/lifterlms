// WP Deps.
import { __ } from '@wordpress/i18n';
import { useSelect, dispatch, select } from '@wordpress/data';
import { store as blocksStore } from '@wordpress/blocks';
import { store as editorStore } from '@wordpress/editor';

// Internal deps.
import metadata from './block.json';

const { name } = metadata;

/**
 * Persist content to the database.
 *
 * Manages where to save data based on the current post type.
 *
 * @since [version]
 *
 * @param {string} content Content to save.]
 * @return {void}
 */
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

/**
 * Block edit component.
 *
 * @since [version
 *
 * @param {Object}   args               Component arguments.
 * @param {Object}   args.attributes    Block attributes object.
 * @param {Function} args.setAttributes Function used to update the block's attributes.
 * @param {Function} args.mergeBlocks   Function called when merging the block with another block.
 * @param {Function} args.onReplace     Function called when replacing the block with another block.
 * @param {Object}   args.style         Block style attributes.
 * @param {string}   args.clientId      Block client ID.
 * @return {WPElement} The edit component.
 */
export default function Edit( {
	attributes,
	setAttributes: origSetAttributes,
	mergeBlocks,
	onReplace,
	style,
	clientId,
} ) {
	const { getBlockType } = useSelect( blocksStore ),
		{ getEditedPostAttribute, getCurrentPostType } = useSelect( editorStore ),
		{ edit: HeadingEdit } = getBlockType( 'core/heading' ),
		titleAttribute = 'llms_certificate' === getCurrentPostType() ? 'certificate_title' : 'title';

	attributes.placeholder = attributes.placeholder || __( 'Certificate of Achievement', 'lifterlms' );
	attributes.content = attributes.content || getEditedPostAttribute( titleAttribute );

	const setAttributes = ( attrs ) => {
		const { content } = attrs;

		if ( undefined !== content ) {
			saveContent( content );
		}

		return origSetAttributes( attrs );
	};

	return (
		<>
			<HeadingEdit { ...{
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
