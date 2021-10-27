/**
 * Internal Dependencies.
 */
const { clickAndWait } = require( './click-and-wait' ),
	{ fillField } = require( './fill-field' ),
	{ visitPage } = require( './visit-page' );

/**
 * Login a user via the LifterLMS student dashboard.
 *
 * @since [version]
 *
 * @param {string} login User login or email address.
 * @param {string} pass  User password.
 * @return {void}
 */
export async function loginStudent( login, pass ) {
	await visitPage( 'dashboard' );

	await fillField( '#llms_login', login );
	await fillField( '#llms_password', pass );

	await clickAndWait( '#llms_login_button' );
}
