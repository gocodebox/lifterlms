/**
 * HTTP code snippet generator for llms-api-node.
 *
 * @package LifterLMS_Rest/Bin
 *
 * @since [version]
 * @version [version]
 */

'use strict'

const CodeBuilder = require( 'httpsnippet/src/helpers/code-builder' );

module.exports = function( source, options ) {

	const
		opts = Object.assign( {
			indent: '  '
		}, options ),
		code = new CodeBuilder( opts.indent ),
		apiOpts = {
			url: `${ source.uriObj.protocol }//${ source.uriObj.host }`,
			consumerKey: 'ck_XXXXXXXXXXXXXXXXXXXXXX',
			consumerSecret: 'cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
		};

	code.push( 'const llmsAPI = require( "llms-api-node" );' );
	code.push( 'const llms = new llmsAPI( %s );', JSON.stringify( apiOpts, null, opts.indent ) );

	code.blank();

	let opener = `llms.${ source.method.toLowerCase() }( '${ source.uriObj.path.replace( '/wp-json/llms/v1', '' ) }',`;
	if ( source.postData.jsonObj ) {
		code.push( 'const postData = %s;', JSON.stringify( source.postData.jsonObj, null, opts.indent ) );
		code.blank();
		opener += ` postData,`;
	}
	opener += ` function( err, data, res ) {`;

	code.push( opener );
	code.push( 1, `if ( err ) {` );
	code.push( 2, `throw new Error( 'Error!' );` );
	code.push( 1, `}`)
	code.push( 1, 'console.log( data );' );
	code.push( '} );');

	return code.join();

};

module.exports.info = {
	key: 'llms',
	title: 'llms-api-node',
	link: 'http://nodejs.org/api/http.html#http_http_request_options_callback',
	description: 'LifterLMS Node API Interface',
};
