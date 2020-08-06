// Internal dependencies.
import { clickAndWait } from './click-and-wait';

// External dependencies.
import fs from 'fs';
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Import a course JSON file
 *
 * @since 2.2.0
 *
 * @param {String}  importFile Filename of the import.
 * @param {String}  importPath Local path where the file is located. By default uses `tests/assets/`.
 * @param {Boolean} navigate   Whether or not to automatically navigate to the imported course when done.
 * @return {Void}
 */
export async function importCourse( importFile, importPath = '', navigate = true ) {

	importPath = importPath || `${ process.cwd() }/tests/assets/`;

	const file = importPath + importFile;

	await visitAdminPage( 'admin.php', 'page=llms-import' );

	const inputSelector = 'input[name="llms_import"]'
	await page.waitForSelector( inputSelector );
	const fileUpload = await page.$( inputSelector );

	fileUpload.uploadFile( file );
	await page.waitFor( 1000 );

	await clickAndWait( 'button.llms-button-primary[type="submit"]' );

	if ( navigate ) {

		// Search for the imported course by name.
		const { title } = JSON.parse( fs.readFileSync( file ) );
		await visitAdminPage( 'edit.php', new URLSearchParams( { s: title, post_type: 'course' } ).toString() );

		// Go to that course.
		await clickAndWait( '.wp-list-table.posts tbody tr:first-child a.row-title' );

	}

}
