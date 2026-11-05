const
	chalk = require( 'chalk' ),
	inquirer = require( 'inquirer' ),
	semver = require( 'semver' ),
	{ readFileSync } = require( 'fs' ),
	{ execSync, logResult } = require( '../../utils' );

/**
 * Call the cli from within the cli.
 *
 * @since 0.0.1
 * @since 0.2.1 Account for spaces in the file paths.
 * @since [version] Added `opts` parameter.
 *
 * @param {string}  cmd    CLI command and options.
 * @param {boolean} silent If `true`, silence STDOUT.
 * @param {Object}  opts   Additional options object passed to `execSync()`, eg: `{ cwd }`.
 * @return {?string} The STDOUT content if `silent` is `true`, otherwise `null`.
 */
function callSelf( cmd, silent = true, opts = {} ) {
	const [ node, cli ] = process.argv;
	let ret = null;
	try {
		ret = execSync( `"${ node }" "${ cli }" ${ cmd }`, silent, opts );
	} catch ( e ) {
		logResult( `${ e.type }: ${ e.message }.`, 'error' );
		console.error( e );
		process.exit( 1 );
	}
	return ret;
}

/**
 * Open a CLI prompt and await user confirmation.
 *
 * @since 0.0.1
 *
 * @param {string}  message Message to prompt for confirmation.
 * @param {boolean} skip    If true, the script is being run with `--yes` and no prompt should be made.
 * @return {Promise} Returns a promise from the inquirer prompt.
 */
function prompt( message, skip = false ) {
	if ( skip ) {
		return true;
	}

	const questions = [
		{
			type: 'confirm',
			name: 'confirm',
			message,
			default: true,
		},
	];

	return inquirer.prompt( questions )
		.then( ( { confirm } ) => confirm );
}

/**
 * Update the version of a bundled library that is versioned separately from the project.
 *
 * When files in the library differ from `trunk` (the last release), the library's patch version
 * is incremented from its version on `trunk`, unless the version was already changed. `update-version`
 * then runs from the library's directory so its package.json, plugin version, and `[version]`
 * placeholders use the library's version instead of the project's version.
 *
 * @since [version]
 *
 * @param {string} dir Path to the library directory, relative to the project root.
 * @return {void}
 */
function prepareLibrary( dir ) {
	dir = dir.replace( /^\.\//, '' ).replace( /\/$/, '' );

	const
		versionFiles = [ `${ dir }/package.json`, `${ dir }/package-lock.json` ],
		changed = execSync( `git diff --name-only trunk -- "${ dir }"`, true )
			.split( '\n' )
			.filter( ( file ) => file && ! versionFiles.includes( file ) );

	if ( ! changed.length ) {
		logResult( `No changes to ${ chalk.bold( dir ) } since the last release.` );
		return;
	}

	const
		current = JSON.parse( readFileSync( `${ dir }/package.json`, 'utf8' ) ).version,
		released = JSON.parse( execSync( `git show "trunk:${ dir }/package.json"`, true ) ).version,
		version = current === released ? semver.inc( released, 'patch' ) : current;

	logResult( `Updating ${ chalk.bold( dir ) } to version ${ chalk.bold( version ) }.` );
	callSelf( `update-version --force ${ version }`, false, { cwd: dir } );
}

module.exports = {
	command: 'prepare',
	description: 'Prepare and build a release.',
	options: [
		[ '-F, --force <version>', 'Specify a version to use. If not specified uses `changelog version next` to determine the version.' ],
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.' ],
		[ '-y, --yes', 'Specify no-interaction mode. Responds "yes" to all confirmation prompts.' ],
		[ '-b, --build <cmd>', 'Specify an npm script to use for the build command.', 'build' ],
		[ '-B, --no-build', 'Disabled build script.' ],
		[ '-l, --libraries <dir...>', 'Bundled library directories with their own version. A library that changed since trunk gets a patch version increment and its [version] placeholders are replaced with the library\'s version.', [] ],
	],
	action: async ( { force, preid, build, yes, libraries } ) => {
		preid = preid ? ` --preid ${ preid }` : '';

		// Prepare release version.
		const version = force ? force : callSelf( `changelog version next${ preid }` );

		if ( ! semver.valid( version ) ) {
			logResult( `The supplied version string ${ chalk.bold( version ) } is invalid.`, 'error' );
			process.exit( 1 );
		}

		// Confirm version.
		if ( ! await prompt( `Proceed using version ${ chalk.bold( version ) }?`, yes ) ) {
			process.exit( 1 );
		}

		// Get the changelog.
		if ( ! yes ) {
			callSelf( `changelog write --dry-run --force ${ version }`, false );
			if ( ! await prompt( 'Use the above output for the changelog and build the release?' ) ) {
				process.exit( 1 );
			}
		}

		// Update files.
		callSelf( `changelog write --force ${ version }`, false );

		// Update bundled library versions before the project's update replaces their [version] placeholders.
		libraries.forEach( prepareLibrary );

		// Update version.
		callSelf( `update-version --force ${ version }` );

		// Build.
		if ( build ) {
			execSync( `npm run ${ build }` );
		}

		logResult( `Release preparation for version ${ chalk.bold( version ) } complete.`, 'success' );
	},
};
