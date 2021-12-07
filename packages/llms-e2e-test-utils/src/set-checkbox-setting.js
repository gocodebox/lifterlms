// Internal dependencies.
import { clickAndWait } from './click-and-wait';

/**
 * Toggles a LifterLMS checkbox setting.
 *
 * @since 3.1.0
 *
 * @param {string}  selector Selector for the setting checkbox.
 * @param {boolean} status   Requested setting status. Use `true` for checked and `false` for unchecked.
 * @param {boolean} save     Whether or not to perform a save after updating the setting.
 * @return {void}
 */
export async function setCheckboxSetting( selector, status = true, save = true ) {
	await page.waitForSelector( selector );

	const currStatus = await page.$eval( selector, ( el ) => el.checked );

	if ( status !== currStatus ) {
		await page.click( selector );

		if ( save ) {
			await clickAndWait( '.llms-save .llms-button-primary' );
		}
	}
}
