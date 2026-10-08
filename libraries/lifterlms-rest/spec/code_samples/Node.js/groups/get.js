const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.get( '/groups?context=edit&page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&search=term&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&exclude=10%2C11%2C12', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );