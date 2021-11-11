const
	getProjectSlug = require( './get-project-slug' ),
	execSync = require( './exec-sync' );

/**
 * Get the project's repo privacy status.
 *
 * Uses the GitHub CLI client (gh) to lookup the project's status via the GitHub api. If the API
 * encounters errors (like a 404 or an authentication error) it will fail silently and result
 * in an "unknown" response.
 *
 * @since 0.0.2
 *
 * @return {string} Returns 'public' or 'private'. If the repo cannot be found, returns 'unknown'.
 */
function getProjectPrivacy() {
	let status = 'unknown';

	try {
		const res = JSON.parse( execSync( `gh api repos/gocodebox/${ getProjectSlug() }`, true ) );
		status = res.private ? 'private' : 'public';
	} catch ( e ) {}

	return status;
}

/**
 * Determine if the project is private.
 *
 * @since 0.0.2
 *
 * @return {boolean | undefined} Returns `true` for private repos, `false` for public repos, and `undefined` for unknown repos.
 */
function isProjectPrivate() {
	const privacy = getProjectPrivacy();
	if ( 'unknown' === privacy ) {
		return undefined;
	}
	return 'private' === privacy;
}

/**
 * Determine if the project is private.
 *
 * @since 0.0.2
 *
 * @return {boolean | undefined} Returns `false` for private repos, `true` for public repos, and `undefined` for unknown repos.
 */
function isProjectPublic() {
	const privacy = getProjectPrivacy();
	if ( 'unknown' === privacy ) {
		return undefined;
	}
	return 'public' === privacy;
}

module.exports = {

	isProjectPrivate,
	isProjectPublic,
	getProjectPrivacy,

};
