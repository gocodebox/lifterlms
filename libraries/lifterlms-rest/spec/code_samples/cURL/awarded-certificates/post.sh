curl --request POST \
  --url https://example.tld/wp-json/llms/v1/awarded-certificates \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"student_id":123,"certificate_id":345,"related_id":1234}'