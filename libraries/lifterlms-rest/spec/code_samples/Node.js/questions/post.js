const llmsAPI = require( "llms-api-node" );
const llms = new llmsAPI( {
  "url": "https://example.tld",
  "consumerKey": "ck_XXXXXXXXXXXXXXXXXXXXXX",
  "consumerSecret": "cs_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
} );

const postData = {
  "date_created": "2019-05-20 17:22:05",
  "date_created_gmt": "2019-05-20 13:22:05",
  "post_type": "llms_question",
  "parent_id": 234,
  "question_type": "choice",
  "points": 10,
  "multi_choices": false,
  "clarifications": "The correct answer is <strong>B</strong> because...",
  "video_src": "https://www.youtube.com/watch?v=videoid",
  "choices": [
    {
      "id": "ABC123",
      "choice": "Answer text.",
      "choice_type": "text",
      "correct": false,
      "marker": "A"
    }
  ],
  "title": "What is your favorite color?",
  "content": "<p>Expectoque quid ad id, quod quaerebam, respondeas.</p>"
};

llms.post( '/questions', postData, function( err, data, res ) {
  if ( err ) {
    throw new Error( 'Error!' );
  }
  console.log( data );
} );