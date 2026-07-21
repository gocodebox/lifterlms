curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/memberships/%7Bid%7D/enrollments?context=edit&page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&status=SOME_STRING_VALUE&student=1%2C2%2C3' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'