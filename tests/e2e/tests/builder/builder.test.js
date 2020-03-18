/**
 * Test the Setup Wizard
 *
 * @since 3.37.8
 * @since 3.37.14 Fix package references.
 */

import {
	clickAndWait,
	clickElementByText,
	fillField,
} from '@lifterlms/llms-e2e-test-utils';

import {
	pressKeyWithModifier,
	visitAdminPage,
} from '@wordpress/e2e-test-utils';

const addSection = async function( title ) {

	await page.click( '#llms-new-section' );
	await page.waitFor( 1000 );
	const selector = '#llms-sections li:last-child h2.llms-headline .llms-input';
	await fillField( selector, title );
	await page.$eval( selector, e => e.blur() );

}

// const addLesson = async function( title, section_title ) {

// 	if ( section_title ) {
// 		await clickElementByText( section_title, '#llms-sections > li h2.llms-headline .llms-input' );
// 	}

// 	await page.waitFor( 10000 );



// 	// await page.click( '#llms-new-lesson' );
// 	await page.waitFor( 1000 );

// 	// await clickElementByText( 'New Lesson', '#llms-sections li.llms-lesson h3.llms-headline .llms-input' );

// 	// await pressKeyWithModifier( 'primary', 'a' );
// 	// await page.keyboard.type( title );
// 	// await page.$eval( selector, e => e.blur() );

// }

const waitForSave = async function(){

	await page.waitForSelector( '#llms-save-button[data-status="unsaved"]' );
	await page.waitForSelector( '#llms-save-button[data-status="saved"]' );

}

let course_id = null;

describe( 'Builder', () => {

	it ( 'should create a new course, title it, publish it, and load the course builder.', async () => {

		const title = 'Test the Course Builder';

		// Launch the Setup Wizard.
		await visitAdminPage( 'post-new.php', 'post_type=course' );

		// Give it a Title.
		await fillField( 'textarea.editor-post-title__input', title );

		// Publish and post publish.
		await page.click( '.editor-post-publish-panel__toggle' );
		await page.waitForSelector( '.editor-post-publish-button' );
		await page.click( '.editor-post-publish-button' );

		// Close the post publish panel.
		await page.waitForSelector( '.editor-post-publish-panel__header-published' );
		await page.click( '.editor-post-publish-panel__header > button' );

		// Launch the builder.
		await clickAndWait( '.llms-builder-launcher .llms-button-primary' );

		expect( await page.$eval( '.llms-course-header h1.llms-headline .llms-input', el => el.textContent ) ).toBe( title );

		// Store course ID for future tests.
		course_id = await page.evaluate( () => {
			return window.llms_builder.course.id;
		} );

	} );

	it ( 'should create and save a new section.', async () => {

		const title = 'Test Section One';

		await visitAdminPage( 'admin.php', 'page=llms-course-builder&course_id=' + course_id );

		await addSection( title );

		await waitForSave();

		await page.reload();

		expect( await page.$eval( '#llms-sections li:last-child h2.llms-headline .llms-input', el => el.textContent ) ).toBe( title );

	} );

	// it ( 'should create and save a new lesson.', async() => {

	// 	const title = 'Test New Lesson One';

	// 	await visitAdminPage( 'admin.php', 'page=llms-course-builder&course_id=' + course_id );

	// 	await addLesson( title, 'Test Section One' );

	// } );

} );
