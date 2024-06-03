import { toggleSidebarPanel } from './toggle-sidebar-panel';

/**
 * Visits a post on the frontend by from within the block editor.
 *
 * @since 3.3.0
 *
 * @return {Promise} A promise representing the link click.
 */
export async function visitPostPermalink() {
	const SELECTOR = '.edit-post-header__settings a[aria-label="View Certificate Template"]';

	await page.waitForSelector( SELECTOR );
	const permalink = await page.$eval( SELECTOR, ( el ) => el.href );

	return page.goto( permalink );
}
