const
	chalk = require( 'chalk' ),
	semver = require( 'semver' ),
	columnify = require( 'columnify' ),
	replace = require( 'replace-in-file' ),
	{ writeFileSync } = require( 'fs' ),
	{ getCurrentVersion, getNextVersion, logResult, getConfig, hasConfig, execSync } = require( '../utils' );

/**
 * Update [version] placeholders via a regex against a list of file globs
 *
 * @since [version]
 *
 * @param {string} files  Comma separated list of file globs.
 * @param {regex}  regex  A regular expression to use for the replacements.
 * @param {string} ignore A comma separated list of file globs to be ignored.
 * @param {string} ver    The semantic version string to replace the placeholder with.
 * @return {Object} Replacement result object from `replace.sync()`.
 */
function updateVersions( files, regex, ignore, ver ) {
	const commasToArray = ( string ) => string.split( ',' ).map( ( s ) => s.trim() );

	files = commasToArray( files );

	logResult( `Replacing ${ chalk.bold( files ) } using regex ${ chalk.bold( regex ) }.` );

	const
		opts = {
			files,
			from: new RegExp( regex, 'g' ),
			to: ver,
			ignore: ignore ? commasToArray( ignore ) : null,
			countMatches: true,
		};

	return replace.sync( opts );
}

/**
 * Updates the version number in the package's config file.
 *
 * If a package.json file is present, uses `npm version` to update the project's version.
 *
 * If there is no package.json, will attempt to update the `extra.llms.version` item in the
 * project's composer.json file.
 *
 * @since [version]
 *
 * @param {string} ver Semantic version string.
 * @return {Object} A replacement result string.
 */
function updateConfig( ver ) {
	const ret = {
		Matches: chalk.yellow( 1 ),
		Replacements: chalk.yellow( 1 ),
	};

	if ( hasConfig( 'package' ) ) {
		// Silence update errors. When updating new files and the package has already been updated the CLI throws an error which we can ignore.
		try {
			logResult( 'Updating package.json.' );
			execSync( `npm version --no-git-tag-version ${ ver }`, true );
			return [
				{
					File: chalk.green( 'package.json' ),
					...ret,
				},
				{
					File: chalk.green( 'package-lock.json' ),
					...ret,
				},
			];
		} catch ( e ) {}
	} else if ( hasConfig( 'composer' ) ) {
		const composer = getConfig( 'composer' );
		if ( composer?.extra?.llms?.version ) {
			logResult( 'Updating composer.json.' );
			composer.extra.llms.version = ver;
			writeFileSync( `${ process.cwd() }/composer.json`, JSON.stringify( composer, null, 2 ) );
			return [
				{
					File: chalk.green( 'composer.json' ),
					...ret,
				},
			];
		}
	}

	return false;
}

const defaultReplacements = [
	// 1. Replace [version] placeholder in all @since, @version, and @deprecated tags.
	[ './**', '(?<=@(?:since|version|deprecated) +)(\\[version\\])' ],

	// 2. Replace [version] placeholder in all deprecate function methods tags.
	[ './*.php,./**/*.php', '(?<=(?:llms_deprecated_function|_deprecated_function|_deprecated_file\\().+)(?<=\')(\\[version\\])(?=\')' ],

	// 3. Replace plugin metadata "Version" with current version.
	[ '*lifterlms*.php', '(?<=[Vv]ersion *[:=] *[ \'\"])(0|[1-9]\d*)\\.(0|[1-9]\\d*)\\.(0|[1-9]\\d*)(?:-((?:0|[1-9]\\d*|\\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\\.(?:0|[1-9]\\d*|\\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\\+([0-9a-zA-Z-]+(?:\\.[0-9a-zA-Z-]+)*))?' ],

	// 4. Replace LIFTERLMS*_VERSION constants with the current version.
	[ '*lifterlms*.php', '(?<=define\\( \'(?:LLMS|LIFTERLMS).*_VERSION\', \')(.*)(?=\' \\);)' ],

	// 5. Replace theme stylesheet's version number with the current version.
	[ './style.css', '(?<=Version: )(.+)' ],
];

module.exports = {
	command: 'update-version',
	description: 'Update the project version and replace all [version] placeholders.',
	options: [
		[ '-i, --increment <level>', 'Increment the version by the specified level. Accepts: major, minor, patch, premajor, preminor, prepatch, or prerelease.', 'patch' ],
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.', 'beta' ],
		[ '-F, --force <version>', 'Specify an explicit version instead of incrementing the current version with --increment.' ],
		[ '-r, --replacements <replacement...>]', 'Replacements to be made. Each replacement is an array containing a list of globs for the files to be tested and a regex used to perform the replacement. It is recommended that this argument to configured via a configuration file as opposed to being passed via a CLI flag.', defaultReplacements ],
		[ '-e, --extra-replacements <replacement...>]', 'Additional replacements added to --replacements array. This option allows adding to the default replacements instead of overwriting them.', [] ],
		[ '-E, --exclude <glob...>', 'Specify files to exclude from the update.', './vendor/**, ./node_modules/**, ./tmp/**, ./dist/**, ./docs/**, ./packages/**' ],
		[ '-s, --skip-config', 'Skip updating the version of the package.json or composer.json file.' ],
	],
	action: ( { increment, preid, exclude, force, skipConfig, replacements, extraReplacements } ) => {
		const version = force ? force : getNextVersion( getCurrentVersion(), increment, preid );

		if ( ! semver.valid( version ) ) {
			logResult( `The supplied version string ${ chalk.bold( version ) } is invalid.`, 'error' );
			process.exit( 1 );
		}

		// Add extraReplacements.
		replacements = [ ...replacements, ...extraReplacements ];

		const res = [];
		if ( ! skipConfig ) {
			const configUpdate = updateConfig( version );
			if ( configUpdate ) {
				configUpdate.forEach( ( configRes ) => res.push( configRes ) );
			}
		}

		logResult( `Updating project files to version ${ chalk.bold( version ) }.` );

		for ( let i = 0; i < replacements.length; i++ ) {
			updateVersions( ...replacements[ i ], exclude, version )
				.filter( ( { hasChanged } ) => hasChanged )
				.forEach( ( update ) => {
					res.push( {
						File: chalk.green( update.file ),
						Matches: chalk.yellow( update.numMatches ),
						Replacements: chalk.yellow( update.numReplacements ),
					} );
				}
				);
		}

		if ( ! res.length ) {
			logResult( 'No updates made.', 'warning' );
		} else {
			logResult( 'Version update completed.', 'success' );
			console.log(
				columnify(
					res,
					{
						headingTransform: ( heading ) => chalk.bold.underline( heading ),
					},
				)
			);
		}
	},
};
