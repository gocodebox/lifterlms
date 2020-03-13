/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since [version] Fix package references.
 */

import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Retrieve the Setup Wizard Page Title.
 *
 * @since  3.37.8
 *
 * @return {String}
 */
const getTitle = async function() {
	return await page.$eval( '.llms-setup-content > form > h1', txt => txt.textContent );
}

describe( 'SetupWizard', () => {

	it ( 'should load and run the entire setup wizard.', async () => {

		// Launch the Setup Wizard.
		await visitAdminPage( 'admin.php', 'page=llms-setup' );

		// Step One.
		expect( await getTitle() ).toBe( 'Welcome to LifterLMS!' );

		// Move to Step Two.
		await Promise.all( [
			page.click( '.llms-setup-actions .llms-button-primary' ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } ),
		] );
		expect( await getTitle() ).toBe( 'Page Setup' );

		// Move to Step Three.
		await Promise.all( [
			page.click( '.llms-setup-actions .llms-button-primary' ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } ),
		] );
		expect( await getTitle() ).toBe( 'Payments' );

		// Move to Step Four.
		await Promise.all( [
			page.click( '.llms-setup-actions .llms-button-primary' ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } ),
		] );
		expect( await getTitle() ).toBe( 'Help Improve LifterLMS & Get a Coupon' );

		// Move to Step Five.
		await Promise.all( [
			page.click( '.llms-setup-actions .llms-button-secondary' ), // Skip the coupon.
			page.waitForNavigation( { waitUntil: 'networkidle2' } ),
		] );
		expect( await getTitle() ).toBe( 'Setup Complete!' );

		// Install a Sample Course.
		await Promise.all( [
			page.click( '.llms-setup-actions .llms-button-primary' ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } ),
		] );
		expect( await page.$eval( '.block-editor h1.screen-reader-text', txt => txt.textContent ) ).toBe( 'Edit Course' );


		const isWelcomeGuideActive = await page.evaluate( () =>
			wp.data.select( 'core/edit-post' ).isFeatureActive( 'welcomeGuide' )
		);

		if ( isWelcomeGuideActive ) {
			await page.evaluate( () =>
				wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' )
			);
		}

		expect( await page.$eval( '.editor-post-title__input', txt => txt.value ) ).toBe( 'The Official Quickstart Course for LifterLMS' );

	} );

} );
