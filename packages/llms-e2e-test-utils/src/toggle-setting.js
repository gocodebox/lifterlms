import { clickAndWait } from './click-and-wait';

/**
 * Toggle a checkbox setting
 *
 * @since [version]
 *
 * @param {string}  selector CSS selector for the checkbox element.
 * @param {Boolean} status   Whether to toggle on (`true`) or off (`false`).
 * @param {Boolean} save     Whether or not to save when completed.
 * @return {void}
 */
export async function toggleSetting( selector, status, save = true ) {

	let changesMade = false;

	const curr_status = await page.$eval( selector, el => el.checked );

	if ( status && ! curr_status || ! status && curr_status ) {
		await page.click( selector );
		changesMade = false;
	}

	if ( save && changesMade ) {
		await clickAndWait( '.llms-save .llms-button-primary' );
	}

}

