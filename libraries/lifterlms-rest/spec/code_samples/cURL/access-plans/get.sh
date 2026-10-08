curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/access-plans?context=edit&page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&search=term&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&exclude=10%2C11%2C12&post_id=123%2C456' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'