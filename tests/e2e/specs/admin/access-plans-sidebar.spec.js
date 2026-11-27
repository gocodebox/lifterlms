/**
 * Access plan fields when the Access Plans meta box is moved to the sidebar.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Save the current user's meta box order for the course screen.
 *
 * @param {import('@playwright/test').Page} page  Playwright page on the course editor.
 * @param {Object}                          order Meta box IDs keyed by context.
 * @return {Promise<void>}
 */
async function setMetaBoxOrder( page, order ) {
	const status = await page.evaluate( async ( boxes ) => {
		const body = new URLSearchParams( {
			action: 'meta-box-order',
			page: 'course',
			_ajax_nonce: document.querySelector( 'input[name="meta-box-order-nonce"]' ).value,
		} );
		Object.entries( boxes ).forEach( ( [ context, ids ] ) => body.append( `order[${ context }]`, ids ) );
		const response = await fetch( window.ajaxurl, { method: 'POST', body, credentials: 'same-origin' } );
		return response.status;
	}, order );
	expect( status ).toBe( 200 );
}

test.describe( 'Admin/AccessPlansSidebar', () => {
	test( 'shows a usable Price field when the meta box is in the sidebar', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: { title: `Sidebar plans ${ Date.now() }`, content: '', status: 'publish' },
		} );
		await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/access-plans',
			data: { title: 'Paid Plan', post_id: course.id, price: 99.99, frequency: 0, access_expiration: 'lifetime' },
		} );

		await admin.editPost( course.id );
		await page.locator( 'input[name="meta-box-order-nonce"]' ).waitFor( { state: 'attached' } );
		await setMetaBoxOrder( page, { side: 'lifterlms-product', normal: '', advanced: '' } );

		try {
			await admin.editPost( course.id );
			const metabox = page.locator( '#side-sortables #lifterlms-product' );
			await expect( metabox ).toBeVisible();
			if ( await metabox.evaluate( ( el ) => el.classList.contains( 'closed' ) ) ) {
				await metabox.locator( '.postbox-header h2' ).first().click();
			}

			const plan = metabox.locator( '.llms-access-plan:not(#llms-new-access-plan-model)' ).first();
			await plan.locator( '.llms-collapsible-header' ).click();

			const price = plan.locator( 'input.llms-plan-price' );
			await price.scrollIntoViewIfNeeded();
			await expect( price ).toHaveValue( '99.99' );

			// The field must be wide enough to see and type a price, not a sliver squeezed by the desktop grid.
			const box = await price.boundingBox();
			expect( box.width ).toBeGreaterThan( 100 );

			await price.fill( '49.99' );
			await expect( price ).toHaveValue( '49.99' );
		} finally {
			await setMetaBoxOrder( page, { side: '', normal: 'lifterlms-product', advanced: '' } );
		}
	} );
} );
