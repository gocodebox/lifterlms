const
	inquirer = require( 'inquirer' );
	chalk = require( 'chalk' ),
	path = require( 'path' ),
	YAML = require( 'yaml' ),
	{ existsSync, mkdirSync, writeFileSync } = require( 'fs' ),
	{
		getChangelogOptions,
		logResult,
		execSync,
		isAttributionValid,
		isLinkValid,
		getChangelogValidationIssues,
	} = require( '../../utils' ),
	opts = getChangelogOptions();

function generateList( option ) {
	return Object.entries( opts[ option ] )
		.map( ( [ value, desc ] ) => ( {
			name: `${ value.charAt( 0 ).toUpperCase() }${ value.slice( 1 ) } [${ desc }]`,
			value,
		} ) );
}

function coerceLink( link ) {
	return ! isNaN( parseInt( link ) ) ? `#${ link }` : link;
}

function writeChangelog( log ) {

	const { dir } = log;
	let { title } = log;
	delete log.dir;
	delete log.title;

	const logDir = path.join( process.cwd(), dir );
	if ( ! existsSync( logDir ) ) {
		mkdirSync( logDir, { recursive: true } );
	}

	if ( log.links ) {
		log.links = log.links.map( coerceLink );
	}

	const validation = getChangelogValidationIssues( log );
	if ( ! validation.valid ) {

		const errs = validation.errors.map( err => `\n  - ${ err }` ).join( '' );

		logResult( `The changelog entry could not be written due to validation errors:${ errs }`, 'error' );

		process.exit( 1 );
	}

	// Remove optional empty values.
	Object.keys( log ).forEach( key => ( ! log[ key ] || ( Array.isArray( log[ key ] ) && ! log[ key ].length ) ) && delete log[ key ] );

	// Make sure filenames are unique.
	let i = 1;
	title = path.join( dir, title );
	const baseTitle = title;
	while ( existsSync( title + '.yml' ) ) {
		title = `${ baseTitle }-${ i }`;
		++i;
	}
	title += '.yml';

	writeFileSync( title, YAML.stringify( log ) );

	logResult( `New changelog entry written to ${ chalk.bold( title ) }.`, 'success' );

}

const defaultTitle = execSync( `git branch --show-current`, true ).replace( '/', '_' );

module.exports = {
	command: 'add',
	description: 'Create a new changelog entry.',
	options: [
		[ '-s, --significance <level>', `The semantic version significance of the change. Accepts: ${ Object.keys( opts.significance ).join( ', ' ) }.`, 'patch' ],
		[ '-t, --type <type>', `The type of change. Accepts: ${ Object.keys( opts.type ).join( ', ' ) }.`, 'changed' ],
		[ '-c, --comment <comment>', 'An internal-use comment to include with the changelog entry which is not published with the final changelog.' ],
		[ '-l, --links <issues...>', 'Link the changelog to one or more GitHub issues. Can be provided multiple times to link to multiple issues.' ],
		[ '-a, --attributions <users...>', 'Attribute the changelog entry to one or more individuals. Attributions are provided to thank contributions which originate from outside the LifterLMS organization. Provide a GitHub username or a markdown-formatted anchor. Can be provided multiple times to attribute to multiple users.' ],
		[ '-e, --entry <entry>', 'The changelog entry.' ],
		[ '-t, --title <title>', 'Changelog entry file name. Uses the current git branch name as the default. Automatically appends a number to the title if the title already exists.', defaultTitle ],
		[ '-i, --interactive', 'Create the changelog interactively.', false ]
	],
	action: ( { significance, type, comment, entry, interactive, links, attributions, dir, title } ) => {

		if ( ! entry && ! interactive ) {
			logResult( 'A changelog entry is required.', 'error' );
			process.exit( 1 );
		}

		if ( interactive ) {

			const commasToArray = ( val ) => val.split( ',' ).filter( val => val ).map( val => val.trim() );

			const questions = [
				{
					type: 'list',
					name: 'significance',
					message: 'Change Significance',
					default: significance,
					choices: generateList( 'significance' ),
					pageSize: Object.keys( opts.significance ).length,
				},
				{
					type: 'list',
					name: 'type',
					message: 'Change Type',
					default: significance,
					choices: generateList( 'type' ),
					pageSize: Object.keys( opts.type ).length,
				},
				{
					type: 'input',
					name: 'comment',
					message: 'Comment [For internal use only]',
					default: comment,
				},
				{
					type: 'input',
					name: 'links',
					message: 'Linked Issues [Separate multiple issues with a comma]',
					default: links ? links.join( ', ' ) : null,
					filter: vals => commasToArray( vals ).map( coerceLink ),
					validate: val => val.every( val => isLinkValid( val ) ) ? true : 'Invalid link',
				},
				{
					type: 'input',
					name: 'attributions',
					message: 'Attributions [Separate multiple individuals with a comma]',
					default: attributions ? attributions.join( ', ' ) : null,
					filter: commasToArray,
					validate: val => val.every( val => isAttributionValid( val ) ) ? true : 'Invalid attribution',
				},
				{
					type: 'input',
					name: 'entry',
					message: 'Changelog Entry Content',
					default: entry,
					validate: ( val ) => val ? true : chalk.red( 'Error: A changelog entry is required.' ),
				}
			];

			inquirer.prompt( questions )
				.then( answers => writeChangelog( { ...answers, dir, title } ) )
				.catch( err => console.log( err ) );

		} else {

			writeChangelog( { significance, type, comment, links, attributions, entry, dir, title } );

		}

	},
};
