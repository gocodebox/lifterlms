== Changelog ==


v3.38.0-beta.2 - 2019-12-19
-------------------------------

+ Update LifterLMS Blocks to v1.7.3.


v3.38.0-beta.1 - 2019-12-13
-------------------------------

##### Form Management Improvements

+ Forms (registration, checkout, account) are now managed via a block editor interface.
+ Customize field labels, description, and placeholders in a simple WYSIWYG interface.
+ Mark fields as required with a toggle.
+ Reorder fields with drag and drop.
+ Customize layout using block editor columns.
+ Use LifterLMS block-level visibility to conditionally display fields based on enrollment or logged in status.

##### Form Localization

+ Added default country and state/region lists (see the "languages" directory).
+ Country and state forms are now searchable dropdowns that adjusted based on the currently selected country.
+ Each country's locale information (such as what a "post code" is called and whether or not the country has states or post codes) will update automatically based on the selected country.
+ Enqueue select2 on account and checkout pages for searchable dropdowns for country & state.

##### Updates

+ New shortcode `[user]` which is used to output user information in a merge code interface.
+ Improved form field generation via `LLMS_Form_Field` class.
+ LifterLMS Settings: renamed "User Information Options" to "User Privacy Options".
+ Reorganized open registration setting.
+ Use `LLMS.wait_for()` for dependency waiting.
+ Moved checkout template variable declarations to the checkout shortcode controller.
+ Removed field display settings in favor of form customization using the form editors.
+ Organized function files. Some functions have been moved.
+ Function `llms_get_minimum_password_strength_name()` now accepts a parameter to retrieve strength name by key.
+ Use `LLMS.wait_for()` for dependency waiting.

##### LifterLMS Blocks v1.6.0

+ Feature: Added form field blocks for use on the Forms manager.
+ Feature: Add logic for `logged_in` and `logged_out` block visibility options.
+ Update: Added isDisabled property to Search component.
+ Update: Adjusted priority of `render_block` filter to 20.
+ Bug fix: Import `InspectorControls` from `wp.blockEditor` in favor of deprecated `wp.editor`
+ Bug fix: Automatically store course/membership instructor with `post_author` data when the post is created.
+ Bug fix: Pass style rules as camelCase.

##### Removed unused Javascript assets

+ Remove unused bootstrap transiton and collapse scripts.
+ Remove topModal vendor dependency.
+ Remove password strength inline enqueues.

##### Bug fixes

+ Only attempt to add a nonce to the datastore when a nonce exists in the settings object.

##### Deprecations

+ Deprecated `LLMS_Person_Handler::register()` method, use `llms_register_user()` instead.
+ Deprecated `llms_get_minimum_password_strength()` with no replacement.

##### Template Updates

+ templates/checkout/form-checkout.php
+ templates/checkout/form-gateways.php
+ templates/global/form-registration.php


= v3.37.6 - 2019-12-12 =
------------------------

+ New transaction creation date is now specified using `llms_current_time()`.
+ Use the last successful transaction time to calculate from when the previously stored next payment date is in the future.
+ Fixed an issue causing transaction post titles to be recorded with missing data due to invalid `strftime()` placeholders.


= v3.37.5 - 2019-12-09 =
------------------------

+ Update LifterLMS Blocks to v1.7.2: fixes a bug causing the block editor to encounter a fatal error when accessing custom post types that don't support custom fields.


= v3.37.4 - 2019-12-06 =
------------------------

##### Bug Fixes

+ Fixed a bug causing certificate _template_ exports to export the site's homepage instead of the certificate preview.
+ When exporting a certificate template, use the `post_author` to determine what user to use for merge code data.
+ Revert Accounts settings tab page id to "account".

##### LifterLMS Blocks v1.7.1

+ Feature: Add logic for `logged_in` and `logged_out` block visibility options.
+ Update: Added `isDisabled` property to Search component.
+ Update: Adjusted priority of `render_block` filter to 20.
+ Update: Added filter, `llms_block_supports_visibility` to allow modification of the return of the check.
+ Update: Disabled block visibility on registration & account forms to prevent a potentially confusing form creation experience.
+ Update: Added block editor rendering for password type fields.
+ Update: Perform post migrations on `current_screen` instead of `admin_enqueue_scripts`.
+ Update: Update various dependencies to use updated gutenberg packages.
+ Bug fix: Fixed a WordPress 5.3 issues with JSON data affecting the ability to save course/membership instructors.
+ Bug fix: Import `InspectorControls` from `wp.blockEditor` in favor of deprecated `wp.editor`
+ Bug fix: Automatically store course/membership instructor with `post_author` data when the post is created.
+ Bug fix: Pass style rules as camelCase.
+ Bug fix: Fixed an issue causing "No HTML Returned" to be displayed in place of the Lesson Progression block on free lessons when viewed by a logged-out user.


= v3.37.3 - 2019-12-03 =
------------------------


+ Added an action `llms_certificate_generate_export` to allow modification of certificate exports before being stored on the server.
+ Don't unslash uploaded file `tmp_name`, thanks [@pondermatic](https://github.com/pondermatic)!
+ TwentyTwenty Theme Support: Hide site header and footer, and set a white body background in single certificates.
+ Renamed setting field IDs to be unique for open/close wrapper fields on the engagements and account settings pages.
+ Removed redundant functions defined in the `LLMS_Settings_Page` class to reduce code redundancy in account and engagement setting page classes.
+ The `LLMS_Settings_Page` base class now automatically defines actions to save and output settings content.


= v3.37.2 - 2019-11-22 =
------------------------

+ LifterLMS notices will now be displayed on pages defined as a Course or Membership sales page.
+ TwentyTwenty Theme: Updated to use `background-color` property instead of `background` shorthand when adding custom elements to style.
+ Added filter `llms_sessions_end_idle_cron_recurrence` to allow customization of the recurrence of the idle session cleanup cronjob.
+ Added filter `llms_quiz_is_open` to allow customization of whether or not a quiz is available to a student.
+ When adding an client-side tracking events to the always make sure the server-side verification nonce is always set on the storage object.
+ The Course/Membership filter on the main students reporting screen now correctly limits post results based on instructor access.


= v3.37.1 - 2019-11-13 =
------------------------

+ TwentyTwenty Theme: Fixed course information block misalignment.
+ Fixed conflict with WooCommerce resulting from the movement of the deprecated LiftreLMS function `is_filtered()`.


= v3.37.0 - 2019-11-11 =
------------------------

##### Updates

+ Tested and compatible with WordPress core 5.3.
+ Add theme support for the TwentyTwenty core default theme.
+ Improved security and data sanitization in with regards to the SendWP integration connector.

##### LifterLMS Rest API 1.0.0-beta.8

+ Added memberships controller, huge thanks to [@pondermatic](https://github.com/pondermatic)!
+ Added new filters:

  + `llms_rest_lesson_filters_removed_for_response`
  + `llms_rest_course_item_schema`
  + `llms_rest_pre_insert_course`
  + `llms_rest_prepare_course_object_response`
  + `llms_rest_course_links`

+ Improved validation when defining instructors for courses.
+ Improved performance on post collection listing functions.
+ Ensure that a course instructor is always set for courses.
+ Fixed `sales_page_url` not returned in `edit` context.
+ In `update_additional_object_fields()` method, use `WP_Error::$errors` in place of `WP_Error::has_errors()` to support WordPress version prior to 5.1.


= v3.36.5 - 2019-11-05 =
------------------------

+ Add filter: `llms_user_caps_edit_others_posts_post_types` to allow 3rd parties to utilize core methods for determining if a user can manage another users LMS content on the admin panel.


= v3.36.4 - 2019-11-01 =
------------------------

+ Fixes a conflict with CartFlows introduced by a Divi theme compatibility fix added in 3.36.3. Is WordPress complicated or what?


= v3.36.3 - 2019-10-24 =
------------------------

##### Updates

+ Added new `LLMS_Membership` class methods: `get_categories()`, `get_tags()` and `toArrayAfter()` methods. Thanks [@pondermatic](https://github.com/pondermatic)!

##### Compatibility

+ Fixed access plan description conflicts with the Classic Editor block. This also resolves compatibility issues with Elementor which uses a hidden TinyMCE instance.
+ Changed `pre_get_posts` callback from `10` (default) to `15`. Fixes conflict with Divi (and possibly other themes) which prevented LifterLMS catalog settings from functioning properly.

##### Bugfixes

+ Added translation to error message encountered when non-members attempt to purchase a members-only access plan. Thanks [@mrosati84](https://github.com/mrosati84)!
+ Fix return of `LLMS_Generator::set_generator()`.
+ Fixed a typo causing invalid imports from returning the expected error. Thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed issue preventing membership post type settings from saving properly due to incorrect sanitization filters.
+ Fixed issue where `wp_list_pluck()` would run on non arrays.
