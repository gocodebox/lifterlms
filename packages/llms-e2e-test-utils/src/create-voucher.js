import { click } from './click';
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';
import { select2Select } from './select2-select';

import {
	visitAdminPage,
} from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new course
 *
 * @since 2.2.1
 *
 * @param {Object} args {
 *     Creation arguments.
 *
 *     @type {String}  name       Voucher (post) title.
 *     @type {String}  course     Name of a course to add to the voucher.
 *     @type {String}  membership Name of a membership to add to the voucher.
 *     @type {Integer} codes      Number of codes to generate.
 *     @type {Integer} uses       Number of uses per code.
 * }
 * @return {String[]} Array of the generated voucher codes.
 */
export async function createVoucher( { name = 'A Voucher', course = 'LifterLMS Quickstart Course', membership = '', codes = 5, uses = 5 } = {} ) {

	await visitAdminPage( 'post-new.php', `post_type=llms_voucher&post_title=${ name }` );

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
	await page.waitFor( 1000 ); // Non-interactive tests aren't publishing without a delay, not sure why.

	return await page.$$eval( '#llms_voucher_tbody input[name="llms_voucher_code[]"', inputs => inputs.map( input => input.value ) );

}
