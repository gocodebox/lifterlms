import { AwardCheck } from './award-check';
import { AwardCertificateButton } from '../../util';

/**
 * Renders a button / modal interface used to generate awarded certificates.
 *
 * @since [version]
 *
 * @param {Object}  params             Component options.
 * @param {number}  params.postId      WP_Post ID of the template.
 * @param {string}  params.postType    Current post type where the button is being displayed.
 * @param {boolean} params.isSaving    Whether or not the editor is currently saving.
 * @param {boolean} params.isPublished Whether or not the current post is published.
 * @return {AwardCheck} Check component which conditionally renders the <AwardCertificateButton> component.
 */
export default function( { postId, postType, isSaving, isPublished } ) {
	return (
		<AwardCheck { ...{ postType } }>
			<AwardCertificateButton
				enableScratch={ false }
				selectTemplate={ false }
				templateId={ postId }
				isDisabled={ isSaving || ! isPublished }
			/>
		</AwardCheck>
	);
}

