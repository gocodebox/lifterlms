// Internal dependencies.
import { clickAndWait } from './click-and-wait';

// External dependencies.
import { visitAdminPage, clickButton } from '@wordpress/e2e-test-utils';

/**
 * Import a course JSON file
 *
 * @since 2.2.0
 * @since 2.2.0 Update to accommodate changes in the LifterLMS core.
 * @since 3.0.0 Use `waitForTimeout()` in favor of deprecated `waitFor()`.
 *
 * @param {string}  importFile Filename of the import.
 * @param {string}  importPath Local path where the file is located. By default uses `tests/assets/`.
 * @param {boolean} navigate   Whether or not to automatically navigate to the imported course when done.
 * @return {void}
 */
export async function importCourse(
	importFile,
	importPath = '',
	navigate = true
) {
	importPath = importPath || `${ process.cwd() }/tests/assets/`;

	const file = importPath + importFile;

	await visitAdminPage( 'admin.php', 'page=llms-import' );

	// Upload button
	await clickButton('Upload');

	const inputSelector = 'input[name="llms_import"]';
	await page.waitForSelector( inputSelector );
	const fileUpload = await page.$( inputSelector );

	fileUpload.uploadFile( file );
	await page.waitForTimeout( 1000 );

	await clickButton( 'Import' );

	if ( navigate ) {
		await clickAndWait( '.llms-admin-notice.notice-success a' );
	}
}
