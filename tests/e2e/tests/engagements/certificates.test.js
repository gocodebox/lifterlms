import {
	insertBlock,
	switchUserToAdmin,
	visitAdminPage
} from '@wordpress/e2e-test-utils';

import {
	clearBlocks,
	clickAndWait,
	clickElementByText,
	createCertificate,
	fillField,
	getAllBlocks,
	loginStudent,
	logoutUser,
	openSidebarPanelTab,
	registerStudent,
	toggleOpenRegistration,
	toggleSidebarPanel,
	visitPostPermalink,
} from '@lifterlms/llms-e2e-test-utils';


/**
 * Retrieves the HTML for the currently-viewed certificate.
 *
 * Modifies the DOM to replace IDs from html wrapper attributes with a mocked ID, "9999".
 *
 * Also replaces the "Awarded" date with a mocked date, "Octember 01, 9999".
 *
 * @since [version]
 *
 * @param {Number} templateVersion The editor template version of the certificate.
 * @return {string} The outerHTML of the certificate element.
 */
async function getCertificateHTML( templateVersion = 2 ) {

	let WRAPPER = '.llms-certificate-wrapper';

	if ( 1 === templateVersion ) {
		WRAPPER = '.llms-certificate-container';
	}

	await page.waitForSelector( WRAPPER );

	let html = await page.$eval( WRAPPER, el => el.outerHTML );

	// Hardcode IDs for the snapshot.
	html = html.replace( /(id="certificate-)\d+(")/, '$1999$2' ); // ID attribute.
	html = html.replace( /( post-)\d+( )/, '$1999$2' ); // ID classname.

	// Replace the awarded date with a mocked date.
	html = html.replace( /(">On ).+(<\/p>)/, '$1Octember 01, 9999$2' );

	// Strip the classname added by the various themes; 2022 theme doesn't include it and this makes it so we can have a single snapshot.
	html = html.replace( ' entry">', '">' );

	return html;

}

/**
 * Retrieves an elements CSS inline css rules a JS object.
 *
 * @since [version]
 *
 * @param {string} selector The DOM query selector string.
 * @return {Object} An object of css rules.
 */
async function getElementStyles( selector ) {

	return await page.$eval( selector, ( { style } ) => {

		const styles = {};

	    for ( let i = 0; i < style.length; i++ ) {
	        let item = style.item( i );
	        styles[ item ] = style[ item ];
	    }

	    return styles;

	} );

}

describe( 'Engagements/Certificates', () => {

	beforeAll( async () => {
		await switchUserToAdmin();
		await toggleOpenRegistration( true );
	} );

	afterAll( async () => {
		await switchUserToAdmin();
		await toggleOpenRegistration( false );
	} );

	describe( 'Templates', () => {

		it ( 'should create a certificate in the block editor', async () => {

			await createCertificate( {
				title: 'A Certificate!',
			} );

			await visitPostPermalink();
			expect( await getCertificateHTML() ).toMatchSnapshot();

		} );

		it ( 'can reset blocks to the default template', async () => {

			await visitAdminPage( 'post-new.php', 'post_type=llms_certificate' );

			await page.waitForTimeout( 1000 );

			// Add a title.
			const TITLE_SELECTOR = '.is-root-container.block-editor-block-list__layout .wp-block-llms-certificate-title';
			await page.waitForSelector( TITLE_SELECTOR );
			await page.click( TITLE_SELECTOR );
			await page.keyboard.type( 'Certificate Title' );
			await page.keyboard.press( 'Tab' );

			// Add a new block.
			await insertBlock( 'Spacer' );

			// Make sure sidebar is open.
			await openSidebarPanelTab();

			// Open confirmation modal.
			await clickElementByText( 'Reset template', '.edit-post-post-status button' );

			// Confirm reset.
			await clickElementByText( 'Reset template', '.components-modal__frame button' );

			await page.waitForTimeout( 1000 );

			expect( await getAllBlocks( false ) ).toMatchSnapshot();

		} );

		it ( 'can reset blocks to the default template when there are no blocks present', async () => {

			await visitAdminPage( 'post-new.php', 'post_type=llms_certificate' );

			await clearBlocks();

			// Make sure sidebar is open.
			await openSidebarPanelTab();

			// Open confirmation modal.
			await clickElementByText( 'Reset template', '.edit-post-post-status button' );

			// Confirm reset.
			await clickElementByText( 'Reset template', '.components-modal__frame button' );

			await page.waitForTimeout( 1000 );

			expect( await getAllBlocks( false ) ).toMatchSnapshot();

		} );

		it ( 'should update the DOM when certificate settings change', async () => {

			await visitAdminPage( 'post-new.php', 'post_type=llms_certificate' );
			await toggleSidebarPanel( 'Settings' );

			const assertStylesMatch = async ( hint = null ) => {

				expect(
					await getElementStyles( '.is-root-container.block-editor-block-list__layout' )
				).toMatchSnapshot(
					{ 'background-image': expect.any( String ) },
					hint
				);

			};

			const testSize = async ( orientation, sizeName ) => {

				await clickElementByText( orientation, '.llms-certificate-doc-settings button' );
				await page.waitForTimeout( 500 );

				await assertStylesMatch( `Size: ${ sizeName } (${ orientation })` );

			};

			// Test registered sizes.
			const { sizes } = await page.evaluate( () => window.llms.certificates ),
				sizeKeys = Object.keys( sizes );

			const SIZE_SELECTOR = '#llms-certificate-control--size';
			await page.waitForSelector( SIZE_SELECTOR );

			// Loop through all available sizes.
			for ( let i = 0; i < sizeKeys.length; i++ ) {

				const { name } = sizes[ sizeKeys[ i ] ];

				await page.select( SIZE_SELECTOR, sizeKeys[ i ] );

				await testSize( 'Landscape', name );
				await testSize( 'Portrait', name );

			}

			// Custom size
			await page.select( SIZE_SELECTOR, 'CUSTOM' );
			await fillField( '#llms-certificate-control--size--custom-width', '5.5' );
			await fillField( '#llms-certificate-control--size--custom-height', '10' );
			await testSize( 'Landscape', 'CUSTOM' );
			await testSize( 'Portrait', 'CUSTOM' );


			// Margins.
			await fillField( '#llms-certificate-control--margin--top', '2.5' );
			await fillField( '#llms-certificate-control--margin--right', '3' );
			await fillField( '#llms-certificate-control--margin--bottom', '7' );
			await fillField( '#llms-certificate-control--margin--left', '10' );
			await assertStylesMatch( 'Margins' );

			// Background color chooser.
			const colors = await page.$$( '.components-circular-option-picker .components-circular-option-picker__option' );

			for ( let j = 0; j < colors.length; j++ ) {

				// The color of the button we're pressing.
				const expectedBgColor = await colors[ j ].evaluate( ( { style } ) => style['background-color'] );

				// Press it.
				await colors[ j ].click();
				await page.waitForTimeout( 500 );

				// Get the updated background color of the editor wrapper.
				const styles = await getElementStyles( '.editor-styles-wrapper' ),
					bgColor = styles['background-color'];

				expect( bgColor ).toBe( expectedBgColor );

			}

		} );

		// it ( 'should be able to award a certificate directly to a student', async () => {} );



		// it ( 'should create a certificate in the classic editor', async () => {

		// 	// Required due to WP core bug in 5.6.1 & later, see https://core.trac.wordpress.org/ticket/52440.
		// 	page.on( 'dialog', dialog => dialog.accept() );

		// 	await visitAdminPage( 'post.php', `post=${ certificateId }&action=edit` );
		// 	await visitPostPermalink();
		// 	expect( await getCertificateHTML() ).toMatchSnapshot();

		// } );


		// it ( 'should be able to view and delete an earned certificate from reporting screens', async () => {

		// 	// Create a user who will earn the certificate.
		// 	const
		// 		first     = 'Student',
		// 		last      = 'WithACert',
		// 		{ email } = await registerStudent( { first, last } );
		// 	await logoutUser();

		// 	await visitAdminPage( 'users.php', `s=${ encodeURIComponent( email ) }` );

		// 	await page.goto( await page.$eval( '#the-list tr:first-child span.llms-reporting a', el => `${ el.href }&stab=certificates` ) );

		// 	const reportingUrl = await page.url();

		// 	// Navigate to the certificate page.
		// 	await page.goto( await page.$eval( '#llms-gb-table-certificates td.actions a', el => el.href ) );

// expect( await getCertificateHTML() ).toMatchSnapshot();

		// 	await page.goto( reportingUrl );
		// 	// page.on( 'dialog', dialog => dialog.accept() ); // Uncomment when https://core.trac.wordpress.org/ticket/52440 is resolved.
		// 	await clickAndWait( '#llms_delete_cert' );

		// } );

	} );

	describe( 'Awarded', () => {

		it ( 'should award a certificate on user registration', async () => {

			await createCertificate( {
				title: 'Awarded on Registration',
				engagement: 'user_registration',
			} );

			const
				first = 'Maude',
				last  = 'Lebowski',
				user  = await registerStudent( { first, last } );

			await page.waitForTimeout( 1000 );

			// Certificate listed on the certs area of the dashboard.
			const selector = '.llms-sd-section.llms-my-certificates li.llms-certificate-loop-item a.llms-certificate';
			expect( await page.$eval( `${ selector } h4.llms-certificate-title`, el => el.textContent ) ).toBe( 'Awarded on Registration' );

			// Visit the certificate permalink.
			await clickAndWait( selector );
			const certUrl = await page.url();

			// View the cert.
			expect( await getCertificateHTML() ).toMatchSnapshot();

			// Logged out user cannot view.
			await logoutUser();
			await page.goto( certUrl );
			expect( await page.waitForSelector( 'body.error404' ) ).toBeTruthy();

			// Student can enable sharing.
			await loginStudent( user.email, user.pass );
			await page.goto( certUrl );

			// Enable sharing.
			await clickAndWait( 'button[name="llms_enable_cert_sharing"]' );

			// Logged out user can view.
			await logoutUser();
			await page.goto( certUrl );
			expect( await getCertificateHTML() ).toMatchSnapshot();
			await expect( await page.$( '#llms-print-certificate' ) ).toBeNull();

		} );

	} );

} );
