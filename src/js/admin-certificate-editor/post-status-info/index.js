import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { store as editorStore } from '@wordpress/editor';

import AwardButton from './award-button';
import ResetButton from './reset-template-button';

/**
 * Renders a button / modal interface used to generate awarded certificates.
 *
 * @since [version]
 *
 * @param {Object}  params             Component options.
 * @param {boolean} params.isPublished Whether or not the current post is published.
 * @param {boolean} params.isSaving    Whether or not the editor is currently saving.
 * @param {number}  params.postId      WP_Post ID of the template.
 * @param {string}  params.postType    Current post type where the button is being displayed.
 * @return {PluginPostStatusInfo} The status info component.
 */
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
