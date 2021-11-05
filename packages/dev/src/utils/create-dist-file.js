const
	{ existsSync } = require( 'fs' ),
	getArchiveFilename = require( './get-archive-filename' ),
	{ getConfig } = require( './configs' ),
	getProjectSlug = require( './get-project-slug' ),
	execSync = require( './exec-sync' );

/**
 * Determine if the project has composer production dependencies warranting a `composer install` during builds.
 *
 * @since 0.0.1
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

module.exports = ( distDir, silent, log = () => {} ) => {
	const name = getArchiveFilename(),
		slug = getProjectSlug(),
		composer = requiresComposerInstall(),
		cwd = distDir;

	// If we have composer dependencies, reinstall with no dev requirements or scripts.
	if ( composer ) {
		log( 'Installing composer production dependencies...' );
		execSync( `composer update --no-dev --no-scripts`, silent );
		execSync( `rm composer.lock`, true );
	}

	// Empty inspected directories in the distribution directory (if any are leftover from the last run of the command).
	if ( existsSync( distDir ) ) {
		execSync( `rm -rf ${ slug }`, silent, { cwd } );
	}

	// Create the initial archive using composer.
	execSync( `composer archive --format=zip --dir=${ distDir } --file=${ name.replace( '.zip', '' ) }`, true );

	// Unzip the initial archive into a subdirectory matching the project's slug.
	execSync( `unzip ${ name } -d ${ slug }`, silent, { cwd } );

	// Remove the original zip file.
	execSync( `rm ${ name }`, true, { cwd } );

	// Zip up the subdirectory.
	execSync( `zip -r ${ name } ${ slug }/`, silent, { cwd } );

	// Remove the subdirectory.
	execSync( `rm -rf ${ slug }/`, silent, { cwd } );

	// If we have composer dependencies, reinstall with dev requirements when we're done.
	if ( composer ) {
		log( 'Reinstalling all composer dependencies...' );
		execSync( `composer update`, silent );
	}

	return name;
};
