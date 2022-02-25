import { AwardCheck } from './award-check';
import { AwardCertificateButton } from '../../util';

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


