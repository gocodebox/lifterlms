== Changelog ==


= v3.37.2 - 2019-11-22 =
------------------------

+ LifterLMS notices will now be displayed on pages defined as a Course or Membership sales page.
+ TwentyTwenty Theme: Updated to use `background-color` property instead of `background` shorthand when adding custom elements to style.
+ Added filter `llms_sessions_end_idle_cron_recurrence` to allow customization of the recurrence of the idle session cleanup cronjob.
+ Added filter `llms_quiz_is_open` to allow customization of whether or not a quiz is available to a student.
+ When adding an client-side tracking events to the always make sure the server-side verification nonce is always set on the storage object.
+ The Course/Membership filter on the main students reporting screen now correctly limits post results based on instructor access.


= v3.37.1 - 2019-11-13 =
------------------------

+ TwentyTwenty Theme: Fixed course information block misalignment.
+ Fixed conflict with WooCommerce resulting from the movement of the deprecated LiftreLMS function `is_filtered()`.


= v3.37.0 - 2019-11-11 =
------------------------

##### Updates

+ Tested and compatible with WordPress core 5.3.
+ Add theme support for the TwentyTwenty core default theme.
+ Improved security and data sanitization in with regards to the SendWP integration connector.

##### LifterLMS Rest API 1.0.0-beta.8

+ Added memberships controller, huge thanks to [@pondermatic](https://github.com/pondermatic)!
+ Added new filters:

  + `llms_rest_lesson_filters_removed_for_response`
  + `llms_rest_course_item_schema`
  + `llms_rest_pre_insert_course`
  + `llms_rest_prepare_course_object_response`
  + `llms_rest_course_links`

+ Improved validation when defining instructors for courses.
+ Improved performance on post collection listing functions.
+ Ensure that a course instructor is always set for courses.
+ Fixed `sales_page_url` not returned in `edit` context.
+ In `update_additional_object_fields()` method, use `WP_Error::$errors` in place of `WP_Error::has_errors()` to support WordPress version prior to 5.1.


= v3.36.5 - 2019-11-05 =
------------------------

+ Add filter: `llms_user_caps_edit_others_posts_post_types` to allow 3rd parties to utilize core methods for determining if a user can manage another users LMS content on the admin panel.


= v3.36.4 - 2019-11-01 =
------------------------

+ Fixes a conflict with CartFlows introduced by a Divi theme compatibility fix added in 3.36.3. Is WordPress complicated or what?


= v3.36.3 - 2019-10-24 =
------------------------

##### Updates

+ Added new `LLMS_Membership` class methods: `get_categories()`, `get_tags()` and `toArrayAfter()` methods. Thanks [@pondermatic](https://github.com/pondermatic)!

##### Compatibility

+ Fixed access plan description conflicts with the Classic Editor block. This also resolves compatibility issues with Elementor which uses a hidden TinyMCE instance.
+ Changed `pre_get_posts` callback from `10` (default) to `15`. Fixes conflict with Divi (and possibly other themes) which prevented LifterLMS catalog settings from functioning properly.

##### Bugfixes

+ Added translation to error message encountered when non-members attempt to purchase a members-only access plan. Thanks [@mrosati84](https://github.com/mrosati84)!
+ Fix return of `LLMS_Generator::set_generator()`.
+ Fixed a typo causing invalid imports from returning the expected error. Thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed issue preventing membership post type settings from saving properly due to incorrect sanitization filters.
+ Fixed issue where `wp_list_pluck()` would run on non arrays.

##### LifterLMS Rest API 1.0.0-beta.8

+ Return links to those taxonomies which have an accessible rest route.
+ Initialize `$prepared_item` array before adding values to it. Thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed `sales_page_type` not returned as `none` if course's `sales_page_content_type` property is empty.
+ Load webhook actions a little bit later, to avoid PHP warnings on first plugin activation.
+ Renamed `sales_page_page_type` and `sales_page_page_url` properties, respectively to `sales_page_type` and `sales_page_url` according to the specs.
+ Add missing quotes in enrollment/access default messages shortcodes.
+ Call `set_bulk()` llms post method passing `true` as second parameter, so to instruct it to return a WP_Error on failure.
+ Add missing quotes in enrollment/access default messages shortcodes.
+ `sales_page_page_id` and `sales_page_url` always returned in edit context.
+ Call `set_bulk()` llms post method passing `true` as second parameter, so to instruct it to return a WP_Error on failure.


= v3.36.2 - 2019-10-01 =
------------------------

##### Updates

+ Tested to WordPress 5.3.0-beta.2
+ Upgrade UI on student course reporting screens.
+ Added logic to physically remove from the membership level and remove enrollments data on related products, when deleting a membership enrollment.
+ Lesson metabox "start" drip method made available only if the parent course has a start date set.

##### Bugfixes

+ Fixed JS error when client-side event tracking settings aren't loaded, thanks [@wenchen](https://github.com/wenchen)!
+ Fixed PHP warning resulting from drip the "Course Start" lesson drip settings when no course start date exists.
+ Fixed fatal error encountered when reviewing an order placed with a payment gateway that's been deactivated.

##### Files Updated

+ assets/js/app/llms-tracking.js
+ includes/admin/post-types/meta-boxes/class.llms.meta.box.lesson.php
+ includes/models/model.llms.lesson.php
+ includes/models/model.llms.student.php
+ lifterlms.php

##### Templates Updated

+ templates/admin/post-types/order-details.php
+ templates/admin/reporting/tabs/students/courses-course.php


= v3.36.1 - 2019-09-24 =
------------------------

##### Updates

+ Include SendWP Connector in LifterLMS Engagement Settings.
+ Removed usage of `WP_Error::has_errors()` to support WordPress version prior to 5.1.
+ Improve performances when checking if an event is valid in `LLMS_Events->is_event_valid()`.
+ Remove redundant check on `is_singular()` and `is_post_type_archive()` in `LLMS_Events->should_track_client_events()`.

##### Bugfixes

+ Fixed a compatibility issue with FitVids.js causing excess white space displayed around videos when using the library, WP plugin, or themes that utilize the library.
+ Fixed an issue allowing recurring charges to continue processing after the order or customer had been deleted from the site.
+ Fixed issue causing Membership Restriction settings from properly saving.
+ Fixed issue that allowed instructors to see all quizzes on a site when the instructor had either no courses or only empty courses (courses with no lessons).
+ Fixed "Last Seen" column displaying wrong date when the student last login date was saved as timestamp.
+ Fixed an issue causing popover notifications to be skipped (never displayed) as a result of redirects.


= v3.36.0 - 2019-09-16 =
------------------------

##### User Interaction event and session Tracking

+ Added user interaction tracking for the following events:

  + User sign in and out.
  + Page load and exit (for LMS content)
  + Page focus and blur (for LMS content)
  + And more to come

+ Interaction events are grouped into sessions automatically. A session is "closed" after 30 minutes of inactivity or a log-out event.
+ Added "Last Seen" student reporting column which reports the last recorded activity for the student.

##### Enhancements

+ Automatically hydrate when calling LLMS_Abstract_Database_Store::to_array().
+ Added CSS to make course and lesson video embeds automatically responsive.

##### Bug Fixes

+ Correctly pass the `$remember` variable when using `llms_set_person_auth_cookie()`.
+ Fixed undefined index error when retrieving an unset value from an unsaved database model.
+ Fix issue causing quotes to be encoded in shortcodes used in course and membership restriction message settings fields.
+ Fix issue preventing manual updates of order dates (next payment, trial expiration, and access expiration) from being saved properly.


= v3.35.2 - 2019-09-06 =
------------------------

+ When sanitizing settings, don't strip tags on editor and textarea fields that allow HTML.
+ Added JS filter `llms_lesson_rerender_change_events` to lesson editor view re-render change events.