/**
 * Course Builder — lesson content editing vs the block editor.
 *
 * Content added to a new lesson inside the builder must keep the builder's
 * TinyMCE editor across saves and reloads (see #3360). Once that content is
 * converted to blocks in the block editor, the builder must show the
 * "created outside of the Course Builder" notice instead of the editor so
 * it cannot overwrite the block markup.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

const LESSON_CONTENT = 'Adding some content here.';
const EDITOR_FIELD = '#llms-model-settings-field--content';
const NOTICE_FIELD = '#llms-model-settings-field--content-page-builder-notice';

/**
 * Create a published course with one (empty) section via the REST API.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @return {Promise<{course: Object, section: Object}>}
 */
async function createCourse( requestUtils ) {
	const stamp = Date.now();

	const course = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/courses',
		data: {
			title: `Builder Lesson Content Course ${ stamp }`,
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

	return { course, section };
}

/**
 * Set the builder's lesson content TinyMCE editor and commit the change to the model.
 *
 * The builder syncs the model on the TinyMCE `change` event, so fire it
 * explicitly rather than relying on typing/blur undo checkpoints.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {string}                          html Content HTML.
 * @return {Promise<void>}
 */
async function setBuilderLessonContent( page, html ) {
	await page.waitForFunction( () => window.tinymce && window.tinymce.get( 'content' ) );
	await page.evaluate( ( value ) => {
		const editor = window.tinymce.get( 'content' );
		editor.setContent( value );
		editor.fire( 'change' );
	}, html );
}

/**
 * Create a new lesson in the builder UI, add content via its editor, and save.
 *
 * @param {import('@playwright/test').Page} page    Playwright page.
 * @param {Object}                          course  Course REST object.
 * @param {Object}                          section Section REST object.
 * @return {Promise<number>} The saved lesson's post ID.
 */
async function createLessonWithContentInBuilder( page, course, section ) {
	await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }` );
	await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
	await page.locator( `#llms-section-${ section.id }` ).waitFor( { state: 'visible' } );

	// Select the section, then add a new lesson to it.
	await page.locator( `#llms-section-${ section.id } .llms-builder-header` ).click();
	await page.locator( '#llms-new-lesson' ).click();

	const tempLesson = page.locator( 'li[id^="llms-lesson-temp_"]' );
	await expect( tempLesson ).toBeVisible( { timeout: 10000 } );

	// Open the lesson editor panel.
	await tempLesson.locator( '.edit-lesson' ).click();
	await expect( page.locator( '#llms-editor-lesson' ) ).toBeVisible();

	// A blank lesson shows the content editor, not the outside-the-builder notice.
	await expect( page.locator( EDITOR_FIELD ) ).toBeVisible( { timeout: 10000 } );
	await expect( page.locator( NOTICE_FIELD ) ).toHaveCount( 0 );

	await setBuilderLessonContent( page, `<p>${ LESSON_CONTENT }</p>` );

	const saveBtn = page.locator( '#llms-save-button' );
	await expect( saveBtn ).toHaveAttribute( 'data-status', 'unsaved', { timeout: 5000 } );
	await saveBtn.click();
	await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved', { timeout: 15000 } );

	// The DOM keeps the temp id, but the row's WP edit link carries the real
	// post ID once the sync completes.
	const editLink = tempLesson.locator( 'a[href*="action=edit"]' ).first();
	await expect( editLink ).toBeVisible( { timeout: 10000 } );

	const href = await editLink.getAttribute( 'href' );
	return parseInt( new URL( href ).searchParams.get( 'post' ), 10 );
}

test.describe( 'Course Builder / Lesson Content', () => {

	test( 'content added in the builder keeps the builder editor after saving and reloading', async ( {
		page,
		requestUtils,
	} ) => {

		const { course, section } = await createCourse( requestUtils );
		const lessonId = await createLessonWithContentInBuilder( page, course, section );

		// The editor must survive the save response (the model flag flipping to
		// "no" here was bug #3360: the notice replaced the editor immediately).
		await expect( page.locator( EDITOR_FIELD ) ).toBeVisible();
		await expect( page.locator( NOTICE_FIELD ) ).toHaveCount( 0 );

		// And a full builder reload.
		await page.reload();
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await page.locator( `#llms-lesson-${ lessonId } .edit-lesson` ).click();
		await expect( page.locator( '#llms-editor-lesson' ) ).toBeVisible();
		await expect( page.locator( EDITOR_FIELD ) ).toBeVisible( { timeout: 10000 } );
		await expect( page.locator( NOTICE_FIELD ) ).toHaveCount( 0 );

		// Content persisted with the editor.
		await page.waitForFunction( () => window.tinymce && window.tinymce.get( 'content' ) );
		const editorContent = await page.evaluate(
			() => window.tinymce.get( 'content' ).getContent( { format: 'text' } ).trim()
		);
		expect( editorContent ).toContain( LESSON_CONTENT );

		const saved = await requestUtils.rest( {
			method: 'GET',
			path: `/llms/v1/lessons/${ lessonId }`,
			params: { context: 'edit' },
		} );
		expect( saved.content.raw ).toContain( LESSON_CONTENT );
	} );

	test( 'converting the lesson to blocks in the block editor replaces the builder editor with a notice', async ( {
		admin,
		page,
		requestUtils,
	} ) => {

		const { course, section } = await createCourse( requestUtils );
		const lessonId = await createLessonWithContentInBuilder( page, course, section );

		// Open the lesson in the block editor; the classic HTML loads as a freeform block.
		await admin.editPost( lessonId );
		await expect( page.locator( '.editor-header, .edit-post-header' ).first() ).toBeVisible();
		await page.waitForFunction( () => {
			return window.wp?.data?.select( 'core/block-editor' )
				?.getBlocks()
				.some( ( block ) => 'core/freeform' === block.name );
		} );

		// Convert the classic block to blocks. This mirrors what the block
		// toolbar's "Convert to blocks" button dispatches; the button's location
		// in the UI varies across WP versions so drive the same action directly.
		await page.evaluate( () => {
			const { select, dispatch } = window.wp.data;
			const freeform = select( 'core/block-editor' )
				.getBlocks()
				.find( ( block ) => 'core/freeform' === block.name );
			dispatch( 'core/block-editor' ).replaceBlocks(
				freeform.clientId,
				window.wp.blocks.rawHandler( {
					HTML: window.wp.blocks.getBlockContent( freeform ),
				} )
			);
		} );

		// Update the (published) lesson.
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

		// Content is now block markup.
		const saved = await requestUtils.rest( {
			method: 'GET',
			path: `/llms/v1/lessons/${ lessonId }`,
			params: { context: 'edit' },
		} );
		expect( saved.content.raw ).toContain( '<!-- wp:paragraph -->' );
		expect( saved.content.raw ).toContain( LESSON_CONTENT );

		// Back in the builder the lesson must show the notice, not the editor.
		await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }` );
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await page.locator( `#llms-lesson-${ lessonId } .edit-lesson` ).click();
		await expect( page.locator( '#llms-editor-lesson' ) ).toBeVisible();

		const notice = page.locator( NOTICE_FIELD );
		await expect( notice ).toBeVisible( { timeout: 10000 } );
		await expect( notice ).toContainText( 'created outside of the Course Builder' );
		await expect( page.locator( EDITOR_FIELD ) ).toHaveCount( 0 );
	} );

} );
