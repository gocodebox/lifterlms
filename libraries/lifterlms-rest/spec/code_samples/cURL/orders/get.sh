curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/orders?context=edit&page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&search=term&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&exclude=10%2C11%2C12&status=active%2Ccompleted&student=123&product=1234&plan=567' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'