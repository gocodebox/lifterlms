curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/groups/%7Bid%7D/members?context=edit&page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&exclude=10%2C11%2C12&group_roles=SOME_STRING_VALUE' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'