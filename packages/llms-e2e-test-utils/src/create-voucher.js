import { click } from './click';
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';
import { select2Select } from './select2-select';

import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new course
 *
 * @since 2.2.1
 * @since 3.0.0 Use `waitForTimeout()` in favor of deprecated `waitFor()`.
 *
 * @param {Object} args            Creation arguments.
 * @param {string} args.name       Voucher (post) title.
 * @param {string} args.course     Name of a course to add to the voucher.
 * @param {string} args.membership Name of a membership to add to the voucher.
 * @param {number} args.codes      Number of codes to generate.
 * @param {number} args.uses       Number of uses per code.
 * @return {string[]} Array of the generated voucher codes.
 */
export async function createVoucher( {
	name = 'A Voucher',
	course = 'LifterLMS Quickstart Course',
	membership = '',
	codes = 5,
	uses = 5,
} = {} ) {
	await visitAdminPage(
		'post-new.php',
		`post_type=llms_voucher&post_title=${ name }`
	);

	if ( course ) {
		await select2Select( '#_llms_voucher_courses', course );
	}

	if ( membership ) {
		await select2Select( '#_llms_voucher_memberships', membership );
	}

	await fillField( '#llms_voucher_add_quantity', codes );
	await fillField( '#llms_voucher_add_uses', uses );

	await click( '#llms_voucher_add_codes' );

	await page.waitForSelector( '#llms_voucher_tbody tr' );

	await clickAndWait( '#publish' );
	await page.waitForTimeout( 1000 ); // Non-interactive tests aren't publishing without a delay, not sure why.

	return await page.$$eval(
		'#llms_voucher_tbody input[name="llms_voucher_code[]"',
		( inputs ) => inputs.map( ( { value } ) => value )
	);
}
