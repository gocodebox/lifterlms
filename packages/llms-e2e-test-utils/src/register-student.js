import { click }         from './click';
import { clickAndWait }  from './click-and-wait';
import { fillField }     from './fill-field';
import { logoutUser }    from './logout-user';
import { select2Select } from './select2-select';
import { visitPage }     from './visit-page';

/**
 * Register a new student via the LifterLMS Open Registration Page
 *
 * @since 2.1.2
 * @since 2.2.1 Add `args.voucher` to enable voucher usage during registration.
 * @since [version] Add arguments for address fields.
 *
 * @param {String} args.email   Optional. Email address. If not supplied one will be created from the first name and last name.
 * @param {String} args.pass    Optional. User password. If not supplied one will be automatically generated.
 * @param {String} args.first   Optional. User's first name.
 * @param {String} args.last    Optional. User's last name.
 * @param {String} args.voucher Optional. Voucher code to use during registration.
 * @param {String} address1     Optional. User's address line 1.
 * @param {String} address2     Optional. User's address line 2.
 * @param {String} city         Optional. User's city.
 * @param {String} country      Optional. User's country.
 * @param {String} state        Optional. User's state.
 * @param {String} postcode     Optional. User's postcode.
 * @param {String} phone        Optional. User's phone.
 * @return {Object} {
 *     Object containing information about the newly created user.
 *
 *     @type {String} email User's email address.
 *     @type {String} pass  User's password.
 * }
 */
export async function registerStudent(
		{
			email    = null,
			pass     = null,
			first    = 'Jamie',
			last     = 'Doe',
			voucher  = '',
			address1 = '1 Avenue Street',
			address2 = '',
			city     = 'A City',
			country  = 'United States',
			state    = 'Texas',
			postcode = '52342',
			phone    = '',
		} = {}
	) {

	const the_int = Math.floor( Math.random() * ( 99990 - 10000 + 1 ) ) + 10000;

	email = email || `${ first }.${ last }+${ the_int }@e2e-tests.tld`,
	pass  = pass || Math.random().toString( 36 ).slice( 2 ) + Math.random().toString( 36 ).slice( 2 );

	await logoutUser();
	await visitPage( 'dashboard' );

	await fillField( '#email_address', email );
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
