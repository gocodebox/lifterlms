import { clickAndWait } from './click-and-wait';
import { fillField }    from './fill-field';
import { logoutUser }   from './logout-user';
import { visitPage }    from './visit-page';

/**
 * Register a new student via the LifterLMS Open Registration Page
 *
 * @since 2.1.2
 *
 * @param {String} args.email Optional. Email address. If not supplied one will be created from the first name and last name.
 * @param {String} args.pass  Optional. User password. If not supplied one will be automatically generated.
 * @param {String} args.first Optional. User's first name.
 * @param {String} args.last  Optional. User's last name.
 * @return {Object} {
 *     Object containing information about the newly created user.
 *
 *     @type {String} email User's email address.
 *     @type {String} pass  User's password.
 * }
 */
export async function registerStudent( { email = null, pass = null, first = 'Jamie', last = 'Doe' } = {} ) {

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

	await clickAndWait( '#llms_register_person' );

	return {
		email,
		pass,
	};

}
