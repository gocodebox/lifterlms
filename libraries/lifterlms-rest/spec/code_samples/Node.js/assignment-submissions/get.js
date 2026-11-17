const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.get( '/assignment-submissions?page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&orderby=SOME_STRING_VALUE&assignment=876%2C877&student=123%2C456&status=SOME_STRING_VALUE', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );