const
	ChangelogEntry = require( './changelog-entry' ),
	determineVersionIncrement = require( './determine-version-increment' ),
	execSync = require( './exec-sync' ),
	getArchiveFilename = require( './get-archive-filename' ),
	getChangelogEntries = require( './get-changelog-entries' ),
	getChangelogOptions = require( './get-changelog-options' ),
	getCurrentVersion = require( './get-current-version' ),
	getDefault = require( './get-default' ),
	getNextVersion = require( './get-next-version' ),
	getProjectSlug = require( './get-project-slug' ),
	{ getConfig, hasConfig } = require( './configs' ),
	logResult = require( './log-result' ),
	parseChangelogFile = require( './parse-changelog-file' ),
	{ isAttributionValid, isLinkValid, getChangelogValidationIssues } = require( './validate-changelog' );

module.exports = {
	ChangelogEntry,

	determineVersionIncrement,
	execSync,
	getArchiveFilename,
	getChangelogOptions,
	getChangelogEntries,
	getConfig,
	getCurrentVersion,
	getDefault,
	getNextVersion,
	getProjectSlug,
	hasConfig,
	logResult,

	isAttributionValid,
	isLinkValid,
	getChangelogValidationIssues,

	parseChangelogFile,
};
