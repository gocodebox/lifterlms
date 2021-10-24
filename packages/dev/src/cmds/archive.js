const
	{ existsSync } = require( 'fs' ),
	chalk = require( 'chalk' ),
	{ getArchiveFilename, getConfig, getProjectSlug, execSync, logResult } = require( '../utils' );

/**
 * Determine if the project has composer production dependencies warranting a `composer install` during builds.
 *
 * @since [version]
 *
 * @return {boolean} Whether or not a composer install is required.
 */
function requiresComposerInstall() {
	const
		pkg = getConfig( 'composer' ),
		keys = pkg.require ? Object.keys( pkg.require ) : [],
		autoload = pkg.autoload ? Object.keys( pkg.autoload ) : [];

	// If we have autoloading enabled we need to build for production.
	if ( 0 !== autoload.length ) {
		return true;
	}

	// Not defined or empty.
	if ( 0 === keys.length ) {
		return false;
	}

	// Has only a php (platform) requirement.
	if ( 1 === keys.length && 'php' === keys[ 0 ] ) {
		return false;
	}

	return true;
}

module.exports = {
	command: 'archive',
	description: 'Build a distribution archive (.zip) file for the project.',
	options: [
		[ '-i, --inspect', 'Automatically unzip the zip file after creation.', false ],
		[ '-d, --dir <dir>', 'Directory where the generated archive file will be saved, relative to the project root directory.', 'dist' ],
		[ '-v, --verbose', 'Output extra information with result messages.', false ],
	],
	action: ( { inspect, dir, verbose } ) => {
		const name = getArchiveFilename(),
			slug = getProjectSlug(),
			composer = requiresComposerInstall(),
			distDir = `${ process.cwd() }/${ dir }`;

		// If we have composer dependencies, reinstall with no dev requirements or scripts.
		if ( composer ) {
			logResult( 'Installing composer production dependencies...', 'info', true );
			execSync( `composer install --no-dev --no-scripts`, ! verbose );
		}

		// Empty inspected directories in the distribution directory (if any are leftover from the last run of the command).
		if ( existsSync( distDir ) ) {
			execSync( `rm -rf ${ slug }`, ! verbose, { cwd: distDir } );
		}

		// Create the initial archive using composer.
		execSync( `composer archive --format=zip --dir=${ distDir } --file=${ name.replace( '.zip', '' ) }`, true );

		// Unzip the initial archive into a subdirectory matching the project's slug.
		execSync( `unzip ${ name } -d ${ slug }`, ! verbose, { cwd: distDir } );

		// Remove the original zip file.
		execSync( `rm ${ name }`, true, { cwd: distDir } );

		// Zip up the subdirectory.
		execSync( `zip -r ${ name } ${ slug }/`, ! verbose, { cwd: distDir } );

		// Remove the subdirectory.
		execSync( `rm -rf ${ slug }/`, ! verbose, { cwd: distDir } );

		logResult( `Distribution file ${ chalk.bold( name ) } created successfully.`, 'success' );

		// Unzip the archive for inspection.
		if ( inspect ) {
			execSync( `unzip ${ name }`, ! verbose, { cwd: distDir } );
		}

		// If we have composer dependencies, reinstall with dev requirements when we're done.
		if ( composer ) {
			logResult( 'Reinstalling all composer dependencies...', 'info', true );
			execSync( `composer install`, ! verbose );
		}
	},
};
