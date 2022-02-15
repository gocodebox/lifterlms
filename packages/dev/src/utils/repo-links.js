const getProjectSlug = require( './get-project-slug' ),
	parseIssueString = require( './parse-issue-string' );

/**
 * Retrieves a link to the specified file on the project's GitHub repository.
 *
 * @since [version]
 *
 * @param {string} path   Path to the file (relative to the root directory), eg: "includes/file.php" or "main.php".
 * @param {string} branch Branch or version number. Defaults to "trunk".
 * @return {string} The full URL to a file on the project's GitHub repository.
 */
function getFileLink( path, branch = 'trunk' ) {
	return `${ getRepoLink() }/blob/${ branch }/${ path }`;
}

/**
 * Retrieves a link to the specified issue on the project's GitHub repository.
 *
 * @since [version]
 *
 * @param {string} issue A GitHub-style issue reference string. Formatted as either "#123" or "organization/repository#123".
 * @return {string} The full URL to the specified issue.
 */
function getIssueLink( issue ) {
	const { org, repo, num } = parseIssueString( issue );
	return `${ getRepoLink( repo, org ) }/issues/${ num }`;
}

/**
 * Retrieves the base link to a project's GitHub repository.
 *
 * @since [version]
 *
 * @param {string} project The project slug.
 * @param {string} org     The project organization.
 * @return {string} The full URL to the current project's GitHub repository.
 */
function getRepoLink( project, org ) {
	project = project || getProjectSlug();
	org = org || 'gocodebox';
	return `https://github.com/${ org }/${ project }`;
}

module.exports = {
	getFileLink,
	getIssueLink,
	getRepoLink,
};
