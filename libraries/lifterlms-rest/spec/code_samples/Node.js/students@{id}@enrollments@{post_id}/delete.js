const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.delete( '/students/123/enrollments/456?trigger=SOME_STRING_VALUE', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );