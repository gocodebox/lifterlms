const { execSync } = require( 'child_process' );

/**
 * Execute a command in a child process.
 *
 * This is a wrapper for node's child_process.execSync() with some
 * quality of life improvements to reduce the necessity of specifying
 * an options object to silence output.
 *
 * @since 0.0.1
 *
 * @param {string}  cmd   Command to execute.
 * @param {boolean} quiet If true, silences stdio output.
 * @param {Object}  opts  Additional options object passed to `execSync()`.
 * @return {string} The stdout from the command.
 */
module.exports = ( cmd, quiet = false, opts = {} ) => {
	const stdio = quiet ? 'pipe' : 'inherit',
		stdout = execSync( cmd, { stdio, ...opts } );

	return quiet ? stdout.toString().trim() : '';
};
