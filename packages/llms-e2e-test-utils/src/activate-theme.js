import { getWPVersion } from './get-wp-version';
import { activateTheme as wpActivateTheme } from '@wordpress/e2e-test-utils';

/**
 * Retrieves the default WP theme based on the WP core version.
 *
 * @since 3.3.0
 *
 * @return {string} Slug of the WP core theme.
 */
function getThemeByCoreVersion() {
	let theme;

	switch ( getWPVersion().split( '.' ).slice( 0, 2 ).join( '.' ) ) {
		case '5.5':
			theme = 'twentytwenty';
			break;

		case '5.6':
		case '5.7':
		case '5.8':
			theme = 'twentytwentyone';
			break;

		case '5.9':
		default:
			theme = 'twentytwentytwo';
			break;
	}

	return theme;
}

/**
 * Activates a theme.
 *
 * @since 3.3.0
 *
 * @param {?string} theme Accepts a theme slug. If not supplied, loads the default theme for the tested WP version.
 * @return {Promise} Promise that resolves when the theme is activated.
 */
export async function activateTheme( theme = null ) {
	theme = theme || getThemeByCoreVersion();
	return wpActivateTheme( theme );
}
