import {
	insertBlock,
	switchUserToAdmin,
	trashAllPosts,
	visitAdminPage,
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
	publishPost,
	registerStudent,
	toggleOpenRegistration,
	toggleSidebarPanel,
	visitPostPermalink,
	wpVersionCompare,
} from '@lifterlms/llms-e2e-test-utils';


/**
 * Retrieves the HTML for the currently-viewed certificate.
 *
 * Modifies the DOM to replace IDs from html wrapper attributes with a mocked ID, "9999".
 *
 * Also replaces the "Awarded" date with a mocked date, "Octember 01, 9999".
 *
 * @since 6.0.0
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
	html = html.replace( /(post-)\d+( )/, '$1999$2' ); // ID classname.

	// Replace the awarded date with a mocked date.
	html = html.replace( /(">On ).+(<\/p>)/, '$1Octember 01, 9999$2' );

	// Strip the classname added by the various themes; 2022 theme doesn't include it and this makes it so we can have a single snapshot.
	html = html.replace( ' entry">', '">' );

	// Replace img src url (only on template v1).
	html = html.replace( /(src=")(http:\/\/.*)(\/wp-content)/, '$1$3' );

	return html;

}

/**
 * Adds various block attributes to block snapshots depending on the WP version.
 * 
 * WP < 5.8: Adds isStackedOnMobile attribute.
 *
 * WP < 6.0: Adds opacity attribute. 
 *
 * @since 6.10.0
 *
 * @param {Object[]} blocks Array of WP_Block objects.
 * @return {Object[]} Updated array.
 */
function backportBlockAttributes( blocks ) {

	// On 5.8 snapshots fail because isStackedOnMobile didn't exist.
	if ( wpVersionCompare( '5.9', '<' ) ) {
		blocks = blocks.map( ( block ) => {
			if ( 'core/columns' === block.name ) {
				block.attributes.isStackedOnMobile = false;
			}
			return block;
		} );
	}

	// On 5.9 and earlier snapshots fail because separator opacity didn't exist.
	if ( wpVersionCompare( '6.0', '<' ) ) {
		const backportSeparators = ( blocksList ) => {
			return blocksList.map( ( block ) => {
				if ( 'core/separator' === block.name ) {
					block.attributes.opacity = 'alpha-channel';
				} else if ( block.innerBlocks.length ) {
					block.innerBlocks = backportSeparators( block.innerBlocks );
				}
				return block;
			} );
		};
		blocks = backportSeparators( blocks );
	}

	return blocks;

}

/**
 * Retrieves an elements CSS inline css rules a JS object.
 *
 * @since 6.0.0
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

// This suite runs on WP Version 5.8 & later.
describeIf( wpVersionCompare( '5.8' ) )( 'Engagements/Certificates', () => {

	beforeAll( async () => {
		await switchUserToAdmin();
		await toggleOpenRegistration( true );
	} );

	afterAll( async () => {
		await switchUserToAdmin();
		await toggleOpenRegistration( false );
		// Ensure future tests don't get Certificate notifications.
		await trashAllPosts( 'llms_engagement' );
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

			expect( backportBlockAttributes( await getAllBlocks( false ) ) ).toMatchSnapshot();

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

			expect( backportBlockAttributes( await getAllBlocks( false ) ) ).toMatchSnapshot();

		} );

		it ( 'should update the DOM when certificate settings change', async () => {

			await visitAdminPage( 'post-new.php', 'post_type=llms_certificate' );
			await toggleSidebarPanel( 'Settings' );

			const assertStylesMatch = async () => {

				expect(
					await getElementStyles( '.is-root-container.block-editor-block-list__layout' )
				).toMatchSnapshot(
					{ 'background-image': expect.any( String ) },
				);

			};

			const testSize = async ( orientation, sizeName ) => {

				await clickElementByText( orientation, '.llms-certificate-doc-settings button' );
				await page.waitForTimeout( 500 );

				await assertStylesMatch();

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
			await assertStylesMatch();

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

	} );

	describe( 'Legacy', () => {

		it ( 'should be able to edit legacy certificates in the classic editor', async () => {

			// Find the bootstrapped post.
			await page.goto( `${ process.env.WP_BASE_URL}/wp-admin/edit.php?s=Template-V1&post_type=llms_my_certificate`  );
			const POST_ROW = '#the-list tr:first-child';

			// Post state label present.
			expect( await page.$eval( `${ POST_ROW } .post-state`, ( { textContent } ) => textContent ) ).toBe( 'Legacy' );

			// Load the classic editor.
			await clickAndWait( `${ POST_ROW } a.row-title` );

			// Confirm it's classic.
			expect( await page.$eval( 'input#title', ( { value } ) => value ) ).toBe( 'Template-V1' );

			// Load the cert on the frontend.
			await clickAndWait( '#sample-permalink a' );
			expect( await getCertificateHTML( 1 ) ).toMatchSnapshot();

		} );


		it ( 'should be able to migrate a legacy certificates to the block editor', async () => {

			// Find the bootstrapped post.
			await page.goto( `${ process.env.WP_BASE_URL}/wp-admin/edit.php?s=Template-V1&post_type=llms_my_certificate`  );
			const POST_ROW = '#the-list tr:first-child';

			// Migrate link.
			await page.hover( '#the-list tr:first-child' );
			await clickAndWait( `${ POST_ROW } .row-actions .llms-migrate-legacy-certificate a` );

			// For some reason clicking the toggle button (handled via insertBlock) is resulting in errors.
			await page.evaluate( () => wp.data.dispatch( 'core/edit-post' ).setIsInserterOpened( true ) );

			// The template isn't actually migrated until an edit is made, otherwise it retains it's initial HTML.
			await insertBlock( 'Paragraph' );
			await publishPost();

			await visitPostPermalink();
			expect( await getCertificateHTML() ).toMatchSnapshot();

		} );

	} );

	describe( 'Awarded', () => {

		// it ( 'should be able to award a certificate directly to a student', async () => {} );

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
