import { click } from './click';
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';
import { logoutUser } from './logout-user';
import { select2Select } from './select2-select';
import { visitPage } from './visit-page';

/**
 * Register a new student via the LifterLMS Open Registration Page
 *
 * @since 2.1.2
 * @since 2.2.1 Add `args.voucher` to enable voucher usage during registration.
 * @since 5.0.0-alpha.2 Add arguments for address fields.
 *
 * @param {Object} args          Function arguments object.
 * @param {string} args.email    Email address. If not supplied one will be created from the first name and last name.
 * @param {string} args.pass     User password. If not supplied one will be automatically generated.
 * @param {string} args.first    User's first name.
 * @param {string} args.last     User's last name.
 * @param {string} args.voucher  Voucher code to use during registration.
 * @param {string} args.address1 User's address line 1.
 * @param {string} args.address2 User's address line 2.
 * @param {string} args.city     User's city.
 * @param {string} args.country  User's country.
 * @param {string} args.state    User's state.
 * @param {string} args.postcode User's postcode.
 * @param {string} args.phone    User's phone.
 * @return {Object} {
 *     Object containing information about the newly created user.
 *
 *     @type {string} email User's email address.
 *     @type {string} pass  User's password.
 * }
 */
export async function registerStudent( {
	email = null,
	pass = null,
	first = 'Jamie',
	last = 'Doe',
	voucher = '',
	address1 = '1 Avenue Street',
	address2 = '',
	city = 'A City',
	country = 'United States',
	state = 'Texas',
	postcode = '52342',
	phone = '',
} = {} ) {
	const theInt = Math.floor( Math.random() * ( 99990 - 10000 + 1 ) ) + 10000;

	email = email || `${ first }.${ last }+${ theInt }@e2e-tests.tld`;
	pass =
		pass ||
		Math.random().toString( 36 ).slice( 2 ) +
			Math.random().toString( 36 ).slice( 2 );

	await logoutUser();
	await visitPage( 'dashboard' );

	await fillField( '#email_address', email );
	await fillField( '#email_address_confirm', email );
	await fillField( '#password', pass );
	await fillField( '#password_confirm', pass );
	await fillField( '#first_name', first );
	await fillField( '#last_name', last );

	if ( address1 ) {
		await fillField( '#llms_billing_address_1', address1 );
	}

	if ( address2 ) {
		await fillField( '#llms_billing_address_2', address2 );
	}

	if ( city ) {
		await fillField( '#llms_billing_city', city );
	}

	if ( country && 'United States' !== country ) {
		await select2Select( '#llms_billing_country', country );
	}

	if ( state ) {
		await select2Select( '#llms_billing_state', state );
	}

	if ( postcode ) {
		await fillField( '#llms_billing_zip', postcode );
	}

	if ( phone ) {
		await fillField( '#llms_phone', phone );
	}

	if ( voucher ) {
		await click( '#llms-voucher-toggle' );
		await page.waitForSelector( '#llms_voucher' );
		await fillField( '#llms_voucher', voucher );
	}

	await clickAndWait( '#llms_register_person' );

	return {
		email,
		pass,
	};
}
