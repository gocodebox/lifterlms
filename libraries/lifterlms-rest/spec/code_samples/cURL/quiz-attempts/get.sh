curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/quiz-attempts?page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&orderby=SOME_STRING_VALUE&student=123&quiz=234&lesson=789&status=SOME_STRING_VALUE&_fields=id%2Cstudent_id%2Cquiz_id%2Cstatus%2Cgrade' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'