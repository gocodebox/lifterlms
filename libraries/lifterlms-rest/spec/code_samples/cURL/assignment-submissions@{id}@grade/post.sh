curl --request POST \
  --url https://example.tld/wp-json/llms/v1/assignment-submissions/%7Bid%7D/grade \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"grade":88,"remarks":"Nice work — consider expanding on your conclusion."}'