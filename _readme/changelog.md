== Changelog ==


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


= v3.19.0 - 2018-06-07 =
------------------------

##### Updates and enhancements

+ Added a "My Memberships" tab to the student dashboard
+ "My Memberships" preview area
+ Updated admin panel order status badges to match frontend order status badges
+ Added a new recurring order status "Pending Cancel." Orders in this state will allow students to access course / membership content until the next payment is due, on this date, instead of a recurring charge being made the order will move to "Cancelled" and the student's enrollment status will change to "Cancelled" removing their access to the course or membership.
+ When a student cancels an active recurring order from the student dashboard, the order will move to "Pending Cancellation" instead of "Cancelled"
+ Students can re-activate an order that's Pending Cancellation moving the expiration date to the next payment due date
+ Added the ability to edit the access expiration date for orders with limited access settings and for orders in the "pending-cancel" state
+ Added a filter to allow customization of the URL used to generate certificate downloads from
+ When viewing taxonomy archives for any course or memberhip taxonomy (categories, tags, and tracks), if a term description exists, it will be used instead of the default catalog description content defined on the catalog page.
+ Added a filter (`llms_archive_description`) to allow filtering of the archive description
+ When `WP_DEBUG` is disabled the scheduled-actions posttype interface is now available via direct link. Useful for debugging but don't want to expose a menu-item link to clients. Access via wp-admin/edit.php?post_type=scheduled-action. Be warned: you shouldn't be modifying scheduled actions manually and that's why we're not exposing this directly, this should be used for debugging only!
+ Updated the function used to check if lessons have featured images to improve performance and resolve an incompatibility issue with WP Overlays plugin.

##### Bug fixes

+ Fixed issue causing "My Courses" title to be duplicated on the student dashboard when viewing the endpoint
+ Fixed issue causing the trial price to be displayed with a strike-through during a sale
+ Fixed coupon issue causing coupons to expire at the beginning of the day on the expiration date instead of at the end of the day
+ Fixed issue causing CSS rules to lose their declared order during exports causing export rendering issues with certain themes and plugin combinations

##### Template Updates

+ [templates/checkout/form-summary.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-summary.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-switch-source.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/lesson-preview.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/view-order.php)


= v3.18.2 - 2018-05-24 =
------------------------

+ Improved integrations settings screen to allow each integration to have it's own settings tab (page) with only its own settings
+ Allow programmatic access to notification content when notification views are accessed via filters
+ Fixed issue causind subscription cancellation notifications to be sent to admins when new orders were created
+ Fixed warning message displayed prior to membership bulk enrollment
+ Fixed multibyte character encoding issue encountered during certificate exports