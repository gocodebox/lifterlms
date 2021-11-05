const
	path = require( 'path' ),
	chalk = require( 'chalk' ),
	semver = require( 'semver' ),
	{ readdirSync, readFileSync, writeFileSync } = require( 'fs' ),
	{ parseChangelogFile, getCurrentVersion, logResult, getProjectSlug } = require( '../utils' );

/**
 * Generate the truncated changelog section content.
 *
 * @since 0.0.1
 *
 * @param {string} file   Changelog file.
 * @param {number} length Number of versions to include.
 * @return {string} The truncated changelog section content.
 */
function getChangelogSection( file, length ) {
	const entries = parseChangelogFile( file ),
		total = entries.length,
		lines = [];

	let i = 0,
		added = 0;

	while ( added < length && i < total ) {
		const currLog = entries[ i ];

		// Don't add prereleases.
		if ( ! semver.prerelease( currLog.version ) ) {
			lines.push( `= v${ currLog.version } - ${ currLog.date } =\n\n` );
			lines.push( currLog.logs );

			++added;

			if ( added < length ) {
				lines.push( '\n\n\n' );
			}
		}

		++i;
	}

	return lines.join( '' );
}

module.exports = {
	command: 'readme',
	description: 'Create a readme.txt file suitable for the WordPress.org plugin repository.',
	options: [
		[ '-o, --output-file <filename>', 'Specify the output readme file name.', 'readme.txt' ],
		[ '-i, --input-file <filename>', 'Specify the input changelog file name.', 'CHANGELOG.md' ],
		[ '-d, --dir <directory>', 'Directory where the readme part files are stored', '.wordpress-org/readme' ],
		[ '-l, --changelog-length <number>', 'Specify the number of versions to display before truncating the changelog.', 10 ],
		[ '-r, --read-more <url>', 'Specify the "Read More" url where changelogs are published.', `https://make.lifterlms.com/tag/${ getProjectSlug() }` ],
	],
	action: ( { outputFile, inputFile, dir, readMore, changelogLength } ) => {
		const version = getCurrentVersion();

		// Don't generate readme files for pre-releases.
		if ( semver.prerelease( version ) ) {
			logResult( 'Cannot generate a readme for prereleases.', 'error' );
			process.exit( 1 );
		}

		const replacements = {
				VERSION: version,
				CHANGELOG_ENTRIES: getChangelogSection( inputFile, changelogLength ),
				READ_MORE_LINK: readMore,
			},
			files = readdirSync( dir );

		let readme = '';

		files.forEach( ( filename, i ) => {
			const file = readFileSync( path.join( dir, filename ), 'utf8' );

			readme += file;

			// Add newlines if it's not the last section.
			if ( files.length - 1 !== i ) {
				readme += '\n\n';
			}
		} );

		// Replace variables.
		Object.keys( replacements ).forEach( ( varname ) => {
			readme = readme.replace( `{{__${ varname }__}}`, replacements[ varname ] );
		} );

		writeFileSync( outputFile, readme );

		logResult( `Generated ${ chalk.bold( outputFile ) } for version ${ chalk.bold( version ) }.`, 'success' );
	},
};
