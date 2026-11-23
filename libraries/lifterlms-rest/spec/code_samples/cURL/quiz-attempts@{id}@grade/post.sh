curl --request POST \
  --url https://example.tld/wp-json/llms/v1/quiz-attempts/%7Bid%7D/grade \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"questions":[{"id":456,"earned":10,"remarks":"Good work, but you missed the second part of the question."}]}'