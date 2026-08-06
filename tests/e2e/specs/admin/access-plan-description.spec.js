/**
 * Access plan description saving via course editor Save vs Save All Plans.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Create a published course with a free access plan.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @return {Promise<{courseId: number, planId: number}>}
 */
async function createCourseWithPlan( requestUtils ) {
	const suffix = Date.now();
	const course = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/courses',
		data: {
			title: `Access Plan Description ${ suffix }`,
			content: '<!-- wp:paragraph --><p>Course content.</p><!-- /wp:paragraph -->',
			status: 'publish',
		},
	} );

	const plan = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/access-plans',
		data: {
			title: 'Test Plan',
			post_id: course.id,
			price: 0,
			frequency: 0,
			access_expiration: 'lifetime',
		},
	} );

	return { courseId: course.id, planId: plan.id };
}

/**
 * Ensure the Access Plans metabox is open and visible in the block editor.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<void>}
 */
async function openAccessPlansMetabox( page ) {
	const metabox = page.locator( '#lifterlms-product' );
	await metabox.waitFor( { state: 'attached' } );

	// WP 6.7+ keeps classic meta boxes in a collapsible bottom pane.
	await page.evaluate( () => {
		window.wp.data.dispatch( 'core/preferences' ).set(
			'core/edit-post',
			'metaBoxesMainIsOpen',
			true
		);
	} );

	await expect( metabox ).toBeVisible();

	if ( await metabox.evaluate( ( el ) => el.classList.contains( 'closed' ) ) ) {
		await metabox.locator( '.postbox-header .handlediv, .postbox-header h2' ).first().click();
	}

	await expect( page.locator( '#llms-access-plans' ) ).toBeVisible();
}

/**
 * Open the first access plan accordion in the Product Options metabox.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<void>}
 */
async function openAccessPlanAccordion( page ) {
	await openAccessPlansMetabox( page );

	const plan = page.locator( '#llms-access-plans .llms-access-plan' ).filter( {
		hasNot: page.locator( '#llms-new-access-plan-model' ),
	} ).first();

	await plan.scrollIntoViewIfNeeded();

	if ( ! ( await plan.evaluate( ( el ) => el.classList.contains( 'opened' ) ) ) ) {
		await plan.locator( '.llms-collapsible-header' ).click();
	}

	await expect( plan.locator( '.llms-collapsible-body' ) ).toBeVisible();
}

/**
 * TinyMCE / textarea id for a plan's description editor.
 *
 * @param {number} planId Access plan ID.
 * @return {string}
 */
function planDescriptionEditorId( planId ) {
	return `_llms_plans_content_llms-access-plan-${ planId }`;
}

/**
 * Set the plan description via TinyMCE (or the underlying textarea).
 *
 * @param {import('@playwright/test').Page} page        Playwright page.
 * @param {number}                          planId      Access plan ID.
 * @param {string}                          description Description HTML/text.
 * @return {Promise<void>}
 */
async function setPlanDescription( page, planId, description ) {
	const editorId = planDescriptionEditorId( planId );
	await page.waitForFunction( ( id ) => {
		return (
			( typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get( id ) ) ||
			document.getElementById( id )
		);
	}, editorId );

	await page.evaluate(
		( { id, text } ) => {
			if ( typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get( id ) ) {
				window.tinyMCE.get( id ).setContent( text );
				window.tinyMCE.get( id ).save();
				return;
			}
			document.getElementById( id ).value = text;
		},
		{ id: editorId, text: description }
	);
}

/**
 * Read the plan description from the editor UI.
 *
 * @param {import('@playwright/test').Page} page   Playwright page.
 * @param {number}                          planId Access plan ID.
 * @return {Promise<string>}
 */
async function getPlanDescriptionFromEditor( page, planId ) {
	const editorId = planDescriptionEditorId( planId );
	return page.evaluate( ( id ) => {
		if ( typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get( id ) ) {
			return window.tinyMCE.get( id ).getContent( { format: 'text' } ).trim();
		}
		const el = document.getElementById( id );
		if ( ! el ) {
			return '';
		}
		return new DOMParser().parseFromString( el.value, 'text/html' ).body.textContent.trim();
	}, editorId );
}

/**
 * Dirty the post and click the block editor Save/Update control.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<void>}
 */
async function saveCoursePost( page ) {
	// Access plan fields live outside the block editor store, so dirty the post
	// with a harmless title tweak to enable Save.
	await page.evaluate( () => {
		const { select, dispatch } = window.wp.data;
		const title = select( 'core/editor' ).getEditedPostAttribute( 'title' ) || '';
		dispatch( 'core/editor' ).editPost( { title: `${ title } ` } );
	} );

	const savePromise = page.waitForResponse( ( response ) => {
		return (
			response.request().method() === 'POST' &&
			response.url().includes( 'admin-ajax.php' ) &&
			( response.request().postData() || '' ).includes( 'llms_update_access_plans' )
		);
	} );

	const topBar = page.getByRole( 'region', { name: 'Editor top bar' } );
	const saveButton = topBar.getByRole( 'button', { name: 'Save', exact: true } );
	const updateButton = topBar.getByRole( 'button', { name: 'Update', exact: true } );

	if ( await saveButton.isVisible() ) {
		await saveButton.click();
	} else {
		await updateButton.click();
	}

	await page
		.getByRole( 'button', { name: 'Dismiss this notice' } )
		.filter( { hasText: /updated|saved|published/i } )
		.waitFor();

	await savePromise;
}

test.describe( 'Admin/AccessPlanDescription', () => {
	test( 'persists plan description when saving the course post', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		const { courseId, planId } = await createCourseWithPlan( requestUtils );
		const description = `Course save description ${ Date.now() }`;

		await admin.editPost( courseId );
		await openAccessPlanAccordion( page );
		await setPlanDescription( page, planId, description );
		await saveCoursePost( page );

		await admin.editPost( courseId );
		await openAccessPlanAccordion( page );

		await expect.poll( async () => getPlanDescriptionFromEditor( page, planId ) ).toContain(
			description
		);
	} );

	test( 'persists plan description when clicking Save All Plans', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		const { courseId, planId } = await createCourseWithPlan( requestUtils );
		const description = `Save all plans description ${ Date.now() }`;

		await admin.editPost( courseId );
		await openAccessPlanAccordion( page );
		await setPlanDescription( page, planId, description );

		const savePromise = page.waitForResponse( ( response ) => {
			return (
				response.request().method() === 'POST' &&
				response.url().includes( 'admin-ajax.php' ) &&
				( response.request().postData() || '' ).includes( 'llms_update_access_plans' )
			);
		} );

		await page.locator( '#llms-save-access-plans' ).click();
		await savePromise;

		await admin.editPost( courseId );
		await openAccessPlanAccordion( page );

		await expect.poll( async () => getPlanDescriptionFromEditor( page, planId ) ).toContain(
			description
		);
	} );
} );
