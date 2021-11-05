const chalk = require( 'chalk' );

/**
 * Log a result to the console
 *
 * @since 0.0.1
 *
 * @param {string} msg  Message to log.
 * @param {string} type Message type. Accepts success, warning, error, or info.
 * @return {void}
 */
module.exports = ( msg, type = 'info' ) => {
	msg = chalk.bold( type.charAt( 0 ).toUpperCase() + type.slice( 1 ) ) + ': ' + msg;

	switch ( type ) {
		case 'success':
			msg = chalk.green( msg );
			break;

		case 'warning':
			msg = chalk.yellow( msg );
			break;

		case 'error':
			msg = chalk.red( msg );
			break;

		case 'info':
			msg = chalk.blue( msg );
			break;
	}

	console.log( msg );
};
