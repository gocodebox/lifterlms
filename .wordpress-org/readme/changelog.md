== Changelog ==


= v4.0.0 - 2020-06-25 =
-----------------------

This is a *major* release. Many backwards incompatible changes have been made that may affect your site if you have custom code which rely on previously deprecated functions or methods. If you're not sure about your custom code, test the upgrade in a [staging site](https://lifterlms.com/docs/staging/).

##### Bug Fixes

+ Fixed an issue encountered during quiz grading.
+ Add RTL language support for popover interfaces found throughout the course builder.
+ Fixed issue encountered in MySQL 8.0 when using the bbPress integration.

##### LifterLMS REST API 1.0.0-beta.13

+ Bugfix: Fixed error response messages on the instructors endpoint.
+ Bugfix: Fixed student progress deletion endpoint issues preventing progress from being fully removed.

##### Action Scheduler Library

Switches from prospress/action-scheduler to woocommerce/action-scheduler. The repository has been moved but it's the same library & upgrades to latest version (3.1.6).

While this is a semantically major upgrade of the library there are no backwards incompatible changes to the public API.

There have been several deprecated functions/classes. The LifterLMS core does not directly use any of these deprecated functions but 3rd parties might and should review the changelog of the library to see if they are affected by any deprecations: https://github.com/woocommerce/action-scheduler/releases.

##### Deprecations

+ Function `LLMS()` is deprecated in favor of `llms()`.

##### Templates Modified

+ templates/global/form-login.php
+ templates/global/form-registration.php

##### Miscellaneous Breaking Changes

**WP Session Manager Library**

Removes the bundled WP Session Manager plugin dependency, all public methods included with this plugin have been removed without direct replacements.

**Removed JS dependencies**

Removes bundled JS bootstrap 3 dependencies: "collapse" and "transition"

**Removed CSS Classes**

Removes classnames from student dashboard login and registration form wrapper elements which conflict with bootstrap causing visual issues.

These classes are not used by the LifterLMS core or add-ons and are a legacy class that hasn't been removed for fear of creating backwards compatibility issues with any custom css, 3rd party themes, etc...

+ templates/global/form-login.php: Removes `col-1` class from the `div.llms-person-login-form-wrapper` element.
+ templates/global/form-registration.php: : Removes `col-2` class from the `div.llms-new-person-form-wrapper` element.

**Removed SVG assets and functionality**

+ LifterLMS no longer utilizes SVGs powered by the `LLMS_Svg` class. The class has been deprecated and removed (see below).
+ The `assets/svg` directory (and all SVG assets contained within) has been removed.
+ The constant `LLMS_SVG_DIR` has been removed.

##### Previously deprecated classes (and files) that have been removed

+ `LLMS_Admin_Analytics`: `includes/admin/class.llms.admin.analytics.php`
+ `LLMS_Analytics`: `includes/class.llms.analytics.php`
+ `LLMS_Analytics_Courses`: `includes/admin/analytics/class.llms.analytics.courses.php`
+ `LLMS_Analytics_Memberships`: `includes/admin/analytics/class.llms.analytics.memberships.php`
+ `LLMS_Analytics_Page`: `includes/admin/analytics/class.llms.analytics.page.php`
+ `LLMS_Analytics_Sales`: `includes/admin/analytics/class.llms.analytics.sales.php`
+ `LLMS_Course_Basic`: `includes/class.llms.course.basic.php`
+ `LLMS_Course_Handler`: `includes/class.llms.course.handler.php`
+ `LLMS_Course_Factory`: `includes/class.llms.course.factory.php`
+ `LLMS_Lesson_Basic`: `includes/class.llms.lesson.basic.php`
+ `LLMS_Meta_Box_Expiration`: `includes/admin/post-types/meta-boxes/class.llms.meta.box.expiration.php`
+ `LLMS_Meta_Box_Video`: `includes/admin/post-types/meta-boxes/class.llms.meta.box.video.php`
+ `LLMS_Number`: `includes/class.llms.number.php`
+ `LLMS_Person`: `includes/class.llms.person.php`
+ `LLMS_Quiz_Legacy`: `includes/class.llms.quiz.legacy.php`
+ `LLMS_Svg`: `includes/class.llms.svg.php`
+ `LLMS_Table_Questions`: `includes/admin/reporting/tables/llms.table.questions.php`
+ `LLMS\Users\User`: `includes/Users/User.php`

##### Previously deprecated class properties that have been removed

+ `LifterLMS->person` (generally accessed via `LLMS()->person`).
+ `LLMS_Analytics_Widget->date_end`
+ `LLMS_Analytics_Widget->date_start`
+ `LLMS_Analytics_Widget->output`
+ `LLMS_Certificate->enabled`
+ `LLMS_Course_Data->$course`
+ `LLMS_Course_Data->$course_id`

##### Previously deprecated class methods that have been removed:

+ `LLMS_Admin_Table::queue_export()`
+ `LLMS_AJAX::get_achievements()`
+ `LLMS_AJAX::get_all_posts()`
+ `LLMS_AJAX::get_associated_lessons()`
+ `LLMS_AJAX::get_certificates()`
+ `LLMS_AJAX::get_courses()`
+ `LLMS_AJAX::get_course_tracks()`
+ `LLMS_AJAX::get_emails()`
+ `LLMS_AJAX::get_enrolled_students()`
+ `LLMS_AJAX::get_enrolled_students_ids()`
+ `LLMS_AJAX::get_lesson()`
+ `LLMS_AJAX::get_lessons()`
+ `LLMS_AJAX::get_lessons_alt()`
+ `LLMS_AJAX::get_memberships()`
+ `LLMS_AJAX::get_question()`
+ `LLMS_AJAX::get_sections()`
+ `LLMS_AJAX::get_sections_alt()`
+ `LLMS_AJAX::get_students()`
+ `LLMS_AJAX::update_syllabus()`
+ `LLMS_Course::get_children_sections()`
+ `LLMS_Course::get_children_lessons()`
+ `LLMS_Course::get_author()`
+ `LLMS_Course::get_author_id()`
+ `LLMS_Course::get_author_name()`
+ `LLMS_Course::get_sku()`
+ `LLMS_Course::get_id()`
+ `LLMS_Course::get_title()`
+ `LLMS_Course::get_permalink()`
+ `LLMS_Course::get_user_postmeta_data()`
+ `LLMS_Course::get_user_postmetas_by_key()`
+ `LLMS_Course::get_checkout_url()`
+ `LLMS_Course::get_start_date()`
+ `LLMS_Course::get_end_date()`
+ `LLMS_Course::get_next_uncompleted_lesson()`
+ `LLMS_Course::get_lesson_ids()`
+ `LLMS_Course::get_syllabus_sections()`
+ `LLMS_Course::get_short_description()`
+ `LLMS_Course::get_syllabus()`
+ `LLMS_Course::get_user_enroll_date()`
+ `LLMS_Course::get_user_post_data()`
+ `LLMS_Course::check_enrollment()`
+ `LLMS_Course::is_user_enrolled()`
+ `LLMS_Course::get_student_progress()`
+ `LLMS_Course::get_membership_link()`
+ `LLMS_Lesson::get_assigned_quiz()`
+ `LLMS_Lesson::get_drip_days()`
+ `LLMS_Lesson::mark_complete()`
+ `LLMS_PlayNice::divi_fb_wc_product_tabs_after()`
+ `LLMS_PlayNice::divi_fb_wc_product_tabs_before()`
+ `LLMS_PlayNice::wc_is_account_page()`
+ `LLMS_Post_Instructors::get_defaults()`
+ `LLMS_Query::set_dashboard_pagination()`
+ `LLMS_Query::add_query_vars()`
+ `LLMS_Question::get_correct_option()`
+ `LLMS_Question::get_correct_option_key()`
+ `LLMS_Question::get_options()`
+ `LLMS_Quiz::get_assoc_lesson()`
+ `LLMS_Quiz::get_passing_percent()`
+ `LLMS_Quiz::get_remaining_attempts_by_user()`
+ `LLMS_Quiz::get_time_limit()`
+ `LLMS_Quiz::get_total_allowed_attempts()`
+ `LLMS_Quiz::get_total_attempts_by_user()`
+ `LLMS_Quiz_Attempt::get_status()`
+ `LLMS_Shortcode_My_Account::lost_password()`
+ `LLMS_Section::count_children_lessons()`
+ `LLMS_Section::delete()`
+ `LLMS_Section::get_children_lessons()`
+ `LLMS_Section::remove_all_child_lessons()`
+ `LLMS_Section::remove_child_lesson()`
+ `LLMS_Section::set_order()`
+ `LLMS_Section::set_title()`
+ `LLMS_Section::update()`
+ `LLMS_Session::init()`
+ `LLMS_Session::maybe_start_session()`
+ `LLMS_Session::set_expiration_variant_time()`
+ `LLMS_Session::set_expiration_time()`
+ `LLMS_Session::use_php_sessions()`
+ `LLMS_Student::delete_quiz_attempt()`
+ `LLMS_Student::get_best_quiz_attempt()`
+ `LLMS_Student::get_quiz_data()`
+ `LLMS_Student::has_access()`
+ `LLMS_Student_Dashboard::output_courses_content()`
+ `LLMS_Student_Dashboard::output_dashboard_content()`
+ `LLMS_Student_Dashboard::output_notifications_content()`
+ `LLMS_Widget_Course_Progress::widget_contents()`

##### Previously deprecated functions that have been removed

+ `is_filtered()`
+ `lifterlms_template_loop_view_link()`
+ `llms_add_user_table_columns()`
+ `llms_add_user_table_rows()`
+ `llms_create_new_person()`
+ `llms_get_question()`
+ `llms_get_quiz()`
+ `llms_set_user_password_rest_key()`
+ `llms_setup_product_data()`
+ `llms_setup_question_data()`
+ `llms_verify_password_reset_key()`

##### Previously deprecated hooks that have been removed

+ Action: `lifterlms_before_memberships_loop_item_title`
+ Action: `lifterlms_after_memberships_loop_item_title`
+ Action: `lifterlms_after_memberships_loop_item_title`
+ Filter: `lifterlms_completed_transaction_message`
+ Filter: `lifterlms_is_filtered`
+ Filter: `lifterlms_get_analytics_pages`
+ Filter: `lifterlms_analytics_tabs_array`

##### Previously deprecated shortcodes that have been removed

+ `[courses]`
+ `[lifterlms_user_statistics]`

##### Previously deprecated templates that have been removed

+ `templates/loop/view-link.php`

##### Previously deprecated global variables that have been removed

+ `$product`
+ `$question`


= v3.41.1 - 2020-06-23 =
------------------------

+ Apply restrictions to post content and excerpts during WP REST requests.


v4.0.0-rc.1 - 2020-06-18
----------------------------

View release notes at [https://make.lifterlms.com/2020/06/18/lifterlms-version-4-0-0-rc-1/](https://make.lifterlms.com/2020/06/18/lifterlms-version-4-0-0-rc-1/).


= v3.41.0 - 2020-06-12 =
------------------------

##### Bug Fixes

+ Fix issues encountered when a user role with the `edit_users` capability has multiple LifterLMS roles (like Student).

##### LifterLMS 4.0.0 Release Preparation

LifterLMS 4.0.0, our first major release in several years, is nearing the end of it's beta testing cycle. Many unused legacy functions, classes, and files are being removed in version 4.0.0 and well as many functions, classes, and files that were previously deprecated.

The following is a list of items that have not been previously deprecated but will be removed from LifterLMS 4.0.0.

For full details on the release, information on beta testing, and more, see our [blog post on the release](https://make.lifterlms.com/2020/06/01/preparing-for-lifterlms-4-0-0/).

##### Deprecations

The WP Session Manager plugin / library that is bundled into the LifterLMS core code base is deprecated from our code base and is being fully removed in favor of an internal session manager.

The bundled Javascript Boostrap 3 modules, "collapse" and "transition" are deprecated from our codebase and are being removed.

The following CSS classes are deprecated and will be removed:

+ `templates/global/form-login.php`: The `col-1` class from the `div.llms-person-login-form-wrapper` element will be removed.
+ `templates/global/form-registration.php`: : The `col-2` class from the `div.llms-new-person-form-wrapper` element will be removed.

The following classes are deprecated:

+ `LLMS_Number`: `includes/class.llms.number.php`
+ `LLMS_Person`: `includes/class.llms.person.php`
+ `LLMS_Table_Questions`: `includes/admin/reporting/tables/llms.table.questions.php`

The following class methods are deprecated:

+ `LLMS_PlayNice::divi_fb_wc_product_tabs_after()`
+ `LLMS_PlayNice::divi_fb_wc_product_tabs_before()`
+ `LLMS_Question::get_correct_option()`
+ `LLMS_Question::get_correct_option_key()`
+ `LLMS_Quiz::get_passing_percent()`, use `LLMS_Quiz::get( 'passing_percent' )` instead.
+ `LLMS_Quiz::get_assoc_lesson()`, use `LLMS_Quiz::get( 'lesson_id' )` instead.
+ `LLMS_Session::init()`
+ `LLMS_Session::maybe_start_session()`
+ `LLMS_Session::set_expiration_variant_time()`
+ `LLMS_Session::set_expiration_time()`
+ `LLMS_Session::use_php_sessions()`

The following class properties are deprecated:

+ `LifterLMS->person` (generally accessed via `LLMS()->person`).

The following functions are deprecated:

+ `lifterlms_template_loop_view_link()`
+ `llms_add_user_table_columns()`
+ `llms_add_user_table_rows()`
+ `llms_get_question()`
+ `llms_get_quiz()`
+ `llms_setup_product_data()`
+ `llms_setup_question_data()`

The following global variables are deprecated:

+ `$product`
+ `$question`

The following action hooks are deprecated:

+ `lifterlms_before_memberships_loop_item_title`
+ `lifterlms_after_memberships_loop_item_title`
+ `lifterlms_after_memberships_loop_item_title`

The following template file is deprecated:

+ `templates/loop/view-link.php`


v4.0.0-beta.3 - 2020-06-10
------------------------------

View beta release notes at [https://make.lifterlms.com/2020/06/10/lifterlms-version-4-0-0-beta-3/](https://make.lifterlms.com/2020/06/10/lifterlms-version-4-0-0-beta-3/).


= v3.40.0 - 2020-06-09 =
------------------------

##### Updates

+ Adds a 1-click installation connector for the MailHawk email delivery plugin.

##### Bugfixes

+ Fixed an issue encountered during checkout when using a coupon against an access plan with a free trial.

##### Deprecations

+ `LLMS_SendWP::do_remote_install()` will be converted to a protected method and should no longer be called directly.
+ `LLMS_Abstract_Email_Provider::output_css()`

##### Templates updated

+ templates/checkout/form-gateways.php


v4.0.0-beta.2 - 2020-06-04
------------------------------

View beta release notes at [https://make.lifterlms.com/2020/06/04/lifterlms-version-4-0-0-beta-2/](https://make.lifterlms.com/2020/06/04/lifterlms-version-4-0-0-beta-2/).


v4.0.0-beta.1 - 2020-06-01
------------------------------

View beta release notes at [https://make.lifterlms.com/2020/06/01/lifterlms-version-4-0-0-beta-1/](https://make.lifterlms.com/2020/06/01/lifterlms-version-4-0-0-beta-1/).


= v3.39.0 - 2020-05-28 =
------------------------

+ Student Welcome notifications and user registered engagements now fire when users are created via the REST POST requests to the `/students` endpoint.
+ Bugfix: Error encountered when printing full-page certificates on certain themes.

##### LifterLMS REST 1.0.0-beta.12

+ Feature: Added the ability to filter student and instructor collection list requests by various user information fields.
+ Fix: Prevent infinite loops encountered when invalid API keys are utilized.
+ Fix: Add an action used to fire LifterLMS core engagement and notification emails


= v3.38.2 - 2020-05-19 =
------------------------

+ Added a default question type ("choice") to prevent malformed questions from being inadvertently stored in the database.
+ When retrieving question data from the database, automatically fall back to the default question type value if no question type is saved.


= v3.38.1 - 2020-05-11 =
------------------------

+ Update: Added methods for retrieving a list of posts associated with a membership.
+ Bug fix: Fixed an issue causing certificate backgrounds to be cropped or cut in certain circumstances.
+ Bug fix: Fixed an issue generating certificate downloads on servers where `mime_content_type()` does not exist.
+ Bug fix: Fixed an issue which caused bbPress course forum restrictions to stop working.


= v3.38.0 - 2020-04-29 =
------------------------

##### Updates

+ The output of course restriction errors which may prevent enrollment is now displayed in it's own template in favor of the logic being included in the `product/pricing-table.php` template.
+ The course progress bar shortcode will now only display the progress bar to enrolled users. An additional option has been added to the shortcode to allow showing a 0% progress bar to non-enrolled users. [Read more](https://lifterlms.com/docs/shortcodes/#lifterlms_course_progress).
+ The "Course Progress" widget now has an option to optionally display the progress bar to non-enrolled users. By default it will display only to enrolled students.
+ Updates LifterLMS Blocks to version 1.9.0

##### Bug fixes

+ Fixed an issue causing free access plans to bypass course enrollment restrictions like capacity and enrollment time periods.
+ Fixed an issue causing custom checkout success redirects to fail when using gateways that require a payment confirmation step. This fixes an issue in the LifterLMS PayPal payment gateway.
+ Fixed an issue causing deprecation theme-compatibility related deprecation notices to be incorrectly thrown.
+ Fixed spelling error in variable passed to the `product/pricing-table.php` template. The misspelled variable is still being passed to the variable for backwards compatibility.
+ Updated the way notification background processors are dispatched. This fixes an issue in the LifterLMS Twilio add-on.

##### Deprecations

+ `LLMS_Notifications::dispatch_processors()` is deprecated in favor of async dispatching via `LLMS_Notifications::schedule_processors_dispatch()`.

##### Templates Updated

+ templates/product/pricing-table.php

##### LifterLMS Blocks

+ Update: Improved script dependencies definitions.
+ Update: Updated asset paths for consistency with other LifterLMS projects.
+ Update: Updated various WP Core references that have been deprecated (maintains backwards compatibility).
+ Update: The Lesson Progression block is no longer rendered server-side in the block editor (minor performance improvement).
+ Update: Converted the course progress block into a dynamic block. Fixes an issue allowing the progress block to be visible to non-enrolled students.
+ Update: Added a filter on the output of the Pricing Table block: `llms_blocks_render_pricing_table_block`.
+ Bug fix: Fixed an issue encountered when using the WP Core "Table" block.
+ Bug fix: Fixed a few areas where `class` was being used instead of `className` to define CSS classes on elements in the block editor.
+ Bug fix: Fixed a user-experience issues encountered on the Course Information block when all possible information is disabled.
+ Bug fix: Fixed an issue causing visibility attributes to render on blocks that don't support them.
+ Bug fix: Fixed an issue preventing 3rd party blocks from modifying default block visibility settings.
+ Bug fix: Fixed a spelling error visible inside the block editor.
+ Bug fix: Fixed an issue causing the "Course Progress" block to be shown to non-enrolled students and visitors.
+ Bug fix: Removed redundant CSS from frontend.
+ Bug fix: Stop outputting editor CSS on the frontend.
+ Bug fix: Dynamic blocks with no content to render will now only output their empty render messages inside the block editor, not on the frontend.
+ Changes to the Classic Editor Block:
  + The classic editor block will no longer show block visibility settings because it is impossible to use those settings to filter the block on the frontend.
  + In order to apply visibility settings to the classic editor block, place the Classic Editor within a "Group" block and apply visibility settings to the Group.


= v3.37.19 - 2020-04-20 =
-------------------------

##### Updates

+ Added a new debugging tool to clear pending batches created by background processors.
+ Added a new method `LLMS_Abstract_Notification_View::get_object()` which can be used by notification views to override the loading of the post (or object) which triggered the notification.

##### Bug Fixes

+ Added localization to strings on the coupon admin screen. Thanks [parfilov](https://github.com/parfilov)!
+ Fixed issue encountered in metaboxes when the `$post` global variable is not set.


= v3.37.18 - 2020-04-14 =
-------------------------

+ Fix regression introduced in version 3.34.0 which prevented checkout success redirection to external domains.
+ Resolved a conflict with LifterLMS, Divi, and WooCommerce encountered when using the Divi frontend pagebuilder on courses and memberships.
+ Fixed issue causing localization issues when creating access plans, thanks [@mcguffin](https://github.com/mcguffin)!