const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

const postData = {
  "name": "A Student Enrolled in a Course",
  "status": "active",
  "topic": "student.created",
  "delivery_url": "https://example.tld/webhook-receipt/endpoint",
  "secret": "$P3CI41-$3CR37!"
};

llms.post( '/webhooks', postData, function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );