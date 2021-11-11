const
	chalk = require( 'chalk' ),
	{ createDistFile, execSync, logResult } = require( '../../utils' );

module.exports = {
	command: 'archive',
	description: 'Build a distribution archive (.zip) file for the project.',
	options: [
		[ '-i, --inspect', 'Automatically unzip the zip file after creation.', false ],
		[ '-d, --dir <dir>', 'Directory where the generated archive file will be saved, relative to the project root directory.', 'dist' ],
		[ '-v, --verbose', 'Output extra information with result messages.', false ],
	],
	action: ( { inspect, dir, verbose } ) => {
		const distDir = `${ process.cwd() }/${ dir }`,
			fileName = createDistFile(
				distDir,
				! verbose,
				( msg ) => logResult( msg, 'info' )
			);
		// Unzip the archive for inspection.
		if ( inspect ) {
			execSync( `unzip ${ fileName }`, ! verbose, { cwd: distDir } );
		}

		logResult( `Distribution file ${ chalk.bold( fileName ) } created successfully.`, 'success' );
	},
};
