/**
 * Select a value from a select2 dropdown field
 *
 * @since 2.2.1
 *
 * @param {String} selector Query selector for the select element.
 * @param {String} value    Option value to select.
 * @return {Void}
 */
export async function select2Select( selector, value ) {

	await page.$eval( selector, ( el ) => {
		jQuery( el ).select2( 'open' );
	} );

	await page.waitFor( 1000 );
	await page.keyboard.type( value );
	await page.waitFor( 1000 );

	await page.keyboard.press( 'Enter' );

};
