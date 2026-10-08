curl --request DELETE \
  --url https://example.tld/wp-json/llms/v1/students/123 \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"reassign":456}'