/**
 * Scan engagement send-volume pause, confirmation, and the standing admin notice.
 *
 * The e2e mu-plugin lowers `llms_engagement_send_warning_threshold` to 2.
 *
 * @since [version]
 */

import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.describe.configure( { mode: 'serial' } );

/**
 * REST call that includes the path in failures.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @param {Object}                                                       options     REST options.
 * @return {Promise<any>}
 */
async function rest( requestUtils, options ) {
	try {
		return await requestUtils.rest( options );
	} catch ( error ) {
		const detail = error && error.message ? error.message : JSON.stringify( error );
		throw new Error( `${ options.method || 'GET' } ${ options.path }: ${ detail }` );
	}
}

/**
 * Create a published course.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @param {string}                                                       title       Course title.
 * @return {Promise<number>}
 */
async function createCourse( requestUtils, title ) {
	const course = await rest( requestUtils, {
		method: 'POST',
		path: '/llms/v1/courses',
		data: {
			title,
			content: '<!-- wp:paragraph --><p>Course content.</p><!-- /wp:paragraph -->',
			status: 'publish',
		},
	} );

	return course.id;
}

/**
 * Enroll a new student and backdate that enrollment.
 *
 * @param {import('@wordpress/e2e-test-utils-playwright').RequestUtils} requestUtils Request utils.
 * @param {number}                                                       courseId    Course ID.
 * @param {string}                                                       suffix      Unique suffix.
 * @param {number}                                                       index       Student index.
 * @param {number}                                                       days        How far back to stamp the enrollment.
 * @return {Promise<void>}
 */
async function enrollBackdatedStudent( requestUtils, courseId, suffix, index, days ) {
	const student = await rest( requestUtils, {
		method: 'POST',
		path: '/llms/v1/students',
		data: {
			username: `idle${ suffix }${ index }`,
			email: `idle${ suffix }${ index }@example.com`,
			first_name: 'Idle',
			last_name: `Student ${ index }`,
		},
	} );

	await rest( requestUtils, {
		method: 'POST',
		path: `/llms/v1/students/${ student.id }/enrollments/${ courseId }`,
	} );

	await rest( requestUtils, {
		method: 'POST',
		path: '/llms-e2e/v1/backdate-enrollment',
		data: {
			user_id: student.id,
			post_id: courseId,
			days,
		},
	} );
}

/**
 * Choose a select2 post by title.
 *
 * @param {import('@playwright/test').Page} page     Playwright page.
 * @param {string}                          selector Underlying select selector.
 * @param {string}                          text     Option text to search for.
 * @return {Promise<void>}
 */
async function select2Post( page, selector, text ) {
	const container = page.locator( selector ).locator( 'xpath=..' ).locator( '.select2-container' );
	await container.click();
	await page.locator( '.select2-search__field' ).fill( text );
	const option = page.locator( '.select2-results__option', { hasText: text } ).first();
	await option.waitFor();
	await option.click();
}

/**
 * Set the hidden "Activity on or after" value the metabox saves.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {string}                          ymd  Date in Y-m-d.
 * @return {Promise<void>}
 */
async function setSinceDate( page, ymd ) {
	await page.locator( '#_llms_engagement_trigger_since_alt_datefield' ).evaluate( ( el, value ) => {
		el.value = value;
	}, ymd );
}

/**
 * Publish or update the engagement and return its post ID.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<number>}
 */
async function saveEngagement( page ) {
	await page.locator( '#publish' ).click();
	await page.waitForURL( /post=\d+/ );
	await page.locator( '#_llms_trigger_type' ).waitFor();
	return Number( new URL( page.url() ).searchParams.get( 'post' ) );
}

test.describe( 'Admin/EngagementSendVolume', () => {
	test( 'Saving over the threshold pauses sending until it is confirmed or the date shrinks the volume', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		const suffix = Date.now();
		const courseTitle = `Never started volume ${ suffix }`;
		const emailTitle = `Volume email ${ suffix }`;
		const engagementTitle = `Volume engagement ${ suffix }`;

		const courseId = await createCourse( requestUtils, courseTitle );
		const email = await rest( requestUtils,  {
			method: 'POST',
			path: '/llms-e2e/v1/email-template',
			data: { title: emailTitle },
		} );
		expect( email.id ).toBeGreaterThan( 0 );

		for ( let index = 1; index <= 3; index++ ) {
			await enrollBackdatedStudent( requestUtils, courseId, suffix, index, 30 );
		}

		await admin.visitAdminPage( 'post-new.php', 'post_type=llms_engagement' );
		await page.locator( '#title' ).fill( engagementTitle );

		await page.evaluate( () => {
			window.jQuery( '#_llms_trigger_type' ).val( 'course_never_started' ).trigger( 'change' );
			window.jQuery( '#_llms_engagement_type' ).val( 'email' ).trigger( 'change' );
		} );

		await page.locator( '#_llms_engagement_trigger_period' ).fill( '14' );
		await setSinceDate( page, '2020-01-01' );
		await select2Post( page, '#_faux_engagement_trigger_post_course', courseTitle );
		await select2Post( page, '#_llms_engagement', emailTitle );

		const engagementId = await saveEngagement( page );

		await expect( page.locator( '.notice-error', { hasText: 'sending has been disabled' } ) ).toBeVisible();
		await expect( page.locator( '.notice-error', { hasText: 'Sending is disabled for these engagements' } ) ).toBeVisible();

		await admin.visitAdminPage( 'edit.php', 'post_type=llms_engagement' );
		const row = page.locator( `#post-${ engagementId }` );
		await expect( row ).toContainText( 'Sending disabled: allow sending above the volume limit' );

		await admin.visitAdminPage( 'post.php', `post=${ engagementId }&action=edit` );
		await page.getByText( 'Allow sending above the volume limit', { exact: true } ).click();
		await saveEngagement( page );

		await expect( page.locator( '.notice-success', { hasText: 'has been re-enabled' } ) ).toBeVisible();
		await expect( page.locator( '#_llms_engagement_send_volume_confirmed' ) ).toBeChecked();

		await admin.visitAdminPage( 'edit.php', 'post_type=llms_engagement' );
		await expect( page.locator( `#post-${ engagementId }` ) ).not.toContainText( 'Sending disabled' );

		await admin.visitAdminPage( 'post.php', `post=${ engagementId }&action=edit` );
		const yesterday = new Date( Date.now() - 24 * 60 * 60 * 1000 ).toISOString().slice( 0, 10 );
		await setSinceDate( page, yesterday );
		await saveEngagement( page );

		await expect( page.locator( '.notice-error', { hasText: 'sending has been disabled' } ) ).toHaveCount( 0 );
		await expect( page.locator( '.notice-error', { hasText: engagementTitle } ) ).toHaveCount( 0 );
		await expect( page.locator( '#_llms_engagement_send_volume_confirmed' ) ).not.toBeChecked();

		await admin.visitAdminPage( 'edit.php', 'post_type=llms_engagement' );
		await expect( page.locator( `#post-${ engagementId }` ) ).not.toContainText( 'Sending disabled' );
	} );

	test( 'A later daily scan pauses an unconfirmed engagement and emails the site admin', async ( {
		admin,
		page,
		requestUtils,
	} ) => {
		const suffix = `${ Date.now() }b`;
		const courseTitle = `Scan volume course ${ suffix }`;
		const emailTitle = `Scan volume email ${ suffix }`;
		const engagementTitle = `Scan volume engagement ${ suffix }`;

		const courseId = await createCourse( requestUtils, courseTitle );
		await rest( requestUtils,  {
			method: 'POST',
			path: '/llms-e2e/v1/email-template',
			data: { title: emailTitle },
		} );

		await admin.visitAdminPage( 'post-new.php', 'post_type=llms_engagement' );
		await page.locator( '#title' ).fill( engagementTitle );
		await page.evaluate( () => {
			window.jQuery( '#_llms_trigger_type' ).val( 'course_never_started' ).trigger( 'change' );
			window.jQuery( '#_llms_engagement_type' ).val( 'email' ).trigger( 'change' );
		} );
		await page.locator( '#_llms_engagement_trigger_period' ).fill( '14' );

		const since = new Date( Date.now() - 90 * 24 * 60 * 60 * 1000 ).toISOString().slice( 0, 10 );
		await setSinceDate( page, since );
		await select2Post( page, '#_faux_engagement_trigger_post_course', courseTitle );
		await select2Post( page, '#_llms_engagement', emailTitle );

		const engagementId = await saveEngagement( page );
		await expect( page.locator( '.notice-error', { hasText: 'sending has been disabled' } ) ).toHaveCount( 0 );
		await expect( page.locator( '.notice-error', { hasText: engagementTitle } ) ).toHaveCount( 0 );

		const author = await rest( requestUtils,  {
			method: 'POST',
			path: '/llms-e2e/v1/engagement-author',
			data: { engagement_id: engagementId },
		} );

		for ( let index = 1; index <= 3; index++ ) {
			await enrollBackdatedStudent( requestUtils, courseId, suffix, index, 30 );
		}

		const firstScan = await rest( requestUtils,  {
			method: 'POST',
			path: '/llms-e2e/v1/run-engagement-scan',
		} );
		const firstMail = firstScan.mails.filter( ( mail ) => mail.message.includes( engagementTitle ) );
		expect( firstMail ).toHaveLength( 1 );
		expect( firstMail[ 0 ].to ).toContain( firstScan.admin_email );
		expect( firstMail[ 0 ].headers.join( '\n' ) ).toContain( author.email );

		const secondScan = await rest( requestUtils,  {
			method: 'POST',
			path: '/llms-e2e/v1/run-engagement-scan',
		} );
		expect( secondScan.mails.filter( ( mail ) => mail.message.includes( engagementTitle ) ) ).toHaveLength( 0 );

		await admin.visitAdminPage( 'index.php' );
		const notice = page.locator( '.notice-error', { hasText: 'Sending is disabled for these engagements' } );
		await expect( notice ).toContainText( engagementTitle );
		await expect( notice.locator( `a[href*="post=${ engagementId }"]` ) ).toHaveCount( 1 );

		await admin.visitAdminPage( 'edit.php', 'post_type=llms_engagement' );
		await expect( page.locator( `#post-${ engagementId }` ) ).toContainText(
			'Sending disabled: allow sending above the volume limit'
		);
	} );
} );
