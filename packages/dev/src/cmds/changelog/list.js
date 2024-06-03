const
	chalk = require( 'chalk' ),
	columnify = require( 'columnify' ),
	{ getChangelogEntries, logResult } = require( '../../utils' );

module.exports = {
	command: 'list',
	description: 'List existing changelog entries.',
	action: ( { dir } ) => {
		const val = {
			major: 2,
			minor: 1,
			patch: 0,
		};

		const entries = getChangelogEntries( dir )
			// Group by significance and then sort by title.
			.sort( ( { significance: aSig, title: aTitle }, { significance: bSig, title: bTitle } ) => {
				if ( val[ aSig ] < val[ bSig ] ) {
					return 1;
				}
				if ( val[ aSig ] > val[ bSig ] ) {
					return -1;
				}
				return aTitle > bTitle ? -1 : 1;
			} )
			.map( ( entry ) => {
				if ( 'major' === entry.significance ) {
					Object.keys( entry ).forEach( ( key ) => entry[ key ] = chalk.bold( entry[ key ] ) );
				} else if ( 'patch' === entry.significance ) {
					Object.keys( entry ).forEach( ( key ) => entry[ key ] = chalk.dim( entry[ key ] ) );
				}
				return entry;
			} );

		if ( ! entries.length ) {
			logResult( 'No changelog entries found.', 'warning' );
			process.exit( 0 );
		}

		console.log( columnify(
			entries,
			{
				headingTransform: ( heading ) => chalk.bold.underline( heading.toUpperCase() ),
				preserveNewLines: true,
				truncate: true,
				maxWidth: 18,
				config: {
					entry: {
						maxWidth: 40,
					},
				},
			},
		) );
	},
};
