const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

llms.get( '/students?context=edit&page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&search=jamie%40lifterlms.com&search_columns=email%2Cusername&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&exclude=10%2C11%2C12&enrolled_in=1%2C2%2C3&enrolled_not_in=4%2C5%2C6&roles=student%2Ccustomer', function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );