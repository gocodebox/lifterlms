#!/usr/bin/env node

const { argv } = process,
	{ readFileSync, readdirSync, lstatSync } = require( 'fs' ),
	path = require( 'path' ),
	{ Command } = require( 'commander' ),
	program = new Command(),
	pkg = JSON.parse( readFileSync( path.join( __dirname, '../package.json' ), 'utf8' ) ),
	{ getDefault } = require( './utils' );

// Setup the CLI program.
program
	.description( pkg.description )
	.version( pkg.version )
	.addHelpCommand( 'help [command]', 'Display help for command.' );

/**
 * Read the contents of the specified directory, registering all as subcommands of the specified parent command.
 *
 * @since 0.0.1
 *
 * @param {Command} parent        Parent command instance.
 * @param {string}  dir           Path to the directory where the command modules should be loaded from.
 * @param {Array[]} optionsParent Array of options shared from the parent to all subcommands.
 * @return {void}
 */
function registerCommands( parent, dir, optionsParent = [] ) {
	readdirSync( dir )
		// Exclude index files, they're picked up automatically so we don't want to double register them.
		.filter( ( file ) => 'index.js' !== file )
		.forEach( ( file ) => {
			const filePath = path.join( dir, file );

			// Register the command.
			registerCommand( parent, filePath, optionsParent );
		} );
}

/**
 * Register a command with the specified parent.
 *
 * @since 0.0.1
 *
 * @param {Command} parent        Parent command instance.
 * @param {string}  filePath      Path to the directory where the command modules should be loaded from.
 * @param {Array[]} optionsParent Array of options shared from the parent to all subcommands.
 * @return {void}
 */
function registerCommand( parent, filePath, optionsParent = [] ) {
	const {
		command,
		description,
		action,
		args = [],
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

	args.forEach( ( cmdArgs ) => cmd.argument( ...cmdArgs ) );

	[ ...options, ...optionsParent, ...optionsShared ].forEach( ( opts ) => {
		// Attempts to parse default values from the config file.
		opts[ 2 ] = getDefault( parent._name ? parent._name + '.' + command : command, opts[ 0 ], opts[ 2 ] );
		cmd.option( ...opts );
	} );

	help.forEach( ( helpText ) => cmd.addHelpText( ...helpText ) );

	// If it's a directory, recursively register files in the directory.
	if ( lstatSync( filePath ).isDirectory() ) {
		registerCommands( cmd, filePath, optionsShared );
		cmd.addHelpCommand( 'help [command]', 'Display help for command.' );
	}
}

// Register all commands.
registerCommands( program, path.join( __dirname, 'cmds' ) );

// Parse incoming arguments.
program.parse( argv );
