const
	chalk = require( 'chalk' ),
	{ getNextVersion, getCurrentVersion, determineVersionIncrement, logResult } = require( '../../utils' );

const whichOpts = [ 'current', 'next' ];

module.exports = {
	command: 'version',
	description: 'List existing changelog entries.',
	args: [
		[ '<which>', `Which version to retrieve. Accepts: ${ whichOpts.join( ', ' ) }.` ],
	],
	options: [
		[ '-p, --preid <identifier>', 'Identifier to be used to prefix premajor, preminor, prepatch or prerelease version increments.' ],
	],
	action: ( which, { dir, preid } ) => {
		if ( ! whichOpts.includes( which ) ) {
			logResult( `Unknown argument: "${ chalk.bold( which ) }".`, 'error' );
			process.exit( 1 );
		}

		const currentVersion = getCurrentVersion();
		if ( ! currentVersion ) {
			logResult( 'No current version found.\n       A version number must defined in the package.json file or in the composer.json file at ".extra.llms.version".', 'error' );
			process.exit( 1 );
		}

		if ( 'current' === which ) {
			console.log( currentVersion );
			process.exit( 0 );
		}

		console.log( getNextVersion( currentVersion, determineVersionIncrement( dir, currentVersion, preid ), preid ) );
	},
};
