const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

const postData = {
  "title": "Getting Started with LifterLMS",
  "content": "<!-- wp:heading -->\\n<h2>Lorem ipsum dolor sit amet.</h2>\\n<!-- /wp:heading -->\\n\\n<!-- wp:paragraph -->\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>\\n<!-- /wp:paragraph -->",
  "excerpt": "Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.",
  "restriction_message": "You must belong to the [lifterlms_membership_link id=\"1234\"] membership to access this content.",
  "date_created": "2019-05-20 17:22:05",
  "date_created_gmt": "2019-05-20 13:22:05",
  "menu_order": 0,
  "slug": "getting-started-with-lifterlms",
  "status": "publish",
  "password": "p4$sW0rd",
  "featured_media": 987,
  "comment_status": "open",
  "ping_status": "open",
  "permalink": "https://example.com/membership/getting-started-with-lifterlms",
  "post_type": "llms_membership",
  "categories": [
    1,
    2,
    3
  ],
  "tags": [
    4,
    5,
    6
  ],
  "restriction_action": "none",
  "restriction_page_id": 456,
  "restriction_url": "https://example.tld/my-custom-url",
  "auto_enroll": [
    456,
    789
  ],
  "catalog_visibility": "catalog_search",
  "instructors": [
    1,
    2,
    3
  ],
  "sales_page_type": "none",
  "sales_page_page_id": 543,
  "sales_page_url": "https://example.tld/custom-sales-page"
};

llms.post( '/memberships/%7Bid%7D', postData, function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );