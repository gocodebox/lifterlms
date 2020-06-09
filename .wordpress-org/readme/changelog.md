== Changelog ==


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


= v3.37.15 - 2020-03-27 =
-------------------------

##### Security Notice

**This releases fixes a security issue. Please upgrade immediately!**

Props to [Omri Herscovici and Sagi Tzadik from Check Point Research](https://www.checkpoint.com/) who found and disclosed the vulnerability resolved in this release.

##### Updates & Bug Fixes

+ Excluded `page.*` events in order to keep the events table small.
+ Fixed error encountered when errors encountered validating custom fields. Thanks to [@wenchen](https://github.com/wenchen)!
+ Fixed issue causing course pagination issues in certain scenarios.

##### LifterLMS REST API Version 1.0.0-beta.11

+ Bugfix: Correctly store user `billing_postcode` meta data.
+ Bugfix: Fixed issue preventing course.created (and other post.created) webhooks from firing.