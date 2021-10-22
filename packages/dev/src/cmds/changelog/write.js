const
	{ readFileSync, writeFileSync } = require( 'fs' ),
	chalk = require( 'chalk' ),
	semver = require( 'semver' ),
	{
		getNextVersion,
		getProjectSlug,
		getCurrentVersion,
		getChangelogOptions,
		getChangelogValidationIssues,
		getChangelogEntries,
		determineVersionIncrement,
		logResult,
		execSync,
	} = require( '../../utils' );

/**
 * Accepts a date/time string and converts it to YYYY-MM-DD format used in changelog version titles.
 *
 * @since [version]
 *
 * @param {string|number} date Timestamp or datetime string parseable by `Date.parse()`.
 * @return {string} Date string in YYYY-MM-DD format.
 */
const formatDate = ( date ) => new Date( date ).toISOString().split( 'T' )[ 0 ];

function getHeaderLines( version, date ) {
	const lines = [ `v${ version } - ${ date }` ];
	lines.push( '-'.repeat( lines[ 0 ].length ) );

	return lines;
}

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

function formatChangelogItem( { entry, type, attributions = [], links = [] }, includeLinks ) {
	entry = entry.trim();

	// Entries should always end in a full stop.
	if ( 'template' !== type && ! [ '.', '?', '!' ].includes( entry.split( '' ).reverse()[ 0 ] ) ) {
		entry += '.';
	}

	// The line is a list item.
	let line = `+ ${ entry }`;

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
		const slug = getProjectSlug();

		links = links.map( ( v ) => {
			let url = 'https://github.com/';
			if ( '#' === v.charAt( 0 ) ) {
				url += `gocodebox/${ slug }${ v }`;
			} else {
				url += v;
			}
			return `[${ v }](${ url })`;
		} );
		line += ' ' + links.join( ', ' );
	}

	return line;
}

function getUpdatedTemplates( includeLinks ) {
	try {
		return execSync( 'git diff --name-only trunk | grep "^templates/"', true ).split( '\n' ).map( ( template ) => {
			return {
				type: 'template',
				entry: includeLinks ? `[${ template }](https://github.com/gocodebox/${ getProjectSlug() }/blob/trunk/${ template })` : template,
			};
		} );
	} catch ( e ) {}
	return [];
}

function formatChangelogVersionEntry( version, date, entries, links ) {
	const
		groups = {},
		{ type } = getChangelogOptions();

	Object.keys( type ).forEach( ( groupKey ) => {
		groups[ groupKey ] = [];
	} );
	groups.template = [];

	// Add updated template list.
	entries = [ ...entries, ...getUpdatedTemplates( links ) ];

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

module.exports = {
	command: 'write',
	description: 'Write existing changelog entries to the changelog file.',
	options: [
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.' ],
		[ '-F, --force <version>', 'Use the specified version string instead of determining the version based on changelog entry significance.' ],
		[ '-l, --log-file <file>', 'The changelog file.', 'CHANGELOG.md' ],
		[ '-d, --date <YYYY-MM-DD>', 'Changelog publication date.', formatDate( Date.now() ) ],
		[ '-n, --no-links', 'Skip appending links to changelog entries.' ],
	],
	action: ( { dir, file, preid, force, logFile, date, skipFiles, links, yes } ) => {
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
			version = getNextVersion( currentVersion, determineVersionIncrement( dir ), preid );
		} else if ( ! semver.valid( version ) ) {
			logResult( `The supplied version string ${ chalk.bold( version ) } is invalid.`, 'error' );
			process.exit( 1 );
		}

		logResult( `Writing changelog for version ${ chalk.bold( version ) }:` );

		const logFileContents = readFileSync( logFile, 'utf8' ),
			logFileParts = logFileContents.split( '\n\n' ),
			[ header, ...body ] = logFileParts,
			items = formatChangelogVersionEntry( version, date, entries, links ).join( '\n' ) + '\n';

		writeFileSync( logFile, [ logFileParts[ 0 ], items, ...body ].join( '\n\n' ) );

		logResult( 'Changelog for version ${ chalk.bold( version ) } written.' );
	},
};
