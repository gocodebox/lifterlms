curl --request DELETE \
  --url https://example.tld/wp-json/llms/v1/groups/%7Bid%7D \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"force":false}'