/**
 * Course Builder — attach existing lesson, edit title/permalink, save.
 *
 * @since 10.1.1
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Set text on a contenteditable element and blur to commit via builder Editable handlers.
 *
 * @param {import('@playwright/test').Locator} locator Contenteditable locator.
 * @param {string}                             text    New text.
 */
async function setContentEditable( locator, text ) {
	await locator.click();
	await locator.evaluate( ( el, value ) => {
		el.focus();
		el.textContent = value;
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		el.blur();
	}, text );
}

test.describe( 'Course Builder / Attach Existing Lesson', () => {

	test( 'attaching an orphan lesson, editing title and permalink, then saving persists the lesson', async ( {
		page,
		requestUtils,
	} ) => {

		const stamp = Date.now();
		const orphanTitle = `Orphan ${ stamp }`;
		const newTitle = `Attached Lesson ${ stamp }`;
		const newSlug = `attached-lesson-${ stamp }`;

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Builder Attach Course ${ stamp }`,
				content: 'x',
				status: 'publish',
			},
		} );

		const section = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/sections',
			data: {
				title: 'Section 1',
				parent_id: course.id,
				order: 1,
			},
		} );

		// Orphan lesson (no parent) so the builder uses the attach action.
		const orphan = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/lessons',
			data: {
				title: orphanTitle,
				content: 'orphan content',
				status: 'publish',
				parent_id: 0,
			},
		} );

		await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }` );
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await page.locator( `#llms-section-${ section.id }` ).waitFor( { state: 'visible' } );

		// Open "Add Existing Lesson" and select the orphan.
		await page.locator( '#llms-existing-lesson' ).click();
		await page.locator( '.webui-popover .select2-container' ).click();
		await page.locator( '.select2-search__field' ).fill( orphanTitle );
		await page.locator( `.select2-results__option:has-text("${ orphanTitle }")` ).first().click();

		// Lesson should appear in the section and open the editor.
		const lessonItem = page.locator( `#llms-lesson-${ orphan.id }` );
		await expect( lessonItem ).toBeVisible( { timeout: 10000 } );

		const editor = page.locator( '#llms-editor-lesson' );
		await expect( editor ).toBeVisible();

		// Edit title (header contenteditable).
		await setContentEditable( editor.locator( '.llms-editable-title' ).first(), newTitle );

		// Edit permalink slug.
		await editor.locator( 'a[href="#llms-edit-slug"]' ).click();
		const slugInput = editor.locator( 'input.permalink' );
		await expect( slugInput ).toBeVisible();
		await slugInput.fill( newSlug );
		await slugInput.press( 'Enter' );
		// Give the async get_permalink call a moment; blur is enough to commit `name`.
		await page.waitForTimeout( 500 );

		// Wait for save button to reflect unsaved state, then save.
		const saveBtn = page.locator( '#llms-save-button' );
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'unsaved', { timeout: 5000 } );
		await saveBtn.click();
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved', { timeout: 15000 } );

		// Must stay saved — residual tracking dirt from quiz/relationship init used to
		// flip this back to "Save changes" after a successful attach/clone sync.
		await page.waitForTimeout( 3000 );
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved' );
		await expect( saveBtn ).toBeDisabled();

		// Still clean after another changes-check interval.
		await page.waitForTimeout( 1500 );
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved' );

		// Reload the builder — the attached lesson must still be present with the new title.
		await page.reload();
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await expect( page.locator( `#llms-lesson-${ orphan.id }` ) ).toBeVisible( { timeout: 10000 } );
		await expect( page.locator( `#llms-lesson-${ orphan.id }` ) ).toContainText( newTitle );

		// Confirm parent association was persisted server-side.
		const saved = await requestUtils.rest( {
			method: 'GET',
			path: `/llms/v1/lessons/${ orphan.id }`,
			params: { context: 'edit' },
		} );
		expect( saved.parent_id ).toBe( section.id );
		const title = typeof saved.title === 'object' ? saved.title.raw : saved.title;
		expect( title ).toBe( newTitle );
	} );

} );
