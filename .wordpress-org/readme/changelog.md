== Changelog ==


= v3.37.19 - 2020-04-20 =
-------------------------

##### Updates

+ Added a new debugging tool to clear pending batches created by background processors.
+ Added a new method `LLMS_Abstract_Notification_View::get_object()` which can be used by notification views to override the loading of the post (or object) which triggered the notification.

# Bug Fixes

+ Added localization to strings on the coupon admin screen. Thanks [parfilov](https://github.com/parfilov)!
+ Fixed issue encountered in metaboxes when the `$post` global variable is not set.


= v3.37.18 - 2020-04-14 =
-------------------------

+ Fix regression introduced in version 3.34.0 which prevented checkout success redirection to external domains.
+ Resolved a conflict with LifterLMS, Divi, and WooCommerce encountered when using the Divi frontend pagebuilder on courses and memberships.
+ Fixed issue causing localization issues when creating access plans, thanks [@mcguffin](https://github.com/mcguffin)!


= v3.37.17 - 2020-04-10 =
-------------------------

##### Updates

+ Updated the lost password and password reset form handlers for improved error handling and extendability by other plugins.

##### Bug Fixes

+ Fixed a conflict with WooCommerce resulting in password reset issues on the WooCommerce account dashboard.
+ Fixed an issue allowing voucher codes from deleted vouchers to still be redeemed.
+ Fixed an issue with pagination on the courses tab of a users BuddyPress profile.
+ Fixed a typo in the `post_status` query arg when retrieving access plans for a course or membership.

##### Deprecations

+ `LLMS_PlayNice::wc_is_account_page()` is no longer required and is deprecated with no replacement
+ WP core `get_password_reset_key()` should be used in favor of `llms_set_user_password_rest_key()`.
+ WP core `check_password_reset_key()` should be used in favor of `llms_verify_password_reset_key()`.


= v3.37.16 - 2020-03-31 =
-------------------------

+ Bugfix: Fix issue causing student dashboard notification view to work incorrectly.


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