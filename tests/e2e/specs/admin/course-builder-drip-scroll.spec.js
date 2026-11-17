/**
 * Course Builder — lesson settings stay scrollable after changing drip method.
 *
 * An open Select2 dropdown pins scroll on #llms-editor-lesson. Choosing a drip
 * method re-renders that panel; the pin must be released or the panel jumps
 * back to the drip field. See #3376.
 *
 * Course Builder — the drip date and time are native date and time inputs that show and save the stored values. See #3492.
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Create a published course with one lesson.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @return {Promise<{course: Object, lesson: Object}>}
 */
async function createCourseWithLesson( requestUtils ) {
	const stamp = Date.now();

	const course = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/courses',
		data: {
			title: `Drip Scroll Course ${ stamp }`,
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

	const lesson = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/lessons',
		data: {
			title: `Drip Scroll Lesson ${ stamp }`,
			content: 'lesson content',
			status: 'publish',
			parent_id: section.id,
			order: 1,
		},
	} );

	return { course, lesson };
}

/**
 * Select2 scroll handlers still bound on an element.
 *
 * @param {import('@playwright/test').Locator} editor Lesson settings panel.
 * @return {Promise<number>}
 */
async function select2ScrollHandlers( editor ) {
	return editor.evaluate( ( el ) => {
		const events = window.jQuery._data( el, 'events' );
		const scroll = ( events && events.scroll ) || [];
		return scroll.filter( ( handler ) => ( handler.namespace || '' ).indexOf( 'select2' ) !== -1 ).length;
	} );
}

test.describe( 'Course Builder / Drip scroll', () => {

	test( 'lesson settings can scroll after changing drip method to a specific date', async ( {
		page,
		requestUtils,
	} ) => {

		const { course, lesson } = await createCourseWithLesson( requestUtils );

		await page.setViewportSize( { width: 1100, height: 560 } );
		await page.goto(
			`/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }#lesson:${ lesson.id }`
		);

		const editor = page.locator( '#llms-editor-lesson' );
		await expect( editor ).toBeVisible( { timeout: 15000 } );

		const drip = page.locator( '#llms-model-settings-field--drip-method' );
		await expect( drip ).toBeVisible();

		const scrollable = await editor.evaluate( ( el ) => el.scrollHeight > el.clientHeight + 20 );
		expect( scrollable ).toBe( true );

		await editor.evaluate( ( el ) => {
			el.scrollTop = el.scrollHeight;
		} );

		await drip.locator( '.select2-selection' ).click();
		await page.locator( '.select2-results__option', { hasText: 'On a specific date' } ).click();

		await expect( page.locator( '#llms-model-settings-field--date-available' ) ).toBeVisible();

		expect( await select2ScrollHandlers( editor ) ).toBe( 0 );

		const scrollTop = await editor.evaluate( ( el ) => {
			el.scrollTop = 0;
			return el.scrollTop;
		} );
		expect( scrollTop ).toBe( 0 );
	} );

	test( 'drip date and time use native inputs and keep the stored formats', async ( {
		page,
		requestUtils,
	} ) => {

		const { course, lesson } = await createCourseWithLesson( requestUtils );

		// The REST API stores the time as `H:i:s`.
		await requestUtils.rest( {
			method: 'POST',
			path: `/llms/v1/lessons/${ lesson.id }`,
			data: {
				drip_method: 'date',
				drip_date: '2030-12-12 14:30:00',
			},
		} );

		await page.goto(
			`/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }#lesson:${ lesson.id }`
		);

		const date = page.locator( '#llms-editor-lesson input[data-attribute="date_available"]' );
		const time = page.locator( '#llms-editor-lesson input[data-attribute="time_available"]' );
		await expect( date ).toBeVisible( { timeout: 15000 } );

		await expect( date ).toHaveAttribute( 'type', 'date' );
		await expect( time ).toHaveAttribute( 'type', 'time' );
		await expect( date ).toHaveValue( '2030-12-12' );
		await expect( time ).toHaveValue( '14:30' );

		const save = page.locator( '#llms-save-button' );
		await expect( save ).toHaveAttribute( 'data-status', 'saved' );

		await date.fill( '2031-01-15' );
		await date.blur();
		await time.fill( '17:15' );
		await time.blur();

		await expect( save ).toHaveAttribute( 'data-status', 'unsaved' );
		await save.click();
		await expect( save ).toHaveAttribute( 'data-status', 'saved' );

		const saved = await requestUtils.rest( { path: `/llms/v1/lessons/${ lesson.id }` } );
		expect( saved.drip_date ).toBe( '2031-01-15 17:15:00' );

		await expect( date ).toHaveValue( '2031-01-15' );
		await expect( time ).toHaveValue( '17:15' );
	} );

} );
