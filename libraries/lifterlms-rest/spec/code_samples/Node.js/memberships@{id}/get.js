const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.get( '/memberships/%7Bid%7D?context=edit&password=p4%24sW0rd', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );