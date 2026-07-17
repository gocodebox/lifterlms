LifterLMS REST API Changelog
============================

v1.0.8 - 2026-06-29
-------------------

##### Security Fixes

+ Additional checks when authenticating REST API requests.


v1.0.7 - 2026-06-25
-------------------

##### Security Fixes

+ Additional checks when assigning the owner of an API key.


v1.0.6 - 2026-06-04
-------------------

##### Security Fixes

+ Additional checks on item permissions.


v1.0.5 - 2026-04-10
-------------------

##### Updates and Enhancements

+ Handle removal of depreciated SQL_CALC_FOUND_ROWS for counting query results.


v1.0.4 - 2026-04-10
-------------------

##### Updates and Enhancements

+ Using standard WP nonce check functions instead of llms_verify_nonce.


v1.0.3 - 2025-11-11
-------------------

##### Security Fixes

+ Fixes security issue where student and instructor REST APIs can be used to modify roles incorrectly. Thanks [@shark3y](https://github.com/shark3y)!


v1.0.2 - 2024-07-18
-------------------

##### Bug Fixes

+ Removes the unavailable quiz resource link from the lessons resource until the quiz resource is added.
+ Show only instructor and student roles in the instructor and student list responses by default.


v1.0.1 - 2024-07-09
-------------------

##### Bug Fixes

+ Update the processed flag to use all arguments. [#2568](https://github.com/gocodebox/lifterlms-rest/issues/2568)

##### Security Fixes

+ Adds additional security checks and escaping.


v1.0.0 - 2024-01-22
-------------------

__includes/abstracts/class-llms-rest-posts-controller.php__
+ Fix fatals when searching for llms post type based resources but the query post type parameter is forced to be something else.
__includes/models/class-llms-rest-webhook.php__
+ Remove the processed flag as the ActionScheduler prevents multiple additions of the same hook.


v1.0.0-beta.29 - 2023-10-24
---------------------------

##### Bug Fixes

+ Avoid PHP fatal when searching for courses/memberships but the query post type parameter is forced to a different post type. e.g. all post types except `post` excluded from search results. [#299](https://github.com/gocodebox/lifterlms-rest/issues/299)


v1.0.0-beta.28 - 2023-06-08
---------------------------

##### Bug Fixes

+ Rebuild and re-release to avoid including unnecessary heavy files.


v1.0.0-beta.27 - 2023-05-31
---------------------------

##### Updates and Enhancements

+ Replaced use of deprecated `strftime()`.
+ Replaced use of the deprecated `FILTER_SANITIZE_STRING` constant.

##### Developer Notes

+ Fixed unit tests on WordPress 6.2.
+ Allow all the resources to be extended using `register_rest_field()`. [#157](https://github.com/gocodebox/lifterlms-rest#157)
+ Added the ability for all the `WP_Post` and `WP_User` based resources to manage custom meta registered via `register_meta()`. [#157](https://github.com/gocodebox/lifterlms-rest#157)
+ Added `llms_rest_{$object_type}_item_schema` that will allow filtering any resource schema. Additional schema fields, added via `register_rest_field()`, are not included. [#157](https://github.com/gocodebox/lifterlms-rest#157)
+ Added `llms_rest_allow_filtering_{$object_type}_item_schema_to_add_fields` filter hook. It allows adding additional fields using the filter hook `llms_rest_{$object_type}_item_schema` without warnings. By default additional fields should be added via `register_rest_field()`. [#157](https://github.com/gocodebox/lifterlms-rest#157)
+ Deprecated `llms_rest_enrollments_item_schema` and `llms_rest_membership_item_schema` filter hooks in favor of `llms_rest_$object_type_item_schema` where the object type is, respectively, equal to 'students-enrollments' and 'llms-membership'. [#157](https://github.com/gocodebox/lifterlms-rest#157)

##### Performance Improvements

+ Cache results of get_item_schema on controller instances for performance. Additional schema fields, added via `register_rest_field()`, are not cached. [#73](https://github.com/gocodebox/lifterlms-rest#73)


v1.0.0-beta.26 - 2023-02-28
---------------------------

##### Bug Fixes

+ Removed the extra parameter passed to `LLMS_Student::enroll()` during status updates. [#278](https://github.com/gocodebox/lifterlms-rest#278)
+ Fixed an issue that produced the enrollment of the current user into a course when they were trying to enroll an user with ID 0. [#308](https://github.com/gocodebox/lifterlms-rest#308)

##### Developer Notes

+ The LifterLMS Core minimum required version has been raised to version 7.0.2. [#308](https://github.com/gocodebox/lifterlms-rest#308)


v1.0.0-beta.25 - 2022-05-11
---------------------------

##### Updates and Enhancements

+ Stop returning an error when updating resource properties with a value equal to the saved one. [#222](https://github.com/gocodebox/lifterlms-rest#222), [#289](https://github.com/gocodebox/lifterlms-rest#289)

##### Bug Fixes

+ Allow deletion of an unenrolled student's progress. [#173](https://github.com/gocodebox/lifterlms-rest#173)
+ Delete API Keys when user is deleted. [#90](https://github.com/gocodebox/lifterlms-rest#90)


v1.0.0-beta-24 - 2022-03-17
---------------------------

##### Bug Fixes

+ Fixed reference to a non-existent schema property, visibiliy in place of visibility, when updating/adding an access plan.
+ Fixed issue when updating a free access plan. [#267](https://github.com/gocodebox/lifterlms-rest#267)
+ Fixed issue causing the access plan `availability_restrictions` property to always return an empty array. [#269](https://github.com/gocodebox/lifterlms-rest#269)
+ Fixed issue that prevented updating the access plan `redirect_forced` property. [#271](https://github.com/gocodebox/lifterlms-rest#271)
+ Fixed issue updating access plans when there are 6 (max) plans associated with a course/membership. [#272](https://github.com/gocodebox/lifterlms-rest#272)


v1.0.0-beta.23 - 2022-02-23
---------------------------

##### Updates and Enhancements

+ Replaced call to deprecated `LLMS_Section::get_parent_course()` with `LLMS_Section::get( 'parent_course' )`.
+ Replaced the calls to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
+ Replaced deprecated `llms_user_removed_from_membership_level` action hook with `llms_user_removed_from_membership`.


v1.0.0-beta.22 - 2021-12-15
---------------------------

##### Developer Notes

+ The LifterLMS Core minimum required version has been raised to version 6.0.0-alpha.1.
+ Renamed `LLMS_REST_API_Keys_Query` and `LLMS_REST_Webhooks_Query` `preprare_query()` methods to `prepare_query`. [gocodebox/lifterlms#859](https://github.com/gocodebox/lifterlms#859)


v1.0.0-beta.21 - 2021-12-07
---------------------------

##### New Features

+ Post based resources like couress, memberships, section, lessons... are now searchable.


v1.0.0-beta.20 - 2021-10-11
---------------------------

+ Fixed an issue that generated a PHP Fatal when retrieving the list of access plans if logged in as administrator.
+ Fixed the access plan's `access_expires` property format not respecting the specs (Y-m-d H:i:s).
+ Fixed an issue that made the access plan's properties `sale_date_start` and `sale_date_end` to be returned as empty.


v1.0.0-beta.19 - 2021-04-13
---------------------------

+ Added collection filtering by post status for courses, lessons, and memberships.


v1.0.0-beta.18 - 2021-02-09
---------------------------

##### Updates

+ Added Access Plan resource and endpoint.
+ Provide a more significant error message when trying to delete an item without permissions.
+ Use `WP_Http` constants in favor of integers when referencing HTTP status codes.

##### Bug fixes

+ Fixes localization issues where a singular name was used in favor of the expected plural form.
+ Fixed issues where an error object was not properly returned when expected
+ Fixed call to undefined function `llms_bad_request_error()`, must be `llms_rest_bad_request_error()`.
+ Fixed access plans resource link.
+ Fixed wrong trigger retrieved when multiple trigger were present for the same user/post pair on Student Enrollment resources.


v1.0.0-beta.17 - 2020-11-24
---------------------------

+ Bugfix: Fixed an issue with webhooks causing a failed webhook to cause other webhooks to stop triggering.
+ Update: Added improved localization methods when running as a standalone plugin.

##### Breaking Change

+ Method `LLMS_REST_Webhook::is_pending()` has been removed.
+ Database column `pending_delivery` on the `lifterlms_rest_webhooks` table (and related model properties) have been deprecated and scheduled for removal.


v1.0.0-beta.16 - 2020-11-05
---------------------------

+ Improved performance of various database queries.


v1.0.0-beta.15 - 2020-09-21
---------------------------

+ Bugfix: Created lessons will now have the derivative `course_id` property set according to the ID of the lesson's parent section.
+ Bugfix: The `course_id` property of lessons is now properly marked as read-only.


v1.0.0-beta.14 - 2020-07-01
---------------------------

##### Breaking Change

+ `LLMS_REST_Controller::prepare_links()` now requires a second parameter, the `WP_REST_Request` for the current request. Any classes extending and overwriting this method must adjust their method signature to accommodate this change.

##### Bug Fixes

+ Fix issue causing response objects to unintentionally include keys of remapped fields. This error occurs only when extending core controllers and attempting to exclude core fields.


v1.0.0-beta.13 - 2020-06-22
---------------------------

+ Bugfix: Fixed error response messages on the instructors endpoint.
+ Bugfix: Fixed student progress deletion endpoint issues preventing progress from being fully removed.


v1.0.0-beta.12 - 2020-05-27
---------------------------

+ Fix: Prevent infinite loops encountered when invalid API keys are utilized.
+ Fix: Add an action used to fire LifterLMS core engagement and notification emails
+ Feature: Added the ability to filter student and instructor collection list requests by various user information fields.


v1.0.0-beta.11 - 2020-03-30
---------------------------

+ Bugfix: Correctly store user `billing_postcode` meta data.
+ Bugfix: Fixed issue preventing course.created (and other post.created) webhooks from firing.


v1.0.0-beta.10 - 2020-02-28
---------------------------

+ Added text domain to i18n functions that were missing the domain.
+ Fixed setting roles instead of appending them when updating user, thanks [@pondermatic](https://github.com/pondermatic)!
+ Added a "trigger" parameter to enrollment-related endpoints.
+ Added `llms_rest_enrollments_item_schema`, `llms_rest_prepare_enrollment_object_response`, `llms_rest_enrollment_links` filter hooks.
+ Fixed return when the enrollment to be deleted doesn't exist, returns `204` instead of `404`.
+ Fixed 'context' query parameter schema, thanks [@pondermatic](https://github.com/pondermatic)!


v1.0.0-beta.9 - 2019-11-11
--------------------------

##### Updates

+ Added memberships controller, huge thanks to [@pondermatic](https://github.com/pondermatic)!
+ Added new filters:

  + `llms_rest_lesson_filters_removed_for_response`
  + `llms_rest_course_item_schema`
  + `llms_rest_pre_insert_course`
  + `llms_rest_prepare_course_object_response`
  + `llms_rest_course_links`

+ Improved validation when defining instructors for courses.
+ Improved performance on post collection listing functions.

##### Bug fixes

+ Ensure that a course instructor is always set for courses.
+ Fixed `sales_page_url` not returned in `edit` context.
+ In `update_additional_object_fields()` method, use `WP_Error::$errors` in place of `WP_Error::has_errors()` to support WordPress version prior to 5.1.


v1.0.0-beta.8 - 2019-10-24
--------------------------

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


v1.0.0-beta.7 - 2019-09-27
--------------------------

##### Updates

+ Added the following properties to the lesson schema and response object: `drip_date`, `drip_days`, `drip_method`, `public`, `quiz`.
+ Added the following links to lesson objects: `prerequisite` and `quiz`.
+ Use `WP_Error::$errors` in place of `WP_Error::has_errors()` to support WordPress version prior to 5.1.
+ Added `llms_rest_lesson_item_schema`, `llms_rest_pre_insert_lesson`, `llms_rest_prepare_lesson_object_response`, `llms_rest_lesson_links` filter hooks.
+ Course properties `access_opens_date`, `access_closes_date`, `enrollment_opens_date`, `enrollment_closes_date` are now nullable.
+ Course properties `prerequisite` and `prerequisite_track` can be be cleared (set to `0` to signify no prerequisite exists).
+ Added prerequisite validation for courses, if `prerequisite` is not a valid course the course `prerequisite` will be set to `0` and if `prerequisite_track` is not a valid course track, the course `prerequisite_track` will be set to `0`.

##### Bug Fixes

+ Fixed lesson `siblings` link that was using the parent course's id instead of the parent section's id.
+ Fixed lesson `parent` link href, replacing 'section' with 'sections'.
+ Fixed lesson progression callback name when defining the filters to be removed while preparing the item for response.
+ Fixed description of the `post_id` path parameter for student enrollments resources. Thanks [@pondermatic](https://github.com/pondermatic).
+ Fixed section parent course object retrieval method when building the resource links.


v1.0.0-beta.6 - 2019-08-27
--------------------------

+ Fix issue causing certain webhooks to not trigger as a result of action load order.
+ Change "access_plans" to "Access Plans" for better human reading.


v1.0.0-beta.5 - 2019-08-22
--------------------------

+ Load all required files and functions when authentication is triggered.
+ Access `$_SERVER` variables via `filter_var` instead of `llms_filter_input` to work around PHP bug https://bugs.php.net/bug.php?id=49184.


v1.0.0-beta.4 - 2019-08-21
--------------------------

+ Load authentication handlers as early as possible. Fixes conflicts with numerous plugins which load user information earlier than expected by the WordPress core.
+ Harden permissions associated with viewing student enrollment information.
+ Returns a 400 Bad Request when invalid dates are supplied.
+ Student Enrollment objects return student and post id's as integers instead of strings.
+ Fixed references to an undefined function.


v1.0.0-beta.3 - 2019-08-19
--------------------------

##### Interface and Experience improvements during API Key creation

+ Better expose that API Keys are never shown again after the initial creation.
+ Allow downloading of API Credentials as a `.txt` file.
+ Add `required` properties to required fields.

##### Updates

+ Added the ability to CRUD webhooks via the REST API.
+ Conditionally throw `_doing_it_wrong` on server controller stubs.
+ Improve performance by returning early when errors are encountered for various methods.
+ Utilizes a new custom property `show_in_llms_rest` to determine if taxonomies should be displayed in the LifterLMS REST API.
+ On the webhooks table the "Delivery URL" is trimmed to 40 characters to improve table readability.

##### Bug fixes

+ Fixed a formatting error when creating webhooks with the default auto-generated webhook name.
+ On the webhooks table a translatable string is output for the status instead of the database value.
+ Fix an issue causing the "Last" page pagination link to display for lists with 0 possible results.
+ Don't output the "Last" page pagination link on the last page.


v1.0.0-beta.2 - 2019-08-15
--------------------------

+ Filter course taxonomies by the `public` property instead of the `show_in_rest` property.
+ Fixed bug preventing async webhooks from being delivered properly.
+ Only load the main plugin function when loading the main plugin file. Fixes issue when running plugin alongside LifterLMS core with bundled API.


v1.0.0-beta.1 - 2019-08-15
--------------------------

+ Initial public beta release.
