/**
 * Bootstraps E2E Tests.
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.0.0-rc.1 Use `runSetupWizard()`.
 * @since 6.0.0 Add theme activation based on current WP version.
 */

import { activateTheme, visitPage, runSetupWizard } from '@lifterlms/llms-e2e-test-utils';

describe( 'Bootstrap', () => {

	beforeAll( () => {
		console.log( 'before all' );
	} );

	afterAll( () => {
		console.log( 'after all' );
	} );

	it ( 'should not say you are probably offline', () => {

		console.log( 'before' );

		expct( true ).toBe( true );

		console.log( 'after' );

	} );

	it ( 'should configure the correct theme based on the tested WP version.', async () => {
		await activateTheme();
	} );

	it ( 'should load and run the entire setup wizard.', async () => {
		await runSetupWizard();
	} );

} );
