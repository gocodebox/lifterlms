// WP Deps.
import { __ } from '@wordpress/i18n';
import { createSlotFill } from '@wordpress/components';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';

// LLMS Deps.
import { Icon, lifterlms } from '@lifterlms/icons';

/**
 * Sidebar Name / ID.
 *
 * @type {String}
 */
export const name = 'llms-edit-post-sidebar';

/**
 * Sidebar Title.
 *
 * @type {Object}
 */
export const title = __( 'LifterLMS settings', 'lifterlms' );

/**
 * Sidebar content Slot & Fill components.
 */
export const { Slot, Fill } = createSlotFill( name );

const LifterLMSIcon = () => <Icon icon={ lifterlms } />;
/**
 * Render component for the sidebar.
 *
 * @since [version]
 *
 * @return {WPElement} Sidebar component.
 */
function renderSidebar() {

	return(
		<>
			<PluginSidebarMoreMenuItem target={ name } icon={ <LifterLMSIcon /> }>
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar name={ name } title={ title }>
				<Slot />
			</PluginSidebar>
		</>
	);
}

// Register the plugin.
registerPlugin(
	name,
	{
		render: renderSidebar,
		icon: <LifterLMSIcon />,
	}
);
