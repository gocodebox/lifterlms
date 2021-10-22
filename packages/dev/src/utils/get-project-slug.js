const { basename } = require( 'path' );

/**
 * Retrieve the package's "slug".
 *
 * This will always be equal to the directory name.
 *
 * For example "lifterlms" or "lifterlms-integration-woocommerce".
 *
 * @since 5.4.1
 *
 * @return {String}
 */
module.exports = () => {
	return basename( process.cwd() );
}
