/**
 * Course Builder — deep links from reporting / hash open the editor panel.
 *
 * Quiz ID links on LifterLMS > Reporting > Quizzes (and lesson builder links)
 * include a `#lesson:{id}:quiz` hash. The builder must expand the section and
 * open the matching settings panel, not just load the course outline.
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

/**
 * Create a published course with one lesson and an attached quiz.
 *
 * @param {import('@playwright/test').Page}                             page         Playwright page.
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @return {Promise<{course: Object, lesson: Object, quizTitle: string}>}
 */
async function createCourseWithQuiz( page, requestUtils ) {
	const stamp = Date.now();
	const quizTitle = `Deep Link Quiz ${ stamp }`;

	const course = await requestUtils.rest( {
		method: 'POST',
		path: '/llms/v1/courses',
		data: {
			title: `Deep Link Course ${ stamp }`,
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
			title: `Deep Link Lesson ${ stamp }`,
			content: 'lesson content',
			status: 'publish',
			parent_id: section.id,
			order: 1,
		},
	} );

	await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }` );
	await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
	await page.locator( `#llms-lesson-${ lesson.id }` ).waitFor( { state: 'visible' } );

	await page.locator( `#llms-lesson-${ lesson.id } .edit-quiz` ).click();
	await expect( page.locator( '#llms-editor-quiz' ) ).toBeVisible();
	await page.locator( '#llms-new-quiz' ).click();
	await expect( page.locator( '#llms-quiz-questions' ) ).toBeVisible();

	await setContentEditable(
		page.locator( '#llms-editor-quiz .llms-editable-title' ).first(),
		quizTitle
	);

	const saveBtn = page.locator( '#llms-save-button' );
	await expect( saveBtn ).toHaveAttribute( 'data-status', 'unsaved', { timeout: 5000 } );
	await saveBtn.click();
	await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved', { timeout: 15000 } );

	return { course, lesson, quizTitle };
}

test.describe( 'Course Builder / Deep Links', () => {

	test( 'reporting quiz ID link opens the quiz settings panel', async ( {
		page,
		requestUtils,
	} ) => {

		const { lesson, quizTitle } = await createCourseWithQuiz( page, requestUtils );

		await page.goto( '/wp-admin/admin.php?page=llms-reporting&tab=quizzes' );
		const search = page.locator( '#quizzes-search-input' );
		await search.click();
		await search.pressSequentially( quizTitle, { delay: 20 } );

		const idLink = page.locator( `td.id a[href*="#lesson:${ lesson.id }:quiz"]` );
		await expect( idLink ).toBeVisible( { timeout: 10000 } );
		await idLink.click();

		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await expect( page.locator( '#llms-editor-quiz.active' ) ).toBeVisible( { timeout: 15000 } );
		await expect( page.locator( '#llms-quiz-settings-fields' ) ).toBeVisible();
		await expect( page.locator( '#llms-editor-quiz .llms-editable-title' ).first() ).toContainText( quizTitle );
	} );

	test( 'lesson hash opens the lesson settings panel', async ( {
		page,
		requestUtils,
	} ) => {

		const stamp = Date.now();

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Lesson Hash Course ${ stamp }`,
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
				title: `Lesson Hash ${ stamp }`,
				content: 'lesson content',
				status: 'publish',
				parent_id: section.id,
				order: 1,
			},
		} );

		await page.goto(
			`/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }#lesson:${ lesson.id }`
		);
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await expect( page.locator( '#llms-editor-lesson.active' ) ).toBeVisible( { timeout: 15000 } );
		await expect( page.locator( '#llms-editor-lesson .llms-editable-title' ).first() ).toContainText(
			`Lesson Hash ${ stamp }`
		);
	} );

} );
