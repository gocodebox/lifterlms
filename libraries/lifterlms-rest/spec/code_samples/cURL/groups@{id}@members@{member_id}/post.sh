curl --request POST \
  --url https://example.tld/wp-json/llms/v1/groups/%7Bid%7D/members/%7Bmember_id%7D \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"group_role":"member"}'