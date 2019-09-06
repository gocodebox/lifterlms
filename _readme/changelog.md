== Changelog ==


= v3.35.2 - 2019-09-06 =
------------------------

+ When sanitizing settings, don't strip tags on editor and textarea fields that allow HTML.
* Added JS filter `llms_lesson_rerender_change_events` to lesson editor view re-render change events.


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



= v3.34.0 - 2019-08-15 =
------------------------

##### LifterLMS REST API v1.0.0-beta.1

+ A robust REST API is now included in the LifterLMS core.
+ Create API Keys to consume and manage LifterLMS resources and students from external applications.
+ Create webhooks to pass LifterLMS resource data to external applications (like Zapier!).
+ The full API specification can be found at [https://gocodebox.github.io/lifterlms-rest/](https://gocodebox.github.io/lifterlms-rest/).

##### Student management capabilities

+ Explicit capabilities have been added to determine which users can create, view, update, and delete students.
+ Admins and LMS Managers have all student management capabilities.
+ Instructors and instructors assistants are granted limited view capabilities allowing them to only view students enrolled in their own courses/memberships.
+ Added the `list_users` capability to the "Instructor" role, allowing instructor's to better view and manage their assistant instructors.
+ The new capabilities are: `create_students`, `view_students`, `view_others_students`, `edit_students`, `edit_others_students`, `delete_students`, & `delete_others_students`.

##### Updates

+ Added new actions to help differentiate enrollment creation and update events.
+ Added methods and logic for managing user management of other users.
+ Added a filter `llms_table_get_table_classes` to LifterLMS admin tables which allows customization of the CSS classes applied to the `<table>` elements. Thanks  [@pondermatic](https://github.com/pondermatic)!
+ Added a filter `llms_install_get_schema` to the database schema to allow 3rd parties to run table installations alongside the core.
+ Added the ability to pull "raw" (unfiltered) data from the database via classes extending the `LLMS_Post_Model` abstract.
+ Added a `bulk_set()` method to the `LLMS_Post_Model` abstract allowing the updating of multiple properties in one command.
+ Added `comment_status`, `ping_status`, `date_gmt`, `modified_gmt`, `menu_order`, `post_password` as gettable\settable post properties via the `LLMS_Post_Model` abstract.
+ Links on reporting tables are now the proper color.
+ The `editable_roles` filter which determines which roles can manage which other roles is now always loaded (instead of being loaded only on the admin panel).
+ Updated LifterLMS Blocks to 1.5.2

##### Bug Fixes

+ Fixed an issue preventing the `user_url` property from being retrieved by the `get()` method of the `LLMS_Abstract_User_Data` class.
+ Fixed an issue causing the `LLMS_Instructors::get_assistants()` method to return assistants for the currently logged in user instead of the instructor of the instantiated object.
+ Fixed an issue which would allow LMS Managers to edit and delete site administrators.

##### Deprecations

**The following functions and methods have been marked as deprecated and will be removed from LifterLMS with the next major release.**

+ LLMS_Course::get_children_sections() use LLMS_Course::get_sections( 'posts' )" instead
+ LLMS_Course::get_children_lessons() use LLMS_Course::get_lessons( 'posts' )" instead
+ LLMS_Course::get_author()
+ LLMS_Course::get_author_id() use LLMS_Course::get( "author" ) instead
+ LLMS_Course::get_author_name()
+ LLMS_Course::get_sku() use LLMS_Course::get( "sku" ) instead
+ LLMS_Course::get_id() use LLMS_Course::get( "id" ) instead
+ LLMS_Course::get_title() use get_the_title() instead
+ LLMS_Course::get_permalink() use get_permalink() instead
+ LLMS_Course::get_user_postmeta_data()
+ LLMS_Course::get_user_postmetas_by_key()
+ LLMS_Course::get_checkout_url()
+ LLMS_Course::get_start_date() use LLMS_Course::get_date( "start_date" ) instead
+ LLMS_Course::get_end_date() use LLMS_Course::get_date( "end_date" ) instead
+ LLMS_Course::get_next_uncompleted_lesson()
+ LLMS_Course::get_lesson_ids() use LLMS_Course::get_lessons( "ids" ) instead
+ LLMS_Course::get_syllabus_sections() use LLMS_Course::get_sections() instead
+ LLMS_Course::get_short_description() use LLMS_Course::get( "excerpt" ) instead
+ LLMS_Course::get_syllabus() use LLMS_Course::get_sections() instead
+ LLMS_Course::get_user_enroll_date()
+ LLMS_Course::get_user_post_data()
+ LLMS_Course::check_enrollment()
+ LLMS_Course::is_user_enrolled() use llms_is_user_enrolled() instead
+ LLMS_Course::get_student_progress() use LLMS_Student::get_progress() instead
+ LLMS_Course::get_membership_link()


= v3.33.2 - 2019-06-26 =
------------------------

+ It is now possible to send test copies of the "Student Welcome" email to yourself.
+ Improved information logged when an error is encountered during an email send.
+ Add backwards compatibility for legacy add-on integrations priority loading method.
+ Fixed undefined index notice when viewing log files on the admin status screen.