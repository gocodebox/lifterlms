== Changelog ==


v3.27.0 - 2019-01-??
------------------------

##### Updates

+ Updated checkout javascript to expose an error addition functions
+ Removed display order field from payment gateway settings in favor of using the gateway table sortable list
+ Abstracted the checkout form submission functionality into a callable function not directly tied to `$_POST` data
+ Added function for checking if request is a REST request
+ Fix checkout nonce to have a unique ID & name

##### Template Updates

+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)


v3.26.? - 2019-01-??
------------------------

+ Fixed a bug preventing viewing quiz results for quizzes with questions that have been deleted.
+ Fixed a bug causing a PHP Notice to be output when registering a new user with a valid voucher.


= v3.26.4 - 2019-01-16 =
------------------------

+ Update to [LifterLMS Blocks 1.3.2](https://make.lifterlms.com/2019/01/15/lifterlms-blocks-version-1-3-1/), fixing an issue preventing template actions from being removed from migrated courses & lessons.


= v3.26.3 - 2019-01-15 =
------------------------

##### Updates

+ Fix issue preventing course difficulty and course length from being edited when using the classic editor plugin.
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


= v3.25.2 - 2018-12-13 =
------------------------

+ Added new filters to the `LLMS_Product` model.
+ Fix issue with student dashboard login redirect causing a white screen on initial login.


= v3.25.1 - 2018-12-12 =
------------------------

##### Updates

+ Editor blocks now display a lock icon when hovering/selecting a block which corresponds to the enrollment visibility settings of the block.
+ Removal of core actions is now handled by a general migrator function instead of by individual blocks.

##### Bug fixes

+ Fixed issue preventing strings from the lifterlms-blocks package from being translateable.
+ Fix issue causing block visibility options to not be properly set when enrollment visibility is first enabled for a block.
+ Fixed compatibility issue with Yoast SEO Premium redirect manager settings, thanks [@moorscode](https://github.com/moorscode)!
+ Fixed typo preventing tag size options (or filters) of course information block from functioning properly. Thanks [@tnorthcutt](https://github.com/tnorthcutt)!

##### Templates Changed

+ [templates/course/meta-wrapper-start.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/meta-wrapper-start.php)


= v3.25.0 - 2018-12-05 =
------------------------

##### WordPress 5.0 Ready!

+ **Tested with WordPress core 5.0 (Gutenberg)!**
+ Editor Blocks: Course and Lesson layouts are now (preferrably) powered by various editor blocks.
+ When a block is added to a course or lesson, the template hook that automatically outputs that element is removed automatically (preventing duplicates).
+ If you use the LifterLMS Labs: Action Manager you may no longer need it!
+ Course & Membership instructors are now managed through an editor "plugin". Check out the rocket icon near the "Publish/Update" button.
+ Instructor metabox will load conditionally based on presence of the block editor
+ New courses and lessons will automatically have a preloaded block editor template
+ Courses and lessons will automatically be "migrated" to these templates when edited on the admin panel
+ Various course settings conditionally load based on the presence of the block editor
+ Added filter to the headline size in the `course/meta-wrapper-start.php` template. Allows customization of headline via the "Course Information" block settings.
+ If you're not ready for WordPress 5.0 you can still upgrade LifterLMS. This release is fully functional without the block editor.

##### Bug Fixes

+ Fixed typo in `quiz/start-button.php` template.
+ Fixed error occurring during activation of LaunchPad via the Add-Ons & More screen.
+ Fixed issue causing quiz reporting screens to be blank for users without `view_others_lifterlms_reports` capabilities.

##### Templates Changed

+ [templates/course/author.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/author.php)
+ [course/meta-wrapper-start.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/meta-wrapper-start.php)
+ [quiz/start-button.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/start-button.php)