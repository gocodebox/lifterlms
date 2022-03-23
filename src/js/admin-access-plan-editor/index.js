import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { registerPlugin } from '@wordpress/plugins';

import AccessPlansList from './components/list';
import CreateButton from './components/create-button';

const { Fill } = window.llms.editPostSidebar;


function render() {

	return (
		<Fill>
			<PanelBody title={ __( 'Access Plans', 'lifterlms' ) }>
				<AccessPlansList />
				<CreateButton />
			</PanelBody>
		</Fill>
	);

}

registerPlugin(
	'llms-access-plan-editor',
	{
		render,
	}
);
