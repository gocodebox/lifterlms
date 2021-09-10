// Internal dependencies.
import { clickAndWait } from './click-and-wait';
import { fillField } from './fill-field';

// External dependencies.
import url from 'url'; // eslint-disable-line no-unused-vars
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Asynchronously loop through an Object
 *
 * @since 1.0.0
 *
 * @param {Object}   obj      Object to loop through.
 * @param {Function} callback Callback function, will be passed to params `key` and `val`.
 * @return {void}
 */
const forEach = async ( obj, callback ) => {
	const keys = Object.keys( obj );
	for ( let i = 0; i < keys.length; i++ ) {
		await callback( keys[ i ], obj[ keys[ i ] ] );
	}
};

/**
 * Create a new user.
 *
 * @since 1.0.0
 * @since 2.2.0 Returns the WP_User ID in the return object.
 * @since 2.2.1 Options object is now optional.
 *
 * @param {Object} opts Hash of user information used to create the new user.
 * @return {Object} Object of created user data.
 */
export async function createUser( opts = {} ) {
	await visitAdminPage( 'user-new.php' );

	const login = `mock_${ Math.random().toString( 36 ).slice( 2 ) }`;
	opts = Object.assign(
		{
			user_login: login,
			email: `${ login }@mock.tld`,
			role: 'student',
			password: `${ Math.random()
				.toString( 36 )
				.slice( 2 ) }${ Math.random().toString( 36 ).slice( 2 ) }`,
		},
		opts
	);

	await forEach( opts, async ( key, val ) => {
		if ( 'role' === key ) {
			await page.select( '#role', val );
		} else if ( 'password' === key ) {
			await page.click( '.wp-generate-pw' );
			await fillField( '#pass1', val );
		} else {
			await fillField( `#${ key }`, val );
		}
	} );

	await clickAndWait( '#createusersub' );

	// Add the user's ID.
	const currUrl = new URL( await page.url() );
	opts.id = currUrl.searchParams.get( 'id' );

	return opts;
}
