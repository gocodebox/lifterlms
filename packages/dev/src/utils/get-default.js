const
	path = require( 'path' ),
	parseYaml = require( 'yaml' ).parse,
	{ existsSync, readFileSync } = require( 'fs' );

/**
 * Find the config file.
 *
 * Looks in the project's root directory for .llmsdev.yml or .llmsdev.yaml.
 *
 * @since 0.0.1
 *
 * @return {string} Returns the full path to the config file or an empty string if none can be found.
 */
function getConfigFilePath() {
	const basePath = path.join( process.cwd(), '.llmsdev' );

	let configFilePath = '';

	[ '.yml', '.yaml' ].some( ( ext ) => {
		const testPath = basePath + ext;
		if ( existsSync( testPath ) ) {
			configFilePath = testPath;
			return true;
		}

		return false;
	} );

	return configFilePath;
}

/**
 * Load the gloabl config file.
 *
 * @since 0.0.1
 *
 * @return {Object} Returns the parsed config file as a JS object or an empty object if none found.
 */
function loadConfigFile() {
	const filePath = getConfigFilePath();
	if ( ! filePath ) {
		return {};
	}

	return parseYaml( readFileSync( filePath, 'utf8' ) );
}

/**
 * Get a default value for a given command and option.
 *
 * @since
 *
 * @param {string} command      Name of the command. When accessing subcommands the command name will be "parent.subcommand".
 * @param {string} setting      The option value, eg: "-v --verbose" or "-m --mode <mode>".
 *                              This string will be parsed and use the value following the two hyphens.
 *                              Using the examples the value from the config would accept the value of "verbose" or "mode".
 * @param {any}    defaultValue The default value as specified in the command options.
 * @return {any} The default value of the option.
 */
module.exports = ( command, setting, defaultValue = undefined ) => {
	setting = setting.split( ' ' )[ 1 ].replace( '--', '' );

	const config = loadConfigFile();
	if (
		! config ||
		0 === Object.keys( config ) ||
		undefined === config[ command ] ||
		undefined === config[ command ][ setting ] ) {
		return defaultValue;
	}

	return config[ command ][ setting ];
};
