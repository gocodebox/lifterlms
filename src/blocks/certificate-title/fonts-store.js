/**
 * Retrieves a list of available font definitions.
 *
 * @since [version]
 *
 * @return {Object} An object of font definitions.
 */
export function getFonts() {
	return window.llms.certificates.fonts;
}

/**
 * Retrieves a single font definition by ID.
 *
 * @since [version]
 *
 * @param {string} id The font ID.
 * @return {?Object} Font definition object or `null` if not found.
 */
export function getFont( id ) {
	const fonts = getFonts();
	return fonts[ id ] || null;
}
