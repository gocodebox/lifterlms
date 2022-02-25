import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { store as editorStore } from '@wordpress/editor';

import AwardButton from './award-button';
import ResetButton from './reset-template-button';

export function PluginStatusButtons( { isPublished, isSaving, postId, postType } ) {
	return (
		<PluginPostStatusInfo>
			<div>
				<AwardButton { ...{ postId, postType, isPublished, isSaving } } />
				&nbsp;
				<ResetButton { ...{ isPublished, isSaving, postType } } />
			</div>
		</PluginPostStatusInfo>
	);
}


export default compose( [
	withSelect( ( wpSelect ) => {
		const {
			isSavingPost,
			isCurrentPostPublished,
			getCurrentPostId,
			getCurrentPostType,
		} = wpSelect( editorStore );
		return {
			isPublished: isCurrentPostPublished(),
			isSaving: isSavingPost(),
			postId: getCurrentPostId(),
			postType: getCurrentPostType(),
		};
	} ),
] )( PluginStatusButtons );
