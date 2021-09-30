/**
 * Select a value from a select2 dropdown field
 *
 * @since 2.2.1
 * @since 2.3.0 Focus on the search selector prior to typing.
 * @since [version] Wait for select2 to be loaded before attempting to open it and wait for select2 dropdown
 *              to close after selecting an option.
 *
 * @param {String} selector Query selector for the select element.
 * @param {String} value    Option value to select.
 * @return {Void}
 */
export async function select2Select( selector, value ) {

	// Wait for select2 to load on the element.
	await page.waitForSelector( `${ selector }.select2-hidden-accessible` );

	await page.$eval( selector, ( el ) => {
		jQuery( el ).select2( 'open' );
	} );

	const SEARCH_SELECTOR = '.select2-search__field';
	await page.waitForSelector( SEARCH_SELECTOR );
	await page.focus( SEARCH_SELECTOR );

	await page.keyboard.type( value );
	await page.waitFor( 1000 );

	await page.keyboard.press( 'Enter' );

	// Wait for the selection box to close.
	await page.waitForSelector(
		`${ selector } + .select2-container .select2-selection[aria-expanded="false"]`
	);

};
