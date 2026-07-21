const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.delete( '/api-keys/987', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );