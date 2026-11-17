curl --request POST \
  --url https://example.tld/wp-json/llms/v1/groups/%7Bid%7D/invitations \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"email":"stephen@example.net","role":"member"}'