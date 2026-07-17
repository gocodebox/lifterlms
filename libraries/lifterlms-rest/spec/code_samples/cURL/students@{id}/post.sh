curl --request POST \
  --url https://example.tld/wp-json/llms/v1/students/123 \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"email":"jamie@lifterlms.com","username":"jamie2019","password":"my_l337-p@$5w0rd!","description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit.","registered_date":"2019-05-03 19:25:01","url":"https://myawesomewebsite.tld","first_name":"Jamie","last_name":"Cook","nickname":"JamieC","name":"Jamie Cook","billing_address_1":"1234 Somewhere Place","billing_address_2":"Suite ABC","billing_city":"Anywhere","billing_state":"CA","billing_postcode":"12345-678","billing_country":"US","roles":["student"]}'