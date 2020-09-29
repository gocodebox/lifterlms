/**
 * Test certificates
 *
 * @since [version]
 */

import { visitAdminPage } from '@wordpress/e2e-test-utils';

import {
	clickAndWait,
	createCertificate,
	fillField,
	loginStudent,
	logoutUser,
	registerStudent,
	toggleOpenRegistration,
} from '@lifterlms/llms-e2e-test-utils';

async function certLooksRight( name, title = 'A Certificate!' ) {
	expect( await page.$eval( '.llms-summary > h1', el => el.textContent ) ).toBe( title );
	expect( await page.$eval( '.llms-summary', el => el.textContent.includes( name ) ) ).toBe( true );
}

describe( 'Engagements/Certificates', () => {

	let certificateId, engagementId;

	beforeAll( async () => {

		await toggleOpenRegistration( true );

		const posts = await createCertificate( {
			title: 'A Certificate!',
			engagement: 'user_registration',
		} );
		certificateId = posts.certificateId;
		engagementId  = posts.engagementId;

	} );

	afterAll( async () => {
		await toggleOpenRegistration( false );
	} );

	describe( 'CRUD Template', () => {

		it ( 'should create a certificate', async () => {

			await visitAdminPage( 'post.php', `post=${ certificateId }&action=edit` );
			await clickAndWait( '#sample-permalink a' );

			await certLooksRight( 'admin' );

		} );

		it ( 'should be able to view a student certificate from reporting screens', async () => {

			await logoutUser();
			// Create a user who will earn the certificate.
			const
				first     = 'Student',
				last      = 'WithACert',
				{ email } = await registerStudent( { first, last } );
			await logoutUser();

			await visitAdminPage( 'users.php', `s=${ encodeURIComponent( email ) }` );

			await page.goto( await page.$eval( '#the-list tr:first-child span.llms-reporting a', el => `${ el.href }&stab=certificates` ) );

			const reportingUrl = await page.url();

			// Navigate to the certificate page.
			await page.goto( await page.$eval( '#llms-gb-table-certificates td.actions a', el => el.href ) );

			await certLooksRight( 'A Student Who Has a Certificate' );

			await page.goto( reportingUrl );
			page.on( 'dialog', dialog => dialog.accept() );
			clickAndWait( '#llms_delete_cert' );

		} );

	} );

	describe( 'Earn and View as a Student', () => {

		it ( 'should reward a certificate on user registration', async () => {

			const
				first = 'Maude',
				last  = 'Lebowski',
				user  = await registerStudent( { first, last } );

			// Certificate listed on the certs area of the dashboard.
			const selector = '.llms-sd-section.llms-my-certificates li.llms-certificate-loop-item a.llms-certificate';
			expect( await page.$eval( `${ selector } h4.llms-certificate-title`, el => el.textContent ) ).toBe( 'A Certificate!' );

			// Visit the certificate permalink.
			await clickAndWait( selector );
			const certUrl = await page.url();

			// Cert "looks" right.
			await certLooksRight( 'Maude Lebowski' );

			// Logged out user cannot view.
			await logoutUser();
			await page.goto( certUrl );
			expect( await page.waitForSelector( 'body.error404' ) ).toBeTruthy();

			// Student can enable sharing.
			await loginStudent( user.email, user.pass );
			await page.goto( certUrl );

			// Looks right to the student.
			await certLooksRight( 'Maude Lebowski' );

			// Enable sharing.
			await clickAndWait( 'button[name="llms_enable_cert_sharing"]' );

			// Logged out user can view.
			await logoutUser();
			await page.goto( certUrl );
			await certLooksRight( 'Maude Lebowski' );
			await expect( await page.$( '#llms-print-certificate' ) ).toBeNull();

		} );

	} );

} );
