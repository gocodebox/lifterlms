/**
 * Highlight (selects) the contents of a node.
 *
 * @since [version]
 *
 * @param {string}  selector      Query selector.
 * @param {boolean} copySelection If `true`, copies the selected text and returns it.
 *                                The browser clipboard-read permission must be granted in order to read from the clipboard.
 * @return {boolean|string} Returns the copied text or `true` if `copySelection` is `false`.
 */
export async function highlightNode( selector, copySelection = false ) {
	await page.waitForSelector( selector );

	await page.evaluate( ( _selector ) => {
		const range = document.createRange(),
			// eslint-disable-next-line @wordpress/no-global-get-selection
			selection = window.getSelection();

		range.selectNodeContents( document.querySelector( _selector ) );

		selection.removeAllRanges();

		selection.addRange( range );
	}, selector );

	if ( copySelection ) {
		await page.bringToFront();

		return await page.evaluate( () => {
			document.execCommand( 'copy' );
			return window.navigator.clipboard.readText();
		} );
	}

	return true;
}
