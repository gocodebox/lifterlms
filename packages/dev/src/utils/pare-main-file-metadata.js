const { readFileSync } = require( 'fs' );

/**
 * Parses WordPress project metadata from a file header comment.
 *
 * @since [version]
 *
 * @return {Object} A key/value object containing the metadata found within the file.
 */
module.exports = ( file ) => {

	const contents = readFileSync( file ).toString(),
		regex = new RegExp( / \* (?<key>[A-Z][A-Za-z ]+)\: (?<val>.*)\n/g, 'g' );

	let matches, metas = {};
	while ( matches = regex.exec( contents ) ) {
		metas[ matches.groups.key ] = matches.groups.val;
	}

	return metas;

}
