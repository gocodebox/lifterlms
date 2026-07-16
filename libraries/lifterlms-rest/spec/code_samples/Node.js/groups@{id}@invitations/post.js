const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

const postData = {
  "email": "stephen@example.net",
  "role": "member"
};

llms.post( '/groups/%7Bid%7D/invitations', postData, function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );