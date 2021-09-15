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

	let waitForFetchRequests;

	beforeEach( async () => {
		waitForFetchRequests = createWaitForFetchRequests();
	} );

	afterEach( async () => {

		await waitForFetchRequests().then( vals => console.log( 'vals: ', vals ) ).catch( err => console.log( 'err:', err ) );
	} );

	it ( 'should load and run the entire setup wizard.', async () => {

		await runSetupWizard();

	} );

} );
