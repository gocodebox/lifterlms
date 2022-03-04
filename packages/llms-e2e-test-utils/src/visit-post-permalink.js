import { toggleSidebarPanel } from './toggle-sidebar-panel';

/**
 * Visits a post on the frontend by from within the block editor.
 *
 * @since [version]
 *
 * @return {Promise} A promise representing the link click.
 */
export async function visitPostPermalink() {
	await toggleSidebarPanel( 'Permalink' );

	const SELECTOR = 'a.edit-post-post-link__link';

	await page.waitForSelector( SELECTOR );
	const permalink = await page.$eval( SELECTOR, ( el ) => el.href );

	return page.goto( permalink );
}
