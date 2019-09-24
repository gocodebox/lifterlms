== Changelog ==


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


= v3.35.1 - 2019-09-04 =
------------------------

+ Fix instances of improper input sanitization and handling.
+ Include scripts, styles, and images for reporting charts and datepickers


= v3.35.0 - 2019-09-04 =
------------------------

##### Security Notice

+ Fixed a security vulnerability disclosed by the WordPress plugin review team. Please upgrade immediately!

##### Updates

+ Explicitly setting css and js file versions for various static assets..
+ Added data sanitization methods in various form handlers.
+ Added nonce verification to various form handlers.

##### Bug fixes

+ Fixed some translation strings that had literal variables instead of placeholders.
+ Fixed undefined index error encountered when attempting to email a voucher export.
+ Fixed undefined index error when PHP file upload errors are encountered during a course import.

##### Deprecations

The following unused classes have been marked as deprecated and will be removed from LifterLMS in the next major release.

+ LLMS_Analytics_Memberships
+ LLMS_Analytics_Courses
+ LLMS_Analytics_Sales
+ LLMS_Meta_Box_Expiration
+ LLMS_Meta_Box_Video

##### Template Updates

+  [admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/courses/overview.php)
+  [admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/memberships/overview.php)
+  [admin/reporting/tabs/quizzes/attempts.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/attempts.php)
+  [admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/overview.php)
+  [admin/reporting/tabs/students/courses-course.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/students/courses-course.php)
+  [admin/reporting/tabs/students/courses.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/students/courses.php)
+  [loop/featured-image.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop/featured-image.php)
+  [myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/view-order.php)
+  [quiz/results.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results.php)
+  [single-certificate.php](https://github.com/gocodebox/lifterlms/blob/master/templates/single-certificate.php)
+  [single-no-access.php](https://github.com/gocodebox/lifterlms/blob/master/templates/single-no-access.php)
+  [taxonomy-course_cat.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_cat.php)
+  [taxonomy-course_difficulty.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_difficulty.php)
+  [taxonomy-course_tag.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_tag.php)
+  [taxonomy-course_track.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_track.php)
+  [taxonomy-membership_cat.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-membership_cat.php)
+  [taxonomy-membership_tag.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-membership_tag.php)


= v3.34.5 - 2019-08-29 =
------------------------

+ Fixed logic issues preventing pending orders from being completed.

##### Templates Changed

+ [checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)

= v3.34.4 - 2019-08-27 =
------------------------

+ Add a new admin settings field type, "keyval", used for displaying custom html alongside a setting.
+ Added filter `llms_order_can_be_confirmed`.
+ Always bind JS for the login form handler on checkout and registration screens.

##### Templates Changed

+ [checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)

##### LifterLMS REST API v1.0.0-beta.6

+ Fix issue causing certain webhooks to not trigger as a result of action load order.
+ Change "access_plans" to "Access Plans" for better human reading.


= v3.34.3 - 2019-08-22 =
------------------------

+ During payment gateway order completion, use `llms_redirect_and_exit()` instead of `wp_redirect()` and `exit()`.

##### LifterLMS REST API v1.0.0-beta.5

+ Load all required files and functions when authentication is triggered.
+ Access `$_SERVER` variables via `filter_var` instead of `llms_filter_input` to work around PHP bug https://bugs.php.net/bug.php?id=49184.


= v3.34.2 - 2019-08-21 =
------------------------

##### LifterLMS REST API v1.0.0-beta.4

+ Load authentication handlers as early as possible. Fixes conflicts with numerous plugins which load user information earlier than expected by the WordPress core.
+ Harden permissions associated with viewing student enrollment information.
+ Returns a 400 Bad Request when invalid dates are supplied.
+ Student Enrollment objects return student and post id's as integers instead of strings.
+ Fixed references to an undefined function.


= v3.34.1 - 2019-08-19 =
------------------------

+ Update LifterLMS REST to v1.0.0-beta.3

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
