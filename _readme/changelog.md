== Changelog ==


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


= v3.33.1 - 2019-06-25 =
------------------------

##### Updates

+ Added method to retrieve the load priority of integrations.
+ The capabilities used to determine if uses can clone and export courses now check `edit_course` instead of `edit_post`.

##### Bug Fixes

+ Fixed an issue which would cause the "Net Sales" line to sometimes display as a bar on the sales revenue reporting chart.
+ Fixed an issue causing a PHP notice to be logged when viewing the sales reporting screen.
+ Fixed an issue causing backslashes to be added before quotation marks in access plan descriptions.
+ Integration classes are now loaded in the order defined by the integration class.
+ Fixed an issue causing a PHP error when viewing the admin logs screen when no logs exist.


= v3.33.0 - 2019-05-21 =
------------------------

##### Updates

+ Added the ability for site administrators to delete (completely remove) enrollment records from the database.
+ Catalogs sorted by Order (`menu_order`) now have an additional sort (by post title) to improve ordering consistency for items with the same order, thanks [@pondermatic](https://github.com/pondermatic)!
+ Hooks in the dashboard order review template now pass the `LLMS_Order`.

##### LifterLMS Blocks

+ Updated to version 1.5.1
+ All blocks are now registered only for post types where they can actually be used.
+ Only register block visibility settings on static blocks. Fixes an issue causing core (or 3rd party) dynamic blocks from being managed within the block editor.

##### Bug Fixes

+ If an enrolled student accesses checkout for a course/membership they're already enrolled in they will be shown a message stating as much.
+ Removed a redundant check for the existence of an order on the dashboard order review template.
+ When an order is deleted, student enrollment records for that order will be removed. This fixes an issue causing admins to not be able to manage the enrollment status of a student enrolled via a deleted order.
+ Fix issue causing errors when using the `[lifterlms_lesson_mark_complete]` shortcode on course post types.
+ Fixed an issue causing quiz questions to generate publicly accessible permalinks which could be indexed by search engines.

##### Templates Changed

+ [course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/templates/myaccount/view-order.php)


= v3.32.0 - 2019-05-13 =
------------------------

##### Updates

+ Added Membership reporting
+ Added the ability to restrict coupons to courses and memberships which are in draft or scheduled status.
+ When recurring payments are disabled, output a "Staging" bubble on the "Orders" menu item.
+ Recurring recharges now add order notes and trigger actions when gateway or recurring payment status errors are encountered.
+ When managing recurring payment status through the warning notice, stay on the same page and clear nonces instead of redirecting to the LifterLMS Settings screen.
+ Updated the Action Scheduler library to the latest version (2.2.5)
+ Exposed the Action Scheduler's scheduled actions interface as a tab on the LifterLMS Status page.

##### LifterLMS Blocks

+ Updated to version 1.4.1.
+ Fixed issue causing asset paths to have invalid double slashes.
+ Fixed issue causing frontend css assets to look for an unresolvable dependency.

##### Bug Fixes

+ Fixed an issue allowing instructors to view a list of students from courses and memberships they don't have access to.
+ WooCommerce compatibility filters added in 3.31.0 are now scheduled at `init` instead of `plugins_loaded`, resolves conflicts with several WooCommerce add-ons which utilize core WC functions before LifterLMS functions are loaded.


= v3.31.0 - 2019-05-06 =
------------------------

##### Updates

+ Tested to WordPress 5.2
+ Adds explicit support for the twentynineteen default theme.
+ The main students reporting table can now be filtered to show only students enrolled in a specific course or membership.
+ Resolve conflict with WooCommerce (3.6 and later) resulting in 404s on the dashboard endpoints "lost password", "order history", and "edit account".
+ Adds a dynamic filter (`llms_notification_view{$trigger_id}_basic_options`) to basic (pop-over) notifications to allow configuration of their settings.
+ The filter `llms_plan_get_checkout_url` now passes a 3rd parameter: `$check_availability`
+ Improves `LLMS_Course_Data` and `LLMS_Quiz_Data` classes by adding shared functionality to a shared abstract, `LLMS_Abstract_Post_Data`
+ Changed access on class methods in `LLMS_Shortcode_Courses` from private to protected, thanks [@andrewvaughan](https://github.com/andrewvaughan)!

##### Bug fixes

+ Treats `post_excerpt` data as HTML instead of plain text. Fixes an issue resulting in HTML tags being stripped from lesson excerpts when duplicating a lesson in the course builder or importing lessons via the course importer.
+ Fix an issue allowing access plan sales prices to be set as negative values.

##### LifterLMS Blocks

+ Updated to LifterLMS Blocks 1.4.0.
+ Adds an "unmigration" utility to LifterLMS -> Status -> Tools & Utilities which can be used to remove LifterLMS blocks from courses and lessons which were migrated to the block editor structure.
+ This tool is only available when the Classic Editor plugin is installed and enabled and it will remove blocks from ALL courses and lessons regardless of whether or not the block editor is being utilized on that post.

##### Deprecations

+ `LLMS_Query::add_query_vars()` use `LLMS_Query::set_query_vars()` instead.


= v3.30.3 - 2019-04-22 =
------------------------

##### Updates

+ Fixed typos and spelling errors in various strings.
+ Corrected a typo in the `content-disposition` header used when exporting voucher CSVs, thanks [@pondermatic](https://github.com/pondermatic)!
+ Improved the quiz attempt grading experience by automatically focusing the remarks field and only toggling the first answer if it's not visible, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Removed commented out code on the Student Dashboard Notifications Tab template, thanks [@tnorthcutt](https://github.com/tnorthcutt)!

##### Bug Fixes

+ Renamed "descrpition" key to "description" found in the return of `LLMS_Instructor()->toArray()`.
+ Fixed an issue causing slashes to be stripped from course content when cloning a course.
+ Fixed an issue causing JS warnings to be thrown in the Javascript console on Course and Membership edit pages on the admin panel due to variables being defined too late, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Fixed an undefined variable notice encountered when filtering quiz attempts on the quiz attempts reporting screen, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Fixed an issue causing slashes to appear before quotation marks when saving remarks on a quiz attempt, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ [@pondermatic](https://github.com/pondermatic) fixed typos and misspellings in comment and docs in over 200 files and while that doesn't concern most users it's worthy of a mention.

##### Deprecations

The following unused classes have been marked as deprecated and will be removed from LifterLMS in the next major release.

+ `LLMS\Users\User`
+ `LLMS_Analytics_Page`
+ `LLMS_Course_Basic`
+ `LLMS_Lesson_Basic`
+ `LLMS_Quiz_Legacy`

##### Template Updates

+ [templates/myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-notifications.php)