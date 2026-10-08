const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.get( '/students/123/enrollments?page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&orderby=SOME_STRING_VALUE&status=SOME_STRING_VALUE&post=1%2C2%2C3', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );