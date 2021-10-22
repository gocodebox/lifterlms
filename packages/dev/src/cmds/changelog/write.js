const
	{ readFileSync, writeFileSync } = require( 'fs' ),
	chalk = require( 'chalk' ),
	semver = require( 'semver' ),
	{
		getNextVersion,
		getCurrentVersion,
		getChangelogOptions,
		getChangelogValidationIssues,
		getChangelogEntries,
		determineVersionIncrement,
		logResult,
	} = require( '../../utils' );

const whichOpts = [ 'current', 'next' ];

const formatDate = ( date ) => new Date( date ).toISOString().split( 'T' )[0];

function getHeaderLines( version, date ) {

	const lines = [ `v${ version } - ${ date }` ];
	lines.push( '-'.repeat( lines[0].length ) );

	return lines;

}

function formatChangelogVersionEntry( version, date, entries ) {

	const
		groups = {},
		{ type } = getChangelogOptions();

	Object.keys( type ).forEach( groupKey => {
		groups[ groupKey ] = [];
	} );

	entries.forEach( entry => {
		groups[ entry.type ].push( entry );
	} );

	console.log( groups );

	const lines = [
		...getHeaderLines( version, date ),
	];




}

module.exports = {
	command: 'write',
	description: "Write existing changelog entries to the changelog file.",
	options: [
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.' ],
		[ '-F, --force <version>', 'Use the specified version string instead of determining the version based on changelog entry significance.' ],
		[ '-l, --log-file <file>', 'The changelog file.', 'CHANGELOG.md' ],
		[ '-d, --date <YYYY-MM-DD>', 'Changelog publication date.', formatDate( Date.now() ) ],
		[ '-s, --skip-files', 'Skip file updates and only write the changelog.' ],
		[ '-y, --yes', 'Automatically confirm changelog updates.' ],
	],
	action: ( { dir, file, preid, force, logFile, date, skipFiles, yes } ) => {

		try {
			date = formatDate( date );
		} catch ( e ) {
			logResult( 'Invalid date supplied. Please provide a date in YYYY-MM-DD format.', 'error' );
			process.exit( 1 );
		}

		const currentVersion = getCurrentVersion();
		if ( ! currentVersion ) {
			logResult( 'No current version found.\n       A version number must defined in the package.json file or in the composer.json file at ".extra.llms.version".', 'error' );
			process.exit( 1 );
		}

		const entries = getChangelogEntries( dir );

		const areEntriesValid = entries.every( entry => {
			const { valid } = getChangelogValidationIssues( entry );
			return valid;
		} );

		if ( ! areEntriesValid ) {
			logResult( 'One or more invalid changelog entries were found. Please resolve all validation issues and try again.', 'error' );
			process.exit( 1 );
		}

		let version = force;

		if ( ! version ) {
			version = getNextVersion( currentVersion, determineVersionIncrement( dir ), preid );
		} else if ( ! semver.valid( version ) ) {
			logResult( `The supplied version string ${ chalk.bold( version ) } is invalid.`, 'error' );
			process.exit( 1 );
		}

		logResult( `Writing changelog for version ${ chalk.bold( version ) }` );

		const logFileContents = readFileSync( logFile, 'utf8' ),
			logFileParts = logFileContents.split( '\n\n' ),
			[ header, ...body ] = logFileParts;


formatChangelogVersionEntry( version, date, entries );
		// writeFileSync( logFile, [ logFileParts[0], formatChangelogVersionEntry( version, date, entries ), ...body ].join( '\n\n' ) );

	},
};
