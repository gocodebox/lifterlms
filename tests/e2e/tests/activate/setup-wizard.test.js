/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.0.0-rc.1 Use `runSetupWizard()`.
 */

import { visitAdminPage } from '@wordpress/e2e-test-utils';

import { runSetupWizard } from '@lifterlms/llms-e2e-test-utils';

describe( 'SetupWizard', () => {

	it ( 'should load and run the entire setup wizard.', async () => {

		await runSetupWizard();

		await page.waitForTimeout( 5000 );

	} );

} );
