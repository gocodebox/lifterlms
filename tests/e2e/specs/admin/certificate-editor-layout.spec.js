/**
 * Certificate editor document sidebar layout (WP 7.0+)
 *
 * WP 7.0 raised default Gutenberg TextControl height to 40px. Inner-margin
 * labels must sit below those inputs, and the Sync awarded metabox must not
 * overflow the document sidebar.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Create a published certificate template and award it once so the Sync button renders.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @return {Promise<number>} Template post ID.
 */
async function createTemplateWithAward( requestUtils ) {
	const suffix = Date.now();
	const template = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/certificates',
		data: {
			title: `Certificate Layout ${ suffix }`,
			content: '<!-- wp:paragraph --><p>Certificate body.</p><!-- /wp:paragraph -->',
			status: 'publish',
		},
	} );

	const me = await requestUtils.rest( {
		method: 'GET',
		path: '/wp/v2/users/me',
	} );

	await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/awarded-certificates',
		data: {
			student_id: me.id,
			certificate_id: template.id,
		},
	} );

	return template.id;
}

/**
 * Open the Certificate Template document sidebar and ensure Settings is expanded.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').Editor} editor Editor utils.
 * @param {import('@playwright/test').Page}                       page   Playwright page.
 * @return {Promise<void>}
 */
async function openCertificateSettings( editor, page ) {
	await editor.openDocumentSettingsSidebar();

	const documentTab = page.getByRole( 'tab', { name: 'Certificate Template' } );
	if ( await documentTab.count() ) {
		await documentTab.click();
	}

	const settingsPanel = page.locator( '.llms-certificate-doc-settings' );
	await expect( settingsPanel ).toBeVisible();

	const toggle = settingsPanel.locator( '.components-panel__body-toggle' );
	if ( await toggle.count() ) {
		const expanded = await toggle.getAttribute( 'aria-expanded' );
		if ( expanded === 'false' ) {
			await toggle.click();
		}
	}
}

test.describe( 'Admin/CertificateEditorLayout', () => {

	test( 'Margin labels sit below inputs and the Sync panel fits the sidebar', async ( { admin, editor, page, requestUtils } ) => {
		await admin.visitAdminPage( '/' );

		const isWp70 = await page.evaluate( () => document.body.classList.contains( 'llms-wp-version-gte-70' ) );
		test.skip( ! isWp70, 'WP 7.0+ form-control sizing only' );

		const templateId = await createTemplateWithAward( requestUtils );
		await admin.editPost( templateId );

		await openCertificateSettings( editor, page );

		const topInput = page.locator( '#llms-certificate-control--margin--top' );
		await expect( topInput ).toBeVisible();

		const topLabel = page.locator( '.llms-certificate-margin-control' ).filter( {
			has: page.locator( '#llms-certificate-control--margin--top' ),
		} ).locator( '.llms-certificate-margin-control__label' );

		await expect( topLabel ).toHaveText( 'Top' );

		const overlap = await page.evaluate( () => {
			const sides = [ 'top', 'right', 'bottom', 'left' ];
			return sides.map( ( side ) => {
				const input = document.querySelector( `#llms-certificate-control--margin--${ side }` );
				const label = input?.closest( '.llms-certificate-margin-control' )
					?.querySelector( '.llms-certificate-margin-control__label' );
				if ( ! input || ! label ) {
					return { side, missing: true };
				}
				const inputBox = input.getBoundingClientRect();
				const labelBox = label.getBoundingClientRect();
				return {
					side,
					inputBottom: inputBox.bottom,
					labelTop: labelBox.top,
					gap: labelBox.top - inputBox.bottom,
					inputHeight: inputBox.height,
				};
			} );
		} );

		for ( const side of overlap ) {
			expect( side.missing, `${ side.side } margin control missing` ).toBeFalsy();
			expect( side.inputHeight, `${ side.side } input height` ).toBeGreaterThanOrEqual( 36 );
			expect( side.gap, `${ side.side } label overlaps input` ).toBeGreaterThanOrEqual( 0 );
		}

		const sequentialHelp = page.locator( '.llms-certificate-sequential-id-control .components-base-control__help' );
		await expect( sequentialHelp ).toBeVisible();

		const syncBox = page.locator( '#certificate_sync' );
		await expect( syncBox ).toBeVisible();
		await syncBox.scrollIntoViewIfNeeded();

		const syncSpacing = await page.evaluate( () => {
			const help = document.querySelector( '.llms-certificate-sequential-id-control .components-base-control__help' );
			const heading = document.querySelector( '#certificate_sync .postbox-header, #certificate_sync h2.hndle' );
			if ( ! help || ! heading ) {
				return null;
			}
			return heading.getBoundingClientRect().top - help.getBoundingClientRect().bottom;
		} );

		expect( syncSpacing ).not.toBeNull();
		expect( syncSpacing ).toBeGreaterThanOrEqual( 12 );

		const syncButton = syncBox.locator( 'a.sync-action' );
		await expect( syncButton ).toBeVisible();

		const sidebarFit = await page.evaluate( () => {
			const sidebar = document.querySelector( '.interface-complementary-area, .editor-sidebar, .edit-post-sidebar' );
			const button = document.querySelector( '#certificate_sync a.sync-action' );
			if ( ! sidebar || ! button ) {
				return null;
			}
			const sidebarBox = sidebar.getBoundingClientRect();
			const buttonBox = button.getBoundingClientRect();
			return {
				scrollOverflow: sidebar.scrollWidth - sidebar.clientWidth,
				buttonOverflow: buttonBox.right - sidebarBox.right,
			};
		} );

		expect( sidebarFit ).not.toBeNull();
		expect( sidebarFit.scrollOverflow ).toBeLessThanOrEqual( 1 );
		expect( sidebarFit.buttonOverflow ).toBeLessThanOrEqual( 1 );
	} );

} );
