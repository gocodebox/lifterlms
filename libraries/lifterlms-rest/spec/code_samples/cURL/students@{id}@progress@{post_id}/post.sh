curl --request POST \
  --url https://example.tld/wp-json/llms/v1/students/123/progress/456 \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"date_created":"2019-05-21T14:22:05","status":"incomplete"}'