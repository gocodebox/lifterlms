const
	url = require( 'url' ),
	chalk = require( 'chalk' ),
	getChangelogOptions = require( './get-changelog-options' );

function highlight( text, formatting ) {
	text = formatting ? chalk.bold( text ) : text;
	return `"${ text }"`;
}

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
		const test = new URL( match[1] );
		return true;
	} catch( e ) {}

	return false;
	// return attr.match( regex );

}

function isLinkValid( link ) {

	// Force values to a string.
	link = link.toString();

	const isValidHash = hash => ! isNaN( parseInt( hash.slice( 1 ) ) );

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
			valid = split[1].includes( '#' ) && isValidHash( '#' + split[1].split( '#' )[1] );
		}
	}

	return valid;

}

function getChangelogValidationIssues( logEntry, formatting = true ) {

	const options = getChangelogOptions(),
		errors   = [],
		warnings = [];

	// Check required fields.
	[ 'significance', 'type', 'entry' ].forEach( key => {
		if ( ! logEntry[ key ] ) {
			errors.push( `Missing required field: ${ highlight( key, formatting ) }.` );
		}
	} );

	// Validate enum values.
	[ 'significance', 'type', ].forEach( key => {

		if ( logEntry[ key ] && ! Object.keys( options[ key ] ).includes( logEntry[ key ] ) ) {
			valid = false;
			errors.push( `Invalid value ${ highlight( logEntry[ key ], formatting ) } supplied for field: ${ highlight( key, formatting ) }.` );
		}

	} );

	// Warn when encountering extra/non-standard keys.
	Object.keys( logEntry )
		// Expected keys.
		.filter( k => ! [ 'title', 'significance', 'type', 'entry', 'comment', 'links', 'attributions' ].includes( k ) )
		.forEach( key => {
			warnings.push( `Unexpected key: ${ highlight( key, formatting ) }.` );
		} );

	// Ensure array fields are arrays.
	[ 'links', 'attributions' ].forEach( key => {
		if ( logEntry[ key ] && ! Array.isArray( logEntry[ key ] ) ) {
			errors.push( `The ${ highlight( key, formatting ) } field must be an array.` );
		}
	} );

	if ( Array.isArray( logEntry.links ) ) {
		logEntry.links.forEach( link => {
			if ( ! isLinkValid( link ) ) {
				errors.push( `The link ${ highlight( link ) } is invalid.` );
			}
		} );
	}

	if ( Array.isArray( logEntry.attributions ) ) {
		logEntry.attributions.forEach( attribution => {
			if ( ! isAttributionValid( attribution ) ) {
				errors.push( `The attribution ${ highlight( attribution ) } is invalid.` );
			}
		} );
	}

	return {
		valid: 0 === errors.length,
		errors,
		warnings,
	};

};

module.exports = {
	isAttributionValid,
	isLinkValid,
	getChangelogValidationIssues,
};
