const getProjectSlug = require( './get-project-slug' );

/**
 * A GitHub-style issue reference object.
 *
 * @typedef {Object} GitHubIssueRef
 * @property {string} org  The GitHub organization slug, eg "gocodebox".
 * @property {string} repo The GitHub repo slug, eg "lifterlms".
 * @property {string} num  The issue number, eg "1234".
 */

/**
 * Parses a GitHub-style issue reference string into it's parts.
 *
 * @since [version]
 *
 * @param {string} issue A GitHub-style issue reference string. Formatted as either "#123" or "organization/repository#123".
 * @return {GitHubIssueRef} An issue object.
 */
module.exports = ( issue ) => {
	let org = 'gocodebox',
		repo = getProjectSlug(),
		num = '';

	// Is an external reference.
	if ( issue.includes( '/' ) ) {
		const split = issue.split( '/' );
		org = split[ 0 ];
		[ repo, num ] = split[ 1 ].split( '#' );
	} else {
		num = issue.slice( 1 );
	}

	return { org, repo, num };
};
