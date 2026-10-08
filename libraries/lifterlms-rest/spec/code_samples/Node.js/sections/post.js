const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

const postData = {
  "title": "Getting Started with LifterLMS",
  "date_created": "2019-05-20 17:22:05",
  "date_created_gmt": "2019-05-20 13:22:05",
  "order": 1,
  "parent_id": 1234,
  "post_type": "section"
};

llms.post( '/sections', postData, function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );