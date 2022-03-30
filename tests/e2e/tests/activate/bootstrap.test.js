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

	/**
	 * This first test will intermittently fail with the fetch_error "You are probably offline".
	 *
	 * I suspect this error comes from the dashboard's widgets when loading WP meetup events since
	 * it makes an async request to pull the data when none yet exists but I can't exactly recreate it
	 * and narrow it down or figure out how to turn that off with WP-CLI or something.
	 *
	 * I've never been able to reproduce the error locally. It only intermittently happens in the CI.
	 *
	 * @link https://github.com/WordPress/gutenberg/discussions/34856
	 * @link https://github.com/WordPress/gutenberg/issues/39862
	 */
	jest.retryTimes( 2 );
	it ( 'should configure the correct theme based on the tested WP version.', async () => {
		await activateTheme();
	} );

	it ( 'should load and run the entire setup wizard.', async () => {
		await runSetupWizard();
	} );

} );
