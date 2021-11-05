const
	chalk = require( 'chalk' ),
	inquirer = require( 'inquirer' ),
	semver = require( 'semver' ),
	{ execSync, logResult } = require( '../../utils' );

/**
 * Call the cli from within the cli.
 *
 * @since 0.0.1
 *
 * @param {string}  cmd    CLI command and options.
 * @param {boolean} silent If `true`, silence STDOUT.
 * @return {?string} The STDOUT content if `silent` is `true`, otherwise `null`.
 */
function callSelf( cmd, silent = true ) {
	const [ node, cli ] = process.argv;
	let ret = null;
	try {
		ret = execSync( `${ node } ${ cli } ${ cmd }`, silent );
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

module.exports = {
	command: 'prepare',
	description: 'Prepare and build a release.',
	options: [
		[ '-F, --force <version>', 'Specify a version to use. If not specified uses `changelog version next` to determine the version.' ],
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.' ],
		[ '-y, --yes', 'Specify no-interaction mode. Responds "yes" to all confirmation prompts.' ],
		[ '-b, --build <cmd>', 'Specify an npm script to use for the build command.', 'build' ],
		[ '-B, --no-build', 'Disabled build script.' ],
	],
	action: async ( { force, preid, build, yes } ) => {
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

		// Update version.
		callSelf( `update-version --force ${ version }` );

		// Build.
		if ( build ) {
			execSync( `npm run ${ build }` );
		}

		logResult( `Release preparation for version ${ chalk.bold( version ) } complete.`, 'success' );
	},
};
