/**
 * Course Builder — add quiz with multiple choice question and save.
 *
 * Guards against Maximum call stack size exceeded in _.deepClone when
 * restart_tracking_tree runs after a temp-id quiz sync (circular
 * Backbone parent/child refs in model attributes).
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

/**
 * Click a builder control that may sit in a nested scroll area under sticky chrome.
 *
 * @param {import('@playwright/test').Locator} locator Target control.
 */
async function clickBuilderAction( locator ) {
	await locator.waitFor( { state: 'visible' } );
	await locator.evaluate( ( el ) => {
		el.scrollIntoView( { block: 'center', inline: 'nearest' } );
	} );
	await locator.click( { force: true } );
}

test.describe( 'Course Builder / Quiz Save', () => {

	test( 'adding a quiz with a multiple choice question saves without stack overflow', async ( {
		page,
		requestUtils,
	} ) => {

		const stamp = Date.now();
		const errors = [];

		page.on( 'pageerror', ( err ) => {
			errors.push( err.message );
		} );

		const course = await requestUtils.rest( {
			method: 'POST',
			path: '/llms/v1/courses',
			data: {
				title: `Builder Quiz Course ${ stamp }`,
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
				title: `Lesson ${ stamp }`,
				content: 'lesson content',
				status: 'publish',
				parent_id: section.id,
				order: 1,
			},
		} );

		await page.goto( `/wp-admin/admin.php?page=llms-course-builder&course_id=${ course.id }` );
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await page.locator( `#llms-lesson-${ lesson.id }` ).waitFor( { state: 'visible' } );

		// Open quiz editor for the lesson.
		await page.locator( `#llms-lesson-${ lesson.id } .edit-quiz` ).click();
		await expect( page.locator( '#llms-editor-quiz' ) ).toBeVisible();

		// Create a new quiz.
		await page.locator( '#llms-new-quiz' ).click();
		await expect( page.locator( '#llms-show-question-bank' ) ).toBeVisible();

		// Open question bank, then add a multiple choice question.
		await clickBuilderAction( page.locator( '#llms-show-question-bank' ) );
		await clickBuilderAction( page.locator( '#llms-add-question--choice' ) );
		const question = page.locator( '#llms-quiz-questions .llms-question' ).first();
		await expect( question ).toBeVisible();

		// Question title uses Quill (bubble); choices use plain contenteditable.
		const titleEditor = question.locator( '.llms-headline .ql-editor' );
		await titleEditor.click();
		await titleEditor.fill( '1+1=' );
		await titleEditor.blur();

		const choices = question.locator( '.llms-question-choice .llms-editable-title' );
		await expect( choices ).toHaveCount( 2 );
		await setContentEditable( choices.nth( 0 ), '2' );
		await setContentEditable( choices.nth( 1 ), '1' );

		// Mark first choice correct if not already.
		const correctBox = question.locator( '.llms-question-choice input[name="correct"]' ).first();
		if ( ! await correctBox.isChecked() ) {
			await correctBox.check();
		}

		const saveBtn = page.locator( '#llms-save-button' );
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'unsaved', { timeout: 5000 } );
		await saveBtn.click();
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved', { timeout: 15000 } );

		// Stay saved — residual tracking dirt / thrown errors used to leave this flaky.
		await page.waitForTimeout( 2000 );
		await expect( saveBtn ).toHaveAttribute( 'data-status', 'saved' );

		const stackErrors = errors.filter( ( msg ) =>
			/Maximum call stack size exceeded/i.test( msg )
		);
		expect( stackErrors, `Unexpected stack overflow: ${ stackErrors.join( '; ' ) }` ).toHaveLength( 0 );

		// Reload — quiz and question should still be attached.
		await page.reload();
		await page.locator( '.wrap.lifterlms.llms-builder' ).waitFor( { state: 'visible' } );
		await page.locator( `#llms-lesson-${ lesson.id } .edit-quiz` ).click();
		const savedQuestion = page.locator( '#llms-editor-quiz #llms-quiz-questions .llms-question' ).first();
		await expect( savedQuestion ).toBeVisible( { timeout: 10000 } );
		// Title lives in the collapsed header; expand for choice text if needed.
		await savedQuestion.locator( '.expand--question' ).click( { force: true } );
		await expect( savedQuestion ).toContainText( '1+1=' );
		await expect( savedQuestion.locator( '.llms-question-choice' ).nth( 0 ) ).toContainText( '2' );
		await expect( savedQuestion.locator( '.llms-question-choice' ).nth( 1 ) ).toContainText( '1' );
	} );

} );
