require( 'regenerator-runtime' );

const teardown = require( '@wordpress/scripts/config/jest-environment-puppeteer/teardown' ),
	{ existsSync, readdirSync, renameSync, rmSync, mkdirSync } = require( 'fs' );

/**
 * Relocate artifacts from the root to the tmp directory.
 *
 * This can be removed if/when @wordpress/scripts allows configuration of the ARTIFACTS_PATH
 * constant
 *
 * @since [version]
 *
 * @link https://github.com/WordPress/gutenberg/issues/34797
 *
 * @return {void}
 */
module.exports = async () => {

	const defaultArtifacts = `${ process.cwd() }/artifacts`,
		artifacts = `${ process.cwd() }/tmp/artifacts`;

	// We have artifacts to move.
	if ( existsSync( defaultArtifacts ) ) {

		// Ensure our directory exists.
		if ( ! existsSync( artifacts ) ) {
			mkdirSync( artifacts, { recursive: true } );
		}

		// Move all the artifacts.
		readdirSync( defaultArtifacts ).forEach( fileName => {
			renameSync( `${ defaultArtifacts }/${ fileName }`, `${ artifacts }/${ fileName }` )
		} );

		// Delete the original directory.
		rmSync( defaultArtifacts + '/', { recursive: true, force: true } );

	}

	// Run the original teardown from @wordpress/scripts.
	await teardown();

};
