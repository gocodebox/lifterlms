import { clickAndWait } from './click-and-wait';
import { visitSettingsPage } from './visit-settings-page';

/**
 * Toggles the open registration setting on or off
 *
 * @since 2.1.2
 * @param {boolean} status Whether to toggle on (`true`) or off (`false`).
 * @return {void}
 */
export async function toggleOpenRegistration( status ) {
	await visitSettingsPage( { tab: 'account' } );

	const curr_status = await page.$eval(
		'#lifterlms_enable_myaccount_registration',
		( el ) => el.checked
	);

	if ( ( status && ! curr_status ) || ( ! status && curr_status ) ) {
		await page.click( '#lifterlms_enable_myaccount_registration' );
		await clickAndWait( '.llms-save .llms-button-primary' );
	}
}
