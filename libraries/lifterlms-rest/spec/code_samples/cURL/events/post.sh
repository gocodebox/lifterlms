curl --request POST \
  --url https://example.tld/wp-json/llms/v1/events \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","slug":"live-q-and-a","post_type":"llms_scheduled_event","status":"publish","location":"https://zoom.us/j/123456789","start_date":"2019-06-01","start_time":"14:00:00","end_date":"2019-06-01","end_time":"15:00:00","timezone":"America/New_York","is_all_day":false,"products":[1234,5678],"title":"Live Q&A","excerpt":"Join us for a live Q&A session with the instructor."}'