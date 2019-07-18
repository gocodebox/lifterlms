== Changelog ==


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


= v3.30.2 - 2019-04-09 =
------------------------

+ Added new filter to allow 3rd parties to determine if a `LLMS_Post_Model` field should be added to the `custom` array when converting the post to an array.
+ Added hooks and filters to the `LLMS_Generator` class to allow 3rd parties to easily generate content during course clone and import operations.
+ Fixed an issue causing all available courses to display when the [lifterlms_courses] shortcode is used with the "mine" parameter and the current user viewing the shortcode is not enrolled in any courses.
+ Fixed a PHP undefined variable warning present on the payment confirmation screen.

##### Template Updates

+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)


= v3.30.1 - 2019-04-04 =
------------------------

##### Updates

+ Added handler to automatically resume pending (incomplete or abandoned) orders.
+ Classes extending the `LLMS_Abstract_API_Handler` can now prevent a request body from being sent.
+ Added dynamic filter `'llms_' . $action . '_more'` to allow customization of the "More" button text and url for student dashboard sections. Thanks @[pondermatic](https://github.com/pondermatic).
+ Remove unused CSS code on the admin panel.

##### Bug Fixes

+ Fixed a bug preventing course imports as a result of action priority ordering issues.
+ Function `llms_get_order_by_key()` correctly returns `null` instead of false when no order is found and will return an `int` instead of a numeric string when an order is found.
+ Changed the method used to sort question choices to accommodate numeric choice markers. This fixes an issue in the Advanced Quizzes add-on causing reorder questions with 10+ choices to sort display in the incorrect order.
+ Increased the specificity of LifterLMS element tooltip hovers. Resolves a conflict causing issues on the WooCommerce tax rate management screen.
+ Fixed an issue causing certain fields in the Customizer from displaying a blue background as a result of very unspecific CSS rules, thanks [@Swapnildhanrale](https://github.com/Swapnildhanrale)!
+ Fixed builder deep links to quizzes freezing due to dependencies not being available during initialization.
+ Fixed builder issue causing duplicate copies of questions to be added when adding existing questions multiple times.

##### Template Updates

+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)


= v3.30.0 - 2019-03-21 =
------------------------

##### Updates

+ **Create custom thank you pages with new access plan checkout redirect options.**
+ Added the ability to sort items on the membership auto enrollment table (drag and drop to sort and reorder).
+ Improved the interface and interactions with the membership auto enrollment table settings.

##### LifterLMS Blocks

+ Updated LifterLMS Blocks to 1.3.8.
+ Fixed an issue causing some installations to be unable to use certain blocks due to jQuery dependencies being declared improperly.

##### Bug Fixes

+ Fixed issue preventing courses with the same title from properly displayed on the membership automatic enrollment courses table on the admin panel.
+ Fixed an issue preventing builder custom fields from being able to specify a custom sanitization callback.
+ Fixed an issue preventing builder custom fields from being able to properly save and render multi-select data.

##### Template Updates

+ [templates/product/access-plan-restrictions.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-restrictions.php)
+ [templates/product/free-enroll-form.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/free-enroll-form.php)


= v3.29.4 - 2019-03-08 =
------------------------

+ Fixed an issue preventing users with email addresses containing an apostrophe from being able to login.