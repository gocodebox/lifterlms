/**
 * Retrieves the text-color to use to denote word count errors or warnings.
 *
 * @since [version]
 *
 * @param {number} wordCount The current word count in the Quill editor instance.
 * @param {Object} options   A `WordCountModuleOptions` options object.
 * @return {string} The CSS color code to use.
 */
export default function( wordCount, options ) {
	const { min, max, colorWarning, colorError } = options;

	let color = 'initial';

	if ( ( min && wordCount < min ) || ( max && wordCount > max ) ) {
		color = colorError;
	} else if ( max && wordCount >= max * 0.9 ) {
		color = colorWarning;
	}

	return color;
}
