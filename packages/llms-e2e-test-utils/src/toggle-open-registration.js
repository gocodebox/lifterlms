import { clickAndWait } from './click-and-wait';
import { toggleSetting } from './visit-settings-page';
import { visitSettingsPage } from './visit-settings-page';

/**
 * Toggles the open registration setting on or off
 *
 * @since 2.1.2
 * @since [version] Use `toggleSetting()`.
 *
 * @param {Boolean} status Whether to toggle on (`true`) or off (`false`).
 * @return {void}
 */
export async function toggleOpenRegistration( status ) {

	await visitSettingsPage( { tab: 'account' } );
	return toggleSetting( '#lifterlms_enable_myaccount_registration', status );

}
