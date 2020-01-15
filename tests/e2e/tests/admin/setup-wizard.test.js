/**
 * Test the Setup Wizard
 *
 * @since [version]
 */

const { visitAdminPage } = require( '@wordpress/e2e-test-utils' );

/**
 * Retrieve the Setup Wizard Page Title.
 *
 * @since  [version]
 *
 * @return {String}
 */
const getTitle = async function() {
	return await page.$eval( '.llms-setup-content > form > h1', txt => txt.textContent );
}

describe( '#SetupWizard', () => {

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
		expect( await page.$eval( '#post-title-0', txt => txt.value ) ).toBe( 'The Official Quickstart Course for LifterLMS' );

		// Cleanup.
		await Promise.all( [
			page.click( '.editor-post-trash' ),
			page.waitForNavigation( { waitUntil: 'networkidle2' } ),
		] );

	} );

} );
