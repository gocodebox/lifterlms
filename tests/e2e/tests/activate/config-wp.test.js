/**
 * Configure the WordPress site.
 *
 * 1. Enable LifterLMS
 * 2. Configure Permalinks.
 *
 * @since 3.37.8
 */

const { visitAdminPage } = require( '@wordpress/e2e-test-utils' );

const { clickAndWait } = require( 'llms-e2e-test-utils' );


describe( 'ConfigureWP', () => {

	it ( 'should enable LifterLMS plugin.', async () => {

		const slug = 'lifterlms';
		await visitAdminPage( 'plugins.php' );

		let deactivate = await page.$( `tr[data-slug="${ slug }"] .deactivate a` );
		if ( ! deactivate ) {
			await clickAndWait( `tr[data-slug="${ slug }"] .activate a` );
			deactivate = await page.waitForSelector( `tr[data-slug="${ slug }"] .deactivate a` );
		}

		await expect( deactivate ).toBeTruthy();

	} );

	it( 'should configure permalinks.', async () => {

		await visitAdminPage( 'options-permalink.php' );
		await page.click( 'input[value="/%postname%/"]', { text: ' Post name' } );

		await clickAndWait( '#submit' );

		await Promise.all( [
			expect( await page.$eval( '#setting-error-settings_updated strong', el => el.textContent ) ).toBe( 'Permalink structure updated.' ),
			expect( await page.$eval( '#permalink_structure', el => el.value ) ).toBe( '/%postname%/' ),
		] );

	} );

} );
