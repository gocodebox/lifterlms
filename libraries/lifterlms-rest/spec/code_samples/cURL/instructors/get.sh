curl --request GET \
  --url 'https://example.tld/wp-json/llms/v1/instructors?page=1&per_page=SOME_INTEGER_VALUE&order=SOME_STRING_VALUE&search=jamie%40lifterlms.com&search_columns=email%2Cusername&orderby=SOME_STRING_VALUE&include=1%2C2%2C3&exclude=10%2C11%2C12&post_in=1%2C2%2C3&post_not_in=4%2C5%2C6&roles=instructor%2Clms_manager' \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH'