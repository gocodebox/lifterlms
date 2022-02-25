// WP Deps.
import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';

// LLMS Deps.
import { PostSearchControl, UserSearchControl } from '@lifterlms/components';

// Internal Deps.
import createAward from './create';
import getMessage from './message';
import { getRedirectUrl, getScratchUrl } from './urls';

/**
 * Button and modal interface for creating a draft certificate from a specified template for a specific student.
 *
 * @since [version]
 *
 * @param {Object}   params                Component configuration object.
 * @param {string}   params.modalTitle     [description]
 * @param {string}   params.buttonLabel    [description]
 * @param {Boolean}  params.isDisabled     [description]
 * @param {Boolean}  params.enableScratch  When `true`, displays a "Start from Scratch" button that links to the awarded certificate editor page.
 * @param {Boolean}  params.selectStudent  When `true`, displays a searchable select element to allow user selection of the user.
 * @param {Boolean}  params.selectTemplate When `true`, displays a searchable select element to allow user selection of the template.
 * @param {?integer} params.studentId      WP_User ID of the student to award the certificate to.
 * @param {?integer} params.templateId     WP_Post ID of the certificate template post to create the certificate from.
 * @return {WPElement} The component.
 */
export default function( {
	modalTitle = __( 'Award a New Certificate', 'lifterlms' ),
	buttonLabel = __( 'Award', 'lifterlms' ),
	isDisabled = false,
	enableScratch = true,
	selectStudent = true,
	selectTemplate = true,
	studentId = null,
	templateId = null,
} ) {

	const [ isOpen, setIsOpen ] = useState( false ),
		[ isBusy, setIsBusy ] = useState( false ),
		[ currStudentId, setStudentId ] = useState( studentId ),
		[ currTemplateId, setTemplateId ] = useState( templateId ),
		closeModal = () => setIsOpen( false ),
		openModal = () => setIsOpen( true ),
		isReady = currStudentId && currTemplateId,
		onClick = () => {
			setIsBusy( true );
			createAward( currStudentId, currTemplateId ).then( ( { id } ) => {
				window.location = getRedirectUrl( id );
			} );
		}

	return (
		<>
			{ isOpen && (
				<Modal
					title={ modalTitle }
					style={ { maxWidth: '420px' } }
					onRequestClose={ closeModal }
				>

					<p>{ getMessage( selectStudent, selectTemplate) }</p>

					{ selectStudent && (
						<UserSearchControl
							isClearable
							label={ __( 'Award to', 'lifterlms' ) }
							selectedValue={ studentId ? [ studentId ] : [] }
							onUpdate={ ( obj ) => {
								const id = obj?.id || null;
								setStudentId( id );
							} }
						/>
					) }

					{ selectTemplate && (
						<PostSearchControl
							isClearable
							postType="llms_certificate"
							label={ __( 'Template', 'lifterlms' ) }
							placeholder={ __( 'Search for a certificate template…', 'lifterlms' ) }
							selectedValue={ templateId ? [ templateId ] : [] }
							onUpdate={ ( obj ) => {
								const id = obj?.id || null;
								setTemplateId( id );
							} }
						/>
					) }

					<div style={ { textAlign: 'right', padding: '24px 32px 0', margin: '24px -32px 0', borderTop: '1px solid #ddd' } }>

						<Button style={ { marginRight: '5px' } } disabled={ ! isReady } isBusy={ isBusy } variant="primary" onClick={ onClick }>
							{ __( 'Create Draft' ) }
						</Button>

						{ enableScratch && (
							<Button style={ { marginRight: '5px' } } variant="secondary" href={ getScratchUrl( studentId ) }>
								{ __( 'Start from Scratch', 'lifterlms' ) }
							</Button>
						) }

						<Button variant="tertiary" onClick={ closeModal }>
							{ __( 'Cancel', 'lifterlms' ) }
						</Button>
					</div>
				</Modal>
			) }
			<Button
				disabled={ isDisabled }
				variant="secondary"
				onClick={ openModal }
				>
				{ buttonLabel }
			</Button>
		</>
	);

}
