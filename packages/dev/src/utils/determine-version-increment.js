const getChangelogEntries = require( './get-changelog-entries' );

module.exports = ( dir ) => {

	const
		logs = Array.from( new Set( getChangelogEntries( dir ).map( ( { significance } ) => significance ) ) ),
		increment = [ 'major', 'minor', 'patch' ].find( level => logs.includes( level ) );

	return increment || 'patch';

};
