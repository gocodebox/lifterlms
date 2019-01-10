== Changelog ==


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


= v3.24.3 - 2018-11-13 =
------------------------

##### Updates

+ Added user email, login, url, nicename, display name, first name, and last name as fields searched when searching orders. Thanks Thanks [@yojance](https://github.com/yojance)!

##### Bug Fixes

+ Fixed issue causing fatal errors encountered during certificate downloading caused by CSS `<link>` tags existing outside of the `<head>` element.
+ Certificates downloaded by users who can see the WP Admin Bar will no longer show the admin bar on the downloaded certificate
+ Fixed issue on iOS Safari causing multiple choice quiz questions to require a "long press" to be properly selected
+ Fixed issue causing access plan sales to end 36m and 1s prior to end of the day on the desired sale end date. Thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Ensure that fallback url slugs for course & membership archives are translateable.


= v3.24.2 - 2018-10-30 =
------------------------

+ Fix issue causing newline characters to be malformed on course builder description fields, resulting in `n` characters being output in strange places.


= v3.24.1 - 2018-10-29 =
------------------------

##### Updates

+ The shortcode `[lifterlms_hide_content]` now accepts multiple IDs and can specify whether the user must belong to either *all* or *any one* of the specified memberships. Thanks [@yojance](https://github.com/yojance)!
+ The action `llms_voucher_used`, called when a voucher code is used, will now pass the voucher code as a 3rd parameter. Thanks [@yojance](https://github.com/yojance)!

##### Bug Fixes

+ Fixed a typo in engagement drop creation dropdown. Thanks [README1ST](https://github.com/README1ST)!
+ Fixed issue causing backslash characters (`\`) to be removed from course elements (sections, lessons, quizzes, and assignments) constructed in the course builder.
+ Fixed an issue in the 3.16.0 database migration script that would cause migrations to get stuck as a result of malformed data saved in an invalid format.
+ Added processing handlers to payment confirmation form. Fixes an issue which would allow multiple payment confirmation requests to be made (if the form was submitted multiple times before the page reloaded) resulting in duplicate charges.

##### Templates Changed

+  templates/checkout/form-confirm-payment.php