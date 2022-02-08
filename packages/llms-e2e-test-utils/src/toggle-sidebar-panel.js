import {
	findSidebarPanelWithTitle,
} from '@wordpress/e2e-test-utils';

import { openSidebarPanelTab } from './open-sidebar-panel-tab';

/**
 * Opens or closes an editor sidebar panel based on the panel's title.
 *
 * @since [version]
 *
 * @param {string}  title        The panel title to open or close.
 * @param {boolean} shouldBeOpen Whether or not the panel should be open.
 * @return {Object|undefined} A puppeteer ElementHandle object if found.
 */
export async function toggleSidebarPanel( title, shouldBeOpen = true ) {
	await openSidebarPanelTab();

	const btn = await findSidebarPanelWithTitle( title ),
		classNames = await (
			await btn.getProperty( 'className' )
		).jsonValue(),
		isOpen = -1 !== classNames.indexOf( 'is-opened' );

	// Open or close the panel as desired.
	if ( isOpen !== shouldBeOpen ) {
		await btn.click();
	}

	return btn;
}
