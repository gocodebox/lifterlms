import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { dispatch, select } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { synchronizeBlocksWithTemplate } from '@wordpress/blocks';
import { doAction } from '@wordpress/hooks';

import { ResetTemplateCheck } from './check';
import { editCertificateTitle } from '../../util';

/**
 * Resets the post's default post type template and then triggers a save action.
 *
 * @since [version]
 *
 * @param {Function} onComplete Callback function invoked when the reset and save actions are completed.
 * @return {void}
 */
function resetTemplate( onComplete ) {

	const { getBlocks, getTemplate } = select( blockEditorStore ),
		{ replaceBlocks, insertBlocks } = dispatch( blockEditorStore ),
		{ editPost, savePost } = dispatch( editorStore ),
		clientIds = getBlocks().map( ( { clientId } ) => clientId ),
		template = synchronizeBlocksWithTemplate( [], getTemplate() );

	editCertificateTitle( '' );

	/**
	 * Action run before the default certificate post type template is reset.
	 *
	 * @since [version]
	 *
	 * @param {array} template Block template array.
	 */
	doAction( 'llms.resetCertificateTemplate.before', template );

	if ( clientIds.length ) {
		replaceBlocks( clientIds, template );
	} else {
		insertBlocks( template );
	}

	/**
	 * Action run after the default certificate post type template is reset.
	 *
	 * @since [version]
	 *
	 * @param {array} template Block template array.
	 */
	doAction( 'llms.resetCertificateTemplate.after', template );

	savePost();
	onComplete();

}

/**
 * Resets a certificate to the default block template defined during post type registration.
 *
 * Renders a "Reset template" button near the "Move to trash" button in the post status
 * area of the editor document settings sidebar.
 *
 * @since [version]
 *
 * @return {?ResetTemplateCheck} Returns the child components to render or `null` if the button should not be displayed.
 */
export default function() {

	const [ isOpen, setIsOpen ] = useState( false ),
		closeModal = () => setIsOpen( false ),
		openModal = () => setIsOpen( true );

	return (
		<ResetTemplateCheck>
			<PluginPostStatusInfo>
				{ isOpen && (
					<Modal
						title={ __( 'Confirm template reset', 'lifterlms' ) }
						style={ { maxWidth: '360px' } }
						onRequestClose={ closeModal }
						>

						<p>{ __( 'Are you sure you wish to replace the certificate content with the original default layout? This action cannot be undone!', 'lifterlms' ) }</p>

						<div style={ { textAlign: 'right' } }>
							<Button variant="tertiary" onClick={ closeModal }>
								{ __( 'Cancel', 'lifterlms' ) }
							</Button>
							&nbsp;
							<Button variant="primary" onClick={ () => resetTemplate( closeModal ) }>
								{ __( 'Reset template', 'lifterlms' ) }
							</Button>
						</div>
					</Modal>
				) }
				<Button onClick={ openModal } isDestructive>{ __( 'Reset template', 'lifterlms' ) }</Button>
			</PluginPostStatusInfo>
		</ResetTemplateCheck>
	);

}
