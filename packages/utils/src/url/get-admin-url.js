import { untrailingSlashIt } from '../formatting';

/**
 * Retrieves the WordPress admin URL.
 *
 * This function relies on the presence of localized data from the LifterLMS plugin which is only
 * present on the WordPress admin panel. If used out of context a default partial path url, `/wp-admin`
 * will be returned.
 *
 * @since [version]
 *
 * @return {string} The WP Admin URL.
 */
export function getAdminUrl() {
	let { admin_url: url = '/wp-admin' } = window.llms || {};
	return untrailingSlashIt( url );
}
