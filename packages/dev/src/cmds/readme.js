const
	path = require( 'path' ),
	chalk = require( 'chalk' ),
	semver = require( 'semver' ),
	{ readdirSync, readFileSync, writeFileSync } = require( 'fs' ),
	{ parseChangelogFile, parseMainFileMetadata, getCurrentVersion, logResult, getProjectSlug } = require( '../utils' );

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

/**
 * Generates the merge code help text.
 *
 * @since [version]
 *
 * @return {string} Help text.
 */
function getMergeCodeHelp() {

	return `
Merge codes:
  The following merge codes can be used in any of the readme part markdown files.

  | Merge Code                    | Description                                                            | Source       |
  | ----------------------------- | -----------------------------------------------------------------------| ------------ |
  | {{__CHANGELOG_ENTRIES__}}     | The most recent 10 changelog entries.                                  | --input-file |
  | {{__LICENSE__}}               | The project's license (GPLv3).                                         | --main-file  |
  | {{__LICENSE_URI__}}           | The URI to the project's license.                                      | --main-file  |
  | {{__MIN_WP_VERSION__}}        | The minimum required WordPress core version.                           | --main-file  |
  | {{__MIN_LLMS_VERSION__}}      | The minimum required LifterLMS version.                                | --main-file  |
  | {{__MIN_PHP_VERSION__}}       | The minimum required PHP version.                                      | --main-file  |
  | {{__PROJECT_URI__}}           | The project's URI.                                                     | --main-file  |
  | {{__READ_MORE_LINK__}}        | A link to the full project changelog.                                  | --main-file  |
  | {{__SHORT_DESCRIPTION__}}     | A short description of the project.                                    | --main-file  |
  | {{__TESTED_LLMS_VERSION__}}   | The latest LifterLMS version the project has been tested against.      | --main-file  |
  | {{__TESTED_WP_VERSION__}}     | The latest WordPress core version the project has been tested against. | --main-file  |
  | {{__VERSION__}}               | The current project version.                                           | package.json |
	`;
}

/**
 * Command: readme
 *
 * @since 0.0.1
 * @since [version] Added the `--main-file` option as well as additional merge codes derived from metadata found in the main file's header comment.
 *              Exits with a warning and exit code `0` (instead of an error and exit code `1`) when running this command against a prerelease. 
 *
 * @type {Object}
 */
module.exports = {
	command: 'readme',
	description: 'Create a readme.txt file suitable for the WordPress.org plugin repository.',
	options: [
		[ '-o, --output-file <filename>', 'Specify the output readme file name.', 'readme.txt' ],
		[ '-i, --input-file <filename>', 'Specify the input changelog file name.', 'CHANGELOG.md' ],
		[ '-m, --main-file <filename>', 'Specify the project main file name where metadata is stored.', `${ getProjectSlug() }.php` ],
		[ '-d, --dir <directory>', 'Directory where the readme part files are stored', '.wordpress-org/readme' ],
		[ '-l, --changelog-length <number>', 'Specify the number of versions to display before truncating the changelog.', 10 ],
		[ '-r, --read-more <url>', 'Specify the "Read More" url where changelogs are published.', `https://make.lifterlms.com/tag/${ getProjectSlug() }` ],
	],
	help: [
		[ 'after', getMergeCodeHelp() ]
	],
	action: ( { outputFile, inputFile, mainFile, dir, readMore, changelogLength } ) => {
		const version = getCurrentVersion();

		// Don't generate readme files for pre-releases.
		if ( semver.prerelease( version ) ) {
			logResult( 'Cannot generate a readme for prereleases.', 'warning' );
			process.exit( 0 );
		}

		const metas = parseMainFileMetadata( mainFile );

		const replacements = {
				PROJECT_URI: metas['Plugin URI'] ?? '',
				LICENSE: metas['License'] ?? '',
				LICENSE_URI: metas['License URI'] ?? '',
				MIN_WP_VERSION: metas['Requires at least'] ?? '',
				TESTED_WP_VERSION: metas['Tested up to'] ?? '',
				MIN_LLMS_VERSION: metas['LLMS requires at least'] ?? '',
				TESTED_LLMS_VERSION: metas['LLMS tested up to'] ?? '',
				MIN_PHP_VERSION: metas['Requires PHP'] ?? '',
				SHORT_DESCRIPTION: metas['Description'] ?? '',
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
			readme = readme.replace( new RegExp( `{{__${ varname }__}}`, 'g' ), replacements[ varname ] );
		} );

		writeFileSync( outputFile, readme );

		logResult( `Generated ${ chalk.bold( outputFile ) } for version ${ chalk.bold( version ) }.`, 'success' );
	},
};
