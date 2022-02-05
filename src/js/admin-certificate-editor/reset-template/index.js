import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { synchronizeBlocksWithTemplate } from '@wordpress/blocks';

/**
 * Resets a certificate to the default block template defined during post type registration.
 *
 * Renders a "Reset template" button near the "Move to trash" button in the post status
 * area of the editor document settings sidebar.
 *
 * @since [version]
 *
 * @return {PluginPostStatusInfo} The component.
 */
export default function() {

	const [ isOpen, setIsOpen ] = useState( false ),
		closeModal = () => setIsOpen( false ),
		openModal = () => setIsOpen( true ),
		{ getBlocks, getTemplate } = useSelect( blockEditorStore ),
		{ replaceBlocks, insertBlocks } = useDispatch( blockEditorStore ),
		{ editPost } = useDispatch( editorStore ),
		resetTemplate = () => {

			const clientIds = getBlocks().map( ( { clientId } ) => clientId ),
				template = synchronizeBlocksWithTemplate( [], getTemplate() );

			closeModal();

			editPost( { certificate_title: '' } );

			if ( clientIds.length ) {
				replaceBlocks( clientIds, template );
			} else {
				insertBlocks( template );
			}

		};

	return (
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
						<Button variant="primary" onClick={ resetTemplate }>
							{ __( 'Reset template', 'lifterlms' ) }
						</Button>
					</div>
				</Modal>
			) }
			<Button onClick={ openModal } isDestructive>{ __( 'Reset template', 'lifterlms' ) }</Button>
		</PluginPostStatusInfo>
	);

}
