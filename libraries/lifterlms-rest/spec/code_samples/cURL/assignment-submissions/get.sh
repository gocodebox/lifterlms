curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/assignment-submissions?page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&orderby=SOME_STRING_VALUE&assignment=876%2C877&student=123%2C456&status=SOME_STRING_VALUE' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'