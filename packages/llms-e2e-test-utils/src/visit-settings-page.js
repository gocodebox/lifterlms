/**
 * External Dependencies.
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';
import { pickBy } from 'lodash';

/**
 * Visit a LifterLMS Settings Page on the admin panel
 *
 * @since 2.1.0
 * @since 2.1.2 Don't add null values to the query string.
 * @param {Object} args {
 *                      Settings page options.
 *     @type {string} tab     Settings page tab ID.
 *     @type {string} section Settings page section ID.
 * }
 * @return {Void}
 */
export async function visitSettingsPage( { tab = null, section = null } = {} ) {
	await visitAdminPage(
		'admin.php',
		new URLSearchParams(
			pickBy( { page: 'llms-settings', tab, section } )
		).toString()
	);
}
