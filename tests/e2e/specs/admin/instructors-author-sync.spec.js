/**
 * The block editor's Author panel follows the primary instructor set in the Instructors tab.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Open the Instructors tab of the Course Options meta box and wait for its rows.
 *
 * @param {import('@playwright/test').Page} page  Playwright page.
 * @param {number}                          count Number of instructor rows to wait for.
 * @return {Promise<import('@playwright/test').Locator>} The instructor rows.
 */
async function openInstructorsTab( page, count ) {
	const metabox = page.locator( '#lifterlms-course-options' );
	await metabox.waitFor( { state: 'attached' } );

	// WP 6.7+ keeps classic meta boxes in a collapsible bottom pane.
	await page.evaluate( () => {
		window.wp.data
			.dispatch( 'core/preferences' )
			.set( 'core/edit-post', 'metaBoxesMainIsOpen', true );
	} );

	await metabox
		.locator( '.llms-nav-item', { hasText: 'Instructors' } )
		.locator( 'a, span' )
		.first()
		.click();

	const rows = metabox.locator(
		'._llms_instructors_data .llms-repeater-rows .llms-repeater-row'
	);
	await expect( rows ).toHaveCount( count );
	return rows;
}

/**
 * Drag an instructor row by its handle above the first row and wait for the instructors to save.
 *
 * @param {import('@playwright/test').Page}    page Playwright page.
 * @param {import('@playwright/test').Locator} rows The instructor rows.
 * @param {number}                             from Zero-based index of the row to move to the top.
 * @return {Promise<void>}
 */
async function dragToTop( page, rows, from ) {
	const handle = rows.nth( from ).locator( '.llms-drag-handle' );
	await handle.scrollIntoViewIfNeeded();
	const start = await handle.boundingBox();
	const target = await rows.first().locator( 'header' ).boundingBox();

	await page.mouse.move(
		start.x + start.width / 2,
		start.y + start.height / 2
	);
	await page.mouse.down();
	await page.mouse.move( start.x + start.width / 2, target.y, { steps: 10 } );
	await page.mouse.move( start.x + start.width / 2, target.y - 10, {
		steps: 2,
	} );
	await page.mouse.up();

	await expect(
		page.locator( '._llms_instructors_data.processing' )
	).toHaveCount( 0 );
}

/**
 * Get the author in the block editor store.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<number>} The author user ID.
 */
function getEditorAuthor( page ) {
	return page.evaluate( () =>
		window.wp.data
			.select( 'core/editor' )
			.getEditedPostAttribute( 'author' )
	);
}

test.describe( 'Admin/InstructorsAuthorSync', () => {
	const ids = [];
	let courseId;

	test.beforeAll( async ( { requestUtils } ) => {
		const suffix = Date.now();
		for ( const name of [ 'first', 'second' ] ) {
			const user = await requestUtils.createUser( {
				username: `instructor${ name }${ suffix }`,
				email: `instructor${ name }${ suffix }@example.com`,
				firstName: name,
				lastName: 'Instructor',
				password: 'password',
				roles: [ 'author' ],
			} );
			ids.push( user.id );
		}

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Instructors Author Sync ${ suffix }`,
				content:
					'<!-- wp:paragraph --><p>Course content.</p><!-- /wp:paragraph -->',
				status: 'publish',
				instructors: ids,
			},
		} );
		courseId = course.id;
	} );

	test.afterAll( async ( { requestUtils } ) => {
		for ( const id of ids ) {
			await requestUtils.rest( {
				method: 'DELETE',
				path: `/wp/v2/users/${ id }`,
				params: { force: true, reassign: 1 },
			} );
		}
	} );

	test( 'moving an instructor to the top updates the Author panel and keeps every instructor', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		await admin.editPost( courseId );
		let rows = await openInstructorsTab( page, 2 );
		expect( await getEditorAuthor( page ) ).toBe( ids[ 0 ] );

		await dragToTop( page, rows, 1 );
		await expect.poll( () => getEditorAuthor( page ) ).toBe( ids[ 1 ] );

		const topBar = page.getByRole( 'region', { name: 'Editor top bar' } );
		const saveButton = topBar.getByRole( 'button', {
			name: 'Save',
			exact: true,
		} );
		await ( ( await saveButton.isVisible() )
			? saveButton
			: topBar.getByRole( 'button', { name: 'Update', exact: true } )
		).click();
		await page.waitForFunction( () => {
			const { select } = window.wp.data;
			return (
				! select( 'core/editor' ).isSavingPost() &&
				! select( 'core/edit-post' ).isSavingMetaBoxes() &&
				! select( 'core/editor' ).isEditedPostDirty()
			);
		} );

		const course = await requestUtils.rest( {
			path: `/llms/v1/courses/${ courseId }`,
		} );
		expect( course.instructors ).toEqual( [ ids[ 1 ], ids[ 0 ] ] );

		await admin.editPost( courseId );
		rows = await openInstructorsTab( page, 2 );
		expect( await getEditorAuthor( page ) ).toBe( ids[ 1 ] );
	} );
} );
