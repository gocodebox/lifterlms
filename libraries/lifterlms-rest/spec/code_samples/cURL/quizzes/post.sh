curl --request POST \
  --url https://example.tld/wp-json/llms/v1/quizzes \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","slug":"final-exam","post_type":"llms_quiz","status":"publish","attempt_limiting":true,"attempts_allowed":1,"time_limiting":true,"time_limit":90,"passing_percentage":65,"show_correct_answer":false,"randomize_questions":false,"parent_id":789,"title":"Final Exam","content":"<h2>Lorem ipsum dolor sit amet.</h2>\\n\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>"}'