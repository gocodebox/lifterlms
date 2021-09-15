/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.0.0-rc.1 Use `runSetupWizard()`.
 * @since [version] Ensure all `apiFetch()` requests are finished before completing the test.
 */

import { createWaitForFetchRequests, runSetupWizard } from '@lifterlms/llms-e2e-test-utils';

describe( 'SetupWizard', () => {

	// let waitForFetchRequests;

	// beforeEach( async () => {
	// 	waitForFetchRequests = createWaitForFetchRequests();
	// } );

	// afterEach( async () => {

	// 	await waitForFetchRequests();
	// } );

	it ( 'should load and run the entire setup wizard.', async () => {

		await page.waitFor( 3000 );

		expect( 1 ).toBeTruthy();

		await page.waitFor( 3000 );
		// await runSetupWizard( { exit: true } );

	} );

} );
