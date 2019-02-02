== Changelog ==


= v3.28.1 - 2019-02-01 =
------------------------

+ Fixed an issues preventing exports to be accessible on Apache servers.
+ Fixed an issue causing servers with certain nginx rules to open CSV exports directly instead of downloading them.


= v3.28.0 - 2019-01-29 =
------------------------

##### Updates

+ Updated reporting table export functions to provide immediate download prompts of the files. Exports are generated in real time and you *must* remain on the page while it generates. The good news is if your site had issues with email or cronjobs it'll no longer be an issue for you.
+ Updated lesson metabox to use icons for attached quizzes
+ Added an orange highlight to the admin "Add-Ons & More" menu item
+ Removed unused cron event.

##### LifterLMS Blocks

+ Updated LifterLMS Blocks to 1.3.4
+ Adds support for handling courses & lessons in "Classic Editor" mode as defined by the Divi page builder
+ Skips course and lesson migration when "Classic" mode is enabled.
+ Adds conditions to identify "Classic" mode when the Classic Editor plugin settings are configured to enforce classic (or block) mode for *all* posts.

##### Database Updates

+ Unschedules the aforementioned unused cron event.

##### Bug fixes

+ Fixed an issue preventing the temp directory old file cleanup cron from firing on schedule.
+ During plugin uninstallation the tmp cleanup cron will now be properly unscheduled.
+ Fixed an issue causing notifications on the student dashboard to appear on top of static headers or the WP Admin Bar when scrolling.
+ Fixed an issue preventing manual updating of customer and source information on orders resulting from unfocusable hidden form fields.
+ Fixed mismatched HTML tags on the Admin Add-Ons screen

##### Deprecations

+ Class method: `LLMS_Admin_Table::queue_export()`
+ Class: `LLMS_Processor_Table_To_Csv`


= v3.27.0 - 2019-01-22 =
------------------------

###### Updates

+ Added the ability to add existing questions to a quiz in the course builder. This allows cloning of existing questions as well as attaching "orphaned" questions currently attached to no quizzes.
+ Added the ability to detach questions from quizzes. Coupled with adding existing questions, questions can now be easily moved between quizzes.
+ Added permalink capabilities to the builder to allow linking to specific items within the builder (a lesson, quiz, etc...).
+ Quizzes with 0 possible points will no longer show a Pass/Fail chart with a 0% (failing) grade on quiz results screens.
+ Replaced option `lifterlms_lock_down` which cannot be set via any setting with a filter to reduce database calls. This will have no effect on anyone unless you manually set this option to "no" via a database query. Having done this would allow the admin bar to be shown to students.

##### Bug Fixes

+ Fixed an issue causing the default "Redeem Voucher" and "My Orders" student dashboard endpoint slugs from not having the correct default values. Thanks [@tnorthcutt](https://github.com/tnorthcutt)!
+ Fixed an issue causing quotation marks in quiz question answers to show escaping slashes on results screens.
+ Fixed a bug preventing viewing quiz results for quizzes with questions that have been deleted.
+ Fixed a bug causing a PHP Notice to be output when registering a new user with a valid voucher.

##### Templates Changed

+ [quiz/results-attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt.php)


= v3.26.4 - 2019-01-16 =
------------------------

+ Update to [LifterLMS Blocks 1.3.2](https://make.lifterlms.com/2019/01/15/lifterlms-blocks-version-1-3-1/), fixing an issue preventing template actions from being removed from migrated courses & lessons.


= v3.26.3 - 2019-01-15 =
------------------------

##### Updates

+ Improved pagination methods on Student Dashboard Endpoints
+ "My Notifications" dashboard tab now consistently paginated like other dashboard endpoints
+ Update to [LifterLMS Blocks 1.3.1](https://make.lifterlms.com/2019/01/15/lifterlms-blocks-version-1-3-1/).

##### Bug Fixes

+ Fixed an issue preventing course difficulty and course length from being edited when using various page builders.
+ Fixed issues causing errors on quiz reporting screens for quiz attempts made by deleted users.

##### Deprecated Functions

+ `LLMS_Student_Dashboard::output_notifications_content()` replaced with `lifterlms_template_student_dashboard_my_notifications()`

##### Templates Changed

+ [myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-notifications.php)
+ [admin/reporting/tabs/quizzes/attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/attempt.php)


= v3.26.2 - 2019-01-09 =
------------------------

+ Fast follow to fix incorrect version number pushed to the readme files for 3.26.1 which prevents upgrading to 3.26.1


= v3.26.1 - 2019-01-09 =
------------------------

##### Updates

+ Tested to WordPress 5.0.3
+ Student CSV reports will now bypass cached data during report generation.
+ Add course and membership catalog visibility settings into the block editor.
+ Includes LifterLMS Blocks 1.3.0.

##### Bug Fixes

+ Fixed issue preventing the course instructors metabox from displaying when using the classic editor plugin.
+ Fixed an issue causing membership background enrollment from processing when the course background processor is disabled via filters.
+ Fixed an issue causing errors when reviewing orders on the admin panel which were placed via a payment gateway which is no longer active.
+ Fixed an issue preventing course difficulty and course length from being edited when using the classic editor plugin.
+ Fixed a very convoluted conflict between LifterLMS, WooCommerce, and Elementor explained at https://github.com/gocodebox/lifterlms/issues/730.


= v3.26.0 - 2018-12-27 =
------------------------

+ Adds conditional support for page builders: Beaver Builder, Divi Builder, and Elementor.
+ Fixed issue causing LifterLMS core sales pages from outputting automatic content (like pricing tables) on migrated posts.
+ Student unenrollment calls always bypass cache during enrollment precheck.
+ Membership post type "name" label is now plural (as it is supposed to be).


= v3.25.4 - 2018-12-17 =
------------------------

+ Adds a filter (`llms_blocks_is_post_migrated`) to allow determining if a course or lesson has been migrated to the WP 5.0 block editor.
+ Added a filter (`llms_dashboard_courses_wp_query_args`) to the WP_Query used to display courses on the student dashboard.
+ Fixed issue on course builder causing prerequisites to not be saved when the first lesson in a course was selected as the prereq.
+ Fixed issue on course builder causing lesson settings to be inaccessible without first saving the lesson to the database.


= v3.25.3 - 2018-12-14 =
------------------------

+ Fixed compatibility issue with the Classic Editor plugin when it was added after a post was migrated to the new editor structure.