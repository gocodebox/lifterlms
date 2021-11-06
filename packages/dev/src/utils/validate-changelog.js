require( 'url' );

const
	ChangelogEntry = require( './changelog-entry' ),
	chalk = require( 'chalk' ),
	getChangelogOptions = require( './get-changelog-options' );

/**
 * Highlights text depending on the formatting request
 *
 * When formatting is disabled, the text is wrapped in quotes.
 *
 * When formatting is enabled, the text will be quoted and emboldened.
 *
 * @since 0.0.1
 *
 * @param {string}  text       The text to highlight.
 * @param {boolean} formatting Whether or not rich formatting should be used.
 * @return {string} The highlighted text.
 */
function highlight( text, formatting = true ) {
	text = formatting ? chalk.bold( text ) : text;
	return `"${ text }"`;
}

/**
 * Determines if an attribution string is valid.
 *
 * Attributions are valid in the following formats:
 *   + GitHub username reference: @thomasplevy
 *   + Markdown link: [Jeffrey Lebowski](https://elduderino.geocites.com/)
 *
 * @since 0.0.1
 *
 * @param {string} attr User-submitted attribution string.
 * @return {boolean} Returns `true` if the attribution string is valid, otherwise `false`.
 */
function isAttributionValid( attr ) {
	attr = attr.toString();

	const firstChar = attr.charAt( 0 );

	// GitHub username.
	if ( '@' === firstChar ) {
		return true;
	}

	const
		match = attr.match( /\[[^\]]*\]\(([^)]*)\)*/ );

	if ( ! match ) {
		return false;
	}

	try {
		new URL( match[ 1 ] );
		return true;
	} catch ( e ) {}

	return false;
}

/**
 * Determine if a supplied link is valid
 *
 * Links are valid in the following formats:
 *   + GitHub issue reference in the current repo: #12345
 *   + GitHub issue reference to another repo: organization/repository#12345
 *
 * @since 0.0.1
 *
 * @param {string} link User-submitted link string.
 * @return {boolean} Returns `true` if the link is valid and false otherwise.
 */
function isLinkValid( link ) {
	// Force values to a string.
	link = link.toString();

	const isValidHash = ( hash ) => ! isNaN( parseInt( hash.slice( 1 ) ) );

	let valid = false;

	// Valid hash string, eg "#123".
	if ( '#' === link.charAt( '0' ) ) {
		valid = isValidHash( link );
	} else {
		// Valid reference to another repo, eg: "org/repo#123".
		const split = link.split( '/' );
		if ( 2 !== split.length ) {
			valid = false;
		} else {
			valid = split[ 1 ].includes( '#' ) && isValidHash( '#' + split[ 1 ].split( '#' )[ 1 ] );
		}
	}

	return valid;
}

/**
 * Determine if the supplied entry is valid.
 *
 * A valid entry can be single or multiple lines.
 *
 * A single-line must:
 * 	 + Begin with a capital letter (the bullet character should be omitted).
 * 	 + End with a full stop: period, question mark, or exclamation point.
 *
 * A multi-line:
 *   + Each line must start with a plus sign bullet character: `+`.
 *   + A single space must follow the bullet.
 *   + The remaining portion of each line follows the same rules as a single-line entry.
 *   + Additionally, a line may end in a colon.
 *
 * @since 0.0.1
 *
 * @param {string} entry The changelog entry string.
 * @return {boolean} Returns `true` if the entry is valid and `false` otherwise.
 */
function isEntryValid( entry ) {
	const singleLineRegex = /^[A-Z].*[.!?]$/,
		multiLineRegex = /^(  )?[+] [A-Z].*[:.!?]$/;

	const test = ( line, regex ) => null !== line.match( regex );

	if ( entry.includes( '\n' ) ) {
		return entry.split( '\n' ).filter( ( line ) => line ).every( ( line ) => test( line, multiLineRegex ) );
	}

	return test( entry, singleLineRegex );
}

/**
 * Object describing changelog validation issues found with a specified ChangelogEntry.
 *
 * @typedef {Object} ChangelogValidationIssues
 * @property {boolean}  valid    Whether or not validation errors were found.
 * @property {string[]} errors   Array of validation error messages.
 * @property {string[]} warnings Array of validation warning messages.
 */

/**
 * Retrieve a list of changelog validation issues.
 *
 * @since 0.0.1
 *
 * @param {ChangelogEntry} logEntry   The changelog entry object to validate.
 * @param {boolean}        formatting Whether or not messages should include formatting (colors and bold).
 * @return {ChangelogValidationIssues} Encountered validation issues
 */
function getChangelogValidationIssues( logEntry, formatting = true ) {
	const options = getChangelogOptions(),
		errors = [],
		warnings = [];

	// Check required fields.
	[ 'significance', 'type', 'entry' ].forEach( ( key ) => {
		if ( ! logEntry[ key ] ) {
			errors.push( `Missing required field: ${ highlight( key, formatting ) }.` );
		}
	} );

	// Validate the entry.
	if ( logEntry.entry && ! isEntryValid( logEntry.entry ) ) {
		errors.push( `The submitted entry text did not pass validation.` );
	}

	// Validate enum values.
	[ 'significance', 'type' ].forEach( ( key ) => {
		if ( logEntry[ key ] && ! Object.keys( options[ key ] ).includes( logEntry[ key ] ) ) {
			errors.push( `Invalid value ${ highlight( logEntry[ key ], formatting ) } supplied for field: ${ highlight( key, formatting ) }.` );
		}
	} );

	// Warn when encountering extra/non-standard keys.
	Object.keys( logEntry )
		// Expected keys.
		.filter( ( k ) => ! Object.keys( ChangelogEntry ).includes( k ) )
		.forEach( ( key ) => {
			warnings.push( `Unexpected key: ${ highlight( key, formatting ) }.` );
		} );

	// Ensure array fields are arrays.
	[ 'links', 'attributions' ].forEach( ( key ) => {
		if ( logEntry[ key ] && ! Array.isArray( logEntry[ key ] ) ) {
			errors.push( `The ${ highlight( key, formatting ) } field must be an array.` );
		}
	} );

	// Validate all links.
	if ( Array.isArray( logEntry.links ) ) {
		logEntry.links.forEach( ( link ) => {
			if ( ! isLinkValid( link ) ) {
				errors.push( `The link ${ highlight( link, formatting ) } is invalid.` );
			}
		} );
	}

	// Validate all attributions.
	if ( Array.isArray( logEntry.attributions ) ) {
		logEntry.attributions.forEach( ( attribution ) => {
			if ( ! isAttributionValid( attribution ) ) {
				errors.push( `The attribution ${ highlight( attribution, formatting ) } is invalid.` );
			}
		} );
	}

	return {
		valid: 0 === errors.length,
		errors,
		warnings,
	};
}

module.exports = {
	isAttributionValid,
	isEntryValid,
	isLinkValid,
	getChangelogValidationIssues,
};
