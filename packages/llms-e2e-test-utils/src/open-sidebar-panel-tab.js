import {
	ensureSidebarOpened,
} from '@wordpress/e2e-test-utils';

/**
 * Opens a sidebar panel tab if it's not already open.
 *
 * @since 3.3.0
 *
 * @param {string} tab Tab to select, accepts "primary" to select the main document settings tab or "block"
 *                     to select the block tab.
 * @return {Promise} A promise that resolves when the desired panel becomes active.
 */
export async function openSidebarPanelTab( tab = 'primary' ) {
	await ensureSidebarOpened();

	let selector = '.edit-post-sidebar__panel-tabs .components-button.edit-post-sidebar__panel-tab';
	if ( 'block' === tab ) {
		selector += '[data-label="Block"]';
	}

	await page.waitForSelector( selector );

	const btn = await page.$( selector ),
		isOpen = await page.$eval( selector, ( { classList } ) => classList.contains( 'is-active' ) );

	if ( ! isOpen ) {
		await btn.click();
	}

	return page.waitForSelector( selector + '.is-active' );
}
