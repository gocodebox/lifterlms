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
  "access_opens_message": "This course opens on [lifterlms_course_info key=\"start_date\"].",
  "access_closes_message": "This course closed on [lifterlms_course_info key=\"end_date\"].",
  "enrollment_opens_message": "Enrollment in this course opens on [lifterlms_course_info key=\"enrollment_start_date\"].",
  "enrollment_closes_message": "Enrollment in this course closed on [lifterlms_course_info key=\"enrollment_end_date\"].",
  "capacity_message": "Enrollment has closed because the maximum number of allowed students has been reached.",
  "length": "7 days",
  "restricted_message": "You must enroll in this course to access course content.",
  "date_created": "2019-05-20 17:22:05",
  "date_created_gmt": "2019-05-20 13:22:05",
  "menu_order": 0,
  "slug": "getting-started-with-lifterlms",
  "status": "publish",
  "password": "p4$sW0rd",
  "featured_media": 987,
  "comment_status": "open",
  "ping_status": "open",
  "permalink": "https://example.com/course/getting-started-with-lifterlms",
  "post_type": "course",
  "catalog_visibility": "catalog_search",
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
  "difficulties": [
    7
  ],
  "tracks": [
    8,
    9
  ],
  "audio_embed": "https://open.spotify.com/track/trackid",
  "video_embed": "https://www.youtube.com/watch?v=videoid",
  "capacity_enabled": false,
  "capacity_limit": 25,
  "prerequisite": 456,
  "prerequisite_track": 789,
  "access_opens_date": "2019-05-20 17:22:05",
  "access_closes_date": "2019-06-05 17:22:05",
  "enrollment_opens_date": "2019-05-15 12:15:00",
  "enrollment_closes_date": "2019-10-01 23:59:59",
  "video_tile": false,
  "instructors": [
    1,
    2,
    3
  ],
  "sales_page_type": "none",
  "sales_page_page_id": 543,
  "sales_page_url": "https://example.tld/custom-sales-page"
};

llms.post( '/courses/123', postData, function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );