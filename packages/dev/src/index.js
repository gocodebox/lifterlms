#!/usr/bin/env node

const { argv } = process,
	{ readFileSync, readdirSync, lstatSync } = require( 'fs' ),
	path = require( 'path' ),
	{ Command } = require( 'commander' ),
	program = new Command(),
	pkg = JSON.parse( readFileSync( path.join( __dirname, '../package.json' ), 'utf8' ) ),
	{ getDefault } = require( './utils' );

program
	.description( pkg.description )
	.version( pkg.version )
	.addHelpCommand( 'help [command]', 'Display help for command.' );


function registerCommands( parent, dir , optionsParent = []) {

	readdirSync( dir )
		// Exclude index files, they're picked up automatically so we don't want to double register them.
		.filter( file => 'index.js' !== file )
		.forEach( file => {

			const filePath = path.join( dir, file );

			// Register the command.
			registerCommand( parent, filePath, optionsParent );

		} );
}

function registerCommand( parent, filePath, optionsParent = [] ) {

	const {
			command,
			description,
			action,
			arguments = [],
			options = [],
			optionsShared = [],
			help = [],
		} = require( filePath );

	const cmd = parent
		.command( command )
		.description( description );

	if ( action ) {
		cmd.action( action );
	}

	arguments.forEach( args => cmd.argument( ...args ) );

	[ ...options, ...optionsParent, ...optionsShared ].forEach( opts => {
		// Attempts to parse default values from the config file.
		opts[2] = getDefault( parent._name ? parent._name + '.' + command : command, opts[0], opts[2] );
		cmd.option( ...opts );
	} );

	help.forEach( help => cmd.addHelpText( ...help ) );

	// If it's a directory, recursively register files in the directory.
	if ( lstatSync( filePath ).isDirectory() ) {
		registerCommands( cmd, filePath, optionsShared );
		cmd.addHelpCommand( 'help [command]', 'Display help for command.' );
	}

}

registerCommands( program, path.join( __dirname, 'cmds' )  );


program.parse( argv );
