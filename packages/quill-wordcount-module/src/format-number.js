/**
 * Formats a number using the builtin {@see Intl.NumberFormat}.
 *
 * @since 2.0.0
 *
 * @param {number} number An integer, float, or numerical string.
 * @return {string} The formatted number string.
 */
export default function( number ) {
	return new Intl.NumberFormat().format( number );
}
