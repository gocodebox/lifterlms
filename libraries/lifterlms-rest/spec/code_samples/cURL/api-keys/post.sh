curl --request POST \
  --url https://example.tld/wp-json/llms/v1/api-keys \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"user_id":456,"description":"My API Key","permissions":"read"}'