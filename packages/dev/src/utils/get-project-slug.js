const { basename } = require( 'path' );

/**
 * Retrieve the package's "slug".
 *
 * This will always be equal to the directory name.
 *
 * For example "lifterlms" or "lifterlms-integration-woocommerce".
 *
 * @since [version]
 *
 * @return {string} The project's slug.
 */
module.exports = () => {
	return basename( process.cwd() );
};
