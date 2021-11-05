const
	path = require( 'path' ),
	{ existsSync, writeFileSync } = require( 'fs' ),
	chalk = require( 'chalk' ),
	inquirer = require( 'inquirer' ),
	{ getCurrentVersion, getChangelogForVersion, getArchiveFilename, logResult, pushDistFile, execSync } = require( '../../utils' );

/**
 * Create a temporary changelog file used to add the changelog to the GitHub release.
 *
 * @since 0.0.1
 *
 * @param {string} version Semver string for the version being published.
 * @param {string} logfile Path to the the changelog file.
 * @return {string} Path to the temporary notes file.
 */
function writeTempNotesFile( version, logfile ) {
	const { date, logs } = getChangelogForVersion( version, logfile ),
		tmpFile = path.join( process.cwd(), 'tmp', 'release-notes.txt' );

	let header = `v${ version } - ${ date }`;
	header = `${ header }\n${ '-'.repeat( header.length ) }`;

	writeFileSync( tmpFile, `${ header }\n\n${ logs }` );

	return tmpFile;
}

module.exports = {
	command: 'create',
	description: 'Create a GitHub release and tag from a specified file or branch.',
	options: [
		[ '-a, --archive <zip>', 'If specified, the zip file will be committed and force-pushed to the specified branch before creating the release. Pass --no-archive to skip this step.', getArchiveFilename() ],
		[ '-A, --no-archive', 'Skip creation from an archive file and use the target --branch for release creation.' ],
		[ '-c, --commit-message <message>', 'Customize the commit message used when pushing to the target branch. Used only when releasing from an archive. The placeholder "%s" is replaced with the release version.', 'Release v%s [ci skip]' ],
		[ '-d, --dir <directory>', 'Directory where distribution files are stored.', 'dist' ],
		[ '-b, --branch <branch>', 'Target branch to use when creating the release.', 'release' ],
		[ '-l, --logfile <file>', 'Specify the changelog file.', 'CHANGELOG.md' ],
		[ '-p, --prerelease', 'Mark the GitHub release as a prerelease and skip merging.' ],
		[ '-P, --prerelease-branch <branch>', 'When creating a prerelease, use this branch as the target branch in favor of the default branch specified via the --branch option.', 'prerelease' ],
		[ '-D, --draft', 'Create the release as an unpublished draft and skip merging.' ],
		[ '-M, --merge <branch>', 'Merge open PRs on the specified branch before creating the release. If publishing a prerelease, or draft merging is automatically disabled as if passing "--no-merge".', 'dev' ],
		[ '-n, --no-merge', 'Disable merging before release creation. Automatically passed when publishing a prerelease.' ],
		[ '-Y, --yes', 'Skip confirmations.' ],
		[ '-v, --verbose', 'Output extra information with result messages.' ],
	],
	action: async ( { archive, dir, commitMessage, branch, logfile, prerelease, prereleaseBranch, draft, merge, yes, verbose } ) => {
		// Ensure the CLI is installed before proceeding.
		try {
			execSync( 'which gh', true );
		} catch ( Error ) {
			logResult( 'The GitHub CLI client "gh" must be installed to use this command.', 'error' );
			process.exit( 1 );
		}

		// If there's untracked files or the working tree is dirty.
		if ( execSync( 'git status -s', true ) ) {
			logResult( 'The working tree must be clean before publishing.', 'error' );
			process.exit( 1 );
		}

		if ( archive ) {
			archive = path.join( dir, archive );

			if ( ! existsSync( archive ) ) {
				logResult( `The distribution file ${ chalk.bold( archive ) } doesn't exist.`, 'error' );
				process.exit( 1 );
			}
		}

		const version = getCurrentVersion();

		commitMessage = commitMessage.replace( '%s', version );

		// Use the prerelease branch when publishing a prerelease.
		if ( prerelease ) {
			branch = prereleaseBranch;
			merge = false;
		}

		// Disable merging if publishing a draft.
		if ( draft ) {
			merge = false;
		}

		// Output information and confirm the release (unless `--yes` is passed);
		if ( ! yes ) {
			logResult( `About to publish a new ${ prerelease ? 'prerelease' : 'release' }${ draft ? ' (draft)' : '' }:`, 'warning' );
			logResult( `${ chalk.bold( version ) }`, ' + Version' );
			if ( archive ) {
				logResult( `${ chalk.bold( archive ) }`, ' + Archive' );
			}
			logResult( `${ chalk.bold( branch ) }`, ' + Branch' );
			if ( merge ) {
				logResult( `${ chalk.bold( merge ) }`, ' + Merge from branch' );
			}

			yes = await inquirer.prompt( [ {
				type: 'expand',
				message: 'Are you sure you wish to proceed?',
				name: 'yes',
				choices: [
					{
						key: 'y',
						name: 'Yes',
						value: true,
					},
					{
						key: 'n',
						name: 'No',
						value: false,
					},
				],
			} ] )
				.then( ( answers ) => answers.yes )
				.catch( ( err ) => console.log( err ) );
		}

		if ( ! yes ) {
			logResult( 'Release aborted.', 'error' );
			process.exit( 1 );
		}

		logResult( `Releasing version ${ chalk.bold( version ) } to the ${ chalk.bold( branch ) } branch.` );

		// Push the distfile to the release branch.
		if ( archive ) {
			pushDistFile( archive, branch, commitMessage, ! verbose );
		}

		// Merge open PRs against the specified branch.
		if ( merge ) {
			execSync( `gh pr merge ${ merge } --merge`, true );
		}

		// Setup the release to pass to the GH CLI.
		const
			notesFile = writeTempNotesFile( version, logfile ),
			createArgs = [];

		if ( archive ) {
			createArgs.push( archive );
		}

		createArgs.push( `--title "Version ${ version }"` );
		createArgs.push( `--target ${ branch }` );
		createArgs.push( `--notes-file ${ notesFile }` );

		if ( draft ) {
			createArgs.push( '--draft' );
		}

		if ( prerelease ) {
			createArgs.push( '--prerelease' );
		}

		// Create the release.
		const res = execSync( `gh release create ${ version } ${ createArgs.join( ' ' ) }`, true );
		logResult( `Release v${ chalk.bold( version ) } published. Permalink: ${ chalk.underline( res ) }.` );

		// Cleanup the tmp notesfile.
		execSync( `rm ${ notesFile }` );
	},
};
