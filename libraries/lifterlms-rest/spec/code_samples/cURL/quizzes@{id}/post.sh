curl --request POST \
  --url https://example.tld/wp-json/llms/v1/quizzes/%7Bid%7D \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","slug":"final-exam","post_type":"llms_quiz","status":"publish","lesson_id":789,"passing_percent":65,"limit_attempts":true,"allowed_attempts":5,"limit_time":true,"time_limit":90,"show_correct_answer":false,"random_questions":false,"can_be_resumed":false,"disable_retake":false,"title":"Final Exam","content":"<h2>Lorem ipsum dolor sit amet.</h2>\\n\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>"}'