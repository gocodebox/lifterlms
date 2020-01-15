const { loginUser } = require( '@wordpress/e2e-test-utils' );

describe( 'Login', () => {

	beforeAll( async () => {
		await page.goto( 'https://test.myliftersite.com/wp-admin' );
	} );

	it ( 'should fail login', async () => {

		await loginUser( 'fake', 'fake' );
		await page.waitForSelector( '#login_error' );
		const err = await page.$( '#login_error' );
		expect( err ).toBeTruthy();

	} );

	it ( 'should login', async () => {

		await loginUser();
		const title = await page.$eval( '.wrap > h1', txt => txt.textContent );
		expect( title ).toBe( 'Dashboard' );

	}, 20000 );

} );
