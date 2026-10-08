/**
 * Displays the course ID in the post status panel.
 *
 * @since    1.3.0
 * @version  10.2.0
 */
// WP Deps.
import { useSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/editor';
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';
const PostId = () => {
	const { postId, postType } = useSelect((select) => {
		const { getCurrentPostId, getCurrentPostType } = select('core/editor');
		return {
			postId: getCurrentPostId(),
			postType: getCurrentPostType(),
		};
	}, []);
	if ('course' !== postType) {
		return null;
	}
	return (
		<PluginPostStatusInfo className="llms-post-id">
			<span>{__('Course ID', 'lifterlms')}</span>
			<span>{postId}</span>
		</PluginPostStatusInfo>
	);
};
registerPlugin('llms-post-id', {
	render: PostId,
});