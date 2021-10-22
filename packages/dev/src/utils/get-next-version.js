const semver = require( 'semver' );

module.exports = ( version, increment, preid = null ) => {
	increment = preid ? `pre${ increment }` : increment;

	// When incrementing a prerelease we want to skip versions like "-beta.0" and go right to "-beta.1".
	if ( increment.includes( 'pre' ) ) {
		const prever = semver.inc( version, increment, preid );
		if ( 0 === semver.prerelease( prever ).reverse()[ 0 ] ) {
			version = prever;
			increment = 'prerelease';
		}
	}

	return semver.inc( version, increment, preid );
};
