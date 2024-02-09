/**
 * Bootstraps E2E Tests.
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 * @since 4.0.0-rc.1 Use `runSetupWizard()`.
 * @since 6.0.0 Add theme activation based on current WP version.
 */

let courseId = null;

import { visitAdminPage } from '@wordpress/e2e-test-utils';
import { createCourse, visitPage } from "@lifterlms/llms-e2e-test-utils";

describe( 'i18n slug', () => {

	beforeEach( async () => {
		if ( ! courseId ) {
			courseId = await createCourse( 'Test i18n' );
		}
	} );

	it ( 'should allow slugs to be modified.', async () => {
		// visit permalinks page
		await visitAdminPage( 'options-permalink.php' );

		// Modify course slug
		await page.waitForSelector( '#course_base' );
		await page.$eval( '#course_base', el => el.value = 'c' );

		await page.click( '#submit' );

		// Visit old course link
		const oldUrlResponse = await visitPage( '/course/test-i18n/' );
		expect( page.url() ).toContain( '/c/test-i18n' );

		const newUrlResponse = await visitPage( '/c/test-i18n/' );
		expect( newUrlResponse.status() ).toBe( 200 );

		// Change course slug back to default
		await visitAdminPage( 'options-permalink.php' );
		await page.waitForSelector( '#course_base' );
		await page.$eval( '#course_base', el => el.value = 'course' );
		await page.click( '#submit' );

		const defaultUrlResponse = await visitPage( '/course/test-i18n/' );
		expect( page.url() ).toContain( '/course/test-i18n' );
	} );

	it( 'should keep english slugs when language switched.', async () => {
		// Set site language to French
		await visitAdminPage( 'options-general.php' );
		await page.select( '#WPLANG', 'fr_FR' );
		await page.click( '#submit' );

		// Set user language to Spanish
		await visitAdminPage( 'profile.php' );
		await page.select( '#locale', 'es_ES' );
		await page.click( '#submit' );

		// Flush the permalinks by visiting the permalinks page
		await visitAdminPage( 'options-permalink.php' );

		// Ensure course link still works
		const response = await visitPage( '/course/test-i18n/' );
		expect( response.status() ).toBe( 200 );

		// Visit settings > permalinks and ensure slugs are in English
		await visitAdminPage( 'options-permalink.php' );
		await page.waitForSelector( '#course_base' );

		expect( await page.$eval( '#course_base', el => el.value ) ).toBe( 'course' );

		// Switch site back to English
		await visitAdminPage( 'options-general.php' );
		await page.select( '#WPLANG', 'en_US' );
		await page.click( '#submit' );

		// Switch user back to English
		await visitAdminPage( 'profile.php' );
		await page.select( '#locale', 'en_US' );
		await page.click( '#submit' );
	} );

} );
