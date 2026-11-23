curl --request POST \
  --url https://example.tld/wp-json/llms/v1/webhooks \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"name":"A Student Enrolled in a Course","status":"active","topic":"student.created","delivery_url":"https://example.tld/webhook-receipt/endpoint","secret":"$P3CI41-$3CR37!"}'