const {
	visitAdminPage
} = require( '@wordpress/e2e-test-utils' );

const { clickAndWait } = require( './click-and-wait' ),
	  { fillField }    = require( './fill-field' );

/**
 * Asynchronously loop through an Object
 *
 * @since  3.37.8
 *
 * @param  {Object}    obj      Object to loop through.
 * @param  {Function}  callback Callback function, will be passed to params `key` and `val`.
 * @return {Void}
 */
const forEach = async ( obj, callback ) => {

	const keys = Object.keys( obj );
	for ( let i = 0; i < keys.length; i++ ) {
		await callback( keys[ i ], obj[ keys[ i ] ] );
	}

}

/**
 * Create a new user.
 *
 * @since  3.37.8
 *
 * @param  {Object} opts Hash of user information used to create the new user.
 * @return {Object}
 */
export async function createUser( opts ) {

	await visitAdminPage( 'user-new.php' );

	const login = `mock_${ Math.random().toString( 36 ).slice( 2 ) }`;
	opts = Object.assign( {
		user_login: login,
		email: `${login}@mock.tld`,
		role: 'student',
		password: `${ Math.random().toString( 36 ).slice( 2 ) }${ Math.random().toString( 36 ).slice( 2 ) }`
	}, opts );

	await forEach( opts, async ( key, val ) => {

		if ( 'role' === key ) {

			await page.select( '#role', val );

		} else if ( 'password' === key ) {

			await page.click( '.wp-generate-pw' );
			await fillField( '#pass1', val );

		} else {

			await fillField( `#${key}`, val );

		}

	} );

	await clickAndWait( '#createusersub' );

	return opts;

}
