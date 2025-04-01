import { clickAndWait } from './click-and-wait';
import { clickElementByText } from './click-element-by-text';
import { findElementByText } from './find-element-by-text';
import { wpVersionCompare } from './wp-version-compare';
import { dismissEditorWelcomeGuide } from './dismiss-editor-welcome-guide';

import { visitAdminPage, clickButton } from '@wordpress/e2e-test-utils';

/**
 * Retrieve the Setup Wizard Page Title.
 *
 * @since 2.1.0
 *
 * @return {string} Content of the title element.
 */
const getTitle = async function() {
	return await page.$eval(
		'.llms-setup-content > form > h1',
		( txt ) => txt.textContent
	);
};

/**
 * Run (and test) the LifterLMS Setup Wizard
 *
 * @since 2.1.0
 * @since 2.2.0 Rework to accommodate setup wizard changes in LifterLMS core.
 * @since 3.2.0 Fix title assertion on WordPress >= v5.9.
 *
 * @param {Object}   options                 Options object.
 * @param {string[]} options.coursesToImport Titles of the course(s) to import through the setup wizard. Pass a falsy to skip import and "Start from Scratch".
 * @param {boolean}  options.exit            Whether or not to exit the setup wizard at the conclusion of setup. If `true`, uses the "Exit" link to leave setup.`
 * @return {void}
 */
export async function runSetupWizard( {
	coursesToImport = [ 'LifterLMS Quickstart Course' ],
	exit = false,
} = {} ) {
	// Launch the Setup Wizard.
	await visitAdminPage( 'admin.php', 'page=llms-setup' );

	// Step One.
	expect( await getTitle() ).toBe( 'Welcome to LifterLMS!' );

	// Move to Step Two.
	await clickAndWait( '.llms-setup-actions .llms-button-primary' );
	expect( await getTitle() ).toBe( 'Page Setup' );

	// Move to Step Three.
	await clickAndWait( '.llms-setup-actions .llms-button-primary' );
	expect( await getTitle() ).toBe( 'Payments' );

	// Move to Step Four.
	await clickAndWait( '.llms-setup-actions .llms-button-primary' );
	expect( await getTitle() ).toBe( 'Help Improve LifterLMS & Get a Coupon' );

	// Move to Step Five.
	await clickAndWait( '.llms-setup-actions .llms-button-secondary' ); // Skip the coupon.
	expect( await getTitle() ).toBe( 'Setup Complete!' );

	// Import button should be disabled.
	expect(
		await page.$eval( '#llms-setup-submit', ( el ) => el.disabled )
	).toBe( true );

	if ( exit ) {
		// Exit the wizard.

		await clickAndWait( '.llms-exit-setup' );
		expect(
			await page.url().includes( '/admin.php?page=llms-settings' )
		).toBe( true );
	} else if ( ! coursesToImport ) {
		// Start from scratch.

		await clickAndWait( '.llms-setup-actions .llms-button-secondary' );
		await dismissEditorWelcomeGuide();
	} else if ( coursesToImport ) {
		// Import courses.

		// Select specified courses.
		for ( const courseTitle of coursesToImport ) {
			await clickElementByText( courseTitle, 'h3' );
		}

		await clickButton( 'Import Courses' );

		await page.waitForNavigation();

		if ( 1 === coursesToImport.length ) {
			// Single course imported.

			expect(
				await page.$eval(
					'.block-editor h1.screen-reader-text',
					( txt ) => txt.textContent
				)
			).toBe( 'Edit Course' );

			await dismissEditorWelcomeGuide();

			expect(
				await page.$eval(
					'.editor-post-title__input',
					// On >= WP 5.9, this is an <h1>, earlier is a <textarea>.
					( txt, isTextNode ) => isTextNode ? txt.textContent : txt.value,
					wpVersionCompare( '5.9' )
				)
			).toBe( coursesToImport[ 0 ] );
		} else {
			expect(
				await page.url().includes( '/edit.php?post_type=course' )
			).toBe( true );

			// All courses should be present in the post table list.
			for ( const courseTitle of coursesToImport ) {
				await findElementByText( courseTitle, '#the-list a.row-title' );
			}
		}
	}
}
