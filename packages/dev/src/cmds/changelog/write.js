const
	path = require( 'path' ),
	{ readFileSync, writeFileSync, readdirSync, rmSync } = require( 'fs' ),
	chalk = require( 'chalk' ),
	semver = require( 'semver' ),
	{
		ChangelogEntry,
		getNextVersion,
		getCurrentVersion,
		getChangelogOptions,
		getChangelogValidationIssues,
		getChangelogEntries,
		getFileLink,
		getIssueLink,
		isProjectPublic,
		determineVersionIncrement,
		logResult,
		execSync,
	} = require( '../../utils' );

/**
 * Accepts a date/time string and converts it to YYYY-MM-DD format used in changelog version titles.
 *
 * @since 0.0.1
 *
 * @param {string|number} date Timestamp or datetime string parseable by `Date.parse()`.
 * @return {string} Date string in YYYY-MM-DD format.
 */
const formatDate = ( date ) => new Date( date ).toISOString().split( 'T' )[ 0 ];

/**
 * Retrieve the an array of lines for the changelog entry's header.
 *
 * @since 0.0.1
 *
 * @param {string} version A semver string.
 * @param {string} date    A date string.
 * @return {string[]} Array of lines.
 */
function getHeaderLines( version, date ) {
	const lines = [ `v${ version } - ${ date }` ];
	lines.push( '-'.repeat( lines[ 0 ].length ) );

	return lines;
}

/**
 * Retrieve the title for the changelog item's type.
 *
 * @since 0.0.1
 *
 * @param {string} type The changelog item type key.
 * @return {string} The changelog item type title.
 */
function getTypeTitle( type ) {
	const map = {
		added: 'New Features',
		changed: 'Updates and Enhancements',
		fixed: 'Bug Fixes',
		deprecated: 'Deprecations',
		removed: 'Breaking Changes',
		dev: 'Developer Notes',
		performance: 'Performance Improvements',
		security: 'Security Fixes',
		template: 'Updated Templates',
	};

	return `\n##### ${ map[ type ] }\n`;
}

/**
 * Formats a single changelog item.
 *
 * @since 0.0.1
 * @since [version] Use `getIssueLink()` for generation of issue links.
 *
 * @param {ChangelogEntry} args              The changelog entry object.
 * @param {string}         args.entry        The content of the changelog entry.
 * @param {string}         args.type         Entry type.
 * @param {string[]}       args.attributions List of individuals attributed to the entry.
 * @param {string[]}       args.links        of GitHub issues linked to the entry.
 * @param {boolean}        includeLinks      Whether or not to include links.
 * @return {string} The formatted changelog entry line.
 */
function formatChangelogItem( { entry, type, attributions = [], links = [] }, includeLinks ) {
	entry = entry.trim();

	// Entries should always end in a full stop.
	if ( 'template' !== type && ! [ '.', '?', '!' ].includes( entry.split( '' ).reverse()[ 0 ] ) ) {
		entry += '.';
	}

	let line = '';

	// Single entry, add a bullet.
	if ( ! entry.includes( '\n' ) ) {
		line += '+ ';
	}

	// Add the line(s).
	line += entry;

	// Add formatted attribution links.
	if ( attributions.length ) {
		attributions = attributions.map( ( v ) => {
			if ( '@' === v.charAt( 0 ) ) {
				v = `[${ v }](https://github.com/${ v })`;
			}
			return v;
		} );
		line += ` Thanks ${ new Intl.ListFormat( 'en', { style: 'long', type: 'conjunction' } ).format( attributions ) }!`;
	}

	// Add issue links.
	if ( includeLinks && links.length ) {
		line += ' ' + links.map( ( iss ) => `[${ iss }](${ getIssueLink( iss ) })` ).join( ', ' );
	}

	return line;
}

/**
 * Retrieve a list changelog entry objects for all the template files that have been modified.
 *
 * Compares the current git branch against the `trunk` branch in order to find all files in the `templates/` directory
 * which have been modified.
 *
 * @since 0.0.1
 * @since [version] Use `getFileLink()` to generate links to the template file.
 *
 * @param {boolean} includeLinks Whether or not the entry items should be formatted as links to the GitHub repository.
 * @param {string}  version      A semver string.
 * @return {ChangelogEntry[]} Array of changelog entry objects.
 */
function getUpdatedTemplates( includeLinks, version ) {
	try {
		return execSync( 'git diff --name-only trunk | grep "^templates/"', true ).split( '\n' ).map( ( template ) => {
			return {
				type: 'template',
				entry: includeLinks ? `[${ template }](${ getFileLink( template, version ) })` : template,
			};
		} );
	} catch ( e ) {}
	return [];
}

/**
 * Format the changelog entry for the given version.
 *
 * @since 0.0.1
 *
 * @param {string}           version A semver string.
 * @param {string}           date    Version release date in YYYY-MM-DD format.
 * @param {ChangelogEntry[]} entries All entry objects to be included.
 * @param {boolean}          links   Whether or not to add links to GitHub issues and templates. For public repos we want to show links, otherwise we don't bother.
 * @return {string[]} Array of lines to be added to the changelog.
 */
function formatChangelogVersionEntry( version, date, entries, links ) {
	const
		groups = {},
		{ type } = getChangelogOptions();

	Object.keys( type ).forEach( ( groupKey ) => {
		groups[ groupKey ] = [];
	} );
	groups.template = [];

	// Add updated template list.
	entries = [ ...entries, ...getUpdatedTemplates( links, version ) ];

	entries.forEach( ( entry ) => {
		groups[ entry.type ].push( entry );
	} );

	const lines = [
		...getHeaderLines( version, date ),
	];

	Object.entries( groups ).forEach( ( [ groupType, groupEntries ] ) => {
		if ( ! groupEntries.length ) {
			return;
		}

		lines.push( getTypeTitle( groupType ) );
		groupEntries.forEach( ( entry ) => {
			lines.push( formatChangelogItem( entry, links ) );
		} );
	} );

	return lines;
}

/**
 * Delete all changelog entry files from the changelog directory.
 *
 * @since 0.0.1
 *
 * @param {string} dir Changelog directory.
 * @return {void}
 */
function cleanupLogs( dir ) {
	readdirSync( dir ).forEach( ( file ) => {
		if ( file.endsWith( '.yml' ) ) {
			rmSync( path.join( dir, file ) );
		}
	} );
}

module.exports = {
	command: 'write',
	description: 'Write existing changelog entries to the changelog file.',
	options: [
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.' ],
		[ '-F, --force <version>', 'Use the specified version string instead of determining the version based on changelog entry significance.' ],
		[ '-l, --log-file <file>', 'The changelog file.', 'CHANGELOG.md' ],
		[ '-d, --date <YYYY-MM-DD>', 'Changelog publication date.', formatDate( Date.now() ) ],
		[ '-L, --links', 'Add GitHub links to templates and issues in changelog entries.', true === isProjectPublic() ],
		[ '-n, --no-links', 'Do not add GitHub links in changelog entries. Use this option to override the --links flag.' ],
		[ '-D, --dry-run', 'Output what would be written to the changelog instead of writing it to the changelog file.' ],
		[ '-k, --keep-entries', 'Preserve entry files deletion after the changelog is written.' ],
	],
	action: ( { dir, preid, force, logFile, date, links, dryRun, keepEntries } ) => {
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

		const areEntriesValid = entries.every( ( entry ) => {
			const { valid } = getChangelogValidationIssues( entry );
			return valid;
		} );

		if ( ! areEntriesValid ) {
			logResult( 'One or more invalid changelog entries were found. Please resolve all validation issues and try again.', 'error' );
			process.exit( 1 );
		}

		let version = force;

		if ( ! version ) {
			version = getNextVersion( currentVersion, determineVersionIncrement( dir, currentVersion, preid ), preid );
		} else if ( ! semver.valid( version ) ) {
			logResult( `The supplied version string ${ chalk.bold( version ) } is invalid.`, 'error' );
			process.exit( 1 );
		}

		logResult( `${ dryRun ? 'Generating' : 'Writing' } changelog for version ${ chalk.bold( version ) }.` );

		const logFileContents = readFileSync( logFile, 'utf8' );

		const
			logFileParts = logFileContents.split( '\n\n' ),
			[ header, ...body ] = logFileParts,
			items = formatChangelogVersionEntry( version, date, entries, links ).join( '\n' ) + '\n';

		if ( dryRun ) {
			console.log( items );
			process.exit( 0 );
		}

		writeFileSync( logFile, [ header, items, ...body ].join( '\n\n' ) );
		logResult( `Changelog for version ${ chalk.bold( version ) } written.` );

		if ( ! keepEntries ) {
			logResult( `Peforming entry file cleanup`, 'warning' );
			cleanupLogs( dir );
		}
	},
};
