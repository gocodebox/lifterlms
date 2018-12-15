== Changelog ==


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


= v3.24.0 - 2018-10-23 =
------------------------

##### "My Grades" Student Dashboard Endpoint

+ A new student dashboard endpoint, "My Grades", has been added
+ The main screen displays a paginated and sortable list of all courses a student is enrolled in and outputs their progress and grade in the courses
+ Students can drill into individual reporting screens for each course where specific details for each course are available for review

##### Grading Enhancements

+ Each lesson can now be assigned an individual "points" value
+ When a course is graded the points assigned to each lesson will be used to calculate the value of the lesson's grade within the overall course grade
+ Lessons can also be assigned a value of "0" to allow a lesson to not count towards the overall grade of the course.
+ Email notifications are now sent to a student when an instructor reviews, grades, or leaves remarks on a quiz attempt.

##### Test Email Notifications

+ An interface and API for sending test email notifications has been added, the following notifications can now be tested:

  + Purchase Receipt
  + Quizzes: Failed (Thanks [@philwp](https://github.com/philwp)!)
  + Quizzes: Graded
  + Quizzes: Passed (Thanks [@philwp](https://github.com/philwp)!)

##### Updates and Enhancements

+ Quiz Passed & Quiz Failed notifications have new names on the admin panel ("Quizzes: Quiz Passed" & "Quizzes: Quiz Failed")
+ The default content for Quiz Passed and Quiz Failed notifications have been enhanced. If you've modified these you can delete your modified content to have your notifications "restored" to the improved defaults.
+ Change the page title of the Student Dashboard page installed via the Setup Wizard to be "Dashboard" instead of "My Courses." Thanks [@philwp](https://github.com/philwp)!
+ In the course builder when a lesson is duplicated, the attached quiz will be duplicated as well
+ Minor increase to performance in the `LLMS_Course->get_lessons()` method
+ Added `student_id` as a parameter passed to the `llms_student_get_progress` filter
+ Updated all access plan templates added in 3.23.0 to ensure `ABSPATH` is defined to prevent direct template access
+ Remove use of deprecated `LLMS_Lesson->get_children_lessons()` in the `LLMS_Course` and `LLMS_Lesson` models as well as in the `course/syllabus.php` template
+ Refactored the `LLMS_Section->get_percent_complete()` method to utilize methods from the `LLMS_Student` model
+ Added the ability for admin table classes to define `<tr>` element CSS classes
+ Admin settings pages with no settings to save (like the Notifications list) no longer display a "Save" button
+ Added actions when creating, updating, and deleting records managed by `LLMS_Abstract_Database_Store` classes
+ Updated system report to include URLs to settings with URLs, adds a small speed boost to support request turn around time.

##### Please Rate & Review LifterLMS on WordPress.org

+ Added a WordPress.org review request link to the footer of LifterLMS admin pages.
+ Added a WordPress.org review request notice which displays a week after installation if the site has 50+ active students.

##### Bug fixes

+ Fixed issue causing HTML entity codes to display in email subject lines. Thanks [@philwp](https://github.com/philwp)!
+ Fixed issue causing post cleanup functions to run queries against unsupported post types.
+ Fixed typos in a handful of i18n functions so that the proper textdomain is now being used
+ Removed `get_option()` call to unused option `lifterlms_logout_endpoint` which ran on WordPress initialization unnecessarily.
+ Removed 3.21.0 fixes for iOS touch issues that are now causing iOS touch issues on quizzes.
+ When an order is deleted, all order transactions will also be deleted. This does not happen until the order is deleted (transactions will remain while the order is in the trash)
+ Fixed an issue causing duplicated quizzes to initially show images for question images & image choices (reorder pictures & picture choice) but the image data would not be properly saved so when returning to the builder or viewing a quiz on the frontend the images would be lost

##### Deprecated Functions & Methods

+ Deprecated `LLMS_Section->get_children_lessons()`, use `LLMS_Section->get_lessons( 'posts' )` instead

##### Template Updates

+ [course/syllabus.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/syllabus.php)
+ [product/access-plan-button.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-button.php)
+ [product/access-plan-description.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-description.php)
+ [product/access-plan-feature.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-feature.php)
+ [product/access-plan-pricing.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-pricing.php)
+ [product/access-plan-restrictions.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-restrictions.php)
+ [product/access-plan-title.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-title.php)
+ [product/access-plan-trial.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-trial.php)
+ [product/free-enroll-form.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/free-enroll-form.php)


= v3.23.0 - 2018-08-27 =
------------------------

##### Access Plan & Pricing Table Template Improvements

+ The pricing table template has been split into multiple templates which are now rendered via action hooks. No visual changes have been made but if you've customized the template using a template override you'll want to review the template changes before updating!
+ New action hooks are available to modify the rendering of access plans in course / membership pricing tables.

  + `llms_access_plan`: Main hook for outputting an entire access plan within the pricing table
  + `llms_before_access_plan`: Called before main content of access plan. Outputs the "Featured" area of plans
  + `llms_acces_plan_content`: Main access plan content. Outputs title, pricing info, restrictions, and description
  + `llms_acces_plan_footer`: Called after main content. Outputs trial info and the checkout / enrollment button

+ Added filters to the returns of many of the functions in the `LLMS_Acces_Plan` model.
+ Minor improvements made to `LLMS_Access_Plan` model

##### Updates and Enhancements

+ Improved handling of empty blank / empty data when adding instructors to courses and memberships
+ Added filters to the "Sales Page Content" type options & functions for courses and memberships to allow 3rd parties to define their own type of sales page functionality
+ Added filters to the saving of access plan data
+ Improved the HTML and added CSS classes to the access plan admin panel html view

##### Bug Fixes

+ Fixes issue causing the "Preview Changes" button on courses to lock the "Update" publishing button which prevents changes from being properly saved.gi
+ Fixed issue causing PHP errors when viewing courses / memberships on the admin panel when an instructor user was deleted
+ Fixed issue causing PHP notices when viewing course / membership post lists on the admin panel when an instructor user was deleted
+ Fixed issue causing PHP warnings to be generated when viewing the user add / edit screen on the admin panel
+ Fixed an issue which would cause access plans to never be available to users. *This bug didn't affect any existing installations except if you wrote custom code that called the `LLMS_Access_Plan::is_available_to_user()` method.*

##### Template Updates

+ [templates/admin/post-types/product-access-plan.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/post-types/product-access-plan.php)
+ [templates/product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/pricing-table.php)


= v3.22.2 - 2018-08-13 =
------------------------

+ Fixed issue causing banners on general settings screen to cause a fatal error when api connection errors occurred
+ Improved CSS on setup wizard