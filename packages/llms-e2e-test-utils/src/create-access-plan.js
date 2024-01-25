import { click } from './click';
import { clickAndWait } from './click-and-wait';
import { createCourse } from './create-course';
import { fillField } from './fill-field';

import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new course
 *
 * @since 2.0.0
 * @since 2.2.2 Use `waitForSelector()`` in favor of `waitFor()`.
 *
 * @param {Object} args        Creation arguments.
 * @param {number} args.postId Post ID of the plan's course or membership.
 * @param {number} args.price  Plan price.
 * @param {string} args.title  Plan title.
 * @return {string} The created plan's purchase link URL.
 */
export async function createAccessPlan( {
	postId = null,
	price = 0.0,
	title = 'Test Plan',
} ) {
	postId = postId || ( await createCourse() );

	await visitAdminPage( 'post.php', `post=${ postId }&action=edit` );

	await click( '#llms-new-access-plan' );

	const selector = '#llms-access-plans .llms-access-plan';
	await page.waitForSelector( selector );

	await fillField( `${ selector }:last-child input.llms-plan-title`, title );

	if ( price > 0 ) {
		await fillField(
			`${ selector }:last-child input.llms-plan-price`,
			price
		);
	} else {
		await click(
			`${ selector }:last-child input[type="checkbox"][data-controller-id="llms-plan-is-free"]`
		);
	}

	await page.click( '#llms-save-access-plans' );

	await new Promise(r => setTimeout(r, 1000));

	// Get the link to the specific access plan we just created, in case there's one already there
	await page.waitForXPath(
		"//*[@id='llms-access-plans']//*[contains(@class, 'llms-plan-title') and normalize-space(.)='" + title + "']/following-sibling::*[contains(@class, 'llms-plan-link')]",
		{ hidden: true }
	);

	const planLinkContainers = await page.$x( "//*[@id='llms-access-plans']//*[contains(@class, 'llms-plan-title') and normalize-space(.)='" + title + "']/following-sibling::*[contains(@class, 'llms-plan-link')]" );

	return await page.evaluate(
		( el ) => el.querySelector( 'a' ).href,
		planLinkContainers[0]
	);
}
