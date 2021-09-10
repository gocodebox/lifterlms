/**
 * Internal Dependencies.
 */
const { clickAndWait } = require( './click-and-wait' ),
	{ fillField } = require( './fill-field' ),
	{ visitPage } = require( './visit-page' );

export async function loginStudent( login, pass ) {
	await visitPage( 'dashboard' );

	await fillField( '#llms_login', login );
	await fillField( '#llms_password', pass );

	await clickAndWait( '#llms_login_button' );
}
