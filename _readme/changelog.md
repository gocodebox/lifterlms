== Changelog ==


v3.31.0-beta.1 - 2019-04-26
-------------------------------

+ Adds explicit support for the twentynineteen default theme.
+ Treats `post_excerpt` data as HTML instead of plain text. Fixes an issue resulting in HTML tags being stripped from lesson excerpts when duplicating a lesson in the course builder or importing lessons via the course importer.
+ Update to LifterLMS Blocks 1.4.0:

  + Adds an "unmigration" utility to LifterLMS -> Status -> Tools & Utilities which can be used to remove LifterLMS blocks from courses and lessons which were migrated to the block editor structure.
  + This tool is only available when the Classic Editor plugin is installed and enabled and it will remove blocks from ALL courses and lessons regardless of whether or not the block editor is being utilized on that post.


= v3.30.3 - 2019-04-22 =
------------------------

##### Updates

+ Fixed typos and spelling errors in various strings.
+ Corrected a typo in the `content-disposition` header used when exporting voucher CSVs, thanks [@pondermatic](https://github.com/pondermatic)!
+ Improved the quiz attempt grading experience by automatically focusing the remarks field and only toggling the first answer if it's not visible, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Removed commented out code on the Student Dashboard Notifications Tab template, thanks [@tnorthcutt](https://github.com/tnorthcutt)!

##### Bug Fixes

+ Renamed "descrpition" key to "description" found in the return of `LLMS_Instructor()->toArray()`.
+ Fixed an issue causing slashes to be stripped from course content when cloning a course.
+ Fixed an issue causing JS warnings to be thrown in the Javascript console on Course and Membership edit pages on the admin panel due to variables being defined too late, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Fixed an undefined variable notice encountered when filtering quiz attempts on the quiz attempts reporting screen, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Fixed an issue causing slashes to appear before quotation marks when saving remarks on a quiz attempt, thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ [@pondermatic](https://github.com/pondermatic) fixed typos and misspellings in comment and docs in over 200 files and while that doesn't concern most users it's worthy of a mention.

##### Deprecations

The following unused classes have been marked as deprecated and will be removed from LifterLMS in the next major release.

+ `LLMS\Users\User`
+ `LLMS_Analytics_Page`
+ `LLMS_Course_Basic`
+ `LLMS_Lesson_Basic`
+ `LLMS_Quiz_Legacy`

##### Template Updates

+ [templates/myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-notifications.php)


= v3.30.2 - 2019-04-09 =
------------------------

+ Added new filter to allow 3rd parties to determine if a `LLMS_Post_Model` field should be added to the `custom` array when converting the post to an array.
+ Added hooks and filters to the `LLMS_Generator` class to allow 3rd parties to easily generate content during course clone and import operations.
+ Fixed an issue causing all available courses to display when the [lifterlms_courses] shortcode is used with the "mine" parameter and the current user viewing the shortcode is not enrolled in any courses.
+ Fixed a PHP undefined variable warning present on the payment confirmation screen.

##### Template Updates

+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)


= v3.30.1 - 2019-04-04 =
------------------------

##### Updates

+ Added handler to automatically resume pending (incomplete or abandoned) orders.
+ Classes extending the `LLMS_Abstract_API_Handler` can now prevent a request body from being sent.
+ Added dynamic filter `'llms_' . $action . '_more'` to allow customization of the "More" button text and url for student dashboard sections. Thanks @[pondermatic](https://github.com/pondermatic).
+ Remove unused CSS code on the admin panel.

##### Bug Fixes

+ Fixed a bug preventing course imports as a result of action priority ordering issues.
+ Function `llms_get_order_by_key()` correctly returns `null` instead of false when no order is found and will return an `int` instead of a numeric string when an order is found.
+ Changed the method used to sort question choices to accommodate numeric choice markers. This fixes an issue in the Advanced Quizzes add-on causing reorder questions with 10+ choices to sort display in the incorrect order.
+ Increased the specificity of LifterLMS element tooltip hovers. Resolves a conflict causing issues on the WooCommerce tax rate management screen.
+ Fixed an issue causing certain fields in the Customizer from displaying a blue background as a result of very unspecific CSS rules, thanks [@Swapnildhanrale](https://github.com/Swapnildhanrale)!
+ Fixed builder deep links to quizzes freezing due to dependencies not being available during initialization.
+ Fixed builder issue causing duplicate copies of questions to be added when adding existing questions multiple times.

##### Template Updates

+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)


= v3.30.0 - 2019-03-21 =
------------------------

##### Updates

+ **Create custom thank you pages with new access plan checkout redirect options.**
+ Added the ability to sort items on the membership auto enrollment table (drag and drop to sort and reorder).
+ Improved the interface and interactions with the membership auto enrollment table settings.

##### LifterLMS Blocks

+ Updated LifterLMS Blocks to 1.3.8.
+ Fixed an issue causing some installations to be unable to use certain blocks due to jQuery dependencies being declared improperly.

##### Bug Fixes

+ Fixed issue preventing courses with the same title from properly displayed on the membership automatic enrollment courses table on the admin panel.
+ Fixed an issue preventing builder custom fields from being able to specify a custom sanitization callback.
+ Fixed an issue preventing builder custom fields from being able to properly save and render multi-select data.

##### Template Updates

+ [templates/product/access-plan-restrictions.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-restrictions.php)
+ [templates/product/free-enroll-form.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/free-enroll-form.php)


= v3.29.4 - 2019-03-08 =
------------------------

+ Fixed an issue preventing users with email addresses containing an apostrophe from being able to login.


= v3.29.3 - 2019-03-01 =
------------------------

##### Bug Fixes

+ Removed attempts to validate & save access plan data when the Classic Editor "post" form is submitted.
+ Fix issue causing 1-click free-enrollment for logged in users to refresh the screen without actually performing an enrollment.

##### Template Updates

+ [product/free-enroll-form.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/free-enroll-form.php)


= v3.29.2 - 2019-02-28 =
------------------------

+ Fix issue causing blank "period" values on access plans from being updated.
+ Fix an issue preventing paid access plans from being switched to "Free".


= v3.29.1 - 2019-02-27 =
------------------------

+ Automatically reorder access plans when a plan is deleted.
+ Skip (don't create) empty plans passed to the access plan save method as a result of deleted access plans.


= v3.29.0 - 2019-02-27 =
------------------------

##### Improved Access Plan Management

+ Added a set of methods for creating access plans programmatically.
+ Updated the Access Plan metabox on courses and lessons with improved data validation.
+ When using the block editor, the "Pricing Table" block will automatically update when access plan changes are saved to the database (from LifterLMS Blocks 1.3.5).
+ Access plans are now created and updated via AJAX requests, resolves a 5.0 editor issue causing duplicated access plans to be created.

##### Student Management Improvements

+ Added the ability for instructors and admins to mark lessons complete and incomplete for students via the student course reporting table.

##### Admin Panel Settings and Reporting Design Changes

+ Replaced LifterLMS logos and icons on the admin panel with our new logo LifterLMS Logo and Icons.
+ Revamped the design and layout of settings and reporting screens.

##### Checkout Improvements

+ Updated checkout javascript to expose an error addition functions
+ Abstracted the checkout form submission functionality into a callable function not directly tied to `$_POST` data
+ Removed display order field from payment gateway settings in favor of using the gateway table sortable list

##### Other Updates

+ Removed code related to an incompatibility between Yoast SEO Premium and LifterLMS resulting from former access plan save methods.
+ Reduced application logic in the `course/complete-lesson-link.php` template file by refactoring button display filters into functions.
+ Added function for checking if request is a REST request
+ Updated LifterLMS Blocks to version 1.3.7

##### Bug Fixes

+ Fixed an issue preventing "Pricing Table" blocks from displaying on the admin panel when the current user was enrolled in the course or no payment gateways were enabled on the site.
+ Fixed the checkout nonce to have a unique ID & name
+ Fixed an issue with deleted quizzes causing quiz notification's to throw fatal errors.
+ Fixed an issue preventing notification timestamps from displaying on the notifications dashboard page.
+ Fix an issue causing `GET` requests with no query string variables from causing issues via incorrect JSON encoding via the API Handler abstract.
+ Fix an issue causing access plan sale end dates from using the default WordPress date format settings.
+ `LLMS_Lesson::has_quiz()` will now properly return a boolean instead of the ID of the associated quiz (or 0 when none found)

##### Template Updates

+ [checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)
+ [course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)
+ [product/access-plan-pricing.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/access-plan-pricing.php)
+ [notifications/basic.php](https://github.com/gocodebox/lifterlms/blob/master/templates/notifications/basic.php)

##### Templates Removed

Admin panel templates replaced with view files which cannot be overridden from a theme or custom plugin.

+ `admin/post-types/product-access-plan.php`
+ `admin/post-types/product.php`


= v3.28.3 - 2019-02-14 =
------------------------

+ ❤❤❤ Happy Valentines Day or whatever ❤❤❤
+ Tested to WordPress 5.1
+ Fixed an issue causing JSON data saved by 3rd party plugins in course or lesson postmeta fields to be not duplicate properly during course duplications and imports.