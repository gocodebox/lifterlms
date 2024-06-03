const
	path = require( 'path' ),
	{ Command } = require( 'commander' ), // Including for the type definition.
	{ readFileSync, writeFileSync } = require( 'fs' ),
	{ execSync, logResult, getProjectSlug } = require( '../utils' ),
	defaultExclude = 'vendor/**, node_modules/**, tmp/**, dist/**, docs/**, src/**, tests/**, *.js.map';

/**
 * Command: pot
 *
 * @since 0.0.1
 *
 * @type {Object}
 */
module.exports = {
	command: 'pot',
	description: 'Generate i18n pot and json files using the WP-CLI.',
	options: [
		[ '-d, --text-domain <text-domain>', 'Specify the text domain. Used to generate the filenames for generated files.', getProjectSlug() ],
		[ '-e, --exclude <glob...>', 'Specify files to exclude from scanning.', defaultExclude ],
		[ '-ee, --extra-exclude <glob...>', 'Additional files to add to the --exclude option.' ],
		[ '-d, --dir <directory>', 'Output directory where generated files will be stored.', 'i18n' ],
		[ '-t, --translator <translator>', 'Customize the Last Translator header.', 'Team LifterLMS <team@lifterlms.com>' ],
		[ '-b, --bugs <url>', 'Customize the bug report location header.', 'https://lifterlms.com/my-account/my-tickets' ],
	],
	/**
	 * Callback action for the pot command
	 *
	 * @since 0.0.1
	 *
	 * @param {Object}  options              Command options.
	 * @param {string}  options.textDomain   Project text domain.
	 * @param {string}  options.exclude      Comma separated list of globs used to exclude files from the pot file generation.
	 * @param {string}  options.extraExclude Extra globs to be added to exclude.
	 * @param {string}  options.dir          Output directory where the generated files will be saved.
	 * @param {string}  options.translator   Translator name and email.
	 * @param {string}  options.bugs         Bug report URL.
	 * @param {Command} command              The command instance.
	 * @param {Command} command.parent       The command's parent command.
	 * @return {void}
	 */
	action: ( { textDomain, exclude, extraExclude, dir, translator, bugs }, { parent } ) => {
		// Ensure WP-CLI is available.
		try {
			execSync( 'which wp', true );
		} catch ( e ) {
			logResult( 'WP-CLI must be installed in your $PATH in order to use this command.', 'error' );
			process.exit( 1 );
		}

		const
			// Replace the WP CLI generator with our own generator string.
			generator = `llms/dev ${ parent.version() }`,
			// Get the year of the first commit to the repo.
			initYear = parseInt( execSync( 'git log --reverse --format="format:%cd" --date="format:%Y" | sed -n 1p', true ) ),
			currDate = new Date(),
			currYear = currDate.getFullYear(),
			pot = path.join( dir, `${ textDomain }.pot` ),
			// Custom Headers.
			headers = {
				'Last-Translator': translator,
				'Language-Team': translator,
				'Report-Msgid-Bugs-To': bugs,
				'X-Generator': generator,
			};

		// Add extra exclude globs, if defined.
		if ( extraExclude ) {
			exclude = exclude + ', ' + extraExclude;
		}

		// Update excludes glob formatting to a format acceptable by WP CLI.
		exclude = exclude.replace( /\/\*\*/g, '/' ).replace( /\.\//g, '' );

		const cmdOpts = `--exclude="${ exclude }" --headers='${ JSON.stringify( headers ) }'`;

		// Generate the POT file.
		execSync( `wp i18n make-pot ./ ${ pot } ${ cmdOpts }` );

		// Get the original header comment.
		let headerComment = execSync( `head -2 ${ pot }`, true ),
			potContents = readFileSync( pot ).toString();

		// If the initial commit date is not equal to the current year, update the copyright to include the date range.
		if ( initYear !== currYear ) {
			potContents = potContents.replace( headerComment, '' );
			headerComment = headerComment.replace( `(C) ${ currYear }`, `(C) ${ initYear }-${ currYear }` );
		}

		// Write the header back to the file.
		writeFileSync( pot, headerComment + potContents );
	},
};
