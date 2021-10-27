import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';

import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Create and publish a new course
 *
 * @since 2.0.0
 *
 * @param {Object} args          Creation arguments.
 * @param {string} args.code     Coupon code (post title).
 * @param {string} args.discount The discount amount with either a leading `$` to specify dollar amount discounts or a trailing `%` for percentage discounts.
 * @return {string} The coupon code.
 */
export async function createCoupon( { code = null, discount = '10%' } ) {
	code = code || Math.random().toString( 36 ).slice( 2 );

	await visitAdminPage(
		'post-new.php',
		`post_type=llms_coupon&post_title=${ code }`
	);

	await page.select(
		'#_llms_discount_type',
		discount.includes( '%' ) ? 'percent' : 'dollar'
	);

	await fillField(
		'#_llms_coupon_amount',
		discount.replace( '%', '' ).replace( '$', '' )
	);

	await clickAndWait( '#publish' );

	return code;
}
