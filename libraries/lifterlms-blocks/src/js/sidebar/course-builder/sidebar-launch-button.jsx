/**
 * Sidebar launch button.
 *
 * @since 2.5.0
 * @version 2.5.1
 */

import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { Button } from '@wordpress/components';

import './editor.scss';

/**
 * Sidebar launch button component.
 *
 * @since 2.5.0
 * @since 2.5.1 Fix button link using localized admin url so to avoid issues when
 *               WordPress is installed in a subdirectory.
 */
const SidebarLaunchButton = () => {
	const postType = select( 'core/editor' )?.getCurrentPostType() ?? '';

	if ( ! [ 'course', 'lesson' ].includes( postType ) ) {
		return null;
	}

	const courseId = Number( window?.llmsBlocks?.courseId ?? select( 'core/editor' )?.getCurrentPostId() ?? 0 );

	if ( ! courseId ) {
		return null;
	}

	return <PluginPostStatusInfo
		className={ 'llms-launch-course-builder' }
	>
		<Button
			href={ window.llms.admin_url + 'admin.php?page=llms-course-builder&course_id=' + courseId }
			className={ 'llms-button-primary' }
		>
			{ __( 'Launch Course Builder', 'lifterlms' ) }
		</Button>
	</PluginPostStatusInfo>;
};

registerPlugin( 'post-status-info-test', {
	render: SidebarLaunchButton,
} );
