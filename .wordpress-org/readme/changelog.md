== Changelog ==


= v3.37.15 - 2020-03-27 =
-------------------------

##### Security Notice

**This releases fixes a security issue. Please upgrade immediately!**

Props to [Omri Herscovici and Sagi Tzadik from Check Point Research](https://www.checkpoint.com/) who found and disclosed the vulnerability resolved in this release.

##### Updates & Bug Fixes

+ Excluded `page.*` events in order to keep the events table small.
+ Fixed error encountered when errors encountered validating custom fields. Thanks to [@wenchen](https://github.com/wenchen)!
+ Fixed issue causing course pagination issues in certain scenarios.

##### LifterLMS REST API Version 1.0.0-beta.11

+ Bugfix: Correctly store user `billing_postcode` meta data.
+ Bugfix: Fixed issue preventing course.created (and other post.created) webhooks from firing.


= v3.37.14 - 2020-03-25 =
-------------------------

+ Update: Added the ability to view the PHP error log file (as defined by `ini_get( 'error_log' )` ) on the LifterLMS -> Status -> Logs page.
+ Update: Added strict comparisons for various condition checks.
+ Bugfix: Fixed an issue where users might be redirected to the wrong course following a course import at the conclusion of the setup wizard.
+ Bugfix: Fixed issue with tracking event data being lost due to cookie size limitations.
+ Bugfix: Fixed issue potentially encountered when checking user capabilities for certificates and achievements.
+ Bugfix: Fixed an issue preventing additional instances of the JS `LLMS.Storage` class from being instantiated.


= v3.37.13 - 2020-03-10 =
-------------------------

+ Remove usage of internal functions marked as deprecated.


= v3.37.12 - 2020-03-10 =
-------------------------

##### Updates

+ Tested up to WordPress Core version 5.4.
+ Added support for post revisions for course, lesson, and mebership post types.

##### Developer updates

+ Added strict comparisons for various condition checks.
+ Added a new filter, `llms_builder_{$post_type}_force_delete` which allows control over whether a post is moved to the trash or immediately deleted when trashed via the course builder.

##### Bugfixes

+ Fixed the name of the "actions" column on the quiz reporting screen.
+ Fixed PHP warnings resulting from functions used to exclude order notes from comment counts.
+ Fixed issue causing order notes to be included in the count displayed on the admin comments list despite their exclusion from the table itself.
+ Fixed PHP notice thrown on the WordPress menu editor interface encountered when student dashboard endpoints have been deleted or removed.
+ Fixed issue causing quotes to be encoded in various email, achievement, and certificate fields.

##### Deprecations

The following have been deprecated with no replacements and will be removed in the next major update:

+ `LLMS_Course_Factory::get_course()`
+ `LLMS_Course_Factory::get_lesson()`
+ `LLMS_Course_Factory::get_product()`
+ `LLMS_Course_Factory::get_quiz()`
+ `LLMS_Course_Factory::get_question()`
+ `LLMS_Course_Handler::get_users_not_enrolled()`


= v3.37.11 - 2020-03-03 =
-------------------------

##### Updates

+ Resolved a conflict with the "Starter Templates" plugin which made it impossible to edit quizzes while the plugin was enabled.

##### Bugfixes

+ Fixed an issue causing lesson post authors to be "lost" when adding an existing lesson to a course.
+ Fixed an issue causing php notices to be generated during existing lesson addition on the course builder.
+ Fixed an issue causing course bbPress forums to be lost when editing that course using the "Quick Edit" function from the courses table.

##### LifterLMS REST v1.0.0-beta.10

+ Added text domain to i18n functions that were missing the domain.
+ Added a "trigger" parameter to enrollment-related endpoints.
+ Added `llms_rest_enrollments_item_schema`, `llms_rest_prepare_enrollment_object_response`, `llms_rest_enrollment_links` filter hooks.
+ Fixed setting roles instead of appending them when updating user, thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed return when the enrollment to be deleted doesn't exist, returns `204` instead of `404`.
+ Fixed 'context' query parameter schema, thanks [@pondermatic](https://github.com/pondermatic)!


= v3.37.10 - 2020-02-19 =
-------------------------

+ Update: Exclude the privacy policy page from the sitewide restriction.
+ Update: Added filter `llms_enable_open_registration`.
+ Fix: Notices are printed on pages configured as a membership restriction redirect page.
+ Fix: Do not apply membership restrictions on the page set as membership's restriction redirect page.
+ Fix: Added flag to print notices when landing on the redirected page.


= v3.37.9 - 2020-02-11 =
------------------------

+ Updated CSS classes used in privacy policy text suggestions per changes in WordPress core 5.3. Thanks [@garretthyder](https://github.com/garretthyder)!
+ Added privacy exported group descriptions. Thanks [@garretthyder](https://github.com/garretthyder)!
+ Added filters `llms_user_enrollment_allowed_post_types` & `llms_user_enrollment_status_allowed_post_types` which allow 3rd parties to enroll users into additional post types via core enrollment methods.
+ Added option for admin settings fields to show an asterisk for required fields.
+ Added option for integration plugins can now add automatically generated "Settings" link to the plugins screen.
+ Bugfix: Fixed an IE compatibility issue related to usage of `Object.assign()`.


= v3.37.8 - 2020-01-21 =
------------------------

+ Fix: Student quiz attempts are now automatically deleted when a quiz is deleted.
+ Fix: "Orphaned" quizzes (those with no parent course and/or lesson) can be deleted from the Quiz reporting table.
+ Fix: Quiz IDs on the quiz reporting screen now link to the quiz within the course builder. If the quiz is an "orphan" there will be no link.


= v3.37.7 - 2020-01-08 =
------------------------

+ Fix error resulting from undefined default value.
+ Fix PHP 7.4 deprecation notice.


= v3.37.6 - 2019-12-12 =
------------------------

+ New transaction creation date is now specified using `llms_current_time()`.
+ Use the last successful transaction time to calculate from when the previously stored next payment date is in the future.
+ Fixed an issue causing transaction post titles to be recorded with missing data due to invalid `strftime()` placeholders.