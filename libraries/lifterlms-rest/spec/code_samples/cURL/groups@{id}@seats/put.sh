curl --request PUT \
  --url https://example.tld/wp-json/llms/v1/groups/123/seats \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"total":20}'