== Changelog ==


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