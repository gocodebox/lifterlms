/**
 * Editor Sidebar Plugins
 *
 * @since 1.0.0
 * @version 2.5.0
 */

// WP Deps.
import { select, subscribe } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { Fragment } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins';

// Internal Deps.
import Instructors from './instructors';
import FormDocumentSettings from './form-document-settings';
import LifterLMSIcon from '../icons/lifterlms-icon';
import { CourseBuilderPanel } from './course-builder/course-builder-panel.jsx';
import { addToolbarLaunchButton } from './course-builder/toolbar-launch-button.jsx';
import './course-builder/sidebar-launch-button.jsx';

/**
 * Registers the sidebar plugin for Courses and Memberships
 *
 * @since 1.0.0
 * @since 2.5.0 Added toolbar launch button for the course builder.
 *
 * @return {?Fragment} Component fragment or null when instructors aren't supported for the given post type.
 */
const Sidebar = () => {
	const postType = select( 'core/editor' ).getCurrentPostType();

	if ( ! [ 'course', 'lesson', 'llms_membership' ].includes( postType ) ) {
		return null;
	}

	if ( [ 'course', 'lesson' ].includes( postType ) ) {
		subscribe( addToolbarLaunchButton );
	}

	return <>
		<PluginSidebarMoreMenuItem
			target="llms-sidebar"
			icon={ <LifterLMSIcon /> }
		>
			{ 'LifterLMS' }
		</PluginSidebarMoreMenuItem>
		{ [ 'lesson', 'course' ].includes( postType ) && <PluginSidebar name="llms-sidebar" title="LifterLMS">
			<CourseBuilderPanel />
		</PluginSidebar> }
	</>;
};

registerPlugin( 'llms', {
	render: Sidebar,
	icon: <LifterLMSIcon />,
} );

/**
 * Register the forms post type document settings sidebar plugin.
 *
 * @since 1.6.0
 */
registerPlugin( 'llms-forms-doc-settings', {
	render: FormDocumentSettings,
	icon: '',
} );
