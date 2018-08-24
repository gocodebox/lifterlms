== Changelog ==


v3.23.0 - 2018-08-??
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


= v3.22.1 - 2018-08-06 =
------------------------

+ Fix issue causing themes to appear as requiring updates when using the LifterLMS Helper


= v3.22.0 - 2018-07-31 =
------------------------

+ Frontend notifications are no longer powered by AJAX requests. This change will significantly reduce the number of requests made but will remove the ability for students to receive asynchronouos notifications. This means that notifications will only be displayed on page load as notification polling will no longer occur while a student is on a page (while reading the content a lesson, for example).
+ Course and membership catalogs items in navigation menus will now have expected CSS classes to identify current item and current item parents
+ The admin panel add-ons screen has been reworked to be powered by the lifterlms.com REST api
+ Some visual changes have been made to the add-ons screen
+ The colors on the voucher screen on the admin panel have been updated to match the rest of the interfaces in LifterLMS


= v3.21.1 - 2018-07-24 =
------------------------

+ Fixed issue causing visual issues on checkout summary when using coupons which apply discounts to a plan trial
+ Fixed issue causing `.mo` files stored in the `languages/lifterlms` safe directory from being loaded before files stored in the default location `languages/plugins`
+ Added methods to integration abstract to allow integration developers to automatically describe missing integration dependencies
+ Tested to WordPress 4.9.8

##### Template Updates

+ [templates/checkout/form-summary.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-summary.php)


= v3.21.0 - 2018-07-18 =
------------------------

##### Updates and Enhancements

+ Added new actions before and after global login form HTML: `llms_before_person_login_form` & `llms_after_person_login_form`
+ Settings API can now create disabled fields
+ Added new actions to the checkout form: `lifterlms_pre_checkout_form` && `lifterlms_post_checkout_form`
+ Added CRUD functions for interacting with data located in the `wp_lifterlms_user_postmeta` table
+ Replaced various database queries for CRUD user postmeta data with new CRUD functions
+ Added new utility function to allow splicing data into associative arrays

##### Bug Fixes

+ If all user information fields are disabled, the "Student Information" are will now be hidden during checkout for logged in users instead of displaying an empty information box
+ Fixed plugin compatibility issue with Advanced Custom Fields
+ Fixed issue causing multiple choice quiz questions to require a double tap on some iOS devices
+ Fixed incorrectly named filter causing section titles to not display on student course reporting screens
+ We do not advocate using PHP 5.5 or lower but if you were using 5.5 or lower and encountered an error during bulk enrollment we've fixed that for. Please upgrade to 7.2 though. We all want faster more secure websites.

##### Template Updates

+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)
+ [templates/global/form-login.php](https://github.com/gocodebox/lifterlms/blob/master/templates/global/form-login.php)


= v3.20.0 - 2018-07-12 =
------------------------

+ Updated user interfaces on admin panel for courses and memberships with relation to "Enrolled" and "Non-Enrolled" student descriptions
+ "Enrolled Student Description" is now the default WordPress editor
+ "Non-Enrolled Student Description" is now the "Sales Page"
+ Additional options for sales pages (the content displayed to visitors and non-enrolled students) have been added:
  + Do nothing (show course description)
  + Show custom content (use a WYSIWYG editor to define content)
  + Redirect to a WordPress page (use custom templates and enhance page builder compatibility and capabilities)
  + Redirect to a custom URL (use a sales page hosted on another domain!)
+ Tested to WordPress 4.9.7

= v3.19.6 - 2018-07-06 =
------------------------

+ Fix file load paths in OptimizePress plugin compatibility function


= v3.19.5 - 2018-07-05 =
------------------------

+ Fixed bug causing `select2` multi-selects from functioning as multi-selects
+ Fixed visual issue with `select2` elements being set without a width causing them to be both too small and too large in various scenarios.
+ Fixed duplicate action on dashboard section template

##### Template Updates

+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)


= v3.19.4 - 2018-07-02 =
------------------------

##### Updates and enhancements

+ Bulk enroll multiple users into a course or membership from the Users table on your admin panel. See how at [https://lifterlms.com/docs/student-bulk-enrollment/](https://lifterlms.com/docs/student-bulk-enrollment/)
+ Added event on builder to allow integrations to run trigger events when course elements are saved
+ Added general redirect method `llms_redirect_and_exit()` which is a wrapper for `wp_redirect()` and `wp_safe_redirect()` which can be plugged (and tested via phpunit)
+ Added new action called before validation occurs for a user account update form submission: `llms_before_user_account_update_submit`
+ Removed placeholders from form fields. Fixes a UX issue causing registration forms to appear cluttered due to having both placeholders and labels.

##### Bug fixes

+ Fixed issue allowing nonce checks to be bypassed on login and registration forms
+ Fixed issue causing a PHP notice if the registration form is submitted without an email address and automatic username generation is enabled
+ Fixed issue preventing email addresses with the "'" character from being able to register, login, or update account information
+ Fixed typo in automatic username generation filter `lifterlms_generated_username` (previously was `lifterlms_gnerated_username`)
+ Fixed issue causing admin panel static assets to have a double slash (//) in the assest URI path
+ FIxed issue allowing users with `view_lifterlms_reports` capability (Instructors) to access sales & enrollment reporting screens. The `view_others_lifterlms_reports` capability (Admins & LMS Managers) is now required to view these reporting tabs.
+ Updated IDs of login and registration nonces to be unique. Fixes an issue causing Chrome to throw non-unique ID warnings in the developer console. Also, IDs are supposed to be unique _anyway_ but thanks for helping us out Google.


= v3.19.3 - 2018-06-14 =
------------------------

+ Fix issue causing new quizzes to be unable to load questions list without reloading the builder