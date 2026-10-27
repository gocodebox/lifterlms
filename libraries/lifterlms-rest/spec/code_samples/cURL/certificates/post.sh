curl --request POST \
  --url https://example.tld/wp-json/llms/v1/certificates \
  --header 'Authorization: Basic REPLACE_BASIC_AUTH' \
  --header 'content-type: application/json' \
  --data '{"date_created":"2019-05-20 17:22:05","date_created_gmt":"2019-05-20 13:22:05","slug":"certificate-of-completion","post_type":"llms_certificate","status":"publish","certificate_title":"Certificate of Completion","sequential_id":1,"size":"LETTER","width":400,"height":400,"unit":"in","orientation":"landscape","margins":[5,5,5,5],"background":"#ffffff","title":"Certificate of Completion Template","content":"<p>Awarded to {first_name} {last_name} on {current_date}.</p>"}'