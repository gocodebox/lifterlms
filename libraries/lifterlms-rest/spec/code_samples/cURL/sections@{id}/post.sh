curl --request POST \
  --url https://example.tld/wp-json/llms/v1/sections/%7Bid%7D \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"title":"Getting Started with LifterLMS","date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","order":1,"parent_id":1234,"post_type":"section"}'