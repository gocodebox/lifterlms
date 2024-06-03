const { getConfig } = require( './configs' );

/**
 * Retrieve the current version number of the project
 *
 * @since 0.0.1
 *
 * @return {string} A semver string or an empty string if no version could be parsed.
 */
module.exports = () => {
	const npm = getConfig( 'package' );
	if ( npm.version ) {
		return npm.version;
	}

	const composer = getConfig( 'composer' );
	if ( composer?.extra?.llms?.version ) {
		return composer.extra.llms.version;
	}

	return '';
};
