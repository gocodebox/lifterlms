import {
	clickAndWait,
	createUser,
	enrollStudent,
	getPostTitleTextContent,
	importCourse,
	logoutUser,
} from '@lifterlms/llms-e2e-test-utils';

import {
	createURL,
	loginUser,
} from '@wordpress/e2e-test-utils';

describe( 'CourseRestrictions', () => {

	let course = {},
		lessons = [];

	beforeAll( async () => {

		await importCourse( 'import-with-restrictions.json' );
		await clickAndWait( '.llms-builder-launcher a.llms-button-primary' );

		course = await page.evaluate( () => window.llms_builder.course );
		lessons = course.sections[0].lessons;

	} );

	describe( 'Enrolled users', () => {

		beforeAll( async () => {
			await enrollStudent( course.id, 4 );
			await logoutUser();
			await loginUser( 'restrictions@email.tld', 'password' );
		} );

		it ( 'should see enrolled user content on the course page', async () => {

			await page.goto( course.permalink );
			expect( await page.$eval( '.entry-content #enrolled-user-content', el => el.textContent ) ).toBe( 'Enrolled user content.' );

		} );

		it ( 'should be able to view a lesson with no restrictions', async () => {

			await page.goto( lessons[0].permalink ); // Lesson: "Regular".

			// On the right page.
			expect( await getPostTitleTextContent() ).toBe( 'Regular' );

			// Mark complete is visible.
			expect( await page.$eval( '#llms_mark_complete', el => el.textContent ) ).toBe( 'Mark Complete' );

		} );

		it ( 'should be redirected when accessing a lesson with unmet prerequisites', async () => {

			await page.goto( lessons[1].permalink ); // Lesson: "Has Prereq".

			// Redirected to the prerequisite lesson.
			expect( page.url() ).toBe( lessons[0].permalink );

			// Shown an error message.
			expect( await page.$eval( '.llms-notice.llms-error li', el => el.textContent ) ).toMatchStringWithQuotes( 'The lesson "Has Prereq" cannot be accessed until the required prerequisite "Regular" is completed.' );

		} );

		it ( 'should be able to access lessons with prerequisites when the prerequisite is complete', async () => {

			await page.goto( lessons[0].permalink ); // Lesson: "Regular".

			await clickAndWait( '#llms_mark_complete' );

			// Redirected to the next lesson (the one with the prereq).
			expect( await page.url() ).toBe( lessons[1].permalink );

			// On the right page.
			expect( await getPostTitleTextContent() ).toBe( 'Has Prereq' );

			// Mark complete is visible.
			expect( await page.$eval( '#llms_mark_complete', el => el.textContent ) ).toMatchStringWithQuotes( 'Mark Complete' );

		} );

		it ( 'should be redirected when accessing a lesson that is not available because of a drip delay', async () => {

			await page.goto( lessons[2].permalink ); // Lesson: "Has Drip".

			// Redirected to the course.
			expect( await page.url() ).toBe( course.permalink );

			// Shown an error message.
			expect( await page.$eval( '.llms-notice.llms-error li', el => el.textContent.replace( /[“”‘’]/g, '"' ).includes( 'The lesson "Has Drip" will be available on ' ) ) ).toBe( true );

		} );

		it ( 'should be able to view free lessons', async () => {

			await page.goto( lessons[3].permalink ); // Lesson: "Is Free".
			expect( await page.$eval( '.entry-content #free-lesson-content', el => el.textContent ) ).toMatchStringWithQuotes( 'Free lesson content.' );

		} );

		it ( 'should be able to access and take a quiz', async () => {

			await page.goto( lessons[4].permalink ); // Lesson: "Has Quiz"

			// On the right page.
			expect( await getPostTitleTextContent() ).toBe( 'Has Quiz' );

			// Take quiz button is visible.
			expect( await page.$eval( '#llms_start_quiz', el => el.textContent.trim() ) ).toBe( 'Take Quiz' );

			await clickAndWait( '#llms_start_quiz' );

			// On the quiz page.
			expect( await page.url() ).toBe( lessons[4].quiz.permalink );

			// Start button visible.
			expect( await page.$eval( '#llms_start_quiz', el => el.textContent.trim() ) ).toBe( 'Start Quiz' );

		} );

	} );

	describe( 'Non-enrolled users', () => {

		beforeAll( async () => {
			await logoutUser();
		} );

		it ( 'should see sales page content on the course page', async () => {

			await page.goto( course.permalink );
			expect( await page.$eval( '.entry-content #non-enrolled-user-content', el => el.textContent ) ).toBe( 'Non-enrolled user content.' );

		} );

		it ( 'should not be able to click syllabus links or view lesson URLs', async () => {

			await page.goto( course.permalink );
			expect( await page.$eval( '.llms-syllabus-wrapper .llms-lesson-preview a', el => el.href ) ).toBe( `${ course.permalink }#llms-lesson-locked` );

		} );

		it ( 'should be redirected to the course when accessing a lesson', async () => {

			await page.goto( lessons[0].permalink ); // Lesson: "Regular".

			// Redirected to the course.
			expect( await page.url() ).toBe( course.permalink );

			// Shown an error message.
			expect( await page.$eval( '.llms-notice.llms-error li', el => el.textContent ) ).toBe( 'You must enroll in this course to access course content.' );

		} );

		it ( 'should be able to view free lessons', async () => {

			await page.goto( lessons[3].permalink ); // Lesson: "Is Free".
			expect( await page.$eval( '.entry-content #free-lesson-content', el => el.textContent ) ).toBe( 'Free lesson content.' );

		} );

		it ( 'should not be able to access quizzes', async () => {

			await page.goto( lessons[4].quiz.permalink );

			// Redirected to dashboard.
			expect( await page.url() ).toBe( createURL( '/dashboard/my-courses/' ) );

			// Shown an error message.
			expect( await page.$eval( '.llms-notice.llms-error li', el => el.textContent ) ).toBe( 'You must be logged in to take quizzes.' );

		} );


	} );


} );
