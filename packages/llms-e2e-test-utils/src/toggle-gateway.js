import { toggleSetting } from './visit-settings-page';
import { visitSettingsPage } from './visit-settings-page';

export async function toggleGateway( id ) {

	await visitSettingsPage( { tab: checkout, section: id } );
	return toggleSetting( `llms_gateway_${ id }_enabled` );

}

