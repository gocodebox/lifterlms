import {
	clickAndWait,
	clickElementByText,
	fillField,
	publishPost,
} from '@lifterlms/llms-e2e-test-utils';

import {
	createNewPost,
	pressKeyWithModifier,
	visitAdminPage,
} from '@wordpress/e2e-test-utils';

const addSection = async function( title ) {

	await page.click( '#llms-new-section' );
	await page.waitForTimeout( 1000 );
	const selector = '#llms-sections li:last-child h2.llms-headline .llms-input';
	await fillField( selector, title );
	await page.$eval( selector, e => e.blur() );

}

// const addLesson = async function( title, section_title ) {

// 	if ( section_title ) {
// 		await clickElementByText( section_title, '#llms-sections > li h2.llms-headline .llms-input' );
// 	}

// 	await page.waitForTimout( 10000 );



// 	// await page.click( '#llms-new-lesson' );
// 	await page.waitForTimout( 1000 );

// 	// await clickElementByText( 'New Lesson', '#llms-sections li.llms-lesson h3.llms-headline .llms-input' );

// 	// await pressKeyWithModifier( 'primary', 'a' );
// 	// await page.keyboard.type( title );
// 	// await page.$eval( selector, e => e.blur() );

// }

const waitForSave = async function(){

	await page.waitForSelector( '#llms-save-button[data-status="unsaved"]' );
	await page.waitForSelector( '#llms-save-button[data-status="saved"]' );

}

let courseId = null;

async function getCourseId() {

	if ( ! courseId ) {

		await createNewPost( {
			title: 'Test Course Builder',
			postType: 'course',
		} );

		await publishPost();

		courseId = await page.evaluate( () => wp.data.select( 'core/editor' ).getCurrentPostId() );

	}

	return courseId;

}

describe( 'Builder', () => {

	beforeEach( async () => {
		await getCourseId();
	} );

	it ( 'should load the course builder from the WP editor metabox.', async () => {

		// Launch the builder.
		await clickAndWait( '.llms-builder-launcher .llms-button-primary' );

		expect( await page.$eval( '.llms-course-header h1.llms-headline .llms-input', el => el.textContent ) ).toBe( 'Test Course Builder' );

	} );

	it ( 'should create and save a new section.', async () => {

		const title = 'Test Section One';

		await visitAdminPage( 'admin.php', 'page=llms-course-builder&course_id=' + courseId );

		await addSection( title );

		await waitForSave();

		await page.reload();

		expect( await page.$eval( '#llms-sections li:last-child h2.llms-headline .llms-input', el => el.textContent ) ).toBe( title );

	} );

	// it ( 'should create and save a new lesson.', async() => {

	// 	const title = 'Test New Lesson One';

	// 	await visitAdminPage( 'admin.php', 'page=llms-course-builder&courseId=' + courseId );

	// 	await addLesson( title, 'Test Section One' );

	// } );

} );
