import { pressKeyWithModifier } from '@wordpress/e2e-test-utils';

/**
 * Type text into a field identified by a selector.
 *
 * @since 1.1.1
 * @since 2.0.0 Automatically cast `text` to a string.
 * @since [version] Wait for the selector before attempting to focus on it.
 *
 * @param {string} selector Query selector to identify the field element.
 * @param {string} text     Text to type into the field.
 * @return {void}
 */
export async function fillField( selector, text ) {
	await page.waitForSelector( selector );
	await page.focus( selector );
	await pressKeyWithModifier( 'primary', 'a' );
	await page.type( selector, text.toString() );
}
