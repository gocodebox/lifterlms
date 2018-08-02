== Changelog ==


= v3.22.0 - 2018-07-31 =
-------------------------------

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


= v3.19.2 - 2018-06-14 =
------------------------

##### Updates and enhancements

+ The course builder will now load quiz question data when the quiz is opened instead of loading all quizzes on builder page load. Improves builder load times and addresses an issue which could cause timeouts in certain environments when attempting to edit very large courses.
+ The currently viewed lesson will now be bold in the lesson outline widget.
+ Added a CSS class `.llms-widget-syllabus .llms-lesson.current-lesson` which can be used to customize the display of the current lesson in the widget.
+ Added the ability to filter quiz attempt reports by quiz status
+ Updated language for access plans on with a limited number of payments to reflect the total number of payments due as opposed to the length (for example in years) that the plan will run.

##### Bug fixes

+ Fixed issue preventing oEmbed media from being used in quiz question descriptions
+ Fixed issue preventing `<iframes>` from being used in quiz question descriptions
+ Quiz results will now exclude questions with 0 points value when displaying the number of questions in the quiz.
+ Fixed error occurring when sorting was applied to quiz attempt reports which would cause quiz attempts from other quizzes to be included in the new sorted report
+ Fixed filter `lifterlms_reviews_section_title` which was unuseable due to the incorrect usage of `_e()` within the filter. Now using `__()` as expected.
+ Fixed issue causing course featured image to display in place of lesson feature images

##### Template Updates

+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/lesson-preview.php)
+ [templates/course/outline-list-small.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/outline-list-small.php)
+ [templates/quiz/results-attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt.php)


= v3.19.1 - 2018-06-07 =
------------------------

+ Fixed CSS specificity issue on admin panel causing white text on white background on system status pages