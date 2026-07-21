/**
 * Playwright E2E Test Utilities for LifterLMS
 *
 * @since 10.0.1
 */

/**
 * Visit a front-end page by path.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {string}                          path URL path relative to site root.
 * @return {Promise<void>}
 */
export async function visitPage( page, path ) {
	await page.goto( `/${ path }/` );
}

/**
 * Fill a form field by selector, clearing it first.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page     Playwright page.
 * @param {string}                          selector CSS selector.
 * @param {string}                          value    Value to type.
 * @return {Promise<void>}
 */
export async function fillField( page, selector, value ) {
	const locator = page.locator( selector );
	await locator.fill( value );
}

/**
 * Log out the current user by clearing browser cookies.
 *
 * This clears cookies client-side without hitting WordPress's logout endpoint,
 * so the server session remains valid for subsequent tests that need it.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @return {Promise<void>}
 */
export async function logoutUser( page ) {
	await page.context().clearCookies();
}

/**
 * Log in as a student user via the LifterLMS dashboard form.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page     Playwright page.
 * @param {string}                          email    Email address.
 * @param {string}                          password Password.
 * @return {Promise<void>}
 */
export async function loginStudent( page, email, password ) {
	await visitPage( page, 'dashboard' );
	await fillField( page, '#llms_login', email );
	await fillField( page, '#llms_password', password );
	await page.locator( '#llms_login_button' ).click();
	await page.waitForLoadState( 'networkidle' );
}

/**
 * Select a value from a Select2 dropdown.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page     Playwright page.
 * @param {string}                          selector CSS selector of the underlying select element.
 * @param {string}                          text     Visible text to search/select.
 * @return {Promise<void>}
 */
export async function select2Select( page, selector, text ) {
	const container = page.locator( selector ).locator( '..' );
	const select2Container = container.locator( '.select2-container' );
	await select2Container.click();

	const searchInput = page.locator( '.select2-search__field' );
	await searchInput.fill( text );
	await page.locator( `.select2-results__option:has-text("${ text }")` ).first().click();
}

/**
 * Register a new student via the LifterLMS open registration form.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {Object}                          opts Registration options.
 * @return {Promise<{email: string, pass: string}>}
 */
export async function registerStudent( page, {
	email = null,
	pass = null,
	first = 'Jamie',
	last = 'Doe',
	voucher = '',
} = {} ) {
	const theInt = Math.floor( Math.random() * ( 99990 - 10000 + 1 ) ) + 10000;
	email = email || `${ first }.${ last }+${ theInt }@e2e-tests.tld`;
	pass = pass || Math.random().toString( 36 ).slice( 2 ) + Math.random().toString( 36 ).slice( 2 );

	await logoutUser( page );
	await visitPage( page, 'dashboard' );

	await fillField( page, '#email_address', email );
	await fillField( page, '#email_address_confirm', email );
	await fillField( page, '#password', pass );
	await fillField( page, '#password_confirm', pass );
	await fillField( page, '#first_name', first );
	await fillField( page, '#last_name', last );
	await fillField( page, '#llms_billing_address_1', '1 Avenue Street' );
	await fillField( page, '#llms_billing_city', 'A City' );

	const stateSelect = page.locator( '#llms_billing_state' );
	if ( await stateSelect.count() > 0 ) {
		await select2Select( page, '#llms_billing_state', 'Texas' );
	}

	await fillField( page, '#llms_billing_zip', '52342' );

	if ( voucher ) {
		await page.locator( '#llms-voucher-toggle' ).click();
		await page.locator( '#llms_voucher' ).waitFor();
		await fillField( page, '#llms_voucher', voucher );
	}

	await page.locator( '#llms_register_person' ).click();
	await page.waitForLoadState( 'networkidle' );

	return { email, pass };
}

/**
 * Toggle the open registration setting.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page   Playwright page.
 * @param {boolean}                         enable Whether to enable or disable.
 * @return {Promise<void>}
 */
export async function toggleOpenRegistration( page, enable ) {
	await visitSettingsPage( page, { tab: 'account' } );
	const checkbox = page.locator( '#lifterlms_enable_myaccount_registration' );
	const isChecked = await checkbox.isChecked();

	if ( enable && ! isChecked ) {
		await checkbox.check();
		await page.locator( '.llms-save .llms-button-primary' ).click();
		await page.waitForLoadState( 'networkidle' );
	} else if ( ! enable && isChecked ) {
		await checkbox.uncheck();
		await page.locator( '.llms-save .llms-button-primary' ).click();
		await page.waitForLoadState( 'networkidle' );
	}
}

/**
 * Enroll the current (logged-in) student into a course via its free access plan.
 *
 * Visits the course sales page and submits the free enrollment form rendered in
 * the pricing table.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page       Playwright page.
 * @param {string}                          courseSlug Course post slug.
 * @return {Promise<void>}
 */
export async function enrollInFreeCourse( page, courseSlug ) {
	await page.goto( `/course/${ courseSlug }/` );
	await page.locator( '.llms-free-enroll-form button[type="submit"]' ).click();
	await page.waitForLoadState( 'networkidle' );
}

/**
 * Mark a lesson complete from the lesson's front-end page.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page       Playwright page.
 * @param {string}                          lessonSlug Lesson post slug.
 * @return {Promise<void>}
 */
export async function completeLesson( page, lessonSlug ) {
	await page.goto( `/lesson/${ lessonSlug }/` );
	await page.locator( '#llms_mark_complete' ).click();
	await page.waitForLoadState( 'networkidle' );
}

/**
 * Visit the LifterLMS settings page.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {Object}                          opts Options.
 * @param {string}                          opts.tab Tab slug.
 * @return {Promise<void>}
 */
export async function visitSettingsPage( page, { tab = 'general' } = {} ) {
	await page.goto( `/wp-admin/admin.php?page=llms-settings&tab=${ tab }` );
}

/**
 * Set a checkbox setting value.
 *
 * @since 10.0.1
 *
 * @param {import('@playwright/test').Page} page     Playwright page.
 * @param {string}                          selector CSS selector.
 * @param {boolean}                         checked  Desired state.
 * @return {Promise<void>}
 */
export async function setCheckboxSetting( page, selector, checked ) {
	const checkbox = page.locator( selector );
	if ( checked ) {
		await checkbox.check();
	} else {
		await checkbox.uncheck();
	}
	await page.locator( '.llms-save .llms-button-primary' ).click();
	await page.waitForLoadState( 'networkidle' );
}
