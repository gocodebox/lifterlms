#!/bin/bash
#
# Bootstrap the wp-env test environment with LifterLMS test data.
# Run after `npx wp-env start`.
#

set -e

CLI="npx wp-env run tests-cli --"

# 1. Enable pretty permalinks and activate LifterLMS.
$CLI wp rewrite structure '/%postname%/' --hard
$CLI wp plugin activate lifterlms

# 2. Run the LifterLMS setup wizard equivalent (create required pages + settings).
#    Uses `wp post list` to avoid creating duplicates on subsequent runs.
DASHBOARD_ID=$($CLI wp post list --post_type=page --name=dashboard --field=ID --posts_per_page=1 2>/dev/null | tail -1 || echo "")
if [ -z "$DASHBOARD_ID" ]; then
  DASHBOARD_ID=$($CLI wp post create --post_type=page --post_title='Dashboard' --post_name='dashboard' --post_status=publish --post_content='[lifterlms_my_account]' --porcelain 2>/dev/null | tail -1 || echo "")
fi
if [ -n "$DASHBOARD_ID" ]; then
  $CLI wp option update lifterlms_myaccount_page_id "$DASHBOARD_ID"
fi

CHECKOUT_ID=$($CLI wp post list --post_type=page --name=purchase --field=ID --posts_per_page=1 2>/dev/null | tail -1 || echo "")
if [ -z "$CHECKOUT_ID" ]; then
  CHECKOUT_ID=$($CLI wp post create --post_type=page --post_title='Purchase' --post_name='purchase' --post_status=publish --post_content='[lifterlms_checkout]' --porcelain 2>/dev/null | tail -1 || echo "")
fi
if [ -n "$CHECKOUT_ID" ]; then
  $CLI wp option update lifterlms_checkout_page_id "$CHECKOUT_ID"
fi

# 3. Bootstrap user accounts.
$CLI wp user meta update 1 first_name Chad
$CLI wp user meta update 1 last_name Feldheimer

$CLI wp user create voucher voucher@email.tld --role=student --user_pass=password 2>/dev/null || true
$CLI wp user create validcreds validcreds@email.tld --role=student --user_pass=password 2>/dev/null || true
$CLI wp user create restrictionstester restrictions@email.tld --role=student --user_pass=password 2>/dev/null || true
$CLI wp user create hasacert hasacert@email.tld --role=student --user_pass=password 2>/dev/null || true

# 4. Set options.
$CLI wp option update can_compress_scripts 1

# 5. Bootstrap posts.
INTEGRITY_ID=$($CLI wp post list --post_type=page --name=integrity-test --field=ID --posts_per_page=1 2>/dev/null | tail -1 || echo "")
if [ -z "$INTEGRITY_ID" ]; then
  $CLI wp post create --post_type=page --post_title="Integrity-Test" --post_name="integrity-test" --post_status=publish 2>/dev/null || true
fi

# 6. Create a course with a lesson for restrictions testing.
COURSE_ID=$($CLI wp post list --post_type=course --name=test-course --field=ID --posts_per_page=1 2>/dev/null | tail -1 || echo "")
if [ -z "$COURSE_ID" ]; then
  COURSE_ID=$($CLI wp post create --post_type=course --post_title="Test Course" --post_name="test-course" --post_status=publish --porcelain 2>/dev/null | tail -1 || echo "")
  if [ -n "$COURSE_ID" ]; then
    LESSON_ID=$($CLI wp post create --post_type=lesson --post_title="Test Lesson" --post_name="test-lesson" --post_status=publish --porcelain 2>/dev/null | tail -1 || echo "")
    if [ -n "$LESSON_ID" ]; then
      $CLI wp post meta update "$LESSON_ID" _llms_parent_course "$COURSE_ID" 2>/dev/null || true
    fi
  fi
fi

# 7. Create a free course (course + section + lesson + free access plan) for the
#    enrollment loop test, so a student can self-enroll and complete a lesson.
FREE_COURSE_ID=$($CLI wp post list --post_type=course --name=free-course --field=ID --posts_per_page=1 2>/dev/null | tail -1 || echo "")
if [ -z "$FREE_COURSE_ID" ]; then
  FREE_COURSE_ID=$($CLI wp post create --post_type=course --post_title="Free Course" --post_name="free-course" --post_status=publish --post_content="Welcome to the free course." --porcelain 2>/dev/null | tail -1 || echo "")
  if [ -n "$FREE_COURSE_ID" ]; then
    FREE_SECTION_ID=$($CLI wp post create --post_type=section --post_title="Free Section" --post_status=publish --porcelain 2>/dev/null | tail -1 || echo "")
    $CLI wp post meta update "$FREE_SECTION_ID" _llms_parent_course "$FREE_COURSE_ID" 2>/dev/null || true
    $CLI wp post meta update "$FREE_SECTION_ID" _llms_order 1 2>/dev/null || true

    FREE_LESSON_ID=$($CLI wp post create --post_type=lesson --post_title="Free Lesson" --post_name="free-lesson" --post_status=publish --post_content="Read this and mark it complete." --porcelain 2>/dev/null | tail -1 || echo "")
    $CLI wp post meta update "$FREE_LESSON_ID" _llms_parent_course "$FREE_COURSE_ID" 2>/dev/null || true
    $CLI wp post meta update "$FREE_LESSON_ID" _llms_parent_section "$FREE_SECTION_ID" 2>/dev/null || true
    $CLI wp post meta update "$FREE_LESSON_ID" _llms_order 1 2>/dev/null || true

    FREE_PLAN_ID=$($CLI wp post create --post_type=llms_access_plan --post_title="Free Access" --post_status=publish --porcelain 2>/dev/null | tail -1 || echo "")
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_product_id "$FREE_COURSE_ID" 2>/dev/null || true
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_is_free yes 2>/dev/null || true
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_price 0 2>/dev/null || true
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_frequency 0 2>/dev/null || true
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_availability open 2>/dev/null || true
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_access_expiration lifetime 2>/dev/null || true
    $CLI wp post meta update "$FREE_PLAN_ID" _llms_enroll_text "Enroll" 2>/dev/null || true
  fi
fi

echo "E2E environment bootstrapped successfully."
