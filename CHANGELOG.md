LifterLMS Changelog
===================

v7.8.3 - 2024-11-04
-------------------

##### Updates and Enhancements

+ Switches new access plans to Paid by default in the new access plan UI. [#2794](https://github.com/gocodebox/lifterlms/issues/2794)


v7.8.2 - 2024-10-31
-------------------

##### Updates and Enhancements

+ Additional styling of the new Access Plan UI.

##### Bug Fixes

+ Renaming front-end javascript translation file not required, as some security scans appear to be deleting it incorrectly. [#2787](https://github.com/gocodebox/lifterlms/issues/2787)


v7.8.1 - 2024-10-29
-------------------

##### Bug Fixes

+ Fixing migration to allow SKUs to be entered in access plans if one already exists.


v7.8.0 - 2024-10-29
-------------------

##### New Features

+ Added attribute 'layout' to My Account block and shortcode to display content as columns or stacked.
+ Changes styling in the Course Syllabus block for the current lesson, when used on a single lesson page. [#2777](https://github.com/gocodebox/lifterlms/issues/2777)
+ Added new feature: Quiz Resume. [#2783](https://github.com/gocodebox/lifterlms/issues/2783)

##### Updates and Enhancements

+ Improved UI of the Access Plans UI for better usability.
+ Improved accessibility of the Access Plans metaboxes to use proper labels and screen reader text where needed.
+ Added Launch Course Builder to the WP Admin Bar when viewing a course.
+ Added Launch Course Builder button when using the Classic Editor, beside “Add Course” button.
+ Added “Clear Reporting Cache” button on admin reporting page.
+ Improved help messaging on the Course Builder, and the Account and Checkout tabs of the LifterLMS settings.
+ Added support for image upload in Result Clarifications box for quizzes.
+ Removes spacing before or after answers for conditional quiz questions.

##### Bug Fixes

+ Fixed reference in `LLMS_Ajax_Handler::quiz_start()` to `LLMS_Quiz_Attempt::get_status()` method removed since LifterLMS 4.0.0.
+ Fix for notifications no longer auto-dismissing. [#2772](https://github.com/gocodebox/lifterlms/issues/2772)
+ Fix for the lifterlms_registration shortcode not working on certain themes on a separate page. [#2779](https://github.com/gocodebox/lifterlms/issues/2779)
+ Using a more reliable method of keeping LifterLMS notices dismissed. [#2767](https://github.com/gocodebox/lifterlms/issues/2767)

##### Breaking Changes

+ Hiding the Plan SKU field for access plans if not in use. Set the `llms_access_plans_allow_skus` option to get this field back.

##### Developer Notes

+ Adds current-lesson CSS class for the current Lesson in the Course Syllabus block. [#2777](https://github.com/gocodebox/lifterlms/issues/2777)
+ Adds new llms_before_registration_validation filter for 3rd party open registration validation.
+ Added filter `llms_quiz_attempt_resume_time_period` for updating quiz resume allowed time period.

##### Updated Templates

+ [templates/admin/reporting/tabs/quizzes/attempt.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/admin/reporting/tabs/quizzes/attempt.php)
+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/checkout/form-checkout.php)
+ [templates/checkout/form-summary.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/checkout/form-summary.php)
+ [templates/content-single-question.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/content-single-question.php)
+ [templates/myaccount/form-redeem-voucher.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/myaccount/form-redeem-voucher.php)
+ [templates/myaccount/header.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/myaccount/header.php)
+ [templates/notifications/basic.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/notifications/basic.php)
+ [templates/quiz/questions/content-choice.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/quiz/questions/content-choice.php)
+ [templates/quiz/questions/content-picture_choice.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/quiz/questions/content-picture_choice.php)
+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/quiz/results-attempt-questions-list.php)
+ [templates/quiz/results-attempt.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/quiz/results-attempt.php)
+ [templates/quiz/results.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/quiz/results.php)
+ [templates/quiz/start-button.php](https://github.com/gocodebox/lifterlms/blob/7.8.0/templates/quiz/start-button.php)


v7.7.8 - 2024-09-17
-------------------

##### Bug Fixes

+ Update version of the Blocks library to avoid having an unsaved changes prompt appear after updating an access plan on a course.
+ Fix for pricing display with additional formatting. [#2762](https://github.com/gocodebox/lifterlms/issues/2762)


v7.7.7 - 2024-09-12
-------------------

##### Bug Fixes

+ Avoid modifying non-LifterLMS block component styling in the editor. [#2752](https://github.com/gocodebox/lifterlms/issues/2752)
+ Use student email for the student login merge code in notification emails if usernames are not used. [#2755](https://github.com/gocodebox/lifterlms/issues/2755)
+ Show video or audio embed URLs in lessons which are valid but contain special characters. [#2749](https://github.com/gocodebox/lifterlms/issues/2749)


v7.7.6 - 2024-08-22
-------------------

##### Bug Fixes

+ Avoid modifying the Lost Password link if no LifterLMS Dashboard page is set. [#2741](https://github.com/gocodebox/lifterlms/issues/2741)
+ Fixes placeholder label on the Dashboard Page selection dropdown. [#2708](https://github.com/gocodebox/lifterlms/issues/2708)
+ Avoid outputting lifterlms_membership_link content if the membership is not published. [#2724](https://github.com/gocodebox/lifterlms/issues/2724)
+ Fix display of quiz question when viewing the quiz results if it contains formatting. [#2734](https://github.com/gocodebox/lifterlms/issues/2734)
+ Fixes sanitization as reported by FKSEC.
+ Fixes warning when trying to get the contents of a media protection file that does not exist. [#2735](https://github.com/gocodebox/lifterlms/issues/2735)

##### Updated Templates

+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/7.7.6/templates/quiz/results-attempt-questions-list.php)


v7.7.5 - 2024-08-15
-------------------

##### Bug Fixes

+ Show video tiles for courses on the Dashboard and My Courses pages when enabled. [#2728](https://github.com/gocodebox/lifterlms/issues/2728)

##### Updated Templates

+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/7.7.5/templates/myaccount/dashboard-section.php)


v7.7.4 - 2024-08-13
-------------------

##### Bug Fixes

+ Reverts changes to restricting pages by membership functionality to avoid conflicts with certain themes and plugins. [#2714](https://github.com/gocodebox/lifterlms/issues/2714)


v7.7.3 - 2024-08-12
-------------------

##### Bug Fixes

+ Fixes revenue display in the Orders table. [#2719](https://github.com/gocodebox/lifterlms/issues/2719)


v7.7.2 - 2024-08-12
-------------------

##### Bug Fixes

+ Fixes fatal error when updating from an old version. Thanks [@verygoodplugins](https://github.com/verygoodplugins)! [#2716](https://github.com/gocodebox/lifterlms/issues/2716)
+ Avoid errors on pages restricted by one or more memberships. [#2714](https://github.com/gocodebox/lifterlms/issues/2714)


v7.7.1 - 2024-08-09
-------------------

##### Updates and Enhancements

+ Removes placeholder image functionality with protected media files. Modify cache value for wordpress.com hosting.

##### Bug Fixes

+ Fixing the Award Certificate button appearing at the top of the Reporting > Students, Certificate tab. [#2709](https://github.com/gocodebox/lifterlms/issues/2709)
+ Fixed warnings from running `wp_kses_post()` on empty `paginate_links()` calls.

##### Updated Templates

+ [templates/myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/7.7.1/templates/myaccount/my-notifications.php)


v7.7.0 - 2024-07-19
-------------------

##### New Features

+ Adding read-only input for easier sharing of a certificate. Thanks [@imknight](https://github.com/imknight)! [#1379](https://github.com/gocodebox/lifterlms/issues/1379)
+ Adds additional protection for media files uploaded to quiz questions in the Course Builder.
+ Adds native Elementor support for Courses, with a default Course template and several basic widgets.

##### Updates and Enhancements

+ Removes the Visibility setting for Vouchers and Coupons. [#2640](https://github.com/gocodebox/lifterlms/issues/2640)
+ Updating internal libraries to their latest versions.
+ Added support for mutliple membership restriction warning. [#2523](https://github.com/gocodebox/lifterlms/issues/2523)

##### Bug Fixes

+ Prevent backslashes from being removed from Result Clarifications. [#2675](https://github.com/gocodebox/lifterlms/issues/2675)
+ Avoids JS error on the front-end. [#2678](https://github.com/gocodebox/lifterlms/issues/2678)
+ Exclude hidden courses when toggled off in the Courses block. [#2690](https://github.com/gocodebox/lifterlms/issues/2690)
+ Avoids saving review meta information for non-courses. Thanks [@bsetiawan88](https://github.com/bsetiawan88)! [#887](https://github.com/gocodebox/lifterlms/issues/887)
+ Improvements to the frontend styling of LifterLMS screens for design, accessibility, and better compatibility with dark mode themes.
+ Allow private VideoPress videos to play when the URL is pasted on Video Embed URL. [#2533](https://github.com/gocodebox/lifterlms/issues/2533)
+ Fixes the Certificate Title block when creating a new certificate template. [#2696] (https://github.com/gocodebox/lifterlms/issues/2696)

##### Security Fixes

+ Adds various security improvements, e.g. better escaping of output, as suggested by the Plugin Checker Plugin.

##### Performance Improvements

+ Caching get_transaction_total queries to improve performance of the
  orders table in the admin dashboard.

##### Updated Templates

+ [templates/achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/achievements/loop.php)
+ [templates/achievements/template.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/achievements/template.php)
+ [templates/admin/analytics/analytics.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/analytics/analytics.php)
+ [templates/admin/notices/staging.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/notices/staging.php)
+ [templates/admin/post-types/order-transactions.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/post-types/order-transactions.php)
+ [templates/admin/post-types/students.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/post-types/students.php)
+ [templates/admin/reporting/nav-filters.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/nav-filters.php)
+ [templates/admin/reporting/reporting.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/reporting.php)
+ [templates/admin/reporting/tabs/courses/course.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/courses/course.php)
+ [templates/admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/courses/overview.php)
+ [templates/admin/reporting/tabs/courses/students.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/courses/students.php)
+ [templates/admin/reporting/tabs/memberships/membership.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/memberships/membership.php)
+ [templates/admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/memberships/overview.php)
+ [templates/admin/reporting/tabs/memberships/students.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/memberships/students.php)
+ [templates/admin/reporting/tabs/quizzes/attempt.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/quizzes/attempt.php)
+ [templates/admin/reporting/tabs/quizzes/attempts.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/quizzes/attempts.php)
+ [templates/admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/quizzes/overview.php)
+ [templates/admin/reporting/tabs/quizzes/quiz.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/quizzes/quiz.php)
+ [templates/admin/reporting/tabs/students/achievements.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/achievements.php)
+ [templates/admin/reporting/tabs/students/certificates.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/certificates.php)
+ [templates/admin/reporting/tabs/students/courses-course.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/courses-course.php)
+ [templates/admin/reporting/tabs/students/courses.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/courses.php)
+ [templates/admin/reporting/tabs/students/information.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/information.php)
+ [templates/admin/reporting/tabs/students/memberships.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/memberships.php)
+ [templates/admin/reporting/tabs/students/student.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/student.php)
+ [templates/admin/reporting/tabs/students/students.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/reporting/tabs/students/students.php)
+ [templates/admin/user-edit.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/admin/user-edit.php)
+ [templates/certificates/actions.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/actions.php)
+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/content-legacy.php)
+ [templates/certificates/content.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/content.php)
+ [templates/certificates/dynamic-styles.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/dynamic-styles.php)
+ [templates/certificates/header.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/header.php)
+ [templates/certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/loop.php)
+ [templates/certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/preview.php)
+ [templates/certificates/template.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/certificates/template.php)
+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/checkout/form-checkout.php)
+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/checkout/form-confirm-payment.php)
+ [templates/checkout/form-coupon.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/checkout/form-coupon.php)
+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/checkout/form-gateways.php)
+ [templates/checkout/form-summary.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/checkout/form-summary.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/checkout/form-switch-source.php)
+ [templates/content-single-question.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/content-single-question.php)
+ [templates/course/audio.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/audio.php)
+ [templates/course/categories.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/categories.php)
+ [templates/course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/complete-lesson-link.php)
+ [templates/course/difficulty.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/difficulty.php)
+ [templates/course/favorite.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/favorite.php)
+ [templates/course/full-description.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/full-description.php)
+ [templates/course/length.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/length.php)
+ [templates/course/lesson-count.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/lesson-count.php)
+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/lesson-preview.php)
+ [templates/course/meta-wrapper-start.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/meta-wrapper-start.php)
+ [templates/course/outline-list-small.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/outline-list-small.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/parent-course.php)
+ [templates/course/short-description.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/short-description.php)
+ [templates/course/syllabus.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/syllabus.php)
+ [templates/course/tags.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/tags.php)
+ [templates/course/tracks.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/tracks.php)
+ [templates/course/video.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/course/video.php)
+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/emails/footer.php)
+ [templates/emails/header.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/emails/header.php)
+ [templates/emails/reset-password.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/emails/reset-password.php)
+ [templates/global/form-login.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/global/form-login.php)
+ [templates/global/form-registration.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/global/form-registration.php)
+ [templates/lesson/audio.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/lesson/audio.php)
+ [templates/lesson/video.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/lesson/video.php)
+ [templates/loop/author.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/author.php)
+ [templates/loop/enroll-date.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/enroll-date.php)
+ [templates/loop/enroll-status.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/enroll-status.php)
+ [templates/loop/featured-image.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/featured-image.php)
+ [templates/loop/loop-start.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/loop-start.php)
+ [templates/loop/none-found.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/none-found.php)
+ [templates/loop/pagination.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/loop/pagination.php)
+ [templates/membership/full-description.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/membership/full-description.php)
+ [templates/membership/price.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/membership/price.php)
+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/dashboard-section.php)
+ [templates/myaccount/form-edit-account.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/form-edit-account.php)
+ [templates/myaccount/form-redeem-voucher.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/form-redeem-voucher.php)
+ [templates/myaccount/my-favorites.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/my-favorites.php)
+ [templates/myaccount/my-grades-single-table.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/my-grades-single-table.php)
+ [templates/myaccount/my-grades-single.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/my-grades-single.php)
+ [templates/myaccount/my-grades.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/my-grades.php)
+ [templates/myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/my-notifications.php)
+ [templates/myaccount/my-orders.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/my-orders.php)
+ [templates/myaccount/navigation.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/navigation.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/view-order-actions.php)
+ [templates/myaccount/view-order-information.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/view-order-information.php)
+ [templates/myaccount/view-order-transactions.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/view-order-transactions.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/myaccount/view-order.php)
+ [templates/notifications/basic.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/notifications/basic.php)
+ [templates/product/access-plan-button.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-button.php)
+ [templates/product/access-plan-description.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-description.php)
+ [templates/product/access-plan-feature.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-feature.php)
+ [templates/product/access-plan-pricing.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-pricing.php)
+ [templates/product/access-plan-restrictions.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-restrictions.php)
+ [templates/product/access-plan-title.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-title.php)
+ [templates/product/access-plan-trial.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan-trial.php)
+ [templates/product/access-plan.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/access-plan.php)
+ [templates/product/free-enroll-form.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/free-enroll-form.php)
+ [templates/product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/product/pricing-table.php)
+ [templates/quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/meta-information.php)
+ [templates/quiz/questions/content-choice.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/questions/content-choice.php)
+ [templates/quiz/questions/content-picture_choice.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/questions/content-picture_choice.php)
+ [templates/quiz/questions/description.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/questions/description.php)
+ [templates/quiz/questions/video.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/questions/video.php)
+ [templates/quiz/questions/wrapper-start.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/questions/wrapper-start.php)
+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/results-attempt-questions-list.php)
+ [templates/quiz/results-attempt.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/results-attempt.php)
+ [templates/quiz/results.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/results.php)
+ [templates/quiz/return-to-lesson.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/return-to-lesson.php)
+ [templates/quiz/start-button.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/quiz/start-button.php)
+ [templates/shared/instructors.php](https://github.com/gocodebox/lifterlms/blob/7.7.0/templates/shared/instructors.php)


v7.6.3 - 2024-05-31
-------------------

##### Bug Fixes

+ Adds additional filtering when using the lifterlms_favorites shortcode. Thanks, Peter Thaleikis.

##### Updated Templates

+ [templates/admin/reporting/nav-filters.php](https://github.com/gocodebox/lifterlms/blob/7.6.3/templates/admin/reporting/nav-filters.php)


v7.6.2 - 2024-05-28
-------------------

##### New Features

+ Added functionality to disable quiz retake after a passed attempt.

##### Updates and Enhancements

+ Adds ability to search by an order ID number in the Orders table. Thanks [@bsetiawan88](https://github.com/bsetiawan88)! [#1583](https://github.com/gocodebox/lifterlms/issues/1583)
+ Added support for showing Private, Drafts and Pending Courses/Memberships in Reporting > Sales Page. [#2490](https://github.com/gocodebox/lifterlms/issues/2490)

##### Bug Fixes

+ Fixes issue of not being able to save an imported or cloned course using Divi or the Classic Editor plugin. [#2649](https://github.com/gocodebox/lifterlms/issues/2649)
+ Fixes broken View link after creating a new lesson using the Course Builder. [#2662](https://github.com/gocodebox/lifterlms/issues/2662)
+ Upgrading Quill text editor version. [#2655](https://github.com/gocodebox/lifterlms/issues/2655)

##### Developer Notes

+ Added logic to delete vouchers data from table when vouchers are deleted. Thanks [@bsetiawan88](https://github.com/bsetiawan88)! [#1087](https://github.com/gocodebox/lifterlms/issues/1087)

##### Updated Templates

+ [templates/admin/reporting/nav-filters.php](https://github.com/gocodebox/lifterlms/blob/7.6.2/templates/admin/reporting/nav-filters.php)


v7.6.1 - 2024-05-02
-------------------

##### Bug Fixes

+ Fix error when trying to add a new lesson outside the course builder. [#2636](https://github.com/gocodebox/lifterlms/issues/2636)
+ Show correct course title when launching Course Builder immediately after creating a new course. [#2606](https://github.com/gocodebox/lifterlms/issues/2606)
+ Updating lifter blocks version for the Launch Course Builder button to appear on the Course edit page.


v7.6.0 - 2024-04-18
-------------------

##### New Features

+ Adds course-level lesson drip settings.
+ Loads translation files later for compatibility with plugins like Loco Translate. [#2429](https://github.com/gocodebox/lifterlms/issues/2429), [#2525](https://github.com/gocodebox/lifterlms/issues/2525)
+ Adds settings in the Permalinks page to edit the custom post type and taxonomy slugs. Slugs are saved in the site language on install on update.
+ Adds `llms_switch_to_site_locale` and `llms_restore_locale` to help LifterLMS add-ons switch to the site language when getting translation strings.

##### Updates and Enhancements

+ Improved the Course Builder UI.
+ Updating the Blocks and Helpers libraries to the latest version.

##### Bug Fixes

+ Allows the style tag when embedding content (iframe), in order to support more services. [#2610](https://github.com/gocodebox/lifterlms/issues/2610)
+ Removes non-working editing of course title in the Course Builder. [#2607](https://github.com/gocodebox/lifterlms/issues/2607)
+ Avoid issue with lost content when the course builder is launched immediately after creating a new course. [#2606](https://github.com/gocodebox/lifterlms/issues/2606)
+ LifterLMS block editor strings now appear in the user's language. [#2525](https://github.com/gocodebox/lifterlms/issues/2525)
+ Fixed user's language setting not honored on backend. [#2324](https://github.com/gocodebox/lifterlms/issues/2324)
+ Fixes XSS, sanitization, and other security issues reported by Signal Labs.
+ Fixes typo with CHF currency.

##### Updated Templates

+ [templates/myaccount/my-orders.php](https://github.com/gocodebox/lifterlms/blob/7.6.0/templates/myaccount/my-orders.php)


v7.5.3 - 2024-02-22
-------------------

##### Bug Fixes

+ Fix fatal error when rendering single course page with reviews enabled. [#2604](https://github.com/gocodebox/lifterlms/issues/2604)


v7.5.2 - 2024-02-16
-------------------

##### Updates and Enhancements

+ Added product images for Aircraft and Memberlite.
+ Updates LifterLMS Rest to [v1.0.0](https://make.lifterlms.com/2024/01/22/lifterlms-rest-api-version-1-0-0/).

##### Bug Fixes

+ Adds error handling when taking a quiz in case of temporary server error or internet issue.

##### Security Fixes

+ Reviews handler now checks nonces and user limits. Thanks, Francesco Carlucci at Wordfence.

##### Updated Templates

+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/7.5.2/templates/emails/footer.php)


v7.5.1 - 2024-01-24
-------------------

##### Updates and Enhancements

+ Added action and description links to the plugins page.

##### Bug Fixes

+ Style updates for buttons in editor.
+ Fixed logic to validate that the terms page exists before adding to email footer.
+ Removed .clear styles since WordPress already sets them by default. [#2573](https://github.com/gocodebox/lifterlms/issues/2573)
+ Improved image appearance in quiz multiple choice and image choice question types. [#2588](https://github.com/gocodebox/lifterlms/issues/2588)

##### Security Fixes

+ Added nonce for course clone link. Thanks, Dhabaleshwar Das.

##### Updated Templates

+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/7.5.1/templates/emails/footer.php)


v7.5.0 - 2023-11-05
-------------------

##### New Features

+ Added `LLMS_Add_On::get_image()` method to get the addon and author image. [#2511](https://github.com/gocodebox/lifterlms/issues/2511)
+ Added a paragraph to show Number of lessons in a course at Course Catalog and My Courses. [#2434](https://github.com/gocodebox/lifterlms/issues/2434)

##### Updates and Enhancements

+ Updates LifterLMS Blocks to [v2.5.2](https://make.lifterlms.com/2023/11/01/lifterlms-blocks-version-2-5-2/).
+ Bundled Add-ons & More Banners/Author Images in Core LifterLMS. [#2511](https://github.com/gocodebox/lifterlms/issues/2511)
+ Updates LifterLMS Rest to [v1.0.0-beta.29](https://make.lifterlms.com/2023/10/24/lifterlms-rest-api-version-1-0-0-beta-29/).
+ Update Action Scheduler to version 3.5.4. To improve compatibility with PHP 8.2.

##### Bug Fixes

+ Fixed checking for the wrong function name when defining the pluggable function `lifterlms_student_dashboard`. [#2550](https://github.com/gocodebox/lifterlms/issues/2550)
+ Only show LifterLMS-authored Addons in All section.
+ Improved compatibility with WordPress 6.4 by using `traverse_and_serialize_blocks` in place of the deprecated `_inject_theme_attribute_in_block_template_content`.
+ PHP 8.2 compatibility fix: Fixed creation of dynamic property `LLMS_Meta_Box_Access::$_saved`.

##### Developer Notes

+ Added `LLMS_Payment_Gateway::can_process_access_plan()` method to determine if an access plan can be processed by the gateway. Also added the filter hook `llms_can_gateway_process_access_plan` to filter its result.
+ Added a check on whether the gateway can process a specific plan when purchasing a plan, or switching the payment gateway of a recurring payment.
+ Added action hook `llms_checkout_form_gateway_cant_process_plan` fired on the checkout form gateways section, when a gateway cannot process a specific plan.
+ Added new filter hook `llms_unschedule_recurring_payment_on_access_pan_expiration` to control whether or not the recurring payments fo an order need to be unscheduled when the related access plan expires (`true` by default).
+ Added 'favorites' in User postmeta for getting all user's favorites.
+ Added filter `llms_course_syllabus_lesson_favorite_visibility` for disabling favorites in syllabus view.
+ Added filter `llms_is_$object_type_favorite` to change object's (lesson, student, course) favorite boolean value.
+ Added `llms_lesson_preview_before_title` and `llms_lesson_preview_after_title` action hooks.
+ Added function `llms_template_syllabus_favorite_lesson_preview`.
+ Added filter `llms_favorites_enabled` to enable/disable Favorites feature.
+ Removed references to the unused quiz's property `random_answers`. Thanks [@AlexVCS](https://github.com/AlexVCS)! [#2552](https://github.com/gocodebox/lifterlms/issues/2552)
+ Improved some unit tests compatibility with PHP 8.2.

##### Security Fixes

+ Improved security when exporting a reporting table: make sure to avoid path traversals. Thanks [Huseyin Tintas (stif)](https://linkedin.com/in/huseyintintas)!

##### Updated Templates

+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/checkout/form-gateways.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/checkout/form-switch-source.php)
+ [templates/content-single-lesson-before.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/content-single-lesson-before.php)
+ [templates/course/favorite.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/course/favorite.php)
+ [templates/course/length.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/course/length.php)
+ [templates/course/lesson-count.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/course/lesson-count.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/course/lesson-preview.php)
+ [templates/loop/content.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/loop/content.php)
+ [templates/myaccount/dashboard.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/myaccount/dashboard.php)
+ [templates/myaccount/my-favorites.php](https://github.com/gocodebox/lifterlms/blob/7.5.0/templates/myaccount/my-favorites.php)


v7.4.2 - 2023-10-06
-------------------

##### Developer Notes

+ Fixing issues in the 7.4.1 release.


v7.4.1 - 2023-10-06
-------------------

##### New Features

+ Added new admin Resources page.

##### Bug Fixes

+ Fixed possible issues when cloning a course containing a quiz built with the Advanced Quizzes addon, after disabling it.

##### Developer Notes

+ Moved attempt randomization logic into the new static method `LLMS_Quiz_Attempt::randomize_attempt_questions()`.
+ Added filter hook `llms_quiz_attempt_questions_array` to allow filtering the quiz attempt's question arrays.


v7.4.0 - 2023-10-03
-------------------

##### New Features

+ Added method `LLMS_Quiz::get_questions_count()` for getting count of questions.
+ Added support for the upcoming "Question Bank" feature of the LifterLMS Advanced Quizzes add-on.

##### Updates and Enhancements

+ Added `nocache_headers()` to prevent browser caching for temporary redirects.

##### Bug Fixes

+ Added "Chaiyaphum" province for the Thailand. [#2527](https://github.com/gocodebox/lifterlms/issues/2527)

##### Developer Notes

+ Course Builder: Correctly get/set (and track changes of) Backbone's model properties which are objects.
+ Added filter hook `llms_admin_show_header` to allow 3rd parties filtering whether or not to show the branded header in the admin.
+ Added filter `llms_generator_new_post_data`, to allow third parties to filter the data used when creating a new post while cloning/exporting a course or lesson.
+ Abstracted the `LLMS_Admin_Setup_Wizard` class, added the class `LLMS_Abstract_Admin_Wizard`.
+ Added filter `llms_quiz_attempt_questions_randomize` to enable/disable questions randomize.
+ Added filter `llms_quiz_attempt_questions` to modify the questions array for the quiz.
+ Added filter `llms_quiz_questions_count` to filter the quiz's question count.

##### Updated Templates

+ [templates/quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/7.4.0/templates/quiz/meta-information.php)


v7.3.0 - 2023-08-08
-------------------

##### Updates and Enhancements

+ When a notice is shown for an access plan on the course edit screen (e.g. When using the WooCommerce integration and no product has been associated to the access plan.) Also display a warning icon next to the access plan title.
+ Made sure only who can `view_others_lifterlms_reports` will be able to see the analytics widget content in the WordPress admin.
+ Better rounding of float values on some reporting screens.
+ Avoid creating a post revision when cloning a course/lesson.
+ When creating pages via `llms_create_pages()`: strip all tags from the page title and slash the page data prior to inserting the page in the db via `wp_insert_post()` to prevent slashes from being stripped from the page title.
+ Updated the WordPress tested version up to 6.3.
+ Improved compatibility with the Divi theme by fixing an issue with the quiz attempt result clarifications not being visible when the Divi option `Defer jQuery And jQuery Migrate` was enabled. [#2470](https://github.com/gocodebox/lifterlms/issues/2470)

##### Bug Fixes

+ Fix spacer block when creating new certificate templates in WP 6.3.
+ Fixed PHP Warning when no course/membership catalog page was set or if the selected page doesn't exist anymore. [#2496](https://github.com/gocodebox/lifterlms/issues/2496)
+ Don't include WordPress default sidebar.php template when using a block theme. [#2488](https://github.com/gocodebox/lifterlms/issues/2488)
+ Updated Kazakhstani Tenge's currency symbol. [#2475](https://github.com/gocodebox/lifterlms/issues/2475)
+ Make the dashboard widget visible only if the current user has LMS Manager capabilities. [#2500](https://github.com/gocodebox/lifterlms/issues/2500)
+ Fixed issue with LifterLMS Navigation Link block and block visibility settings. [#2474](https://github.com/gocodebox/lifterlms/issues/2474)
+ Use student dashboard as default value for navigation link block. [#2465](https://github.com/gocodebox/lifterlms/issues/2465)
+ Fixed typo in a function name that could potentially produce a fatal. Thanks [@kamalahmed](https://github.com/kamalahmed)!

##### Developer Notes

+ Added the parameter `$tab` (ID/slug of the tab) to the `lifterlms_reporting_tab_cap` filter hook. Thanks [@sapayth](https://github.com/sapayth)! [#2468](https://github.com/gocodebox/lifterlms/issues/2468)
+ Added new filter hook `llms_can_analytics_widget_be_processed` that will allow to filter whether or not an analytics widget can be processed/displayed.
+ Added new filter `llms_install_get_pages`.
+ Added new public static method `LLMS_Admin_Dashboard_Widget::get_dashboard_widget_data()`.
+ Added `llms_dashboard_checklist` and `llms_dashboard_widget_data` filters to adjust dashboard content. [#2491](https://github.com/gocodebox/lifterlms/issues/2491)

##### Updated Templates

+ [templates/admin/reporting/tabs/widgets.php](https://github.com/gocodebox/lifterlms/blob/7.3.0/templates/admin/reporting/tabs/widgets.php)
+ [templates/global/sidebar.php](https://github.com/gocodebox/lifterlms/blob/7.3.0/templates/global/sidebar.php)
+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/7.3.0/templates/quiz/results-attempt-questions-list.php)


v7.2.1 - 2023-06-13
-------------------

##### Updates and Enhancements

+ Updated LifterLMS Blocks to [2.5.1](https://make.lifterlms.com/2023/06/13/lifterlms-blocks-version-2-5-1/). [#2461](https://github.com/gocodebox/lifterlms/issues/2461)


v7.2.0 - 2023-06-07
-------------------

##### New Features

+ Added `LLMS_ASSETS_VERSION` constant for cache busting.
+ Add course builder explainer video and lesson IDs.
+ Add new dashboard widget.
+ Added query to remove order comments on plugin uninstall when the constant `LLMS_REMOVE_ALL_DATA` is set to `true`. [#2322](https://github.com/gocodebox/lifterlms/issues/2322)
+ Added support for showing multiple difficulties when using Gutenberg Editor. [#2433](https://github.com/gocodebox/lifterlms/issues/2433)
+ Add shortcode wrapper blocks.
+ Added new navigation link block.
+ Added `llms_is_editor_block_rendering` helper function.
+ Added `llms_is_block_editor` helper function.

##### Updates and Enhancements

+ Adjusted `llms_modify_dashboard_pagination_links_disable` filter to return false only on Dashboard page.
+ Updates LifterLMS REST to [v1.0.0-beta.27](https://make.lifterlms.com/2023/05/31/lifterlms-rest-api-version-1-0-0-beta-27).
+ Raised the minimum support WordPress core version to 5.9.
+ Updates LifterLMS Blocks to [2.5.0](https://make.lifterlms.com/2023/06/06/lifterlms-blocks-version-2-5-0/).

##### Bug Fixes

+ Fixed LifterLMS specific block templates not correctly working on Windows file system.
+ Added `function_exists` check for `llms_blocks_is_post_migrated()`.
+ Update so dismissed notifications don't remain on viewport top layer.
+ Made sure to always enqueue iziModal assets when rendering achievements cards.

##### Developer Notes

+ Added new filter hook `llms_builder_settings` to filter the settings passed to the course builder.

##### Updated Templates

+ [templates/admin/reporting/tabs/widgets.php](https://github.com/gocodebox/lifterlms/blob/7.2.0/templates/admin/reporting/tabs/widgets.php)
+ [templates/course/syllabus.php](https://github.com/gocodebox/lifterlms/blob/7.2.0/templates/course/syllabus.php)


v7.1.4 - 2023-04-28
-------------------

##### Bug Fixes

+ Fixed an issue that prevented the correct saving of the course length when using the block editor. [#2426](https://github.com/gocodebox/lifterlms/issues/2426)

##### Developer Notes

+ Fixed an issue running unit tests on PHP 7.4 and WordPress 6.2 expecting `render_block()` returning a string while we were applying a filter that returned the boolean `true`.


v7.1.3 - 2023-04-25
-------------------

##### Updates and Enhancements

+ Wrapped some elements in HTML for better styling.
+ In Course and Lesson settings, replaced outdated URLs to WordPress' documentation about the list of sites you can embed from.
+ Updated few Italian province names. [#2256](https://github.com/gocodebox/lifterlms/issues/2256)
+ Avoid use of inline styles in course reviews. [#410](https://github.com/gocodebox/lifterlms/issues/410)

##### Bug Fixes

+ Fixed "Unsaved Data" warning when adding vouchers. [#2394](https://github.com/gocodebox/lifterlms/issues/2394)
+ Fixed "Course Length" and "Difficulty" fields visible in the Block Editor which is meant for Classic Editor. [#2174](https://github.com/gocodebox/lifterlms/issues/2174)
+ Added missing `$post_id` parameter to the `the_title` filter hook when retrieving a form title. [#2332](https://github.com/gocodebox/lifterlms/issues/2332)
+ Added missing Armed Forces options to the US States dropdown in the Billing information form. [#2325](https://github.com/gocodebox/lifterlms/issues/2325)
+ Using `strpos()` instead of `str_starts_with()` for compatibility. [#2415](https://github.com/gocodebox/lifterlms/issues/2415)

##### Developer Notes

+ Added helper function `llms_get_floats_rounding_precision()` to return precision for rounding off floating values and filter hook `lifterlms_floats_rounding_precision` to filter precision value in reporting. [#2237](https://github.com/gocodebox/lifterlms/issues/2237)
+ Added `lifterlms_dashboard_memberships_not_enrolled_text` filter hook to allow altering the message displaying on the student dashboard when the current user is not enrolled in any memberships. [#2396](https://github.com/gocodebox/lifterlms/issues/2396)
+ Added `lifterlms_dashboard_courses_not_enrolled_text` filter hook to allow altering the message displaying on the student dashboard when the current user is not enrolled in any courses. [#2396](https://github.com/gocodebox/lifterlms/issues/2396)

##### Updated Templates

+ [templates/course/syllabus.php](https://github.com/gocodebox/lifterlms/blob/7.1.3/templates/course/syllabus.php)


v7.1.2 - 2023-03-27
-------------------

##### Updates and Enhancements

+ Making the LifterLMS logo link to the LifterLMS.com site.

##### Bug Fixes

+ Fix bug in `llms_featured_img` function when featured image file is not available. [#2381](https://github.com/gocodebox/lifterlms/issues/2381)
+ Fixed manual certificates awarding broken when using the block editor. [#2386](https://github.com/gocodebox/lifterlms/issues/2386)


v7.1.1 - 2023-03-13
-------------------

##### Bug Fixes

+ Fixed notice display on WooCommerce dashboard pages.
+ Fixed View button URL when using WP in subdirectory.
+ Fixed blank System Report's copy for Support.


v7.1.0 - 2023-03-02
-------------------

##### New Features

+ Added lessons count column on the Courses post list table.
+ Added a new Dashboard page under the LifterLMS menu in the admin, whicih includes recent activity widgets and links to useful resources.
+ Added link to the course builder for each lesson on the Lessons post list table. Also added a link to either edit or add a quiz.

##### Updates and Enhancements

+ Updates LifterLMS Helper to [v3.5.0](https://make.lifterlms.com/2023/02/28/lifterlms-helper-version-3-5-0/).
+ Make the LifterLMS menu meta box initially available on Appearance -> Menus.
+ Updates LifterLMS REST to [v1.0.0-beta.26](https://make.lifterlms.com/2023/02/28/lifterlms-rest-api-version-1-0-0-beta-26/).

##### Bug Fixes

+ Catch possible fatal when trying to display a "broken" basic notification and set its status to 'error' so that it'll be excluded from the next fetches.
+ Catch possible fatal when sending notification emails and in that case remove from the queue the item that produced it.
+ Fix cloned course retaining original course's ID in some restriction messages.
+ Fixed possible admin notices duplication when activating/deactivating or installing add-ons from the page Add-ons & more.
+ Avoided setting the `llms-tracking` cookie when there are no events to track.
+ Updated styles across the entire plugin.
+ Updated Add-ons & more list to hide old (uncategorized) products.

##### Deprecations

+ Deprecated methods `LLMS_Admin_Notices_Core::sidebar_support()` and `LLMS_Admin_Notices_Core::clear_sidebar_notice()`.
+ Removed notice for theme sidebar support.

##### Developer Notes

+ The function `llms_is_user_enrolled()` will always return `false` for non existing users. While, before, it could return `true` if a now removed user was enrolled into a the given course or membership.
+ Added new `LLMS_Course::get_lessons_count()` method. It can be used in place of `count( LLMS_Course::get_lessons() )` to improve performance.
+ Fixed compatibility with PHP 8.1 by using an empty string as menu parent page for the course builder submenu page in place of NULL.
+ Avoid passing null values to `urlencode()` and `urldecode()` that would produce PHP warnings on PHP 8.1+.
+ Added `$autoload` parameter to the function `llms_get_student`.

##### Performance Improvements

+ Improve performance when querying notifications via the LLMS_Notifications_Query and there's no need to count the total notifications found, or for pagination information.
+ Immediately return false when running `llms_is_user_enrolled()` on logged out or no longer existing users, avoiding running additional DB queries e.g. when displaying course or membership catalogs for visitors.
+ Skip counting the total transactions found when retrieving the last or the first transaction for an order.

##### Updated Templates

+ templates/admin/reporting/nav-filters.php
+ templates/admin/reporting/reporting.php
+ templates/admin/reporting/tabs/courses/course.php
+ templates/admin/reporting/tabs/memberships/membership.php
+ templates/admin/reporting/tabs/quizzes/quiz.php
+ templates/admin/reporting/tabs/students/student.php
+ templates/admin/reporting/tabs/widgets.php
+ templates/checkout/form-confirm-payment.php


v7.0.1 - 2022-11-14
-------------------

##### Bug Fixes

+ Fixed a fatal error encountered on the payment confirmation screen when attempting to confirm a non-existent order. [#2093](https://github.com/gocodebox/lifterlms/issues/2093)
+ Use `sanitize_file_name()` in favor of `sanitize_title()` for generating the file name of reporting table export files. [#1540](https://github.com/gocodebox/lifterlms/issues/1540)
+ Resolved conflict encountered on post edit screens when using LifterLMS, Yoast SEO, and the Classic Editor plugin. [#2298](https://github.com/gocodebox/lifterlms/issues/2298)

##### Developer Notes

+ A stub method, `get_title()` has been added to the `LLMS_Abstract_Exportable_Admin_Table` abstract class. This method should be defined by any extending classes and will throw a `_doing_it_wrong()` error when undefined.
+ Added new filter to allow customizing which user roles are affected by the `LLMS_Admin_Menus::instructor_menu_hack` function.


v7.0.0 - 2022-10-04
-------------------

##### New Features

+ Added handling for admin settings options that store their option values in a nested array.
+ Added new AJAX checkout and payment source switching endpoints for payment gateways to utilize instead of the preexisting synchronous form submission methods.
+ On purchase completed retrieve the redirection URL from the INPUT_POST 'redirect' variable, if no 'redirect' variable is passed via INPUT_GET. The INPUT_POST 'redirect' variable comes from the new checkout form's hidden field 'redirect' populated with LLMS_Access_Plan::get_redirection_url(). [#2229](https://github.com/gocodebox/lifterlms/issues/2229)

##### Updates and Enhancements

+ Full Site Editing: **[BREAKING]** The wrappers in the custom header and footer templates have been changed to the semantic HTML tags `<header>` and `<footer>` in favor of default `<div>` tags. [#2281](https://github.com/gocodebox/lifterlms/issues/2281)
+ When an order post is restored from the trash its post status will now be "llms-pending" in favor of the default "draft" status.

##### Bug Fixes

+ Fixed unclosed checkout div wrapper on empty cart. [#2277](https://github.com/gocodebox/lifterlms/issues/2277)
+ Don't attempt to lookup the default payment gateway from user meta data.
+ Fixed required fields duplication when the form is a child of a `.wp-block-column` element. [#2134](https://github.com/gocodebox/lifterlms/issues/2134)
+ Fixed an issue that prevented disabling the access plan’s option, Override Membership Redirects, once enabled. [#2234](https://github.com/gocodebox/lifterlms/issues/2234)
+ Disabled `scroll-behavior: smooth` on checkout screen to address form element validity checking issues on Chromium-based browsers. [#2206](https://github.com/gocodebox/lifterlms/issues/2206)

##### Deprecations

+ Deprecated `LLMS_Controller_Orders::switch_payment_source()` in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Deprecated the `lifterlms_update_option_{$type}` action in favor of the `llms_update_option_{$type}` filter.
+ Method `LLMS_Controller_Orders::confirm_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::confirm_pending_order()`.
+ Method `LLMS_Controller_Orders::create_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::create_pending_order()`.
+ Method `LLMS_Controller_Orders::switch_payment_source()` is deprecated in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Passing jQuery selections into the `window.LLMS.Spinner` functions is deprecated. Use JS `Elements` or selection strings parseable by `document.querySelector()` instead.
+ Deprecated hook `llms_{$method}_title` in favor of `llms_{$method}_refund_title`.

##### Developer Notes

+ Added admin settings helper function, `llms_get_dashicon_link()`, intended to enable the addition of external resource helper links to settings field descriptions.
+ The `LLMS_Student` object can be instantiated as an empty object and bypass current user autoloading. In the future this may affect integrations using the `lifterlms_new_pending_order` action hook which will receive an "empty" student object during order setup by gateways utilizing new AJAX-powered checkout endpoints.
+ Added a filter, `llms_gateway_{$this->id}_logging_enabled`, which will allow force enabling/disabling of gateway logging functions.
+ Improved payment gateway secure string logging by adding a method, `add_secure_string()` allowing developers to add secure strings during runtime without the necessity of registering the strings using filters.
+ Introduces new function `llms_is_option_secure()` for determining if an "secured" option is defined in a "secure" manner.
+ Implemented new gateway feature: `modify_recurring_payments`. [#2176](https://github.com/gocodebox/lifterlms/issues/2176)
+ Added two new parameters to LLMS_Access_Plan::get_redirection_url() - `$encode` to optionally get a raw (not encoded) URL. - `$querystring_only` to optionally get only the redirect URL if set via NPUT_GET variable.
+ Added new parameter `$querystring_only` to the filter hook `llms_plan_get_checkout_redirection`.
+ Admin settings fields now display `after_html` for additional field types which support `desc`.
+ The CSS for `.llms-spinning` and `.llms-spinner` elements is no longer loaded as part of the `lifterlms.css` and `admin.css` files, instead it is loaded dynamically when `window.LLMS.Spinner` functions are called. In some cases CSS overrides to these elements which relied on CSS rule load order may no longer successfully override the default CSS rules. These overrides may need to be updated to have more specific selectors in order to ensure the overrides are retained.
+ The Javascript object, `window.LLMS.Spinner`, has been converted to a module accessible from the same variable.
+ The `window.LLMS.Spinner` methods now accept JS Elements and selector strings parseable by `document.querySelector()` in addition to jQuery selections.
+ Added new filter `llms_transaction_can_be_refunded` enabling custom refund restrictions to be applied to a transaction.

##### Updated Templates

+ [templates/block-templates/archive-course.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/archive-course.html)
+ [templates/block-templates/archive-llms_membership.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/archive-llms_membership.html)
+ [templates/block-templates/single-no-access.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/single-no-access.html)
+ [templates/block-templates/taxonomy-course_cat.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/taxonomy-course_cat.html)
+ [templates/block-templates/taxonomy-course_difficulty.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/taxonomy-course_difficulty.html)
+ [templates/block-templates/taxonomy-course_tag.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/taxonomy-course_tag.html)
+ [templates/block-templates/taxonomy-course_track.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/taxonomy-course_track.html)
+ [templates/block-templates/taxonomy-membership_cat.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/taxonomy-membership_cat.html)
+ [templates/block-templates/taxonomy-membership_tag.html](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/block-templates/taxonomy-membership_tag.html)
+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/checkout/form-gateways.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/checkout/form-switch-source.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/7.0.0/templates/myaccount/view-order-actions.php)


v6.11.0 - 2022-09-22
--------------------

##### Updates and Enhancements

+ Since version 6.0.0, the Certificate Title Block provided the option to use four Google-hosted fonts. These fonts will now be served from the site's server in favor of serving them from the Google Fonts CDN. For more information about this change, please refer to https://make.wordpress.org/themes/2022/06/18/complying-with-gdpr-when-using-google-fonts/. If you wish to continue loading fonts from Google's CDN, add the following code to your functions.php file: `add_filter( 'llms_use_google_webfonts', '__return_true' );`. [#2189](https://github.com/gocodebox/lifterlms/issues/2189)
+ Upgraded included library, `@woocommerce/action-scheduler`, to version [3.5.2](https://github.com/woocommerce/action-scheduler/releases/tag/3.5.2).

##### Bug Fixes

+ Fixed a division by zero error encountered on quiz reporting screens for quizzes with 0 total available points. [#2270](https://github.com/gocodebox/lifterlms/issues/2270)


v7.0.0-rc.1 - 2022-09-14
------------------------

##### New Features

+ Added handling for admin settings options that store their option values in a nested array.
+ Added new AJAX checkout and payment source switching endpoints for payment gateways to utilize instead of the preexisting synchronous form submission methods.
+ On purchase completed retrieve the redirection URL from the INPUT_POST 'redirect' variable, if no 'redirect' variable is passed via INPUT_GET. The INPUT_POST 'redirect' variable comes from the new checkout form's hidden field 'redirect' populated with LLMS_Access_Plan::get_redirection_url(). [#2229](https://github.com/gocodebox/lifterlms/issues/2229)

##### Updates and Enhancements

+ When an order post is restored from the trash its post status will now be "llms-pending" in favor of the default "draft" status.

##### Bug Fixes

+ Don't attempt to lookup the default payment gateway from user meta data.
+ Fixed an issue that prevented disabling the access plan’s option, Override Membership Redirects, once enabled. [#2234](https://github.com/gocodebox/lifterlms/issues/2234)
+ Disabled `scroll-behavior: smooth` on checkout screen to address form element validity checking issues on Chromium-based browsers. [#2206](https://github.com/gocodebox/lifterlms/issues/2206)

##### Deprecations

+ Deprecated `LLMS_Controller_Orders::switch_payment_source()` in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Deprecated the `lifterlms_update_option_{$type}` action in favor of the `llms_update_option_{$type}` filter.
+ Method `LLMS_Controller_Orders::confirm_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::confirm_pending_order()`.
+ Method `LLMS_Controller_Orders::create_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::create_pending_order()`.
+ Method `LLMS_Controller_Orders::switch_payment_source()` is deprecated in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Passing jQuery selections into the `window.LLMS.Spinner` functions is deprecated. Use JS `Elements` or selection strings parseable by `document.querySelector()` instead.
+ Deprecated hook `llms_{$method}_title` in favor of `llms_{$method}_refund_title`.

##### Developer Notes

+ Added admin settings helper function, `llms_get_dashicon_link()`, intended to enable the addition of external resource helper links to settings field descriptions.
+ The `LLMS_Student` object can be instantiated as an empty object and bypass current user autoloading. In the future this may affect integrations using the `lifterlms_new_pending_order` action hook which will receive an "empty" student object during order setup by gateways utilizing new AJAX-powered checkout endpoints.
+ Added a filter, `llms_gateway_{$this->id}_logging_enabled`, which will allow force enabling/disabling of gateway logging functions.
+ Improved payment gateway secure string logging by adding a method, `add_secure_string()` allowing developers to add secure strings during runtime without the necessity of registering the strings using filters.
+ Introduces new function `llms_is_option_secure()` for determining if an "secured" option is defined in a "secure" manner.
+ Implemented new gateway feature: `modify_recurring_payments`. [#2176](https://github.com/gocodebox/lifterlms/issues/2176)
+ Added two new parameters to LLMS_Access_Plan::get_redirection_url() - `$encode` to optionally get a raw (not encoded) URL. - `$querystring_only` to optionally get only the redirect URL if set via NPUT_GET variable.
+ Added new parameter `$querystring_only` to the filter hook `llms_plan_get_checkout_redirection`.
+ Admin settings fields now display `after_html` for additional field types which support `desc`.
+ The CSS for `.llms-spinning` and `.llms-spinner` elements is no longer loaded as part of the `lifterlms.css` and `admin.css` files, instead it is loaded dynamically when `window.LLMS.Spinner` functions are called. In some cases CSS overrides to these elements which relied on CSS rule load order may no longer successfully override the default CSS rules. These overrides may need to be updated to have more specific selectors in order to ensure the overrides are retained.
+ The Javascript object, `window.LLMS.Spinner`, has been converted to a module accessible from the same variable.
+ The `window.LLMS.Spinner` methods now accept JS Elements and selector strings parseable by `document.querySelector()` in addition to jQuery selections.
+ Added new filter `llms_transaction_can_be_refunded` enabling custom refund restrictions to be applied to a transaction.

##### Updated Templates

+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-rc.1/templates/checkout/form-gateways.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-rc.1/templates/checkout/form-switch-source.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-rc.1/templates/myaccount/view-order-actions.php)


v6.10.2 - 2022-09-14
--------------------

##### Updates and Enhancements

+ Updated `woocommerce/action-scheduler` to version [3.5.1](https://github.com/woocommerce/action-scheduler/releases/tag/3.5.1).

##### Security Fixes

+ Fixed a data sanitization issue related to achievement permalinks.


v6.10.1 - 2022-09-07
--------------------

##### Bug Fixes

+ Fixed a PHP warning raised when logging errors during email notification dispatch. [#2250](https://github.com/gocodebox/lifterlms/issues/2250)
+ Fixed issue preventing one-time orders for being included in membership revenue reporting widgets. [#2254](https://github.com/gocodebox/lifterlms/issues/2254)


v7.0.0-beta.1 - 2022-08-29
--------------------------

##### New Features

+ Added handling for admin settings options that store their option values in a nested array.
+ Added new AJAX checkout and payment source switching endpoints for payment gateways to utilize instead of the preexisting synchronous form submission methods.
+ On purchase completed retrieve the redirection URL from the INPUT_POST 'redirect' variable, if no 'redirect' variable is passed via INPUT_GET. The INPUT_POST 'redirect' variable comes from the new checkout form's hidden field 'redirect' populated with LLMS_Access_Plan::get_redirection_url(). [#2229](https://github.com/gocodebox/lifterlms/issues/2229)

##### Updates and Enhancements

+ When an order post is restored from the trash its post status will now be "llms-pending" in favor of the default "draft" status.

##### Bug Fixes

+ Don't attempt to lookup the default payment gateway from user meta data.
+ Fixed an issue that prevented disabling the access plan’s option, Override Membership Redirects, once enabled. [#2234](https://github.com/gocodebox/lifterlms/issues/2234)
+ Disabled `scroll-behavior: smooth` on checkout screen to address form element validity checking issues on Chromium-based browsers. [#2206](https://github.com/gocodebox/lifterlms/issues/2206)

##### Deprecations

+ Deprecated `LLMS_Controller_Orders::switch_payment_source()` in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Deprecated the `lifterlms_update_option_{$type}` action in favor of the `llms_update_option_{$type}` filter.
+ Method `LLMS_Controller_Orders::confirm_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::confirm_pending_order()`.
+ Method `LLMS_Controller_Orders::create_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::create_pending_order()`.
+ Method `LLMS_Controller_Orders::switch_payment_source()` is deprecated in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Passing jQuery selections into the `window.LLMS.Spinner` functions is deprecated. Use JS `Elements` or selection strings parseable by `document.querySelector()` instead.
+ Deprecated hook `llms_{$method}_title` in favor of `llms_{$method}_refund_title`.

##### Developer Notes

+ Added admin settings helper function, `llms_get_dashicon_link()`, intended to enable the addition of external resource helper links to settings field descriptions.
+ The `LLMS_Student` object can be instantiated as an empty object and bypass current user autoloading. In the future this may affect integrations using the `lifterlms_new_pending_order` action hook which will receive an "empty" student object during order setup by gateways utilizing new AJAX-powered checkout endpoints.
+ Added a filter, `llms_gateway_{$this->id}_logging_enabled`, which will allow force enabling/disabling of gateway logging functions.
+ Improved payment gateway secure string logging by adding a method, `add_secure_string()` allowing developers to add secure strings during runtime without the necessity of registering the strings using filters.
+ Introduces new function `llms_is_option_secure()` for determining if an "secured" option is defined in a "secure" manner.
+ Implemented new gateway feature: `modify_recurring_payments`. [#2176](https://github.com/gocodebox/lifterlms/issues/2176)
+ Added two new parameters to LLMS_Access_Plan::get_redirection_url() - `$encode` to optionally get a raw (not encoded) URL. - `$querystring_only` to optionally get only the redirect URL if set via NPUT_GET variable.
+ Added new parameter `$querystring_only` to the filter hook `llms_plan_get_checkout_redirection`.
+ Admin settings fields now display `after_html` for additional field types which support `desc`.
+ The CSS for `.llms-spinning` and `.llms-spinner` elements is no longer loaded as part of the `lifterlms.css` and `admin.css` files, instead it is loaded dynamically when `window.LLMS.Spinner` functions are called. In some cases CSS overrides to these elements which relied on CSS rule load order may no longer successfully override the default CSS rules. These overrides may need to be updated to have more specific selectors in order to ensure the overrides are retained.
+ The Javascript object, `window.LLMS.Spinner`, has been converted to a module accessible from the same variable.
+ The `window.LLMS.Spinner` methods now accept JS Elements and selector strings parseable by `document.querySelector()` in addition to jQuery selections.
+ Added new filter `llms_transaction_can_be_refunded` enabling custom refund restrictions to be applied to a transaction.

##### Updated Templates

+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-beta.1/templates/checkout/form-gateways.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-beta.1/templates/checkout/form-switch-source.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-beta.1/templates/myaccount/view-order-actions.php)


v6.10.0 - 2022-08-29
--------------------

##### Updates and Enhancements

+ Updtaed woocommerce/action-scheduler to version [3.5.0](https://github.com/woocommerce/action-scheduler/releases/tag/3.5.0).
+ Upgrades the bundled `quill-wordcount` module to version 2.0, addressing an issue encountered when counting words with non-Latin character languages.

##### Bug Fixes

+ Make `<pre>` elements in quiz attempt results scrollable.
+ Make sure the current user can edit the lesson, when changing its completion status from the admin reporting.
+ Added missing textodmain for the string 'Move {post_title} to the Trash'. [#2224](https://github.com/gocodebox/lifterlms/issues/2224)
+ Fixed PHP fatal error when quick editing an award. [#2231](https://github.com/gocodebox/lifterlms/issues/2231)
+ Updated Spain's provinces list. [#2243](https://github.com/gocodebox/lifterlms/issues/2243)

##### Deprecations

+ The files `assets/vendor/quill/quill.module.wordcount.js` and `assets/vendor/quill/quill.module.wordcount.min.js` are to be removed in the next major release. Instead of loading these files directly, use `wp_enqueue_script( 'llms-quill-wordcount' )`.


v6.9.0 - 2022-07-28
-------------------

##### Updates and Enhancements

+ Removed site-wide font-weight styles targeting `<h1>` through `<h6>` elements. [#2217](https://github.com/gocodebox/lifterlms/issues/2217)

##### Bug Fixes

+ Fixed issue preventing decimals from being used for coupon discount amounts. [#2149](https://github.com/gocodebox/lifterlms/issues/2149)
+ Added AR (Arezzo) to Italy's states list. [#2214](https://github.com/gocodebox/lifterlms/issues/2214)


v7.0.0-alpha.4 - 2022-07-18
---------------------------

+ Fixed error causing recurring payment reschedules to fail with a fatal error.


v7.0.0-alpha.3 - 2022-07-16
---------------------------

+ Add max-length sanitization to admin settings which specify a max length.
+ Fixed invalid user links on admin order screens when viewing incomplete orders missing a registered user.
+ Added new function `llms_is_secure()`.
+ Added `lifterlms-` and `llms-` as automatically stripped prefixed when using `llms_strip_prefixes()`.
+ Added new temporary metadata, `temp_gateway_ids` to orders for use by gateways when switching payment methods.
+ Moved `LLMS.Spinner` Javascript into an `@lifterlms/components` module and removed its reliance on jQuery.
+ Disabled `scroll-behavior: scroll` on checkout screens to address a validity reporting issue on Chromium-based browsers.


v6.8.0 - 2022-07-12
-------------------

##### Bug Fixes

+ Fixed Hello Theme's word-break and spacing for quiz answer options. [#2132](https://github.com/gocodebox/lifterlms/issues/2132)
+ Fixed text/label alignment in Twenty-Twenty-Two theme.
+ Fixed regression introduced in version 6.3.0 which prevented the Courses nav item from being customized in the BuddyPress profile nav menu. [#2142](https://github.com/gocodebox/lifterlms/issues/2142)

##### Developer Notes

+ Added new filter `llms_product_get_restrictions` hook to filter the list of restrictions placed on a given product. [#2201](https://github.com/gocodebox/lifterlms/issues/2201)


v7.0.0-alpha.2 - 2022-06-23
---------------------------

##### New Features

+ Added handling for admin settings options that store their option values in a nested array.
+ Added new AJAX checkout and payment source switching endpoints for payment gateways to utilize instead of the preexisting synchronous form submission methods.

##### Bug Fixes

+ Don't attempt to lookup the default payment gateway from user meta data.
+ Fixes Hello Theme's word-break and spacing for quiz answer options. Also fixes text/label alignment in Twenty-Twenty-Two Theme. [#2132](https://github.com/gocodebox/lifterlms/issues/2132)

##### Deprecations

+ Deprecated `LLMS_Controller_Orders::switch_payment_source()` in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Deprecated the `lifterlms_update_option_{$type}` action in favor of the `llms_update_option_{$type}` filter.
+ Method `LLMS_Controller_Orders::confirm_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::confirm_pending_order()`.
+ Method `LLMS_Controller_Orders::create_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::create_pending_order()`.
+ Method `LLMS_Controller_Orders::switch_payment_source()` is deprecated in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Deprecated hook `llms_{$method}_title` in favor of `llms_{$method}_refund_title`.

##### Developer Notes

+ Added admin settings helper function, `llms_get_dashicon_link()`, intended to enable the addition of external resource helper links to settings field descriptions.
+ The `LLMS_Student` object can be instantiated as an empty object and bypass current user autoloading. In the future this may affect integrations using the `lifterlms_new_pending_order` action hook which will receive an "empty" student object during order setup by gateways utilizing new AJAX-powered checkout endpoints.
+ Added a filter, `llms_gateway_{$this->id}_logging_enabled`, which will allow force enabling/disabling of gateway logging functions.
+ Improved payment gateway secure string logging by adding a method, `add_secure_string()` allowing developers to add secure strings during runtime without the necessity of registering the strings using filters.
+ Implemented new gateway feature: `modify_recurring_payments`. [#2176](https://github.com/gocodebox/lifterlms/issues/2176)
+ Admin settings fields now display `after_html` for additional field types which support `desc`.
+ Added new filter `llms_transaction_can_be_refunded` enabling custom refund restrictions to be applied to a transaction.

##### Updated Templates

+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-alpha.2/templates/checkout/form-gateways.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-alpha.2/templates/checkout/form-switch-source.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-alpha.2/templates/myaccount/view-order-actions.php)


v7.0.0-alpha.1 - 2022-06-15
---------------------------

##### New Features

+ Added new AJAX checkout and payment source switching endpoints for payment gateways to utilize instead of the preexisting synchronous form submission methods.

##### Bug Fixes

+ Don't attempt to lookup the default payment gateway from user meta data.

##### Deprecations

+ Deprecated `LLMS_Controller_Orders::switch_payment_source()` in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Method `LLMS_Controller_Orders::confirm_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::confirm_pending_order()`.
+ Method `LLMS_Controller_Orders::create_pending_order()` is deprecated in favor of `LLMS_Controller_Checkout::create_pending_order()`.
+ Method `LLMS_Controller_Orders::switch_payment_source()` is deprecated in favor of `LLMS_Controller_Checkout::switch_payment_source()`.
+ Deprecated hook `llms_{$method}_title` in favor of `llms_{$method}_refund_title`.

##### Developer Notes

+ The `LLMS_Student` object can be instantiated as an empty object and bypass current user autoloading. In the future this may affect integrations using the `lifterlms_new_pending_order` action hook which will receive an "empty" student object during order setup by gateways utilizing new AJAX-powered checkout endpoints.
+ Improved payment gateway secure string logging by adding a method, `add_secure_string()` allowing developers to add secure strings during runtime without the necessity of registering the strings using filters.
+ Implemented new gateway feature: `modify_recurring_payments`. [#2176](https://github.com/gocodebox/lifterlms/issues/2176)
+ Added new filter `llms_transaction_can_be_refunded` enabling custom refund restrictions to be applied to a transaction.

##### Updated Templates

+ [templates/checkout/form-gateways.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-alpha.1/templates/checkout/form-gateways.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-alpha.1/templates/checkout/form-switch-source.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/7.0.0-alpha.1/templates/myaccount/view-order-actions.php)


v6.7.0 - 2022-06-09
-------------------

##### Updates and Enhancements

+ Update LifterLMS Blocks to [v2.4.3](https://make.lifterlms.com/2022/06/09/lifterlms-blocks-version-2-4-3/).
+ Upgraded Action Scheduler to [v3.4.1](https://github.com/woocommerce/action-scheduler/releases/tag/3.4.1).
+ Upgraded Action Scheduler to [v3.4.2](https://github.com/woocommerce/action-scheduler/releases/tag/3.4.2).

##### Bug Fixes

+ Fixed a fatal error on PHP 8+ when restoring a post type from revision. [#2164](https://github.com/gocodebox/lifterlms/issues/2164)


v6.6.0 - 2022-05-23
-------------------

##### PHP Minimum Required Version Change

+ **Raised the minimum supported PHP version to 7.4.**

##### WordPress Minimum Required Version Change

+ **Raised the minimum supported WordPress core version to 5.6.**

##### New Features

+ Added support for WordPress 6.0.

##### Bug Fixes

+ Fixed the ability for 3rd party plugins to use the `lifterlms_external_engagement_handler_arguments` and `lifterlms_external_engagement_query_arguments` filters.
+ Added automatic exclusion of "no cache" pages from the WP Engine server-side cache when using "pretty" permalinks. [#1717](https://github.com/gocodebox/lifterlms/issues/1717)
+ Stop subtracting LifterLMS order note comments from global comment counts via the `wp_count_comments` filter on WordPress 6.0 and later. See related WordPress Trac ticket [#19901](https://core.trac.wordpress.org/ticket/19901)


v6.5.0 - 2022-05-11
-------------------

##### Upcoming PHP Version Requirement Change

**This will be the last version of LifterLMS to support PHP 7.3. The next version of LifterLMS, expected before the end of May 2022, will raise the minimum supported PHP version to 7.4. PHP 7.3 reached its official [end of life](https://www.php.net/eol.php) on December 6, 2021. If you are still using PHP 7.3 please upgrade to PHP 7.4 or later as soon as possible.**

##### Updates and Enhancements

+ Updates LifterLMS Rest to [v1.0.0-beta.25](https://make.lifterlms.com/2022/05/11/lifterlms-rest-api-version-1-0-0-beta-25/).

##### Bug Fixes

+ Students who have already completed a lesson will now automatically bypass the lesson's drip restrictions. [#1835](https://github.com/gocodebox/lifterlms/issues/1835)
+ Properly encode certificate JS localization data. [#2140](https://github.com/gocodebox/lifterlms/issues/2140)

##### Developer Notes

+ Added a new filter, `llms_lesson_drip_bypass_if_completed`, which controls the automatic bypass of drip restrictions for completed lessons. [#1835](https://github.com/gocodebox/lifterlms/issues/1835)
+ Allow avoiding error return when updating an `LLMS_Post_Model` post meta with the same value as the one stored in the database. [#909](https://github.com/gocodebox/lifterlms/issues/909)


v6.4.0 - 2022-04-19
-------------------

##### Upcoming PHP Version Requirement Change

**LifterLMS will drop support for PHP 7.3 by May, 2022. This will raise the minimum supported PHP version to 7.4. PHP 7.3 reached its official [end of life](https://www.php.net/eol.php) on December 6, 2021. If you are still using PHP 7.3 please upgrade to PHP 7.4 or later as soon as possible.**

##### New Features

+ Any "secure" payment gateway options will be automatically masked when written to debug log files.

##### Updates and Enhancements

+ When building notification content, only parse merge codes used in the notification. [#1465](https://github.com/gocodebox/lifterlms/issues/1465)
+ Improved checks related to the number of quiz attempts allowed for each student.
+ Prevent browser page caching on quizzes. [#2092](https://github.com/gocodebox/lifterlms/issues/2092)

##### Bug Fixes

+ Allowed classes extended from the manual payment gateway class to display payment instructions.
+ Allowed the `LLMS_Shortcode_User_Info` class to be filtered by the `llms_load_shortcodes` and `llms_load_shortcode_path` hooks.
+ Stop using the deprecated `FILTER_SANITIZE_STRING` constant.
+ Fixed an issue that caused shortcodes to not be replaced in some engagement emails. [#2070](https://github.com/gocodebox/lifterlms/issues/2070)
+ Improve core forms detection so to exclude duplicates. [#2052](https://github.com/gocodebox/lifterlms/issues/2052)
+ Added Aosta (AO) to the list of Italian provinces. [#2098](https://github.com/gocodebox/lifterlms/issues/2098)
+ Fixed a compatibility issue with the Elementor Pro Theme Builder encountered on course and membership catalogs. [#2111](https://github.com/gocodebox/lifterlms/issues/2111)
+ Fixed an issue where merge codes in reusable blocks on certificate templates were not replaced when the template was displayed or when the certificate was awarded and published. [#2058](https://github.com/gocodebox/lifterlms/issues/2058)
+ Fixed an issue with OceanWP and Twenty Twenty themes where the Terms and Conditions checkbox was displayed incorrectly. [#1938](https://github.com/gocodebox/lifterlms/issues/1938)

##### Developer Notes

+ Added a new filter, `llms_secure_strings` allowing developers to register strings that should be automatically masked when written to log files.
+ Added new filter `llms_no_cache` to control whether or not LifterLMS will send nocache headers. [#2092](https://github.com/gocodebox/lifterlms/issues/2092)
+ Added new filter `llms_template_loader_restricted_priority` to control the priority of the `template_include` hook callback used to load restricted content single templates.


v6.3.0 - 2022-04-07
-------------------

##### Upcoming PHP Version Requirement Change

**LifterLMS will drop support for PHP 7.3 by May, 2022. This will raise the minimum supported PHP version to 7.4. PHP 7.3 reached its official [end of life](https://www.php.net/eol.php) on December 6, 2021. If you are still using PHP 7.3 please upgrade to PHP 7.4 or later as soon as possible.**

##### New Features

+ Automatically add student's dashboard endpoints to the BuddyPress profile nav. [#627](https://github.com/gocodebox/lifterlms/issues/627)

##### Updates and Enhancements

+ Upgraded LifterLMS Blocks to [v2.4.2](https://make.lifterlms.com/2022/04/07/lifterlms-blocks-version-2-4-2/).
+ Updated LifterLMS Helper to [v3.4.2](https://make.lifterlms.com/2022/04/01/lifterlms-helper-version-3-4-2/).

##### Bug Fixes

+ Fixed paged queries in student dashboard not working when using plain permalinks.
+ Fixed an issue that prevented searching students in some admin areas when WordPress was installed in a subdirectory. [#2096](https://github.com/gocodebox/lifterlms/issues/2096)
+ Fixed lesson's comments status not reflecting default global setting when created with the course builder. [#2099](https://github.com/gocodebox/lifterlms/issues/2099)

##### Deprecations

+ Deprecated `LLMS_Integration_Buddypress::achievements_screen()` method with no replacement.
+ Deprecated `LLMS_Integration_Buddypress::certificates_screen()` method with no replacement.
+ Deprecated `LLMS_Integration_Buddypress::courses_screen()` method with no replacement.
+ Deprecated `LLMS_Integration_Buddypress::memberships_screen()` method with no replacement.
+ Deprecated `LLMS_Integration_Buddypress::remove_courses_paginate_links_filter()` method with no replacement.
+ Deprecated `LLMS_Integration_Buddypress::modify_courses_paginate_links()` method with no replacement.

##### Developer Notes

+ Added `llms_get_paged_query_var()` function that returns the page number query var for the current request.
+ Added new filter `llms_buddypress_profile_endpoints` to control the LifterLMS endpoints to be added to the BuddyPress profile.
+ Added new filter `llms_buddypress_min_nav_item_slug` to control the LifterLMS main BuddyPress' nav item slug.
+ Added new filter `llms_buddypress_min_nav_item_label` to control the LifterLMS main BuddyPress' nav item label.
+ Added new filter `llms_buddypress_min_nav_item_position` to control the LifterLMS main BuddyPress' nav item position.


v6.2.0 - 2022-03-30
-------------------

##### Updates and Enhancements

+ Changed the `llmsStudentsSelect2()` JavaScript function to use the LifterLMS REST API "list students" endpoint instead of the `LLMS_AJAX_Handler::query_students()` PHP function.
+ Upgraded LifterLMS Blocks to [v2.4.1](https://make.lifterlms.com/2022/03/30/lifterlms-blocks-version-2-4-1/).

##### Bug Fixes

+ Fixed issue with hidden checkboxes on LifterLMS forms.
+ Fixed a compatiblity issue with the Divi Theme Builder ignoring access restrictions when using template with custom body. [#2063](https://github.com/gocodebox/lifterlms/issues/2063)
+ Fixed an error encountered on the Engagements > Certificates screen when using the BuddyBoss theme. [#2080](https://github.com/gocodebox/lifterlms/issues/2080)

##### Deprecations

+ Deprecated `LLMS_AJAX_Handler::query_students()`. Use the [REST API list students](https://developer.lifterlms.com/rest-api/#tag/Students/paths/~1students/get) endpoint instead.

##### Developer Notes

+ Added new filter `llms_template_loader_priority` to control the priority of the `template_include` hook callback used to load restricted content templates.


v6.1.0 - 2022-03-23
-------------------

##### Upcoming PHP Version Requirement Change

**LifterLMS will drop support for PHP 7.3 by May, 2022. This will raise the minimum supported PHP version to 7.4. PHP 7.3 reached its official [end of life](https://www.php.net/eol.php) on December 6, 2021. If you are still using PHP 7.3 please upgrade to PHP 7.4 or later as soon as possible.**

##### New Features

+ Added the `{earned_date}` certificate merge code.

##### Updates and Enhancements

+ Changed the label for the `{current_date}` certificate merge code from 'Earned Date' to 'Current Date'.
+ Updates LifterLMS REST to [v1.0.0-beta.24](https://make.lifterlms.com/2022/03/17/lifterlms-rest-api-version-1-0-0-beta-24/).

##### Bug Fixes

+ Fixed an issue encountered when editing an order with a completed payment plan. [#2067](https://github.com/gocodebox/lifterlms/issues/2067)
+ Fixed access of protected LLMS_Abstract_Query properties.


v6.0.0 - 2022-03-08
-------------------

**This major release of LifterLMS focuses on improving the experience of creating, designing, and managing achievements and certificates: use the block editor to design certificates, sync awards with their templates, award achievements and certificates on demand without requiring an engagement trigger, and [much more](https://lifterlms.com/docs/getting-started-with-lifterlms-6-0/). In addition, this release removes a significant number of previously deprecated classes, methods, and functions. Please read the full Breaking Changes sections for more information on removed code.**

##### New Features

+ The block editor is now enabled by default for certificates when using WordPress versions 5.8 and later.
  + Existing certificates are marked as "legacy" and will continue to use the classic editor until migrated.
  + To migrate a certificate, click the "Migrate Certificate" button. This will force the certificate's content into blocks.
+ A number of new settings are available to certificates when using the block editor:
  + Set the certificate's display (and print) size using common paper sizes such as US Letter, US Legal, A3, A4, and more.
  + Set the certificate's display orientation: portrait of landscape.
  + Set the certificate's inner margins.
  + Set the certificate's background color.
+ A new block, the Certificate Title Block, has been made available to certificates.
  + The block works like a WordPress core Heading Block with added options for selecting from a few display fonts (provided by Google Web Fonts).
  + The block controls the title of awarded certificates.
+ Added the ability for administrators and LMS managers to edit earned certificates/achievements from the students reporting screen, as well as award new certificates/achievements to students.
+ Added the ability to sync awarded certificates with the template used to generate them. [#1078](https://github.com/gocodebox/lifterlms/issues/1078)
+ The `post_name` of earned certificate posts will be generated with a randomized 3+ character string in favor of relying on sequential numbers.
+ Added certificate global options for the default size of new certificates and certificate templates.
+ Certificate and email template merge code buttons now include [llms-user] information shortcodes.
+ Added certificate sequential ID functionality merge code.
+ Added a link to return to the student dashboard when viewing an awarded certificate. [#1959](https://github.com/gocodebox/lifterlms/issues/1959)
+ Provide additional information to hooks on the student single course reporting screen.

##### Updates and Enhancements

+ Added new default images for use with achievements and certificates.
  + The site-wide default images can be customized on the admin panel under Settings -> Engagements.
  + The old default images will automatically be used for legacy certificates and can be forced by filtering `llms_use_legacy_engagement_images`. [#1081](https://github.com/gocodebox/lifterlms/issues/1081)
+ Certificates no longer use the `header.php` and `footer.php` files from the site's theme, instead custom templates (`templates/certificates/header.php` and `templates/certificates/footer.php`) are used instead. These templates are minimal and exclude theme wrappers which reduces the visual conflicts encountered from theme wrappers, backgrounds, and more, especially when printing certificates. [#463](https://github.com/gocodebox/lifterlms/issues/463)
+ The achievements and certificates dashboard endpoints are now paginated. [#669](https://github.com/gocodebox/lifterlms/issues/669)
+ Added pagination to achievement and certificate reporting pages.
+ The URL of earned user certificates has been changed from "my_certificate" to "certificate". Requests to the old url are automatically redirected to the new url, including instances where the URL slug has been translated.
+ The URL of certificate template previews has been changed from "certificate" to "certificate-template".
+ The certificate merge code, `{first_name}`, now outputs an empty string in favor of falling back to the user's nickname when there is no first name for the user. [#1640](https://github.com/gocodebox/lifterlms/issues/1640)
+ The look and behavior of the certificate {{MINI_CERTIFICATE}} pop-over notification merge code now displays a placeholder preview of the certificate in favor of attempting to render a tiny version of the actual certificate. [#1950](https://github.com/gocodebox/lifterlms/issues/1950)
+ The coupon code in the student's order details table is now wrapped in a `<code>` tag instead of an `<a>` tag. [#2033](https://github.com/gocodebox/lifterlms/issues/2033)
+ Updates LifterLMS REST to [v1.0.0-beta.23](https://make.lifterlms.com/2022/02/23/lifterlms-rest-api-version-1-0-0-beta-23/).
+ Updated LifterLMS Blocks to [version 2.4.0](https://make.lifterlms.com/2022/02/25/lifterlms-blocks-version-2-4-0/).

##### Bug Fixes

+ Delayed engagements are automatically unscheduled when the related post is deleted.
+ A disabled student dashboard endpoint will no longer display the endpoint's summary on the main dashboard page. [#535](https://github.com/gocodebox/lifterlms/issues/535)
+ Prior to sending a delayed engagement the recipient's enrollment in the related post is verified resulting the engagement not being triggered if the recipient's enrollment has been terminated. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ Post search filter boxes on various post tables will now longer display a link to the selected post.
+ Basic notification code is no longer loaded on the admin panel.
+ Fixed the label hover on picture type quizzes in some themes. [#2015](https://github.com/gocodebox/lifterlms/issues/2015)

##### Database Migration

+ A database migration is required when upgrading from versions earlier than 6.0.0. A description of the required updates can be found at [https://lifterlms.com/docs/lifterlms-database-updates/#600](https://lifterlms.com/docs/lifterlms-database-updates/#600).

##### Deprecations

+ Public access to properties of the abstract `LLMS_Database_Query` has been deprecated.
  + Public access to class property `LLMS_Database_Query::$found_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_found_results()`.
  + Public access to class property `LLMS_Database_Query::$max_pages`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_max_pages()`.
  + Public access to class property `LLMS_Database_Query::$number_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_number_results()`.
  + Public access to class property `LLMS_Database_Query::$results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_results()`.
  + Public access to class property `LLMS_Database_Query::$query_vars`. The variable as a whole cannot be publicly accessed, instead use `LLMS_Database_Query::get()` and `LLMS_Database_Query::set()` to read and write to the array.
  + The above changes were made to the abstract class `LLMS_Database_Query` but the following concrete classes that utilize the abstract are also affected by this change: `LLMS_Query_User_Postmeta`, `LLMS_Student_Query`, `LLMS_Query_Quiz_Attempt`, `LLMS_Events_Query`, and `LLMS_Notifications_Query`.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Achievement::format_string()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_title()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Achievement::create()` is deprecated with no replacement.
+ Method `LLMS_Achievements::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement()`.
+ Class `LLMS_Certificate` is deprecated with no direct replacement.
  + Method `LLMS_Certificate::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Certificate::format_string()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
+ Method `LLMS_Certificates::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate()`.
+ Method `LLMS_Engagements::init()` is deprecated with no replacement.
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Method `LLMS_Database_Query::set_found_results()` is deprecated.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement_User::has_user_earned()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::init()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::get_content_html()` is deprecated with no replacement.
+ Class `LLMS_Certificate_User` is deprecated with no direct replacement.
  + Method `LLMS_Certificate_User::init()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::set_shortcode_user()` is deprecated with no replacement.
+ Engagement debug logging is removed. Use `llms_log()` directly instead.
+ Filter `llms_db_query_get_default_args` is deprecated in favor of `llms_{$this->id}_query_get_default_args`.
+ Filter `llms_certificate_has_user_earned` is deprecated in favor of `llms_earned_certificate_dupcheck`.
+ Unused public class property `LLMS_Achievements::$content` is deprecated with no replacement.
+ Method `LLMS_Admin_Post_Types::meta_metabox_init()` is deprecated with no replacement.
+ The site options `lifterlms_certificate_bg_img_width`, `lifterlms_certificate_bg_img_height`, and `lifterlms_certificate_legacy_image_size` are now used only for certificates and certificate templates created using the classic editor.
  + The settings, found on the Engagements Settings screen, are hidden by default.
  + During the database upgrade from versions earlier than 6.x, an site option, `llms_has_legacy_certificates`  is added when at least one certificate is found. This option will display the settings so they can continue to be used for legacy certificates.
  + After migrating all certificates on a site, the settings will still display. In order to remove them from the screen a developer can either delete the option `llms_has_legacy_certificates` or return `false` from the filter `llms_has_legacy_certificates`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ The constant `LLMS_ENGAGEMENT_DEBUG` is deprecated with no replacement.
+ Engagement debugging via `LLMS_Engagements::log` is deprecated. Use `llms_log()` instead.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Filter `lifterlms_register_post_type_llms_my_certificate` is deprecated in favor of `lifterlms_register_post_type_my_certificate`.
+ Deprecated the misspelled protected method `LLMS_Database_Query::preprare_query()` and replaced with `LLMS_Database_Query::prepare_query()`.
  + Class method `LLMS_Events_Query::preprare_query` replaced with `LLMS_Events_Query::prepare_query()`.
  + Class method `LLMS_Query_Quiz_Attempt::preprare_query` replaced with `LLMS_Query_Quiz_Attempt::prepare_query()`.
  + Class method `LLMS_Query_User_Postmeta::preprare_query` replaced with `LLMS_Query_User_Postmeta::prepare_query()`.
  + Class method `LLMS_Student_Query::preprare_query` replaced with `LLMS_Student_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`. [#859](https://github.com/gocodebox/lifterlms/issues/859)

##### Breaking Changes

+ Removed FSE template: `templates/block-templates/single-certificate.html`.
+ Removed the deprecated `LLMS()` function in favor of the `llms()` function.
+ Removed the deprecated the `LLMS_SendWP::do_remote_install()` method in favor of the `LLMS_Abstract_Email_Provider::do_remote_install()` method.
+ Removed the deprecated `LLMS_Abstract_Email_Provider::output_css()` method.
+ Removed the deprecated `LLMS_Abstract_Generator_Posts::increment()` method.
+ Removed the deprecated `LLMS_Admin_Users_Table::load_dependencies()` method.
+ Removed the deprecated `LLMS_Admin_Import::localize_stat()` method.
+ Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::generator_course_status()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::output_step_html()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::scripts()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::watch_course_generation()` method.
+ Removed the deprecated `llms_format_decimal()` function.
+ Removed the deprecated `llms_set_person_auth_cookie()` function.
+ Removed the deprecated `LLMS_Course::sections` property.
+ Removed the deprecated `LLMS_Course::sku` property.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_pw_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::is_inline_script_enqueued()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::format_date()` method.
+ Removed the deprecated `LLMS_Generator::get_author_id_from_raw()` method.
+ Removed the deprecated `LLMS_Generator::get_default_post_status()` method.
+ Removed the deprecated `LLMS_Generator::get_generated_posts()` method.
+ Removed the deprecated `LLMS_Generator::increment()` method.
+ Removed the deprecated `llms__created` action hook from the `LLMS_Abstract_Database_Store::create()` method.
+ Removed the deprecated `llms__deleted` action hook from the `LLMS_Abstract_Database_Store::delete()` method.
+ Removed the deprecated `llms__updated` action hook from the `LLMS_Abstract_Database_Store::update()` method.
+ Removed the deprecated `llms_user_removed_from_membership_level` action hook from the `LLMS_Student::unenroll()` method.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `lifterlms_template_pricing_table()` function.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `templates/product/pricing-table.php` file.
+ Removed the deprecated `LLMS_Frontend_Password` class.
+ Removed the deprecated `LLMS_Install::db_updates()` method.
+ Removed the deprecated `LLMS_Install::update_notice()` method.
+ Removed the deprecated `LLMS_Notifications::dispatch_processors()` method.
+ Removed the deprecated `llms_processors_async_dispatching` filter hook from the `LLMS_Notifications::__construct()` method.
+ Removed the deprecated `LLMS_Notifications::$_instance` property.
+ Removed the deprecated `LLMS_Person_Handler::register()` method.
+ Removed the deprecated `LLMS_Person_Handler::sanitize_field()` method.
+ Removed the deprecated `LLMS_Person_Handler::update()` method.
+ Removed the deprecated `LLMS_Person_Handler::validate_fields()` method.
+ Removed the deprecated `LLMS_Person_Handler::voucher_toggle_script()` method.
+ Removed the deprecated `templates/admin/notices/db-update.php` file.
+ Removed the deprecated `templates/admin/notices/db-updating.php` file.
+ Removed the deprecated `llms_usernames_blacklist` filter hook in the `llms_get_usernames_blocklist()` function.
+ Removed the deprecated `includes/libraries/wp-background-processing/index.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-async-request.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-background-process.php` file.
+ Removed the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
+ Removed the deprecated `LLMS_Section::get_order()` method.
+ Removed the deprecated `LLMS_Section::get_parent_course()` method.
+ Removed the deprecated `LLMS_Section::set_parent_course()` method.
+ Removed the deprecated `LLMS_AJAX::check_voucher_duplicate()` method.
+ Removed the deprecated `LLMS_AJAX::get_ajax_data()` method.
+ Removed the deprecated `LLMS_AJAX::register_script()` method.
+ Removed the deprecated `LLMS_Interface_Post_Audio` interface.
+ Removed the deprecated `LLMS_Interface_Post_Sales_Page` interface.
+ Removed the deprecated `LLMS_Interface_Post_Video` interface.
+ Removed the deprecated `LLMS_Achievements::$_instance` property.
+ Removed the deprecated `LLMS_Certificates::$_instance` property.
+ Removed the deprecated `LLMS_Emails::$_instance` property.
+ Removed the deprecated `LLMS_Engagements::$_instance` property.
+ Removed the deprecated `LLMS_Events::$_instance` property.
+ Removed the deprecated `LLMS_Grades::$_instance` property.
+ Removed the deprecated `LLMS_Integrations::$_instance` property.
+ Removed the deprecated `LLMS_Payment_Gateways::$_instance` property.
+ Removed the deprecated `LLMS_Processors::$_instance` property.
+ Removed the deprecated `LLMS_Sessions::$_instance` property.

##### Developer Notes

+ Added `LLMS_Awards_Query`, used for querying data about awarded certificates and achievements.
  + The method signature `LLMS_Student::get_achievements()` and `LLMS_Student::get_certificates()` now use this class under tho hood.
  + The previous method signature, which passed data into a direct SQL query, is now deprecated.
+ Achievement and certificate data storage locations have been modified, primarily to reduce reliance on the `wp_postmeta` table which will result in a site-wide performance improvement, especially on large sites.
  + Meta properties `_llms_achievement_content` and `_llms_certificate_content` have been removed in favor of `WP_Post::$post_content`.
  + Meta properties `_llms_achievement_title` and `_llms_certificate_title` have been removed in favor of `WP_Post::$post_title`.
  + Meta properties `_llms_achievement_template` and `_llms_certificate_template` have been removed in favor of `WP_Post::$post_parent`.
  + Meta properties `_llms_achievement_image` and `_llms_certificate_image` have been moved the meta property `_thumbnail_id` in order to utilize the WordPress core's featured image functionality and internal APIs.
+ Reliance on `lifterlms_user_postmeta` for achievement and certificate data will be removed in a future release.
  + User postmeta properties `_achievement_earned` and `_certificate_earned` will continue to be recorded but are no longer being used internally.
  + The `updated_date` is now accessible via `WP_Post::$post_date`.
  + The `user_id` is now accessible via `WP_Post::$post_author`.
+ Added new Javascript UI components library, modeled after `@wordpress/components`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/components).
+ Added a new SVG icon library, modeled after `@wordpress/icons`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/icons).
+ The merge code button seen on certificate and email template editors is now an SVG image instead of a PNG.
+ Added utility function for escaping and quoting strings. [#1027](https://github.com/gocodebox/lifterlms/issues/1027)
+ Added the ability to force an admin metabox field value through the new `meta` arg. [#2016](https://github.com/gocodebox/lifterlms/issues/2016)
+ Added new utility function for stripping prefixes from strings.

##### Performance Improvements

+ Increased the number of files that are autoloaded instead of manually loaded.

##### Updated Templates

+ [templates/achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/achievements/loop.php)
+ [templates/achievements/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/achievements/template.php)
+ [templates/admin/notices/db-update.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/admin/notices/db-update.php)
+ [templates/admin/notices/db-updating.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/admin/notices/db-updating.php)
+ [templates/admin/reporting/reporting.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/admin/reporting/reporting.php)
+ [templates/admin/reporting/tabs/students/courses-course.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/admin/reporting/tabs/students/courses-course.php)
+ [templates/admin/reporting/tabs/students/information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/admin/reporting/tabs/students/information.php)
+ [templates/block-templates/single-certificate.html](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/block-templates/single-certificate.html)
+ [templates/certificates/actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/actions.php)
+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/content-legacy.php)
+ [templates/certificates/content.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/content.php)
+ [templates/certificates/dynamic-styles.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/dynamic-styles.php)
+ [templates/certificates/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/footer.php)
+ [templates/certificates/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/header.php)
+ [templates/certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/loop.php)
+ [templates/certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/preview.php)
+ [templates/certificates/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/certificates/template.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/checkout/form-switch-source.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/content-certificate.php)
+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/emails/footer.php)
+ [templates/emails/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/emails/header.php)
+ [templates/myaccount/my-grades-single-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/myaccount/my-grades-single-table.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/myaccount/view-order-actions.php)
+ [templates/myaccount/view-order-information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/myaccount/view-order-information.php)
+ [templates/myaccount/view-order-transactions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/myaccount/view-order-transactions.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/myaccount/view-order.php)
+ [templates/product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/product/pricing-table.php)
+ [templates/single-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0/templates/single-certificate.php)


v6.0.0-rc.1 - 2022-03-03
------------------------

##### New Features

+ Added a link to return to the student dashboard when viewing an awarded certificate. [#1959](https://github.com/gocodebox/lifterlms/issues/1959)
+ The block editor is now enabled by default for certificates when using WordPress versions 5.8 and later.
  + Existing certificates are marked as "legacy" and will continue to use the classic editor until migrated.
  + To migrate a certificate, click the "Migrate Certificate" button. This will force the certificate's content into blocks.
+ A number of new settings are available to certificates when using the block editor:
  + Set the certificate's display (and print) size using common paper sizes such as US Letter, US Legal, A3, A4, and more.
  + Set the certificate's display orientation: portrait of landscape.
  + Set the certificate's inner margins.
  + Set the certificate's background color.
+ A new block, the Certificate Title Block, has been made available to certificates.
  + The block works like a WordPress core Heading Block with added options for selecting from a few display fonts (provided by Google Web Fonts).
  + The block controls the title of awarded certificates.
+ + Added the ability to sync awarded certificates with the template used to generate them. [#1078](https://github.com/gocodebox/lifterlms/issues/1078)
+ The `post_name` of earned certificate posts will be generated with a randomized 3+ character string in favor of relying on sequential numbers.
+ Added the ability for administrators and LMS managers to edit earned certificates/achievements from the students reporting screen, as well as award new certificates/achievements to students.
+ + Added certificate global options for the default size of new certificates and certificate templates.
+ Certificate and email template merge code buttons now include [llms-user] information shortcodes.
+ Added certificate sequential ID functionality merge code. [Read more](@TODO).
+ Provide additional information to hooks on the student single course reporting screen.

##### Updates and Enhancements

+ Added pagination to achievement and certificate reporting pages.
+ Certificates no longer use the `header.php` and `footer.php` files from the site's theme, instead custom templates (`templates/certificates/header.php` and `templates/certificates/footer.php`) are used instead. These templates are minimal and exclude theme wrappers which reduces the visual conflicts encountered from theme wrappers, backgrounds, and more, especially when printing certificates. [#463](https://github.com/gocodebox/lifterlms/issues/463)
+ The achievements and certificates dashboard endpoints are now paginated. [#669](https://github.com/gocodebox/lifterlms/issues/669)
+ Added new default images for use with achievements and certificates.
  + The site-wide default images can be customized on the admin panel under Settings -> Engagements.
  + The old default images can be used by filtering `llms_use_legacy_engagement_images`. [#1081](https://github.com/gocodebox/lifterlms/issues/1081)
+ The URL of earned user certificates has been changed from "my_certificate" to "certificate". Requests to the old url are automatically redirected to the new url, including instances where the URL slug has been translated.
+ The URL of certificate template previews has been changed from "certificate" to "certificate-template".
+ The certificate merge code, `{first_name}`, now outputs an empty string in favor of falling back to the user's nickname when there is no first name for the user. [#1640](https://github.com/gocodebox/lifterlms/issues/1640)
+ Updates LifterLMS REST to [v1.0.0-beta.23](https://make.lifterlms.com/2022/02/23/lifterlms-rest-api-version-1-0-0-beta-23/).
+ Updated LifterLMS Blocks to [version 2.4.0](https://make.lifterlms.com/2022/02/25/lifterlms-blocks-version-2-4-0/).
+ The look and behavior of the certificate {{MINI_CERTIFICATE}} pop-over notification merge code now displays a placeholder preview of the certificate in favor of attempting to render a tiny version of the actual certificate. [#1950](https://github.com/gocodebox/lifterlms/issues/1950)

##### Bug Fixes

+ + Fixed how the protected `LLMS_Notifications_Query::$found_results` property is accessed in `LLMS_Abstract_Notification_Controller::has_subscriber_received()`. + Fixed how the protected `LLMS_Notifications_Query::$max_pages` property is accessed in `lifterlms_template_student_dashboard_my_notifications()`.
+ Delayed engagements are automatically unscheduled when the related post is deleted.
+ Prior to sending a delayed engagement the recipient's enrollment in the related post is verified resulting the engagement not being triggered if the recipient's enrollment has been terminated. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ A disabled student dashboard endpoint will no longer display the endpoint's summary on the main dashboard page. [#535](https://github.com/gocodebox/lifterlms/issues/535)
+ Post search filter boxes on various post tables will now longer display a link to the selected post.
+ Basic notification code is no longer loaded on the admin panel.

##### Deprecations

+ Public access to properties of the abstract `LLMS_Database_Query` has been deprecated.
  + Public access to class property `LLMS_Database_Query::$found_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_found_results()`.
  + Public access to class property `LLMS_Database_Query::$max_pages`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_max_pages()`.
  + Public access to class property `LLMS_Database_Query::$number_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_number_results()`.
  + Public access to class property `LLMS_Database_Query::$results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_results()`.
  + Public access to class property `LLMS_Database_Query::$query_vars`. The variable as a whole cannot be publicly accessed, instead use `LLMS_Database_Query::get()` and `LLMS_Database_Query::set()` to read and write to the array.
  + The above changes were made to the abstract class `LLMS_Database_Query` but the following concrete classes that utilize the abstract are also affected by this change: `LLMS_Query_User_Postmeta`, `LLMS_Student_Query`, `LLMS_Query_Quiz_Attempt`, `LLMS_Events_Query`, and `LLMS_Notifications_Query`.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Achievement::format_string()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_title()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Achievement::create()` is deprecated with no replacement.
+ Method `LLMS_Achievements::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement()`.
+ Class `LLMS_Certificate` is deprecated with no direct replacement.
  + Method `LLMS_Certificate::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Certificate::format_string()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
+ Method `LLMS_Certificates::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate()`.
+ Method `LLMS_Engagements::init()` is deprecated with no replacement.
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Method `LLMS_Database_Query::set_found_results()` is deprecated.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement_User::has_user_earned()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::init()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::get_content_html()` is deprecated with no replacement.
+ Class `LLMS_Certificate_User` is deprecated with no direct replacement.
  + Method `LLMS_Certificate_User::init()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::set_shortcode_user()` is deprecated with no replacement.
+ Engagement debug logging is removed. Use `llms_log()` directly instead.
+ Filter `llms_db_query_get_default_args` is deprecated in favor of `llms_{$this->id}_query_get_default_args`.
+ Filter `llms_certificate_has_user_earned` is deprecated in favor of `llms_earned_certificate_dupcheck`.
+ Unused public class property `LLMS_Achievements::$content` is deprecated with no replacement.
+ Method `LLMS_Admin_Post_Types::meta_metabox_init()` is deprecated with no replacement.
+ The site options `lifterlms_certificate_bg_img_width`, `lifterlms_certificate_bg_img_height`, and `lifterlms_certificate_legacy_image_size` are now used only for certificates and certificate templates created using the classic editor.
  + The settings, found on the Engagements Settings screen, are hidden by default.
  + During the database upgrade from versions earlier than 6.x, an site option, `llms_has_legacy_certificates`  is added when at least one certificate is found. This option will display the settings so they can continue to be used for legacy certificates.
  + After migrating all certificates on a site, the settings will still display. In order to remove them from the screen a developer can either delete the option `llms_has_legacy_certificates` or return `false` from the filter `llms_has_legacy_certificates`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ The constant `LLMS_ENGAGEMENT_DEBUG` is deprecated with no replacement.
+ Engagement debugging via `LLMS_Engagements::log` is deprecated. Use `llms_log()` instead.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Filter `lifterlms_register_post_type_llms_my_certificate` is deprecated in favor of `lifterlms_register_post_type_my_certificate`.
+ Deprecated the misspelled protected method `LLMS_Database_Query::preprare_query()` and replaced with `LLMS_Database_Query::prepare_query()`.
  + Class method `LLMS_Events_Query::preprare_query` replaced with `LLMS_Events_Query::prepare_query()`.
  + Class method `LLMS_Query_Quiz_Attempt::preprare_query` replaced with `LLMS_Query_Quiz_Attempt::prepare_query()`.
  + Class method `LLMS_Query_User_Postmeta::preprare_query` replaced with `LLMS_Query_User_Postmeta::prepare_query()`.
  + Class method `LLMS_Student_Query::preprare_query` replaced with `LLMS_Student_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`. [#859](https://github.com/gocodebox/lifterlms/issues/859)

##### Breaking Changes

+ Removed FSE template: `templates/block-templates/single-certificate.html`.
+ Removed the deprecated `LLMS()` function in favor of the `llms()` function.
+ Removed the deprecated the `LLMS_SendWP::do_remote_install()` method in favor of the `LLMS_Abstract_Email_Provider::do_remote_install()` method.
+ Removed the deprecated `LLMS_Abstract_Email_Provider::output_css()` method.
+ Removed the deprecated `LLMS_Abstract_Generator_Posts::increment()` method.
+ Removed the deprecated `LLMS_Admin_Users_Table::load_dependencies()` method.
+ Removed the deprecated `LLMS_Admin_Import::localize_stat()` method.
+ Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::generator_course_status()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::output_step_html()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::scripts()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::watch_course_generation()` method.
+ Removed the deprecated `llms_format_decimal()` function.
+ Removed the deprecated `llms_set_person_auth_cookie()` function.
+ Removed the deprecated `LLMS_Course::sections` property.
+ Removed the deprecated `LLMS_Course::sku` property.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_pw_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::is_inline_script_enqueued()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::format_date()` method.
+ Removed the deprecated `LLMS_Generator::get_author_id_from_raw()` method.
+ Removed the deprecated `LLMS_Generator::get_default_post_status()` method.
+ Removed the deprecated `LLMS_Generator::get_generated_posts()` method.
+ Removed the deprecated `LLMS_Generator::increment()` method.
+ Removed the deprecated `llms__created` action hook from the `LLMS_Abstract_Database_Store::create()` method.
+ Removed the deprecated `llms__deleted` action hook from the `LLMS_Abstract_Database_Store::delete()` method.
+ Removed the deprecated `llms__updated` action hook from the `LLMS_Abstract_Database_Store::update()` method.
+ Removed the deprecated `llms_user_removed_from_membership_level` action hook from the `LLMS_Student::unenroll()` method.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `lifterlms_template_pricing_table()` function.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `templates/product/pricing-table.php` file.
+ Removed the deprecated `LLMS_Frontend_Password` class.
+ Removed the deprecated `LLMS_Install::db_updates()` method.
+ Removed the deprecated `LLMS_Install::update_notice()` method.
+ Removed the deprecated `LLMS_Notifications::dispatch_processors()` method.
+ Removed the deprecated `llms_processors_async_dispatching` filter hook from the `LLMS_Notifications::__construct()` method.
+ Removed the deprecated `LLMS_Notifications::$_instance` property.
+ Removed the deprecated `LLMS_Person_Handler::register()` method.
+ Removed the deprecated `LLMS_Person_Handler::sanitize_field()` method.
+ Removed the deprecated `LLMS_Person_Handler::update()` method.
+ Removed the deprecated `LLMS_Person_Handler::validate_fields()` method.
+ Removed the deprecated `LLMS_Person_Handler::voucher_toggle_script()` method.
+ Removed the deprecated `templates/admin/notices/db-update.php` file.
+ Removed the deprecated `templates/admin/notices/db-updating.php` file.
+ Removed the deprecated `llms_usernames_blacklist` filter hook in the `llms_get_usernames_blocklist()` function.
+ Removed the deprecated `includes/libraries/wp-background-processing/index.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-async-request.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-background-process.php` file.
+ Removed the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
+ Removed the deprecated `LLMS_Section::get_order()` method.
+ Removed the deprecated `LLMS_Section::get_parent_course()` method.
+ Removed the deprecated `LLMS_Section::set_parent_course()` method.
+ Removed the deprecated `LLMS_AJAX::check_voucher_duplicate()` method.
+ Removed the deprecated `LLMS_AJAX::get_ajax_data()` method.
+ Removed the deprecated `LLMS_AJAX::register_script()` method.
+ Removed the deprecated `LLMS_Interface_Post_Audio` interface.
+ Removed the deprecated `LLMS_Interface_Post_Sales_Page` interface.
+ Removed the deprecated `LLMS_Interface_Post_Video` interface.
+ Removed the deprecated `LLMS_Achievements::$_instance` property.
+ Removed the deprecated `LLMS_Certificates::$_instance` property.
+ Removed the deprecated `LLMS_Emails::$_instance` property.
+ Removed the deprecated `LLMS_Engagements::$_instance` property.
+ Removed the deprecated `LLMS_Events::$_instance` property.
+ Removed the deprecated `LLMS_Grades::$_instance` property.
+ Removed the deprecated `LLMS_Integrations::$_instance` property.
+ Removed the deprecated `LLMS_Payment_Gateways::$_instance` property.
+ Removed the deprecated `LLMS_Processors::$_instance` property.
+ Removed the deprecated `LLMS_Sessions::$_instance` property.

##### Developer Notes

+ Added `LLMS_Awards_Query`, used for querying data about awarded certificates and achievements.
  + The method signature `LLMS_Student::get_achievements()` and `LLMS_Student::get_certificates()` now use this class under tho hood.
  + The previous method signature, which passed data into a direct SQL query, is now deprecated.
+ Achievement and certificate data storage locations have been modified, primarily to reduce reliance on the `wp_postmeta` table which will result in a site-wide performance improvement, especially on large sites.
  + Meta properties `_llms_achievement_content` and `_llms_certificate_content` have been removed in favor of `WP_Post::$post_content`.
  + Meta properties `_llms_achievement_title` and `_llms_certificate_title` have been removed in favor of `WP_Post::$post_title`.
  + Meta properties `_llms_achievement_template` and `_llms_certificate_template` have been removed in favor of `WP_Post::$post_parent`.
  + Meta properties `_llms_achievement_image` and `_llms_certificate_image` have been moved the meta property `_thumbnail_id` in order to utilize the WordPress core's featured image functionality and internal APIs.
+ Reliance on `lifterlms_user_postmeta` for achievement and certificate data will be removed in a future release.
  + User postmeta properties `_achievement_earned` and `_certificate_earned` will continue to be recorded but are no longer being used internally.
  + The `updated_date` is now accessible via `WP_Post::$post_date`.
  + The `user_id` is now accessible via `WP_Post::$post_author`.
+ Added new Javascript UI components library, modeled after `@wordpress/components`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/components).
+ Added a new SVG icon library, modeled after `@wordpress/icons`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/icons).
+ The merge code button seen on certificate and email template editors is now an SVG image instead of a PNG.
+ Added utility function for escaping and quoting strings. [#1027](https://github.com/gocodebox/lifterlms/issues/1027)
+ Added the ability to force an admin metabox field value through the new `meta` arg. [#2016](https://github.com/gocodebox/lifterlms/issues/2016)
+ Added new utility function for stripping prefixes from strings.

##### Performance Improvements

+ Increased the number of files that are autoloaded instead of manually loaded.

##### Updated Templates

+ [templates/achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/achievements/loop.php)
+ [templates/achievements/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/achievements/template.php)
+ [templates/admin/notices/db-update.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/admin/notices/db-update.php)
+ [templates/admin/notices/db-updating.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/admin/notices/db-updating.php)
+ [templates/admin/reporting/reporting.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/admin/reporting/reporting.php)
+ [templates/admin/reporting/tabs/students/courses-course.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/admin/reporting/tabs/students/courses-course.php)
+ [templates/admin/reporting/tabs/students/information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/admin/reporting/tabs/students/information.php)
+ [templates/block-templates/single-certificate.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/block-templates/single-certificate.html)
+ [templates/certificates/actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/actions.php)
+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/content-legacy.php)
+ [templates/certificates/content.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/content.php)
+ [templates/certificates/dynamic-styles.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/dynamic-styles.php)
+ [templates/certificates/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/footer.php)
+ [templates/certificates/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/header.php)
+ [templates/certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/loop.php)
+ [templates/certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/preview.php)
+ [templates/certificates/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/certificates/template.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/checkout/form-switch-source.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/content-certificate.php)
+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/emails/footer.php)
+ [templates/emails/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/emails/header.php)
+ [templates/myaccount/my-grades-single-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/myaccount/my-grades-single-table.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/myaccount/view-order-actions.php)
+ [templates/myaccount/view-order-information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/myaccount/view-order-information.php)
+ [templates/myaccount/view-order-transactions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/myaccount/view-order-transactions.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/myaccount/view-order.php)
+ [templates/product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/product/pricing-table.php)
+ [templates/single-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-rc.1/templates/single-certificate.php)


v6.0.0-beta.2 - 2022-02-22
--------------------------

##### New Features

+ Added a link to return to the student dashboard when viewing an awarded certificate. [#1959](https://github.com/gocodebox/lifterlms/issues/1959)
+ The block editor is now enabled by default for certificates when using WordPress versions 5.8 and later.
  + Existing certificates are marked as "legacy" and will continue to use the classic editor until migrated.
  + To migrate a certificate, click the "Migrate Certificate" button. This will force the certificate's content into blocks.
+ A number of new settings are available to certificates when using the block editor:
  + Set the certificate's display (and print) size using common paper sizes such as US Letter, US Legal, A3, A4, and more.
  + Set the certificate's display orientation: portrait of landscape.
  + Set the certificate's inner margins.
  + Set the certificate's background color.
+ A new block, the Certificate Title Block, has been made available to certificates.
  + The block works like a WordPress core Heading Block with added options for selecting from a few display fonts (provided by Google Web Fonts).
  + The block controls the title of awarded certificates.
+ + Added the ability to sync awarded certificates with the template used to generate them. [#1078](https://github.com/gocodebox/lifterlms/issues/1078)
+ The `post_name` of earned certificate posts will be generated with a randomized 3+ character string in favor of relying on sequential numbers.
+ Added the ability for administrators and LMS managers to edit earned certificates/achievements from the students reporting screen, as well as award new certificates/achievements to students.
+ + Added certificate global options for the default size of new certificates and certificate templates.
+ Certificate and email template merge code buttons now include [llms-user] information shortcodes.
+ Added certificate sequential ID functionality merge code. [Read more](@TODO).

##### Updates and Enhancements

+ Added pagination to achievement and certificate reporting pages.
+ Certificates no longer use the `header.php` and `footer.php` files from the site's theme, instead custom templates (`templates/certificates/header.php` and `templates/certificates/footer.php`) are used instead. These templates are minimal and exclude theme wrappers which reduces the visual conflicts encountered from theme wrappers, backgrounds, and more, especially when printing certificates. [#463](https://github.com/gocodebox/lifterlms/issues/463)
+ The achievements and certificates dashboard endpoints are now paginated. [#669](https://github.com/gocodebox/lifterlms/issues/669)
+ Added new default images for use with achievements and certificates.
  + The site-wide default images can be customized on the admin panel under Settings -> Engagements.
  + The old default images can be used by filtering `llms_use_legacy_engagement_images`. [#1081](https://github.com/gocodebox/lifterlms/issues/1081)
+ The URL of earned user certificates has been changed from "my_certificate" to "certificate". Requests to the old url are automatically redirected to the new url, including instances where the URL slug has been translated.
+ The URL of certificate template previews has been changed from "certificate" to "certificate-template".
+ The certificate merge code, `{first_name}`, now outputs an empty string in favor of falling back to the user's nickname when there is no first name for the user. [#1640](https://github.com/gocodebox/lifterlms/issues/1640)
+ Updates LifterLMS REST to [v1.0.0-beta.22](https://make.lifterlms.com/2021/12/15/lifterlms-rest-api-version-1-0-0-beta-22/).
+ The look and behavior of the certificate {{MINI_CERTIFICATE}} pop-over notification merge code now displays a placeholder preview of the certificate in favor of attempting to render a tiny version of the actual certificate. [#1950](https://github.com/gocodebox/lifterlms/issues/1950)

##### Bug Fixes

+ + Fixed how the protected `LLMS_Notifications_Query::$found_results` property is accessed in `LLMS_Abstract_Notification_Controller::has_subscriber_received()`. + Fixed how the protected `LLMS_Notifications_Query::$max_pages` property is accessed in `lifterlms_template_student_dashboard_my_notifications()`.
+ Delayed engagements are automatically unscheduled when the related post is deleted.
+ Prior to sending a delayed engagement the recipient's enrollment in the related post is verified resulting the engagement not being triggered if the recipient's enrollment has been terminated. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ A disabled student dashboard endpoint will no longer display the endpoint's summary on the main dashboard page. [#535](https://github.com/gocodebox/lifterlms/issues/535)
+ Post search filter boxes on various post tables will now longer display a link to the selected post.
+ Basic notification code is no longer loaded on the admin panel.

##### Deprecations

+ Public access to properties of the abstract `LLMS_Database_Query` has been deprecated.
  + Public access to class property `LLMS_Database_Query::$found_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_found_results()`.
  + Public access to class property `LLMS_Database_Query::$max_pages`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_max_pages()`.
  + Public access to class property `LLMS_Database_Query::$number_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_number_results()`.
  + Public access to class property `LLMS_Database_Query::$results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_results()`.
  + Public access to class property `LLMS_Database_Query::$query_vars`. The variable as a whole cannot be publicly accessed, instead use `LLMS_Database_Query::get()` and `LLMS_Database_Query::set()` to read and write to the array.
  + The above changes were made to the abstract class `LLMS_Database_Query` but the following concrete classes that utilize the abstract are also affected by this change: `LLMS_Query_User_Postmeta`, `LLMS_Student_Query`, `LLMS_Query_Quiz_Attempt`, `LLMS_Events_Query`, and `LLMS_Notifications_Query`.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Achievement::format_string()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_title()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Achievement::create()` is deprecated with no replacement.
+ Method `LLMS_Achievements::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement()`.
+ Class `LLMS_Certificate` is deprecated with no direct replacement.
  + Method `LLMS_Certificate::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Certificate::format_string()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
+ Method `LLMS_Certificates::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate()`.
+ Method `LLMS_Engagements::init()` is deprecated with no replacement.
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Method `LLMS_Database_Query::set_found_results()` is deprecated.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement_User::has_user_earned()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::init()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::get_content_html()` is deprecated with no replacement.
+ Class `LLMS_Certificate_User` is deprecated with no direct replacement.
  + Method `LLMS_Certificate_User::init()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::set_shortcode_user()` is deprecated with no replacement.
+ Engagement debug logging is removed. Use `llms_log()` directly instead.
+ Filter `llms_db_query_get_default_args` is deprecated in favor of `llms_{$this->id}_query_get_default_args`.
+ Filter `llms_certificate_has_user_earned` is deprecated in favor of `llms_earned_certificate_dupcheck`.
+ Unused public class property `LLMS_Achievements::$content` is deprecated with no replacement.
+ Method `LLMS_Admin_Post_Types::meta_metabox_init()` is deprecated with no replacement.
+ The site options `lifterlms_certificate_bg_img_width`, `lifterlms_certificate_bg_img_height`, and `lifterlms_certificate_legacy_image_size` are now used only for certificates and certificate templates created using the classic editor.
  + The settings, found on the Engagements Settings screen, are hidden by default.
  + During the database upgrade from versions earlier than 6.x, an site option, `llms_has_legacy_certificates`  is added when at least one certificate is found. This option will display the settings so they can continue to be used for legacy certificates.
  + After migrating all certificates on a site, the settings will still display. In order to remove them from the screen a developer can either delete the option `llms_has_legacy_certificates` or return `false` from the filter `llms_has_legacy_certificates`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ The constant `LLMS_ENGAGEMENT_DEBUG` is deprecated with no replacement.
+ Engagement debugging via `LLMS_Engagements::log` is deprecated. Use `llms_log()` instead.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Filter `lifterlms_register_post_type_llms_my_certificate` is deprecated in favor of `lifterlms_register_post_type_my_certificate`.
+ Deprecated the misspelled protected method `LLMS_Database_Query::preprare_query()` and replaced with `LLMS_Database_Query::prepare_query()`.
  + Class method `LLMS_Events_Query::preprare_query` replaced with `LLMS_Events_Query::prepare_query()`.
  + Class method `LLMS_Query_Quiz_Attempt::preprare_query` replaced with `LLMS_Query_Quiz_Attempt::prepare_query()`.
  + Class method `LLMS_Query_User_Postmeta::preprare_query` replaced with `LLMS_Query_User_Postmeta::prepare_query()`.
  + Class method `LLMS_Student_Query::preprare_query` replaced with `LLMS_Student_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`. [#859](https://github.com/gocodebox/lifterlms/issues/859)

##### Breaking Changes

+ Removed FSE template: `templates/block-templates/single-certificate.html`.
+ Removed the deprecated `LLMS()` function in favor of the `llms()` function.
+ Removed the deprecated the `LLMS_SendWP::do_remote_install()` method in favor of the `LLMS_Abstract_Email_Provider::do_remote_install()` method.
+ Removed the deprecated `LLMS_Abstract_Email_Provider::output_css()` method.
+ Removed the deprecated `LLMS_Abstract_Generator_Posts::increment()` method.
+ Removed the deprecated `LLMS_Admin_Users_Table::load_dependencies()` method.
+ Removed the deprecated `LLMS_Admin_Import::localize_stat()` method.
+ Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::generator_course_status()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::output_step_html()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::scripts()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::watch_course_generation()` method.
+ Removed the deprecated `llms_format_decimal()` function.
+ Removed the deprecated `llms_set_person_auth_cookie()` function.
+ Removed the deprecated `LLMS_Course::sections` property.
+ Removed the deprecated `LLMS_Course::sku` property.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_pw_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::is_inline_script_enqueued()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::format_date()` method.
+ Removed the deprecated `LLMS_Generator::get_author_id_from_raw()` method.
+ Removed the deprecated `LLMS_Generator::get_default_post_status()` method.
+ Removed the deprecated `LLMS_Generator::get_generated_posts()` method.
+ Removed the deprecated `LLMS_Generator::increment()` method.
+ Removed the deprecated `llms__created` action hook from the `LLMS_Abstract_Database_Store::create()` method.
+ Removed the deprecated `llms__deleted` action hook from the `LLMS_Abstract_Database_Store::delete()` method.
+ Removed the deprecated `llms__updated` action hook from the `LLMS_Abstract_Database_Store::update()` method.
+ Removed the deprecated `llms_user_removed_from_membership_level` action hook from the `LLMS_Student::unenroll()` method.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `lifterlms_template_pricing_table()` function.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `templates/product/pricing-table.php` file.
+ Removed the deprecated `LLMS_Frontend_Password` class.
+ Removed the deprecated `LLMS_Install::db_updates()` method.
+ Removed the deprecated `LLMS_Install::update_notice()` method.
+ Removed the deprecated `LLMS_Notifications::dispatch_processors()` method.
+ Removed the deprecated `llms_processors_async_dispatching` filter hook from the `LLMS_Notifications::__construct()` method.
+ Removed the deprecated `LLMS_Notifications::$_instance` property.
+ Removed the deprecated `LLMS_Person_Handler::register()` method.
+ Removed the deprecated `LLMS_Person_Handler::sanitize_field()` method.
+ Removed the deprecated `LLMS_Person_Handler::update()` method.
+ Removed the deprecated `LLMS_Person_Handler::validate_fields()` method.
+ Removed the deprecated `LLMS_Person_Handler::voucher_toggle_script()` method.
+ Removed the deprecated `templates/admin/notices/db-update.php` file.
+ Removed the deprecated `templates/admin/notices/db-updating.php` file.
+ Removed the deprecated `llms_usernames_blacklist` filter hook in the `llms_get_usernames_blocklist()` function.
+ Removed the deprecated `includes/libraries/wp-background-processing/index.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-async-request.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-background-process.php` file.
+ Removed the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
+ Removed the deprecated `LLMS_Section::get_order()` method.
+ Removed the deprecated `LLMS_Section::get_parent_course()` method.
+ Removed the deprecated `LLMS_Section::set_parent_course()` method.
+ Removed the deprecated `LLMS_AJAX::check_voucher_duplicate()` method.
+ Removed the deprecated `LLMS_AJAX::get_ajax_data()` method.
+ Removed the deprecated `LLMS_AJAX::register_script()` method.
+ Removed the deprecated `LLMS_Interface_Post_Audio` interface.
+ Removed the deprecated `LLMS_Interface_Post_Sales_Page` interface.
+ Removed the deprecated `LLMS_Interface_Post_Video` interface.
+ Removed the deprecated `LLMS_Achievements::$_instance` property.
+ Removed the deprecated `LLMS_Certificates::$_instance` property.
+ Removed the deprecated `LLMS_Emails::$_instance` property.
+ Removed the deprecated `LLMS_Engagements::$_instance` property.
+ Removed the deprecated `LLMS_Events::$_instance` property.
+ Removed the deprecated `LLMS_Grades::$_instance` property.
+ Removed the deprecated `LLMS_Integrations::$_instance` property.
+ Removed the deprecated `LLMS_Payment_Gateways::$_instance` property.
+ Removed the deprecated `LLMS_Processors::$_instance` property.
+ Removed the deprecated `LLMS_Sessions::$_instance` property.

##### Developer Notes

+ Added `LLMS_Awards_Query`, used for querying data about awarded certificates and achievements.
  + The method signature `LLMS_Student::get_achievements()` and `LLMS_Student::get_certificates()` now use this class under tho hood.
  + The previous method signature, which passed data into a direct SQL query, is now deprecated.
+ Achievement and certificate data storage locations have been modified, primarily to reduce reliance on the `wp_postmeta` table which will result in a site-wide performance improvement, especially on large sites.
  + Meta properties `_llms_achievement_content` and `_llms_certificate_content` have been removed in favor of `WP_Post::$post_content`.
  + Meta properties `_llms_achievement_title` and `_llms_certificate_title` have been removed in favor of `WP_Post::$post_title`.
  + Meta properties `_llms_achievement_template` and `_llms_certificate_template` have been removed in favor of `WP_Post::$post_parent`.
  + Meta properties `_llms_achievement_image` and `_llms_certificate_image` have been moved the meta property `_thumbnail_id` in order to utilize the WordPress core's featured image functionality and internal APIs.
+ Reliance on `lifterlms_user_postmeta` for achievement and certificate data will be removed in a future release.
  + User postmeta properties `_achievement_earned` and `_certificate_earned` will continue to be recorded but are no longer being used internally.
  + The `updated_date` is now accessible via `WP_Post::$post_date`.
  + The `user_id` is now accessible via `WP_Post::$post_author`.
+ Added new Javascript UI components library, modeled after `@wordpress/components`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/components).
+ Added a new SVG icon library, modeled after `@wordpress/icons`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/icons).
+ The merge code button seen on certificate and email template editors is now an SVG image instead of a PNG.
+ Added utility function for escaping and quoting strings. [#1027](https://github.com/gocodebox/lifterlms/issues/1027)
+ Added new utility function for stripping prefixes from strings.

##### Performance Improvements

+ Increased the number of files that are autoloaded instead of manually loaded.

##### Updated Templates

+ [templates/achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/achievements/loop.php)
+ [templates/achievements/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/achievements/template.php)
+ [templates/admin/notices/db-update.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/notices/db-update.php)
+ [templates/admin/notices/db-updating.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/notices/db-updating.php)
+ [templates/admin/reporting/reporting.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/reporting/reporting.php)
+ [templates/admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/reporting/tabs/courses/overview.php)
+ [templates/admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/reporting/tabs/memberships/overview.php)
+ [templates/admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/reporting/tabs/quizzes/overview.php)
+ [templates/admin/reporting/tabs/students/information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/admin/reporting/tabs/students/information.php)
+ [templates/block-templates/archive-course.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/archive-course.html)
+ [templates/block-templates/archive-llms_membership.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/archive-llms_membership.html)
+ [templates/block-templates/single-no-access.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/single-no-access.html)
+ [templates/block-templates/taxonomy-course_cat.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/taxonomy-course_cat.html)
+ [templates/block-templates/taxonomy-course_difficulty.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/taxonomy-course_difficulty.html)
+ [templates/block-templates/taxonomy-course_tag.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/taxonomy-course_tag.html)
+ [templates/block-templates/taxonomy-course_track.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/taxonomy-course_track.html)
+ [templates/block-templates/taxonomy-membership_cat.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/taxonomy-membership_cat.html)
+ [templates/block-templates/taxonomy-membership_tag.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/block-templates/taxonomy-membership_tag.html)
+ [templates/certificates/actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/actions.php)
+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/content-legacy.php)
+ [templates/certificates/content.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/content.php)
+ [templates/certificates/dynamic-styles.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/dynamic-styles.php)
+ [templates/certificates/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/footer.php)
+ [templates/certificates/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/header.php)
+ [templates/certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/loop.php)
+ [templates/certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/preview.php)
+ [templates/certificates/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/certificates/template.php)
+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/checkout/form-confirm-payment.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/checkout/form-switch-source.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/content-certificate.php)
+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/course/lesson-preview.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/course/parent-course.php)
+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/emails/footer.php)
+ [templates/emails/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/emails/header.php)
+ [templates/loop-main.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/loop-main.php)
+ [templates/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/loop.php)
+ [templates/myaccount/my-grades-single-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/myaccount/my-grades-single-table.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/myaccount/view-order-actions.php)
+ [templates/myaccount/view-order-information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/myaccount/view-order-information.php)
+ [templates/myaccount/view-order-transactions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/myaccount/view-order-transactions.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/myaccount/view-order.php)
+ [templates/product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/product/pricing-table.php)
+ [templates/quiz/questions/content-picture_choice.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/quiz/questions/content-picture_choice.php)
+ [templates/quiz/results.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/quiz/results.php)
+ [templates/single-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.2/templates/single-certificate.php)


v5.10.0 - 2022-02-22
--------------------

##### Updates and Enhancements

+ Added an option to specify a custom checkout form title for free access plans. [#1774](https://github.com/gocodebox/lifterlms/issues/1774)
+ Updated LifterLMS Blocks to [v2.3.2](https://make.lifterlms.com/2022/02/22/lifterlms-blocks-version-2-3-2/). [#1774](https://github.com/gocodebox/lifterlms/issues/1774)

##### Bug Fixes

+ Fixed ability to sort course students table by completed date. [#1969](https://github.com/gocodebox/lifterlms/issues/1969)
+ Fixed reporting issue encountered when a course has no lessons. [#2012](https://github.com/gocodebox/lifterlms/issues/2012)
+ Fixed broken checkout on Twenty Twenty-Two Theme when using the password strength meter. [#1997](https://github.com/gocodebox/lifterlms/issues/1997)
+ Fixed block template slug generation from path in Windows environments. [#2001](https://github.com/gocodebox/lifterlms/issues/2001)
+ Fixed an issue encountered when using the search box on the voucher admin posts list screen. [#2005](https://github.com/gocodebox/lifterlms/issues/2005)

##### Updated Templates

+ [templates/admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/admin/reporting/tabs/courses/overview.php)
+ [templates/admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/admin/reporting/tabs/memberships/overview.php)
+ [templates/admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/admin/reporting/tabs/quizzes/overview.php)
+ [templates/block-templates/archive-course.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/archive-course.html)
+ [templates/block-templates/archive-llms_membership.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/archive-llms_membership.html)
+ [templates/block-templates/single-certificate.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/single-certificate.html)
+ [templates/block-templates/single-no-access.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/single-no-access.html)
+ [templates/block-templates/taxonomy-course_cat.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/taxonomy-course_cat.html)
+ [templates/block-templates/taxonomy-course_difficulty.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/taxonomy-course_difficulty.html)
+ [templates/block-templates/taxonomy-course_tag.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/taxonomy-course_tag.html)
+ [templates/block-templates/taxonomy-course_track.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/taxonomy-course_track.html)
+ [templates/block-templates/taxonomy-membership_cat.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/taxonomy-membership_cat.html)
+ [templates/block-templates/taxonomy-membership_tag.html](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/block-templates/taxonomy-membership_tag.html)
+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/checkout/form-confirm-payment.php)
+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/course/lesson-preview.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/course/parent-course.php)
+ [templates/loop-main.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/loop-main.php)
+ [templates/loop.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/loop.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/myaccount/view-order.php)
+ [templates/quiz/questions/content-picture_choice.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/quiz/questions/content-picture_choice.php)
+ [templates/quiz/results.php](https://github.com/gocodebox/lifterlms/blob/5.10.0/templates/quiz/results.php)


v6.0.0-beta.1 - 2022-02-16
--------------------------

##### New Features

+ Added a link to return to the student dashboard when viewing an awarded certificate. [#1959](https://github.com/gocodebox/lifterlms/issues/1959)
+ The block editor is now enabled by default for certificates when using WordPress versions 5.8 and later.
  + Existing certificates are marked as "legacy" and will continue to use the classic editor until migrated.
  + To migrate a certificate, click the "Migrate Certificate" button. This will force the certificate's content into blocks.
+ A number of new settings are available to certificates when using the block editor:
  + Set the certificate's display (and print) size using common paper sizes such as US Letter, US Legal, A3, A4, and more.
  + Set the certificate's display orientation: portrait of landscape.
  + Set the certificate's inner margins.
  + Set the certificate's background color.
+ A new block, the Certificate Title Block, has been made available to certificates.
  + The block works like a WordPress core Heading Block with added options for selecting from a few display fonts (provided by Google Web Fonts).
  + The block controls the title of awarded certificates.
+ + Added the ability to sync awarded certificates with the template used to generate them. [#1078](https://github.com/gocodebox/lifterlms/issues/1078)
+ The `post_name` of earned certificate posts will be generated with a randomized 3+ character string in favor of relying on sequential numbers.
+ Added the ability for administrators and LMS managers to edit earned certificates/achievements from the students reporting screen, as well as award new certificates/achievements to students.
+ + Added certificate global options for the default size of new certificates and certificate templates.
+ Certificate and email template merge code buttons now include `[llms-user]` information shortcodes.
+ Added certificate sequential ID functionality merge code.

##### Updates and Enhancements

+ Added pagination to achievement and certificate reporting pages.
+ Certificates no longer use the `header.php` and `footer.php` files from the site's theme, instead custom templates (`templates/certificates/header.php` and `templates/certificates/footer.php`) are used instead. These templates are minimal and exclude theme wrappers which reduces the visual conflicts encountered from theme wrappers, backgrounds, and more, especially when printing certificates. [#463](https://github.com/gocodebox/lifterlms/issues/463)
+ The achievements and certificates dashboard endpoints are now paginated. [#669](https://github.com/gocodebox/lifterlms/issues/669)
+ Added new default images for use with achievements and certificates.
  + The site-wide default images can be customized on the admin panel under Settings -> Engagements.
  + The old default images can be used by filtering `llms_use_legacy_engagement_images`. [#1081](https://github.com/gocodebox/lifterlms/issues/1081)
+ The URL of earned user certificates has been changed from "my_certificate" to "certificate". Requests to the old url are automatically redirected to the new url, including instances where the URL slug has been translated.
+ The URL of certificate template previews has been changed from "certificate" to "certificate-template".
+ The certificate merge code, `{first_name}`, now outputs an empty string in favor of falling back to the user's nickname when there is no first name for the user. [#1640](https://github.com/gocodebox/lifterlms/issues/1640)
+ Updates LifterLMS REST to [v1.0.0-beta.22](https://make.lifterlms.com/2021/12/15/lifterlms-rest-api-version-1-0-0-beta-22/).
+ The look and behavior of the certificate {{MINI_CERTIFICATE}} pop-over notification merge code now displays a placeholder preview of the certificate in favor of attempting to render a tiny version of the actual certificate. [#1950](https://github.com/gocodebox/lifterlms/issues/1950)

##### Bug Fixes

+ + Fixed how the protected `LLMS_Notifications_Query::$found_results` property is accessed in `LLMS_Abstract_Notification_Controller::has_subscriber_received()`. + Fixed how the protected `LLMS_Notifications_Query::$max_pages` property is accessed in `lifterlms_template_student_dashboard_my_notifications()`.
+ Delayed engagements are automatically unscheduled when the related post is deleted.
+ Prior to sending a delayed engagement the recipient's enrollment in the related post is verified resulting the engagement not being triggered if the recipient's enrollment has been terminated. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ A disabled student dashboard endpoint will no longer display the endpoint's summary on the main dashboard page. [#535](https://github.com/gocodebox/lifterlms/issues/535)
+ Post search filter boxes on various post tables will now longer display a link to the selected post.
+ Basic notification code is no longer loaded on the admin panel.

##### Deprecations

+ Public access to properties of the abstract `LLMS_Database_Query` has been deprecated.
  + Public access to class property `LLMS_Database_Query::$found_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_found_results()`.
  + Public access to class property `LLMS_Database_Query::$max_pages`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_max_pages()`.
  + Public access to class property `LLMS_Database_Query::$number_results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_number_results()`.
  + Public access to class property `LLMS_Database_Query::$results`. The property is no longer publicly writable but can be read via `LLMS_Database_Query::get_results()`.
  + Public access to class property `LLMS_Database_Query::$query_vars`. The variable as a whole cannot be publicly accessed, instead use `LLMS_Database_Query::get()` and `LLMS_Database_Query::set()` to read and write to the array.
  + The above changes were made to the abstract class `LLMS_Database_Query` but the following concrete classes that utilize the abstract are also affected by this change: `LLMS_Query_User_Postmeta`, `LLMS_Student_Query`, `LLMS_Query_Quiz_Attempt`, `LLMS_Events_Query`, and `LLMS_Notifications_Query`.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Achievement::format_string()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_title()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Achievement::create()` is deprecated with no replacement.
+ Method `LLMS_Achievements::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement()`.
+ Class `LLMS_Certificate` is deprecated with no direct replacement.
  + Method `LLMS_Certificate::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Certificate::format_string()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
+ Method `LLMS_Certificates::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate()`.
+ Method `LLMS_Engagements::init()` is deprecated with no replacement.
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Method `LLMS_Database_Query::set_found_results()` is deprecated.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement_User::has_user_earned()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::init()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::get_content_html()` is deprecated with no replacement.
+ Class `LLMS_Certificate_User` is deprecated with no direct replacement.
  + Method `LLMS_Certificate_User::init()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::set_shortcode_user()` is deprecated with no replacement.
+ Engagement debug logging is removed. Use `llms_log()` directly instead.
+ Filter `llms_db_query_get_default_args` is deprecated in favor of `llms_{$this->id}_query_get_default_args`.
+ Filter `llms_certificate_has_user_earned` is deprecated in favor of `llms_earned_certificate_dupcheck`.
+ Unused public class property `LLMS_Achievements::$content` is deprecated with no replacement.
+ Method `LLMS_Admin_Post_Types::meta_metabox_init()` is deprecated with no replacement.
+ The site options `lifterlms_certificate_bg_img_width`, `lifterlms_certificate_bg_img_height`, and `lifterlms_certificate_legacy_image_size` are now used only for certificates and certificate templates created using the classic editor.
  + The settings, found on the Engagements Settings screen, are hidden by default.
  + During the database upgrade from versions earlier than 6.x, an site option, `llms_has_legacy_certificates`  is added when at least one certificate is found. This option will display the settings so they can continue to be used for legacy certificates.
  + After migrating all certificates on a site, the settings will still display. In order to remove them from the screen a developer can either delete the option `llms_has_legacy_certificates` or return `false` from the filter `llms_has_legacy_certificates`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`. [#290](https://github.com/gocodebox/lifterlms/issues/290)
+ The constant `LLMS_ENGAGEMENT_DEBUG` is deprecated with no replacement.
+ Engagement debugging via `LLMS_Engagements::log` is deprecated. Use `llms_log()` instead.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Filter `lifterlms_register_post_type_llms_my_certificate` is deprecated in favor of `lifterlms_register_post_type_my_certificate`.
+ Deprecated the misspelled protected method `LLMS_Database_Query::preprare_query()` and replaced with `LLMS_Database_Query::prepare_query()`.
  + Class method `LLMS_Events_Query::preprare_query` replaced with `LLMS_Events_Query::prepare_query()`.
  + Class method `LLMS_Query_Quiz_Attempt::preprare_query` replaced with `LLMS_Query_Quiz_Attempt::prepare_query()`.
  + Class method `LLMS_Query_User_Postmeta::preprare_query` replaced with `LLMS_Query_User_Postmeta::prepare_query()`.
  + Class method `LLMS_Student_Query::preprare_query` replaced with `LLMS_Student_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`. [#859](https://github.com/gocodebox/lifterlms/issues/859)

##### Breaking Changes

+ Removed FSE template: `templates/block-templates/single-certificate.html`.
+ Removed the deprecated `LLMS()` function in favor of the `llms()` function.
+ Removed the deprecated the `LLMS_SendWP::do_remote_install()` method in favor of the `LLMS_Abstract_Email_Provider::do_remote_install()` method.
+ Removed the deprecated `LLMS_Abstract_Email_Provider::output_css()` method.
+ Removed the deprecated `LLMS_Abstract_Generator_Posts::increment()` method.
+ Removed the deprecated `LLMS_Admin_Users_Table::load_dependencies()` method.
+ Removed the deprecated `LLMS_Admin_Import::localize_stat()` method.
+ Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::generator_course_status()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::output_step_html()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::scripts()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::watch_course_generation()` method.
+ Removed the deprecated `llms_format_decimal()` function.
+ Removed the deprecated `llms_set_person_auth_cookie()` function.
+ Removed the deprecated `LLMS_Course::sections` property.
+ Removed the deprecated `LLMS_Course::sku` property.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_pw_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::is_inline_script_enqueued()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::format_date()` method.
+ Removed the deprecated `LLMS_Generator::get_author_id_from_raw()` method.
+ Removed the deprecated `LLMS_Generator::get_default_post_status()` method.
+ Removed the deprecated `LLMS_Generator::get_generated_posts()` method.
+ Removed the deprecated `LLMS_Generator::increment()` method.
+ Removed the deprecated `llms__created` action hook from the `LLMS_Abstract_Database_Store::create()` method.
+ Removed the deprecated `llms__deleted` action hook from the `LLMS_Abstract_Database_Store::delete()` method.
+ Removed the deprecated `llms__updated` action hook from the `LLMS_Abstract_Database_Store::update()` method.
+ Removed the deprecated `llms_user_removed_from_membership_level` action hook from the `LLMS_Student::unenroll()` method.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `lifterlms_template_pricing_table()` function.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `templates/product/pricing-table.php` file.
+ Removed the deprecated `LLMS_Frontend_Password` class.
+ Removed the deprecated `LLMS_Install::db_updates()` method.
+ Removed the deprecated `LLMS_Install::update_notice()` method.
+ Removed the deprecated `LLMS_Notifications::dispatch_processors()` method.
+ Removed the deprecated `llms_processors_async_dispatching` filter hook from the `LLMS_Notifications::__construct()` method.
+ Removed the deprecated `LLMS_Notifications::$_instance` property.
+ Removed the deprecated `LLMS_Person_Handler::register()` method.
+ Removed the deprecated `LLMS_Person_Handler::sanitize_field()` method.
+ Removed the deprecated `LLMS_Person_Handler::update()` method.
+ Removed the deprecated `LLMS_Person_Handler::validate_fields()` method.
+ Removed the deprecated `LLMS_Person_Handler::voucher_toggle_script()` method.
+ Removed the deprecated `templates/admin/notices/db-update.php` file.
+ Removed the deprecated `templates/admin/notices/db-updating.php` file.
+ Removed the deprecated `llms_usernames_blacklist` filter hook in the `llms_get_usernames_blocklist()` function.
+ Removed the deprecated `includes/libraries/wp-background-processing/index.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-async-request.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-background-process.php` file.
+ Removed the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
+ Removed the deprecated `LLMS_Section::get_order()` method.
+ Removed the deprecated `LLMS_Section::get_parent_course()` method.
+ Removed the deprecated `LLMS_Section::set_parent_course()` method.
+ Removed the deprecated `LLMS_AJAX::check_voucher_duplicate()` method.
+ Removed the deprecated `LLMS_AJAX::get_ajax_data()` method.
+ Removed the deprecated `LLMS_AJAX::register_script()` method.
+ Removed the deprecated `LLMS_Interface_Post_Audio` interface.
+ Removed the deprecated `LLMS_Interface_Post_Sales_Page` interface.
+ Removed the deprecated `LLMS_Interface_Post_Video` interface.
+ Removed the deprecated `LLMS_Achievements::$_instance` property.
+ Removed the deprecated `LLMS_Certificates::$_instance` property.
+ Removed the deprecated `LLMS_Emails::$_instance` property.
+ Removed the deprecated `LLMS_Engagements::$_instance` property.
+ Removed the deprecated `LLMS_Events::$_instance` property.
+ Removed the deprecated `LLMS_Grades::$_instance` property.
+ Removed the deprecated `LLMS_Integrations::$_instance` property.
+ Removed the deprecated `LLMS_Payment_Gateways::$_instance` property.
+ Removed the deprecated `LLMS_Processors::$_instance` property.
+ Removed the deprecated `LLMS_Sessions::$_instance` property.

##### Developer Notes

+ Added `LLMS_Awards_Query`, used for querying data about awarded certificates and achievements.
  + The method signature `LLMS_Student::get_achievements()` and `LLMS_Student::get_certificates()` now use this class under tho hood.
  + The previous method signature, which passed data into a direct SQL query, is now deprecated.
+ Achievement and certificate data storage locations have been modified, primarily to reduce reliance on the `wp_postmeta` table which will result in a site-wide performance improvement, especially on large sites.
  + Meta properties `_llms_achievement_content` and `_llms_certificate_content` have been removed in favor of `WP_Post::$post_content`.
  + Meta properties `_llms_achievement_title` and `_llms_certificate_title` have been removed in favor of `WP_Post::$post_title`.
  + Meta properties `_llms_achievement_template` and `_llms_certificate_template` have been removed in favor of `WP_Post::$post_parent`.
  + Meta properties `_llms_achievement_image` and `_llms_certificate_image` have been moved the meta property `_thumbnail_id` in order to utilize the WordPress core's featured image functionality and internal APIs.
+ Reliance on `lifterlms_user_postmeta` for achievement and certificate data will be removed in a future release.
  + User postmeta properties `_achievement_earned` and `_certificate_earned` will continue to be recorded but are no longer being used internally.
  + The `updated_date` is now accessible via `WP_Post::$post_date`.
  + The `user_id` is now accessible via `WP_Post::$post_author`.
+ Added new Javascript UI components library, modeled after `@wordpress/components`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/components).
+ Added a new SVG icon library, modeled after `@wordpress/icons`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/icons).
+ The merge code button seen on certificate and email template editors is now an SVG image instead of a PNG.
+ Added utility function for escaping and quoting strings. [#1027](https://github.com/gocodebox/lifterlms/issues/1027)
+ Added new utility function for stripping prefixes from strings.

##### Performance Improvements

+ Increased the number of files that are autoloaded instead of manually loaded.

##### Updated Templates

+ [templates/achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/achievements/loop.php)
+ [templates/achievements/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/achievements/template.php)
+ [templates/admin/notices/db-update.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/notices/db-update.php)
+ [templates/admin/notices/db-updating.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/notices/db-updating.php)
+ [templates/admin/reporting/reporting.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/reporting/reporting.php)
+ [templates/admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/reporting/tabs/courses/overview.php)
+ [templates/admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/reporting/tabs/memberships/overview.php)
+ [templates/admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/reporting/tabs/quizzes/overview.php)
+ [templates/admin/reporting/tabs/students/information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/admin/reporting/tabs/students/information.php)
+ [templates/block-templates/archive-course.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/archive-course.html)
+ [templates/block-templates/archive-llms_membership.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/archive-llms_membership.html)
+ [templates/block-templates/single-no-access.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/single-no-access.html)
+ [templates/block-templates/taxonomy-course_cat.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/taxonomy-course_cat.html)
+ [templates/block-templates/taxonomy-course_difficulty.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/taxonomy-course_difficulty.html)
+ [templates/block-templates/taxonomy-course_tag.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/taxonomy-course_tag.html)
+ [templates/block-templates/taxonomy-course_track.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/taxonomy-course_track.html)
+ [templates/block-templates/taxonomy-membership_cat.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/taxonomy-membership_cat.html)
+ [templates/block-templates/taxonomy-membership_tag.html](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/block-templates/taxonomy-membership_tag.html)
+ [templates/certificates/actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/actions.php)
+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/content-legacy.php)
+ [templates/certificates/content.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/content.php)
+ [templates/certificates/dynamic-styles.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/dynamic-styles.php)
+ [templates/certificates/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/footer.php)
+ [templates/certificates/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/header.php)
+ [templates/certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/loop.php)
+ [templates/certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/preview.php)
+ [templates/certificates/template.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/certificates/template.php)
+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/checkout/form-confirm-payment.php)
+ [templates/checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/checkout/form-switch-source.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/content-certificate.php)
+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/course/lesson-preview.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/course/parent-course.php)
+ [templates/emails/footer.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/emails/footer.php)
+ [templates/emails/header.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/emails/header.php)
+ [templates/loop-main.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/loop-main.php)
+ [templates/loop.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/loop.php)
+ [templates/myaccount/my-grades-single-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/myaccount/my-grades-single-table.php)
+ [templates/myaccount/view-order-actions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/myaccount/view-order-actions.php)
+ [templates/myaccount/view-order-information.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/myaccount/view-order-information.php)
+ [templates/myaccount/view-order-transactions.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/myaccount/view-order-transactions.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/myaccount/view-order.php)
+ [templates/product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/product/pricing-table.php)
+ [templates/quiz/questions/content-picture_choice.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/quiz/questions/content-picture_choice.php)
+ [templates/quiz/results.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/quiz/results.php)
+ [templates/single-certificate.php](https://github.com/gocodebox/lifterlms/blob/6.0.0-beta.1/templates/single-certificate.php)


v5.9.0 - 2022-02-15
-------------------

##### Updates and Enhancements

+ Picture choice questions are now organized using flexbox in favor of a float-powered column layout.
+ Resolved PHP 8.1 deprecation warnings. [#1859](https://github.com/gocodebox/lifterlms/issues/1859)

##### Bug Fixes

+ Updated `llms_get_endpoint_url()` to better adhere to a site's permalink structure with regards to the presence of a trailing slash in the generated url. [#1983](https://github.com/gocodebox/lifterlms/issues/1983)
+ Only allow users with `edit_post` capabilities to bypass content restrictions.
+ Fixed stretched images in quiz description/questions when using the Twenty Twenty-Two theme. [#1976](https://github.com/gocodebox/lifterlms/issues/1976)

##### Deprecations

+ Method `LLMS_AJAX::check_voucher_duplicate()` is deprecated in favor of `LLMS_AJAX_HANDLER::check_voucher_duplicate()`.

##### Updated Templates

+ [templates/admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/admin/reporting/tabs/courses/overview.php)
+ [templates/admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/admin/reporting/tabs/memberships/overview.php)
+ [templates/admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/admin/reporting/tabs/quizzes/overview.php)
+ [templates/block-templates/archive-course.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/archive-course.html)
+ [templates/block-templates/archive-llms_membership.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/archive-llms_membership.html)
+ [templates/block-templates/single-certificate.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/single-certificate.html)
+ [templates/block-templates/single-no-access.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/single-no-access.html)
+ [templates/block-templates/taxonomy-course_cat.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/taxonomy-course_cat.html)
+ [templates/block-templates/taxonomy-course_difficulty.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/taxonomy-course_difficulty.html)
+ [templates/block-templates/taxonomy-course_tag.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/taxonomy-course_tag.html)
+ [templates/block-templates/taxonomy-course_track.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/taxonomy-course_track.html)
+ [templates/block-templates/taxonomy-membership_cat.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/taxonomy-membership_cat.html)
+ [templates/block-templates/taxonomy-membership_tag.html](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/block-templates/taxonomy-membership_tag.html)
+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/checkout/form-confirm-payment.php)
+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/course/lesson-preview.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/course/parent-course.php)
+ [templates/loop-main.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/loop-main.php)
+ [templates/loop.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/loop.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/myaccount/view-order.php)
+ [templates/quiz/questions/content-picture_choice.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/quiz/questions/content-picture_choice.php)
+ [templates/quiz/results.php](https://github.com/gocodebox/lifterlms/blob/5.9.0/templates/quiz/results.php)


v6.0.0-alpha.4 - 2022-02-11
---------------------------

##### Updates and Enhancements

+ Removed usage of PHP features deprecated in PHP 8.1.
+ Added a link to return to the student dashboard when viewing an awarded certificate.
+ Allow block templates to be overridden from themes or plugins.
+ Added a "Reset Certificate" button to restore certificates to the default template.
+ Added links from achievement and certificate templates to view all awards generated from the template.
+ Added the ability to sync achievements (sync all awards to the parent template and sync one award to it's parent).
+ Improved class autoloading.

##### Bug Fixes

+ Fixed certificate print compatibility issues with the OceanWP and Genesis themes.
+ Fixed custom font usage in the Certificate Title block to utilize WP Core functionality introduced in version 5.9.
+ Fixed access to protected properties in the `LLMS_Notifications_Query` class.

##### Breaking Changes

+ Removed the Single Certificate block template.


v5.8.0 - 2022-01-26
-------------------

##### New Features

+ Add theme support for the Twenty Twenty-Two theme. [#1824](https://github.com/gocodebox/lifterlms/issues/1824)
+ Added WordPress Full Site Editing compatibility for various LifterLMS-powered templates.

##### Updates and Enhancements

+ The minimum required WordPress core version is now version 5.5.
+ Tested against WordPress version 5.9.
+ Updated LifterLMS Blocks: [v2.3.0](https://make.lifterlms.com/2022/01/25/lifterlms-blocks-version-2-3-0/), [v2.3.1](https://make.lifterlms.com/2022/01/26/lifterlms-blocks-version-2-3-1/).
+ Remove the "description" registered with LifterLMS custom post types. [#710](https://github.com/gocodebox/lifterlms/issues/710)

##### Updated Templates

+ [templates/block-templates/archive-course.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/archive-course.html)
+ [templates/block-templates/archive-llms_membership.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/archive-llms_membership.html)
+ [templates/block-templates/single-certificate.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/single-certificate.html)
+ [templates/block-templates/single-no-access.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/single-no-access.html)
+ [templates/block-templates/taxonomy-course_cat.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/taxonomy-course_cat.html)
+ [templates/block-templates/taxonomy-course_difficulty.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/taxonomy-course_difficulty.html)
+ [templates/block-templates/taxonomy-course_tag.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/taxonomy-course_tag.html)
+ [templates/block-templates/taxonomy-course_track.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/taxonomy-course_track.html)
+ [templates/block-templates/taxonomy-membership_cat.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/taxonomy-membership_cat.html)
+ [templates/block-templates/taxonomy-membership_tag.html](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/block-templates/taxonomy-membership_tag.html)
+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/course/lesson-preview.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/course/parent-course.php)
+ [templates/loop-main.php](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/loop-main.php)
+ [templates/loop.php](https://github.com/gocodebox/lifterlms/blob/5.8.0/templates/loop.php)


v6.0.0-alpha.3 - 2022-01-14
---------------------------

##### Updates and Enhancements

+ Automatically dequeue print-only stylesheets to reduce theme and plugin conflicts when printing certificates.
+ Only enable the Certificate Title block font-family selector for WordPress 5.9 and later.
+ Only enable the Block Editor for certificates on WordPress 5.8 and later.
+ Replaced welcome message placeholder text with a real welcome message.

##### Bug Fixes

+ Explicitly define a default font-family ("default") for the Certificate Title block.
+ Fixed visual issues encountered on certificates when resizing the browser window.
+ Fixed issue with the certificate block template on WordPress 5.8 (divider blocks aren't centered by default).

##### Breaking Changes

+ Removed the deprecated `LLMS()` function in favor of the `llms()` function.
+ Removed the deprecated `LLMS_SendWP::do_remote_install()` method in favor of the `LLMS_Abstract_Email_Provider::do_remote_install()` method.
+ Removed the deprecated `LLMS_Abstract_Email_Provider::output_css()` method.
+ Removed the deprecated `LLMS_Abstract_Generator_Posts::increment()` method.
+ Removed the deprecated `LLMS_Admin_Users_Table::load_dependencies()` method.
+ Removed the deprecated `LLMS_Admin_Import::localize_stat()` method.
+ Removed the deprecated `LLMS_Admin_Notices_Core::check_staging()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::generator_course_status()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::output_step_html()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::scripts()` method.
+ Removed the deprecated `LLMS_Admin_Setup_Wizard::watch_course_generation()` method.
+ Removed the deprecated `llms_format_decimal()` function.
+ Removed the deprecated `llms_set_person_auth_cookie()` function.
+ Removed the deprecated `LLMS_Course::sections` property.
+ Removed the deprecated `LLMS_Course::sku` property.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_pw_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::enqueue_inline_script()` method.
+ Removed the deprecated `LLMS_Frontend_Assets::is_inline_script_enqueued()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::add_custom_values()` method.
+ Removed the deprecated `LLMS_Generator::format_date()` method.
+ Removed the deprecated `LLMS_Generator::get_author_id_from_raw()` method.
+ Removed the deprecated `LLMS_Generator::get_default_post_status()` method.
+ Removed the deprecated `LLMS_Generator::get_generated_posts()` method.
+ Removed the deprecated `LLMS_Generator::increment()` method.
+ Removed the deprecated `llms__created` action hook from the `LLMS_Abstract_Database_Store::create()` method.
+ Removed the deprecated `llms__deleted` action hook from the `LLMS_Abstract_Database_Store::delete()` method.
+ Removed the deprecated `llms__updated` action hook from the `LLMS_Abstract_Database_Store::update()` method.
+ Removed the deprecated `llms_user_removed_from_membership_level` action hook from the `LLMS_Student::unenroll()` method.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `lifterlms_template_pricing_table()` function.
+ Removed the deprecated and misspelled `$purchaseable` global variable in the `templates/product/pricing-table.php` file.
+ Removed the deprecated `LLMS_Frontend_Password` class.
+ Removed the deprecated `LLMS_Install::db_updates()` method.
+ Removed the deprecated `LLMS_Install::update_notice()` method.
+ Removed the deprecated `LLMS_Notifications::dispatch_processors()` method.
+ Removed the deprecated `llms_processors_async_dispatching` filter hook from the `LLMS_Notifications::__construct()` method.
+ Removed the deprecated `LLMS_Notifications::$_instance` property.
+ Removed the deprecated `LLMS_Person_Handler::register()` method.
+ Removed the deprecated `LLMS_Person_Handler::sanitize_field()` method.
+ Removed the deprecated `LLMS_Person_Handler::update()` method.
+ Removed the deprecated `LLMS_Person_Handler::validate_fields()` method.
+ Removed the deprecated `LLMS_Person_Handler::voucher_toggle_script()` method.
+ Removed the deprecated `templates/admin/notices/db-update.php` file.
+ Removed the deprecated `templates/admin/notices/db-updating.php` file.
+ Removed the deprecated `llms_usernames_blacklist` filter hook in the `llms_get_usernames_blocklist()` function.
+ Removed the deprecated `includes/libraries/wp-background-processing/index.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-async-request.php` file.
+ Removed the deprecated `includes/libraries/wp-background-processing/wp-background-process.php` file.
+ Removed the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
+ Removed the deprecated `LLMS_Section::get_order()` method.
+ Removed the deprecated `LLMS_Section::get_parent_course()` method.
+ Removed the deprecated `LLMS_Section::set_parent_course()` method.
+ Removed the deprecated `LLMS_AJAX::get_ajax_data()` method.
+ Removed the deprecated `LLMS_AJAX::register_script()` method.
+ Removed the deprecated `LLMS_Interface_Post_Audio` interface.
+ Removed the deprecated `LLMS_Interface_Post_Sales_Page` interface.
+ Removed the deprecated `LLMS_Interface_Post_Video` interface.
+ Removed the deprecated `LLMS_Achievements::$_instance` property.
+ Removed the deprecated `LLMS_Certificates::$_instance` property.
+ Removed the deprecated `LLMS_Emails::$_instance` property.
+ Removed the deprecated `LLMS_Engagements::$_instance` property.
+ Removed the deprecated `LLMS_Events::$_instance` property.
+ Removed the deprecated `LLMS_Grades::$_instance` property.
+ Removed the deprecated `LLMS_Integrations::$_instance` property.
+ Removed the deprecated `LLMS_Payment_Gateways::$_instance` property.
+ Removed the deprecated `LLMS_Processors::$_instance` property.
+ Removed the deprecated `LLMS_Sessions::$_instance` property.


v5.7.0 - 2022-01-11
-------------------

##### Updates and Enhancements

+ Informed developers about the deprecated `LLMS_Section::get_next_available_lesson_order()` method.
+ Informed developers about the deprecated `LLMS_Section::get_order()` method.
+ Informed developers about the deprecated `LLMS_Section::get_parent_course()` method.
+ Informed developers about the deprecated `LLMS_Section::set_parent_course()` method.

##### Deprecations

+ Deprecated `LLMS_Frontend_Assets::enqueue_inline_pw_script()` with no replacement.
+ Deprecated the `LLMS_Lesson::get_order()` method in favor of the `LLMS_Lesson::get( 'order' )` method.
+ Deprecated the `LLMS_Lesson::get_parent_course()` method in favor of the `LLMS_Lesson::get( 'parent_course' )` method.
+ Deprecated the `LLMS_Lesson::set_parent_course()` method in favor of the `LLMS_Lesson::set( 'parent_course', $course_id )` method.
+ Deprecated the `LLMS_AJAX_Handler::add_lesson_to_course()` method with no replacement.
+ Deprecated the `LLMS_AJAX_Handler::create_lesson()` method with no replacement.
+ Deprecated the `LLMS_AJAX_Handler::create_section()` method with no replacement.
+ Deprecated the `LLMS_Lesson_Handler::assign_to_course()` method with no replacement.
+ Deprecated the `LLMS_Post_Handler::create_section()` method with no replacement.

##### Updated Templates

+ [templates/course/lesson-navigation.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/course/lesson-navigation.php)
+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/course/lesson-preview.php)
+ [templates/course/parent-course.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/course/parent-course.php)


v6.0.0-alpha.2 - 2022-01-04
---------------------------

##### New Features

+ Added certificate global options for the default size of new certificates and certificate templates.

##### Updates and enhancements

+ The site options `lifterlms_certificate_bg_img_width`,
`lifterlms_certificate_bg_img_height`, and
`lifterlms_certificate_legacy_image_size` are now used only for certificates
and certificate templates created using the classic editor.
  + The settings, found on the Engagements Settings screen, are hidden by default.
  + During the database upgrade from versions earlier than 6.x, an site option, `llms_has_legacy_certificates`  is added when at least one certificate is found. This option will display the settings so they can continue to be used for legacy certificates.
  + After migrating all certificates on a site, the settings will still display. In order to remove them from the screen a developer can either delete the option `llms_has_legacy_certificates` or return `false` from the filter `llms_has_legacy_certificates`.
+ Restore certificate save hooks after executing callback updates to facilitate scenarios where more than one certificate is updated in a single request.

##### Bug Fixes

+ Only register the Certificate Title block for use on certificate post types.

##### Updated Templates

+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/content-legacy.php)


v6.0.0-alpha.1 - 2021-12-28
---------------------------

**This version is an unstable pre-release! We strongly advise against installing this in a production environment.**

##### New Features

+ The block editor is now enabled by default for certificates.
  + Existing certificates are marked as "legacy" and will continue to use the classic editor until migrated.
  + To migrate a certificate, click the "Migrate Certificate" button. This will force the certificate's content into blocks.
+ A number of new settings are available to certificates when using the block editor:
  + Set the certificate's display (and print) size using common paper sizes such as US Letter, US Legal, A3, A4, and more.
  + Set the certificate's display orientation: portrait of landscape.
  + Set the certificate's inner margins.
  + Set the certificate's background color.
+ A new block, the Certificate Title Block, has been made available to certificates.
  + The block works like a WordPress core Heading Block with added options for selecting from a few display fonts (provided by Google Web Fonts).
  + The block controls the title of awarded certificates.
+ + Added the ability to sync awarded certificates with the template used to generate them. [#1078](https://github.com/gocodebox/lifterlms#1078)
+ The `post_name` of earned certificate posts will be generated with a randomized 3+ character string in favor of relying on sequential numbers.
+ Added the ability for administrators and LMS managers to edit earned certificates/achievements from the students reporting screen, as well as award new certificates/achievements to students.
+ Certificate and email template merge code buttons now include [llms-user] information shortcodes.
+ Added certificate sequential ID functionality merge code. [Read more](@TODO).

##### Updates and Enhancements

+ Added pagination to achievement and certificate reporting pages.
+ Certificates no longer use the `header.php` and `footer.php` files from the site's theme, instead custom templates (`templates/certificates/header.php` and `templates/certificates/footer.php`) are used instead. These templates are minimal and exclude theme wrappers which reduces the visual conflicts encountered from theme wrappers, backgrounds, and more, especially when printing certificates. [#463](https://github.com/gocodebox/lifterlms#463)
+ The achievements and certificates dashboard endpoints are now paginated. [#669](https://github.com/gocodebox/lifterlms#669)
+ Added new default images for use with achievements and certificates.
  + The site-wide default images can be customized on the admin panel under Settings -> Engagements.
  + The old default images can be used by filtering `llms_use_legacy_engagement_images`. [#1081](https://github.com/gocodebox/lifterlms#1081)
+ The URL of earned user certificates has been changed from "my_certificate" to "certificate". Requests to the old url are automatically redirected to the new url, including instances where the URL slug has been translated.
+ The URL of certificate template previews has been changed from "certificate" to "certificate-template".
+ The certificate merge code, `{first_name}`, now outputs an empty string in favor of falling back to the user's nickname when there is no first name for the user. [#1640](https://github.com/gocodebox/lifterlms#1640)
+ Updates LifterLMS REST to [v1.0.0-beta.22](https://make.lifterlms.com/2021/12/15/lifterlms-rest-api-version-1-0-0-beta-22/).

##### Bug Fixes

+ Delayed engagements are automatically unscheduled when the related post is deleted.
+ Prior to sending a delayed engagement the recipient's enrollment in the related post is verified resulting the engagement not being triggered if the recipient's enrollment has been terminated. [#290](https://github.com/gocodebox/lifterlms#290)
+ A disabled student dashboard endpoint will no longer display the endpoint's summary on the main dashboard page. [#535](https://github.com/gocodebox/lifterlms#535)
+ Post search filter boxes on various post tables will now longer display a link to the selected post.
+ Basic notification code is no longer loaded on the admin panel.

##### Deprecations

+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Achievement::format_string()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_title()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content()` is deprecated with no replacement.
  + Method `LLMS_Achievement::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Achievement::create()` is deprecated with no replacement.
+ Method `LLMS_Achievments::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement()`.
+ Class `LLMS_Certificate` is deprecated with no direct replacement.
  + Method `LLMS_Certificate::is_enabled()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_blogname()` is deprecated with no replacement.
  + Method `LLMS_Certificate::format_string()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate::get_title()` is deprecated with no replacement.
+ Method `LLMS_Certificates::trigger_engagement()` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate()`.
+ Method `LLMS_Engagements::init()` is deprecated with no replacement.
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Method `LLMS_Database_Query::set_found_results()` is deprecated.
+ Class `LLMS_Achievement_User` is deprecated with no direct replacement.
  + Method `LLMS_Achievement_User::has_user_earned()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::init()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Achievement_User::get_content_html()` is deprecated with no replacement.
+ Class `LLMS_Certificate_User` is deprecated with no direct replacement.
  + Method `LLMS_Certificate_User::init()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::trigger()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::get_content_html()` is deprecated with no replacement.
  + Method `LLMS_Certificate_User::set_shortcode_user()` is deprecated with no replacement.
+ Engagement debug logging is removed. Use `llms_log()` directly instead.
+ Filter `llms_db_query_get_default_args` is deprecated in favor of `llms_{$this->id}_query_get_default_args`.
+ Filter `llms_certificate_has_user_earned` is deprecated in favor of `llms_earned_certificate_dupcheck`.
+ Unused public class property `LLMS_Achievements::$content` is deprecated with no replacement.
+ Method `LLMS_Engagements::handle_certificate` is deprecated in favor of `LLMS_Engagement_Handler::handle_certificate`. [#290](https://github.com/gocodebox/lifterlms#290)
+ Method `LLMS_Engagements::handle_achievement` is deprecated in favor of `LLMS_Engagement_Handler::handle_achievement`. [#290](https://github.com/gocodebox/lifterlms#290)
+ The constant `LLMS_ENGAGEMENT_DEBUG` is deprecated with no replacement.
+ Engagement debugging via `LLMS_Engagements::log` is deprecated. Use `llms_log()` instead.
+ Method `LLMS_Engagements::handle_email` is deprecated in favor of `LLMS_Engagement_Handler::handle_email`.
+ Filter `lifterlms_register_post_type_llms_my_certificate` is deprecated in favor of `lifterlms_register_post_type_my_certificate`.
+ Deprecated the misspelled protected method `LLMS_Database_Query::preprare_query()` and replaced with `LLMS_Database_Query::prepare_query()`.
  + Class method `LLMS_Events_Query::preprare_query` replaced with `LLMS_Events_Query::prepare_query()`.
  + Class method `LLMS_Query_Quiz_Attempt::preprare_query` replaced with `LLMS_Query_Quiz_Attempt::prepare_query()`.
  + Class method `LLMS_Query_User_Postmeta::preprare_query` replaced with `LLMS_Query_User_Postmeta::prepare_query()`.
  + Class method `LLMS_Student_Query::preprare_query` replaced with `LLMS_Student_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`.
  + Class method `LLMS_Notifications_Query::preprare_query` replaced with `LLMS_Notifications_Query::prepare_query()`. [#859](https://github.com/gocodebox/lifterlms#859)

##### Developer Notes

+ Added `LLMS_Awards_Query`, used for querying data about awarded certificates and achievements.
  + The method signature `LLMS_Student::get_achievements()` and `LLMS_Student::get_certificates()` now use this class under tho hood.
  + The previous method signature, which passed data into a direct SQL query, is now deprecated.
+ Achievement and certificate data storage locations have been modified, primarily to reduce reliance on the `wp_postmeta` table which will result in a site-wide performance improvement, especially on large sites.
  + Meta properties `_llms_achievement_content` and `_llms_certificate_content` have been removed in favor of `WP_Post::$post_content`.
  + Meta properties `_llms_achievement_title` and `_llms_certificate_title` have been removed in favor of `WP_Post::$post_title`.
  + Meta properties `_llms_achievement_template` and `_llms_certificate_template` have been removed in favor of `WP_Post::$post_parent`.
  + Meta properties `_llms_achievement_image` and `_llms_certificate_image` have been moved the meta property `_thumbnail_id` in order to utilize the WordPress core's featured image functionality and internal APIs.
+ Reliance on `lifterlms_user_postmeta` for achievement and certificate data will be removed in a future release.
  + User postmeta properties `_achievement_earned` and `_certificate_earned` will continue to be recorded but are no longer being used internally.
  + The `updated_date` is now accessible via `WP_Post::$post_date`.
  + The `user_id` is now accessible via `WP_Post::$post_author`.
+ Added new Javascript UI components library, modeled after `@wordpress/components`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/components).
+ Added a new SVG icon library, modeled after `@wordpress/icons`. [Read more](https://github.com/gocodebox/lifterlms/tree/dev-600/packages/icons).
+ The merge code button seen on certificate and email template editors is now an SVG image instead of a PNG.
+ Added utility function for escaping and quoting strings. [#1027](https://github.com/gocodebox/lifterlms#1027)
+ Added new utility function for stripping prefixes from strings.

##### Updated Templates

+ [templates/achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/achievements/loop.php)
+ [templates/achievements/template.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/achievements/template.php)
+ [templates/admin/reporting/tabs/students/information.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/admin/reporting/tabs/students/information.php)
+ [templates/certificates/actions.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/actions.php)
+ [templates/certificates/content-legacy.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/content-legacy.php)
+ [templates/certificates/content.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/content.php)
+ [templates/certificates/dynamic-styles.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/dynamic-styles.php)
+ [templates/certificates/footer.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/footer.php)
+ [templates/certificates/header.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/header.php)
+ [templates/certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/loop.php)
+ [templates/certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/preview.php)
+ [templates/certificates/template.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/certificates/template.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/content-certificate.php)
+ [templates/single-certificate.php](https://github.com/gocodebox/lifterlms/blob/trunk/templates/single-certificate.php)


v5.6.0 - 2021-12-07
-------------------

##### New Features

+ Added an option to prevent users (by role) from copying site content and saving local copies of images.
+ Added new site setting to disallow concurrent user sessions for specified user roles.

##### Updates and Enhancements

+ Updates LifterLMS REST to [v1.0.0-beta.21](https://make.lifterlms.com/2021/12/07/lifterlms-rest-api-version-1-0-0-beta-21/).

##### Developer Notes

+ Database migration functions can now be namespaced, eliminating the need to prefix update function names with a version number.


v5.5.0 - 2021-11-05
-------------------

##### New Features

+ Includes the LLMS-CLI beta, a set of WP-CLI commands for LifterLMS and LifterLMS add-ons, as part of the core plugin:
  + To get started, run `wp llms --help` in your terminal or read the [online command documentation](https://developer.lifterlms.com/cli/commands/llms/).
  + Please note that the LLMS-CLI is included as a public beta feature. The command API is in a pre-release state and, as such, is subject to change without warning.
  + If you encounter any issues or wish to provide feedback on the LLMS-CLI please get in touch at [https://github.com/gocodebox/lifterlms-cli](https://github.com/gocodebox/lifterlms-cli).

##### Bug Fixes

+ Fix AJAX post search when using search queries containing quotes.

##### Deprecations

+ The `lifterlms_register_post_type_llms_engagement` is deprecated in favor of `lifterlms_register_post_type_engagement`.
+ The `lifterlms_register_post_type_llms_achievement` is deprecated in favor of `lifterlms_register_post_type_achievement`.
+ The `lifterlms_register_post_type_llms_certificate` is deprecated in favor of `lifterlms_register_post_type_certificate`.
+ The `lifterlms_register_post_type_llms_my_certificate` is deprecated in favor of `lifterlms_register_post_type_my_certificate`.
+ The `lifterlms_register_post_type_llms_email` is deprecated in favor of `lifterlms_register_post_type_email`.
+ The `lifterlms_register_post_type_llms_coupon` is deprecated in favor of `lifterlms_register_post_type_coupon`.
+ The `lifterlms_register_post_type_llms_voucher` is deprecated in favor of `lifterlms_register_post_type_voucher`.

##### Developer Notes

+ The `llms-addons` style asset no longer ships an unminified version.
+ The `llms-admin-add-ons` style asset no longer ships an unminified version and the filename of the distributed file has changed.
+ All the LifterLMS post types are now registered using the static method `LLMS_Post_Types::register_post_type()`.
+ Upgraded woocommerce/action-scheduler to [v3.4.0](https://github.com/woocommerce/action-scheduler/releases/tag/3.4.0).


v5.4.1 - 2021-10-26
-------------------

##### Bug fixes

+ Exclude internal-use-only properties (related to reporting caches and student counts) when exporting or cloning courses. [#1532](https://github.com/gocodebox/lifterlms/issues/1532)
+ Don't sanitize input from user forms until validation has succeeded. [#1829](https://github.com/gocodebox/lifterlms/issues/1829.)
+ Fixed an issue encountered when fields are removed from reusable blocks, causing some user forms from functioning as expected. [#1832](https://github.com/gocodebox/lifterlms/issues/1832)


v5.4.0 - 2021-10-14
-------------------

##### Updates

+ Added logic to prevent the permanent deletion of courses or memberships with active subscriptions.
+ When a subscription attempts to charge a recurring payment against a deleted course or membership the transaction will be cancelled and the order marked as failed.
+ Updates LifterLMS Blocks to [v2.2.1](https://make.lifterlms.com/2021/09/29/lifterlms-blocks-version-2-2-1/).
+ Updates LifterLMS REST to [v1.0.0-beta.20](https://make.lifterlms.com/2021/10/11/lifterlms-rest-api-version-1-0-0-beta-20/).

##### Bug fixes

+ Fixed issue encountered when cloning lessons with attached assignments.
+ Fixed an error encountered when viewing an order for a deleted course or membership on the student dashboard.

##### Templates Updated

+ templates/myaccount/view-order.php


v5.3.3 - 2021-10-05
-------------------

##### Updates

+ Update woocommerce/actions-scheduler to version 3.3.0.

##### Bug fixes

+ Fixed an issue causing the latest earned achievement to not display on the "My Grades" tab in certain scenarios.
+ Fix issue causing a `waiting...` message to display on the JS dev console.
+ Fix improper usage of `apply_filters_deprecated()` encountered when using deprecated theme settings filters in the course builder.
+ Fixed missing text domain, thanks [chetansatasiya](https://github.com/chetansatasiya)!

##### Developer notes

+ Improved the `LLMS.waitFor()` runtime JS dependency loader to output improved debugging information.


v5.3.2 - 2021-09-21
-------------------

##### Updates

+ Updated the SendWP integration account management URL.

##### Bug fixes

+ Fixed issue encountered with TinyMCE editor instances in repeater metabox groups.
+ Fixed issue causing the latest achievement to not display when reviewing grades on the student dashboard.


v5.3.1 - 2021-09-13
-------------------

##### Bug fixes

+ Fixed quote slashing for non-admin roles when editing content in the course builder.
+ The LifterLMS admin icon now uses an encoded SVG to improve admin color scheme compatibility.
+ Fixed an issue with empty admin notices.

##### Dev updates

+ The creation date of `llms_orders` is now determined by `llms_current_time()`.


v5.3.0 - 2021-08-31
-------------------

##### Updates

+ Improved logic used to determine when a limited length subscription has completed its payment schedule.
+ Improved accessibility of various icon buttons on the admin orders view/edit screen.
+ Improved display of quiz attempts containing questions which have been deleted from the database.
+ POT files from included library plugins (like LifterLMS REST) are now excluded from LifterLMS distributions.

##### Development updates

+ Introduced `LLMS_Trait_Singleton` to replace redundant singleton pattern definitions across classes in the codebase.
+ Moved the loading of the autoloader to the main `lifterlms.php` file.
+ Updated the `LLMS_Payment_Gateway` abstract class to utilize `LLMS_Abstract_Options_Data` for accessing gateway options.
+ Audio and video embed methods shared by `LLMS_Course` and `LLMS_Membership` have been relocated to `LLMS_Trait_Audio_Video_Embed`.
+ Sales page methods shared by `LLMS_Course` and `LLMS_Membership` have been relocated to `LLMS_Trait_Sales_Page`.

##### Bug Fixes

+ Fixed a visual issue encountered on the payment confirmation screen on small screens / mobile devices.
+ Fix untranslatable time period strings (day, week, month, and year) found on the admin orders view/edit screen.
+ Fixed an error encountered when attempting to grade a quiz attempt containing deleted questions.

##### Deprecations

+ Removed usage and references to the `LLMS_Order` post meta property `date_billing_end`. To determine if a subscription has ended, use `LLMS_Order::get_remaining_payments()` instead.
+ Removed private method `LLMS_Order::calculate_billing_end_date()`.
+ Deprecated the class property `$_instance` from the following classes, use the public method `instance()` instead:
  + `LLMS_Achievements`
  + `LLMS_Certificates`
  + `LLMS_Emails`
  + `LLMS_Engagements`
  + `LLMS_Events`
  + `LLMS_Grades`
  + `LLMS_Integrations`
  + `LLMS_Notifications`
  + `LLMS_Payment_Gateways`
  + `LLMS_Processors`
  + `LLMS_Sessions`

##### Templates Updated

+ templates/checkout/form-confirm-payment.php
+ templates/admin/reporting/tabs/quizzes/attempt.php
+ templates/quiz/results-attempt-questions-list.php


v5.2.1 - 2021-08-17
-------------------

##### Updates

+ [LifterLMS Helper Version 3.4.1](https://make.lifterlms.com/2021/08/17/lifterlms-helper-version-3-4-1/).
+ Made minor development-related changes to the `LLMS_Order` class.

##### Bug Fixes

+ Fixed an issue encountered when a course or membership sales page redirect is enabled but no URL is saved.


v5.2.0 - 2021-08-10
-------------------

##### Upcoming Payment Reminder Notification

+ A new notification, the "Upcoming Payment Reminder" notification has been added. This notification sends a reminder to students a configurable number of days before a payment is do for a recurring subscription.
+ When upgrading to version 5.2.0, this notification will be automatically *disabled*, visit LifterLMS -> Settings -> Notifications and select the new notification to enable it after upgrading.
+ Props to [@niluzok](https://github.com/niluzok) for doing the initial work required to build this notification!

##### Updates

+ Reworked the database upgrader script to allow for minor upgrades which don't require significant data migration to upgrade silently without requiring user consent to initiate.
+ Improved internal methods used to generate tables in the body of email notifications.

##### Bug Fixes

+ Student registration date is now displayed in the site's timezone in favor of UTC time.
+ Properly pass options `template_path` and `default_path` to the template handler when creating an admin notice using a template.
+ Removed translation (and incorrect text domain) from a logging function encountered when a recurring payment errors as a result of the payment gateway having been deactivated.

##### Deprecations

+ `LLMS_Install::db_updates()` is deprecated, use ``LLMS_DB_Upgrader::enqueue_updates()` instead.
+ `LLMS_Install::update_notice()` is deprecated with no replacement.
+ Template `admin/notices/db-update.php` is deprecated in favor of `includes/admin/views/db-update.php`.
+ Template `admin/notices/db-updating.php` is deprecated with no replacement.


v5.1.3 - 2021-08-04
-------------------

+ Bugfix: Fixed an issue where a white box would be output over the certificate background image.
+ Bugfix: Fixed an issue in the course builder causing lessons to be orphaned from a course when moved into an unsaved section.
+ [LifterLMS Helper Version 3.4.0](https://make.lifterlms.com/2021/08/04/lifterlms-helper-version-3-4-0/)


v5.1.2 - 2021-07-28
-------------------

+ Bugfix: Pass second parameter to the `get_the_excerpt` filter.
+ Fix: Corrected typos in error messages encountered during password reset.


v5.1.1 - 2021-07-26
-------------------

+ Bugfix: Fixed a bug causing malformed character codes to be rendered in forms when installing forms with translated labels.
+ [LifterLMS Helper version 3.3.1](https://make.lifterlms.com/2021/07/26/lifterlms-helper-version-3-3-1/)


v5.1.0 - 2021-07-19
-------------------

##### Updates

+ **Raised the minimum required WordPress core version to 5.8!**
+ Adds WordPress core 5.8 compatibility.
+ Improved user information forms required field validation.
+ Added functionality to ensure that user email and password fields are *always* displayed to logged out users on checkout and registration forms.
+ Added functionality to ensure that user email and password fields are *always* displayed on the account edit form.
+ [LifterLMS Blocks version 2.2.0](https://make.lifterlms.com/2021/07/19/lifterlms-blocks-version-2-2-0/)

##### Bug fixes

+ Fixed an issue preventing certain orphaned quizzes from being deleted.
+ Prevent users from submitting a password change without submitting their current password.
+ Allow logged in users to checkout when no form fields are set to display.


v5.0.2 - 2021-07-08
-------------------

##### LifterLMS Blocks

+ Upgraded to [version 2.1.1](https://make.lifterlms.com/2021/07/08/lifterlms-blocks-version-2-1-1/).

##### Bug Fixes

+ Fixed issue with non-Latin characters in dashboard endpoint URL slugs.
+ Fixed issue preventing address localization when using the [lifterlms_registration] shortcode.


v5.0.1 - 2021-06-28
-------------------

##### Updates

+ Update to [LifterLMS Blocks v2.1.0](https://make.lifterlms.com/2021/06/28/lifterlms-blocks-version-2-1-0/).
+ Added a new filter to allow programmatically alter required field validation results.

##### Bugfixes

+ Fixed an issue causing preventing form layout options from working when passed into shortcodes.
+ Fixed an issue preventing custom radio, select, and dropdown fields from working during checkout.
+ Fixed an accessibility issue encountered during password strength validation.


v5.0.0 - 2021-06-22
-------------------

##### User Information Form Builder

+ Customize all user information collection forms using the block editor for drag and drop and WYSIWYG form building.
+ Customize field labels, placeholders, descriptions and more with an easy point and click interface.
+ Determine if fields are required or optional with a simple toggle switch.
+ Update the form layout with the block editor. Reorder fields, add columns, and more with a simple drag and drop interface.
+ Remove unwanted fields with the click of a button.

##### User Location Information Form Fields

+ During user account creation and updates the user location fields are now locale aware ensuring that the proper terminology is used and only locale-required fields are displayed for the selected locale.
+ The "Country" field has been updated to be automatically populated with a list of countries. View the full list in the file at `languages/countries.php` and the filter `lifterlms_countries` can be used to modify the default list at runtime.
+ The "State" field on user forms has been updated to be automatically populated with a list of states (provinces or regions) for the selected country. This list of states can be found in the file at `languages/states.php` and the filter `lifterlms_states` can be used to modify the default list at runtime.
+ Both "Country" and "State" fields are now searchable dropdowns elements.
+ The lists of countries and states will be automatically updated during future releases based on information provided by [GeoNames](https://www.geonames.org/) APIs.

##### Mergecodes everywhere via new `[llms-user]` shortcode

+ Allows merging most user information field data into any post or page, email, or notification (as well as widgets and more).

##### Updates

+ Email and password confirmation fields may now be made optional.
+ "User Information Options" have been largely removed in favor of determining which fields are displayed via the forms UI
+ The former "User Information Options" settings area has been renamed to "User Privacy Options".
+ Removed email lookup logic since `wp_authenticate()` supports email addresses as `user_login` since WP 4.5.
+ Custom user fields added via filters are now displayed on the admin panel at priority 11 instead of 10.
+ Added shortcode processing in LifterLMS-generated emails.
+ If a symbol cannot be found for the supplied currency code, return the code instead of an empty string.

##### Bug Fixes

+ Changed the filter on return of `LLMS_Person_Handler::get_password_reset_fields()` from `lifterlms_lost_password_fields` to `llms_password_reset_fields`.
+ Fixed duplicate references to the `llms-select2` script.

##### Development changes

+ Added before and after actions hooks for admin tools.
+ The filter `lifterlms_before_user_${action}` is now triggered by `do_action_ref_array()` instead of `do_action()` allowing modification of `$posted_data` and `$fields` via hooks.
+ A number of action and filter hooks have been moved to new locations within the codebase. They will continue to function as expected (with some minor exceptions).
+ Enqueue select2 on account and checkout pages for searchable dropdowns for country & state.
+ Stop loading removed processor "table_to_csv".

##### Library & Vendor Updates

+ Updates LifterLMS Blocks to version 2.0.1.
+ Updates woocommerce/actions-scheduler to version 3.2.1.
+ Load core libraries from new location and load WP Background Processing lib.
+ The vendor script dependency `topModal.js` has been removed.

##### Templates Updated

+ templates/checkout/form-checkout.php
+ templates/checkout/form-confirm-payment.php
+ templates/checkout/form-gateways.php
+ templates/global/form-login.php
+ templates/global/form-registration.php
+ templates/myaccount/form-edit-account.php
+ templates/product/free-enroll-form.php

##### Deprecations

The following have been deprecated and will be removed from LifterLMS in a major update following version 5.0.0.

+ Class Method: `LLMS_Person_Handler::get_available_fields()` is deprecated in favor of `LLMS_Forms::get_form_fields()`.
+ Class Method: `LLMS_Person_Handler::register()` is deprecated, in favor of `llms_register_user()`.
+ Class Method: `LLMS_Person_Handler::sanitize_field()` (private method) is deprecated with no replacement.
+ Class Method: `LLMS_Person_Handler::update()` is deprecated, in favor of `llms_update_user()`.
+ Class Method: `LLMS_Person_Handler::validate_fields()` is deprecated with no replacement.
+ Class Method: `LLMS_Person_Handler::voucher_toggle_script()` is deprecated with no replacement.
+ Filter: `llms_usernames_blacklist` is deprecated, use `llms_usernames_blocklist` instead.
+ Filter: `lifterlms_get_user_custom_fields` is deprecated with no replacement.
+ Function: `llms_get_minimum_password_strength()` is deprecated with no replacement.
+ Option: `lifterlms_registration_generate_username` is deprecated in favor of the new method `LLMS_Forms::are_usernames_enabled()`.

##### Removed Items

+ Private method `LLMS_Processors::includes()` has been removed.
+ Private methods `LLMS_Person_Handler::fill_fields()` and `LLMS_Person_Handler::insert_data()` were removed.
+ Previously deprecated class method `LLMS_Quiz::get_lessons()` has been removed.
+ Previously deprecated class method `LLMS_Controller_Quizzes::take_quiz()` has been removed.
+ Previously deprecated class `LLMS_Processor_Table_To_Csv` has been removed.


v5.0.0-rc.2 - 2021-06-18
------------------------

+ Remove password description merge codes from reusable block schema.
+ Explicitly define required field attributes on reusable block schema.
+ Requires WP 5.7 or later to edit forms & show an upgrade nudge when requirements are not met.
+ Add a link from the (now) legacy account settings area to help experienced users find the new form building area
+ Add a (subtle) custom fields add-on upgrade nudge when viewing the forms list on the admin panel
+ Update LifterLMS Blocks to 2.0.0-rc.2


v5.0.0-rc.1 - 2021-06-15
------------------------

+ Updates Action Scheduler library to version 3.2.0
+ Remove the {min_strength} and {min_length} merge codes from the User Password block description.
+ Don't load removed files during OptimizePress compatibility.
+ Add a 5.0.0 DB upgrade routine and welcome notice
+ Add the LifterLMS Helper as an included library
+ Add WordPress 5.8 compatibility on the Widgets and Customizer screens.
+ Move form location definitions into a schema file
+ Require WordPress 5.7+ to manage forms via the block editor
+ Upgrades LifterLMS Blocks to 2.0.0-rc.1


v5.0.0-beta.2 - 2021-06-01
---------------------------

+ Updates LifterLMS Blocks to 2.0.0-beta.6.
+ (Re-)introduces the user information shortcode as `[llms-user]`.
+ Add Admins status tool to reinstall core forms & reusable blocks.
+ Fixed issue causing data from conditionally disabled fields (like state) from being cleared during form submission
+ Updated form post type labels and added missing labels
+ Removed the previously deprecated class `LLMS_Frontend_Forms` and it's deprecated class methods `reset_password()` and `voucher_check()`.
+ Removed the previously deprecated class `LLMS_Frontend_Password` and it's deprecated class methods: `retrieve_password()`, `check_password()`, and `reset_password()`.
+ Updated country and state localization lists.


v5.0.0-beta.1 - 2021-05-19
---------------------------

+ LifterLMS Blocks 2.0.0-beta.5
+ Added site-wide field name validation
+ Reworked the output of user information fields on the admin panel to share a handler and APIs with frontend fields.
+ Deprecated filter: `lifterlms_get_user_custom_fields` in favor of `llms_admin_profile_fields`
+ Improved previewing of form posts using WP Core block editor UI elements
+ Open Registration form can now always be previewed regardless of the open registration site setting


v5.0.0-alpha.6 - 2021-05-07
---------------------------

+ LifterLMS Blocks 2.0.0-beta.4
+ Fix default reusable password field type from plain text to password
+ Change the default reusable block post titles to reduce confusion when searching for blocks in the editor


v5.0.0-alpha.5 - 2021-05-03
---------------------------

+ Reorganized new files into subdirectories.
+ Added serverside password minimum length validation.
+ Fix duplicate password strength meter output.
+ Fix the user password field type from text to password
+ Fix the phone number field type from text to tel
+ Fix user state select field
+ Don't autoload field values from specified datastore when a "value" is explicitly passed to the field.
+ Only load published reusable blocks on the frontend of the website
+ Improved the UX for editing a users account by automatically "hiding" password and email fields and only requiring them to be submitted when users explicit request an update via the field's "change" toggle button.


v5.0.0-alpha.4 - 2021-04-26
---------------------------

+ Default form templates now use reusable blocks.
+ Improved the user experience surrounding fields with a confirmation field (email address and password).
+ Added the ability to define a field's column width instead of requiring the usage of WP column blocks.
+ Added support for reusable blocks on form posts
+ Upgraded LifterLMS Blocks to 2.0.0-beta.3.


v5.0.0-alpha.3 - 2021-03-23
---------------------------

+ Fixed issue preventing users from editing their email address and password on the dashboard account edit screens.
+ Fixed issues with country names with the article "the" in their name, for example "The Netherlands" instead of "Netherlands The".
+ Upgraded LifterLMS Blocks to version 2.0.0-beta.2.


v5.0.0-alpha.2 - 2021-03-22
---------------------------

##### Updates

+ Updates LifterLMS Blocks to version 2.0.0-beta.1
+ Adds functionality to force usage of the Block Editor for editing LifterLMS forms
+ Updates localization functionality and methods to have more accurate information.
+ Added a function for determining if open registration is enabled.
+ Added a WP Admin Bar link below the "Edit Page" link to enable editing the form (if a form exists on the page).

##### Bug Fixes

+ Fixed an issue encountered when custom HTML fields exist on a form (backwards compatibility for pre 5.x fields API).


v5.0.0-alpha.1 - 2021-01-07
---------------------------

##### User Information Form Builder

+ Customize all user information collection forms using the block editor for drag and drop and WYSIWYG form building.
+ Customize field labels, placeholders, descriptions and more with an easy point and click interface.
+ Determine if fields are required or optional with a simple toggle switch.
+ Update the form layout with the block editor. Reorder fields, add columns, and more with a simple drag and drop interface.
+ Remove unwanted fields with the click of a button.

##### User Location Information Form Fields

+ During user account creation and updates the user location fields are now locale aware ensuring that the proper terminology is used and only locale-required fields are displayed for the selected locale.
+ The "Country" field has been updated to be automatically populated with a list of countries. View the full list in the file at `languages/countries.php` and the filter `lifterlms_countries` can be used to modify the default list at runtime.
+ The "State" field on user forms has been updated to be automatically populated with a list of states (provinces or regions) for the selected country. This list of states can be found in the file at `languages/states.php` and the filter `lifterlms_states` can be used to modify the default list at runtime.
+ Both "Country" and "State" fields are now searchable dropdowns elements.
+ The lists of countries and states will be automatically updated during future releases based on information provided by [GeoNames](https://www.geonames.org/) APIs.

##### Mergecodes everywhere via new `[user]` shortcode

+ TODO.

##### Updates

+ Email and password confirmation fields may now be made optional.
+ "User Information Options" have been largely removed in favor of determining which fields are displayed via the forms UI
+ The former "User Information Options" settings area has been renamed to "User Privacy Options".

##### Bug Fixes

+ Changed the filter on return of `LLMS_Person_Handler::get_password_reset_fields()` from `lifterlms_lost_password_fields` to `llms_password_reset_fields`.

##### Development changes

+ The filter `lifterlms_before_user_${action}` is now triggered by `do_action_ref_array()` instead of `do_action()` allowing modification of `$posted_data` and `$fields` via hooks.
+ A number of action and filter hooks have been moved to new locations within the codebase. They will continue to function as expected (with some minor exceptions).
+ Enqueue select2 on account and checkout pages for searchable dropdowns for country & state.

##### Library & Vendor Updates

+ Load core libraries from new location and load WP Background Processing lib.
+ The vendor script dependency `topModal.js` has been removed.

##### Templates Updated

+ templates/global/form-login.php
+ templates/global/form-registration.php
+ templates/product/free-enroll-form.php

##### Deprecations

The following have been deprecated and will be removed from LifterLMS in a major update following version 5.0.0.

+ Class Method: `LLMS_Person_Handler::get_available_fields()` is deprecated in favor of `LLMS_Forms::get_form_fields()`.
+ Class Method: `LLMS_Person_Handler::register()` is deprecated, in favor of `llms_register_user()`.
+ Class Method: `LLMS_Person_Handler::sanitize_field()` (private method) is deprecated with no replacement.
+ Class Method: `LLMS_Person_Handler::update()` is deprecated, in favor of `llms_update_user()`.
+ Class Method: `LLMS_Person_Handler::validate_fields()` is deprecated with no replacement.
+ Class Method: `LLMS_Person_Handler::voucher_toggle_script()` is deprecated with no replacement.
+ Filter: `llms_usernames_blacklist` is deprecated, use `llms_usernames_blocklist` instead.
+ Function: `llms_get_minimum_password_strength()` is deprecated with no replacement.
+ Option: `lifterlms_registration_generate_username` is deprecated in favor of the new method `LLMS_Forms::are_usernames_enabled()`.

##### Removed Items

+ Private method `LLMS_Processors::includes()` has been removed.
+ Private methods `LLMS_Person_Handler::fill_fields()` and `LLMS_Person_Handler::insert_data()` were removed.
+ Previously deprecated class method `LLMS_Quiz::get_lessons()` has been removed.
+ Previously deprecated class method `LLMS_Controller_Quizzes::take_quiz()` has been removed.
+ Previously deprecated class `LLMS_Processor_Table_To_Csv` has been removed.


v4.21.3 - 2021-05-31
--------------------

##### Updates

+ Increase 3rd party support for WP core hook `lostpassword_post` hook.

##### Bug fixes

+ Props to [Hemant Patidar](https://www.linkedin.com/in/hemantsolo/) for discovering an issue preventing rate limiting in various security plugins from working on the LifterLMS password recovery form.
+ Fixed an issue encountered when updating LifterLMS premium add-ons via the LifterLMS Helper encountered when API errors are occur.
+ Updated the failure error code from 'activation' to 'deactivation' in the `LLMS_Add_On` class.
+ Updated the API connection error message returned when using the `LLMS_Abstract_API_Handler` class.

##### Deprecations

+ Class `LLMS_Frontend_Password` is deprecated, see deprecated methods and their replacements below:

  + `LLMS_Frontend_Password::retrieve_password()` is deprecated in favor of `LLMS_Controller_Account::lost_password()`.
  + `LLMS_Frontend_Password::check_password_reset_key()` is deprecated in favor of `check_password_reset_key()`.
  + `LLMS_Frontend_Password::reset_password()` is deprecated in favor of `reset_password()`.


v4.21.2 - 2021-05-17
--------------------

##### Security Update

This releases fixes a security issue affecting LifterLMS versions 4.21.1 and earlier:

+ Thank you to [Amirmohammad vakili](https://www.linkedin.com/in/amirmuhammad-vakili-65a7a11b3/) for reporting an insecure direct object reference issue.

##### Updates

+ Added the `view_grades` capability which is used to determine whether or not a user has the ability to view another user's grades on the website's frontend.

##### Bug fixes

+ Fixed an issue causing PHP errors when attempting to access a quiz attempt that doesn't exist.
+ Fixed a localization issue encountered when entering transaction amounts on the admin panel.


v4.21.1 - 2021-04-29
--------------------

##### Security Update

This releases fixes two security issues affecting LifterLMS versions 4.21.0 and earlier:

+ Thank you to [Amirmohammad vakili](https://www.linkedin.com/in/amirmuhammad-vakili-65a7a11b3/) for reporting a way to store XSS.
+ Thank you to Ashish Jha from [Bluefire Redteam](https://www.bluefire-redteam.com/) for reporting a reflected XSS issue on checkout screens.


v4.21.0 - 2021-04-19
--------------------

##### Updates

+ Certificate exports will now automatically include (most) externally hosted images and stylesheets.
+ Opt-in forward compatibility changes have been made to the `LLMS_Abstract_Options_Data` class.

##### Bugfixes

+ Fixed an issue causing one-time payment orders from being included in totals on some reporting screens.
+ Fixed an issue causing student enrollment counts to be incorrect under some circumstances.
+ Fixed issues resulting in unnecessary duplicated instances of course background data processing.
+ Fixed an error encountered when a course is deleted prior to its background data being processed.
+ Fixed an escaping issue causing passwords with a backslash character from being usable following a password reset.


v4.20.0 - 2021-03-16
--------------------

##### Bugfixes

+ Fixed an issue causing a fatal error when attempting to access reports for deleted students. Thanks Thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed an issue encountered on the builder causing the last section to be returned when retrieving the previous section for the first section.


v4.19.0 - 2021-03-11
--------------------

##### Supported Version Requirement Updates

+ **The minimum supported PHP version has been raised to PHP 7.3. Please upgrade to a [supported PHP version](https://www.php.net/supported-versions).**
+ **The minimum supported WordPress core version has been raised to version 5.3.**

##### Bug fixes

+ Fixed an issue causing TinyMCE editor instances to be unusable within metaboxes when using the block editor.


v4.18.0 - 2021-03-04
--------------------

**This is the last release of LifterLMS that will declare support for PHP 7.2. PHP 7.2 reached its official [end of life](https://www.php.net/eol.php) on November 30, 2020. With the next release of LifterLMS the minimum supported PHP version will be raised to 7.3. If you're currently using PHP 7.2 please contact your host and request an upgrade to a [supported PHP version](https://www.php.net/supported-versions) as soon as possible!**

##### Updates

+ Tested up to WordPress core version 5.7
+ Updated several occurrences of `json_encode()` with preferred `wp_json_encode()`.

##### Bug fixes

+ Added a tie-breaker when there are multiple enrollment statuses with the same date & time. Thanks [@pondermatic](https://github.com/pondermatic)!
+ On admin order pages and tables don't print links for deleted students.
+ Fixed an issue on admin order pages when viewing an order for a deleted student.


v4.17.0 - 2021-02-22
--------------------

##### Updates

+ The post type feature "llms-sales-page" has been added to course and membership post types, signifying they support custom sales pages.

##### Bug fixes

+ Fixed compatibility issues with Yoast SEO 15.8.
+ Fixed duplicate action hook in `content-no-access-after.php` template.
+ Added early returns to several templates to prevent undefined variables errors.
+ Fixed an undefined variable encountered in course builder JS debug logging.

##### Templates Updated

+ content-no-access-after.php
+ quiz/meta-information.php
+ quiz/results.php
+ quiz/start-button.php


v4.16.0 - 2021-02-18
--------------------

##### Updates

+ Added preview management to the student dashboard to allow previewing of the dashboard as a site visitor.
+ Added a new filter to allow customization of courses output by the [lifterlms_courses] shortcode. Thanks [@reedhewitt](https://github.com/reedhewitt)!
+ Added compatibility code to reduce plugin conflicts encountered in the course builder. Resolves a conflict encountered when building quizzes with Yoast SEO installed.

##### Bug fixes

+ Fixed undefined variable error encountered when creating custom notification types. Thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed incorrect variables passed to `sprintf()` in logging functions used by the course data background processor. Thanks [@pondermatic](https://github.com/pondermatic)!


v4.15.0 - 2021-02-09
--------------------

##### Updates

+ Database migration: remove any "orphaned" access plans which were not properly cleaned up during deletion of parent course or membership.
+ Improved performance of membership post association query methods.

##### Bug fixes

+ Access plans will now be automatically deleted when their parent course or membership is deleted.
+ Fix an issue with donut charts/graphs on RTL sites.
+ Fix an issue causing unpublished (draft/private) courses from being returned during queries for membership post associations.

##### LifterLMS REST 1.0.0-beta.15

###### Updates

+ Added Access Plan resource and endpoint.
+ Provide a more significant error message when trying to delete an item without permissions.
+ Use `WP_Http` constants in favor of integers when referencing HTTP status codes.

###### Bug fixes

+ Fixes localization issues where a singular name was used in favor of the expected plural form.
+ Fixed issues where an error object was not properly returned when expected
+ Fixed call to undefined function `llms_bad_request_error()`, must be `llms_rest_bad_request_error()`.
+ Fixed access plans resource link.
+ Fixed wrong trigger retrieved when multiple trigger were present for the same user/post pair on Student Enrollment resources.


v4.14.0 - 2021-02-04
--------------------

##### Updates

+ Added a user preference option allowing users to opt-out of the course builder's autosave functionality. [More information](https://lifterlms.com/docs/using-course-builder/#manual-saving).
+ 5-star review request displayed at 30 enrollments instead of 50.

##### Bug fixes

+ Fixed an issue encountered when using shortcodes in the description of an access plan.
+ Fixed an issue encountered when editing auto-draft courses on the course builder.

##### Deprecations

+ `LLMS_Controller_Quizzes::take_quiz()` is deprecated in favor of `LLMS_AJAX_Handler::quiz_start()`.
+ Method `LLMS_Quiz::get_lessons()` is deprecated with no replacement.


v4.13.0 - 2021-01-26
--------------------

##### Updates

+ **The minimum supported WordPress core version has been raised to 5.2.** For more information, please review the [LifterLMS Minimum System Requirements](https://lifterlms.com/docs/minimum-system-requirements-lifterlms/).
+ When cloning courses and lessons the cloned post will be created as a draft.
+ When cloning courses the suffix "(Clone)" will be appended to the title of the course to unify cloning behavior with lessons.
+ Added information about LifterLMS specific constant values to the LifterLMS system report.
+ Added a new constant `LLMS_IS_SITE_CLONE` which can be used to force the site's clone status.

##### Bug fixes

+ Reverts site clone detection check changes implemented in 4.12.0 to restore pre 4.12.0 functionality which only runs checks on the admin panel for logged in users with the `manage_lifterlms` capability.
+ Restore reliance on `mb_convert_encoding()` when passing html strings into `DOMDocument` and use the alternate method introduced in version 4.8.0 as a fallback.
+ Fixed an issue encountered when unexpected or malformed data is stored in the LifterLMS admin notices option.


v4.12.0 - 2021-01-20
--------------------

##### Updates

+ Automatic site clone detection checks have been adjusted to always run in favor of only running on the admin panel.
+ LifterLMS Site Features (like recurring payment status) can now be configured via constant values.
+ Added `llms_load_admin_tools` action to allow 3rd parties to easily hook into our admin tools system.
+ Made numerous performance improvements on the course data background processor.
+ Course data background processing will now be automatically throttled for courses with 500 students or more as opposed to the old value of 2,500 or more.

##### Bug fixes

+ Fixed an incorrect HTML `for` attribute and added an `id` to the related input element on the student dashboard voucher redemption endpoint.
+ Fixed a pagination error encountered when using course or membership list shortcodes on the static front page.
+ Make sure `is_lifterlms()` exists before calling it in navigation menu-related classes.

##### Deprecations

+ `LLMS_Admin_Notices_Core::check_staging()` is deprecated in favor of `LLMS_Staging::notice()`.
+ Unused property `LLMS_Course::$sections` is replaced by `LLMS_Course::get_sections()`.
+ Unused property `LLMS_Course::$sku` is deprecated with no replacement.
+ `LLMS_Frontend_Forms` is deprecated, functionality is available via `LLMS_Controller_Account`.
+ `LLMS_Frontend_Forms::reset_password()` is deprecated in favor of `LLMS_Controller_Account::reset_password()`.

##### Templates Updated

+ templates/myaccount/form-redeem-voucher.php


v4.11.0 - 2021-01-07
--------------------

##### Updates

+ Adds the ability to use the Instructors blocks on the membership post type. Thanks [@alaa-alshamy](https://github.com/alaa-alshamy)!
+ Updated LifterLMS Blocks to [Version 1.11.1](https://make.lifterlms.com/2020/12/29/lifterlms-blocks-version-1-11-1/).

##### Bug fixes

+ Fixed a PHP Notice encountered when trying to retrieve next lesson from an empty section.

##### Templates updated

+ templates/course/author.php


v4.10.2 - 2021-01-04
--------------------

##### Updates

+ Improved performance of `llms_get_enrolled_students()`.
+ Refactored lesson navigation query functions.

##### Bug fixes

+ Fixed sorting error when sorting student reports by name.


v4.10.1 - 2020-12-10
--------------------

##### Bug fixes

+ Fixed visual issues encountered on the admin Add-Ons screen.
+ Use `hr.wp-header-end` in favor of a second (hidden) <h1> to "catch" admin notices on the Add-Ons screen.
+ Replace incorrect usage of invalid ID `llms_shop` with `courses` during catalog template loader checks.
+ Function `llms_get_post()` will now only allow instantiation of LifterLMS classes.
+ Remove unneeded require autoloaded file `includes/class.llms.quiz.data.php`.


v4.10.0 - 2020-12-01
--------------------

##### Updates

+ Adds native theme support for the WordPress default theme Twenty Twenty-One.
+ Improved the `llms_archive_description()` function and related filter.

##### Bug fixes

+ Fix issue encountered when using multiple role plugins to add the Instructor role to an Administrator user account. Thanks [@daniel-shuy](https://github.com/daniel-shuy)!
+ Fixed an issue encountered when using non-latin characters in a course post URL slug. Thanks [@alaa-alshamy](https://github.com/alaa-alshamy)!

##### Templates Updated

+ templates/loop/pagination.php


v4.9.0 - 2020-11-24
-------------------

+ Tested up to WordPress core 5.6 (RC.1).
+ Raised the minimum required WordPress core version to 5.1.
+ Add new localization utilities for developers.
+ Fixed various issues found on PHP 8.
+ Added script localization for block editor scripts.
+ Updated LifterLMS Rest to [Version 1.0.0-beta.17](https://make.lifterlms.com/2020/11/24/lifterlms-rest-api-version-1-0-0-beta-17/).
+ Updated LifterLMS Blocks to [Version 1.10.0](https://make.lifterlms.com/2020/11/24/lifterlms-blocks-version-1-10-0/).


v4.8.0 - 2020-11-16
-------------------

##### Updates

+ Added additional course imports and templates at the end of the setup wizard
+ Added a cloud importer enabling 1-click importing of courses and course templates via the importer at LifterLMS -> Import
+ Added strict comparisons in several places.
+ Course "extra" data is only added to course arrays during exports to improve performance on the course builder.
+ Improved template override loading performance on sites with no child theme.

##### Bug fixes

+ Fixed issues related to reliance on methods provided by the `mb_string` PHP module.

##### Deprecations

+ `LLMS_Admin_Setup_Wizard::generator_course_status()` is deprecated with no replacement.
+ `LLMS_Admin_Setup_Wizard::watch_course_generation()` is deprecated with no replacement.


v4.7.1 - 2020-11-05
-------------------

##### Bug fixes

+ During import generation set the post excerpt during the initial post insert instead of during metadata updates after creation.

##### LifterLMS REST API 1.0.0-beta.16

+ Improved performance of various database queries.


v4.7.0 - 2020-11-02
-------------------

##### Updates

+ Major refactor of the `LLMS_Generator` class.
+ Course export structure improved to include images and reusable blocks found in post content.
+ When importing courses images will be automatically sideloaded into the media library as new attachment posts
+ When importing courses reusable blocks will be imported
+ Improved the success message displayed following a course import
+ The class `LLMS_Admin_Reporting` is now always loaded on the admin panel.
+ Performance improvements have been made to the `LLMS_Events_Query` to support using the `no_found_rows` query argument.
+ When an order's billing plan "completes", a new meta property will be added to the order, `plan_ended`, which can be used to query orders with completed plans.
+ Made improvements to the admin payment rescheduler tool to have more accurate reporting information.

##### Bug fixes

+ Replaced an instance of the LifterLMS (old) 1.0 rocket logo with the current rocket logo. Thanks [@imknight](https://github.com/imknight)!
+ Ensure builder `switch-number` fields are set with the `number` type attribute. Thanks [@imknight](https://github.com/imknight)!
+ Don't display a "View Post" link when updating post types that aren't publicly queryable. Thanks [@imknight](https://github.com/imknight)!
+ Fixed the incorrect output of an achievement's title in a popover notification when using the {{ACHIEVEMENT_TITLE}} merge code. Thanks [@CadenG150](https://github.com/@CadenG150)!
+ Fixed an error encountered when plugins utilize the `WP_Users_List_Table` class outside of the `users.php` screen.

##### Deprecations

+ `LLMS_Admin_Import::localize_stat()` is deprecated with no replacement.
+ `LLMS_Admin_Users_Table::load_dependencies()` is deprecated with no replacement. The included class, `LLMS_Admin_Reporting` is now always loaded.
+ `LLMS_Generator::add_custom_values()` is deprecated in favor of `LLMS_Generator_Courses::add_custom_values`.
+ `LLMS_Generator::get_author_id_from_raw()` is deprecated in favor of `LLMS_Generator_Courses::get_author_id_from_raw()`.
+ `LLMS_Generator::get_default_post_status()` is deprecated in favor of `LLMS_Generator_Courses::get_default_post_status()`.
+ `LLMS_Generator::get_generated_posts()` is deprecated in favor of `LLMS_Generator::get_generated_content()`.
+ `LLMS_Generator::format_date()` is deprecated in favor of `LLMS_Generator_Courses::format_date()`.
+ `LLMS_Generator::increment()` is deprecated with no replacement.


v4.6.0 - 2020-10-19
-------------------

+ Added an admin tool to help automatically identify and schedule missed recurring payments
+ Use `llms_deprecated_function()` in favor of `llms_log()`.
+ Removed logging and use `apply_filters_deprecated()` in favor of `apply_filters()`.


v4.5.1 - 2020-10-14
-------------------

##### Updates

+ Added logic in `LLMS_Database_Query` to reduce unnecessary DB reads when total results are not required.

##### Bug fixes

+ Removed the course "Excerpt" area in favor of utilization of the course sales page content.
+ Show sales reporting currency symbol based on LifterLMS site options in favor of the browser's locale settings.
+ Fixed an issue causing achievement-related JS DOM events to be bound unnecessarily. Thanks to [@imknight](https://github.com/imknight)!
+ Fixed an issue causing site administrator capabilities to be removed during LifterLMS data removal.
+ Fixed an issue causing an instructors course post count to display 0 on the admin panel courses post table. Thanks to [nhandl3](https://github.com/nhandl3)!
+ Only display the admin bar "View Manager" to users who can bypass content restrictions.
+ Updated jQuery code to stop using deprecated events and methods in preparation for jQuery upgrades in the WordPress core.
+ Fixed PHP notice encountered on the admin panel when using Yoast SEO.


v4.5.0 - 2020-10-06
-------------------

##### Updates

+ Students can now choose to make their certificates publicly accessible. Huge thanks to [@alaa-alshamy](https://github.com/alaa-alshamy) for contributing this awesome new feature!
+ When accessing a certificate that does not have sharing enabled, a 404 will be served in favor of an error message.
+ Admin payment gateway notices will no longer redisplay a week after being dismissed.
+ Log files will be automatically split when a file is 5MB or larger, ensuring that log files never grow too large.
+ During student registration, `wp_signon()` is used to login the newly created user.
+ Improved slow background process database queries run during the automatic "closing" of idle user sessions.

##### Bug fixes

+ `LLMS_User_Certificate::get_related_post_id()` and `LLMS_User_Certificate::get_user_id()` will now always return an integer.
+ Fixes issues related to account sign on/out and session start/end events being recorded incorrectly.

##### Deprecations

+ `llms_set_person_auth_cookie()` is deprecated in favor of WP core methods such as `wp_signon()`, `wp_set_current_user()`, and/or `wp_set_auth_cookie()`.


v4.4.4 - 2020-09-21
-------------------

##### Bug fixes

+ Don't pass unsupported parameter `$use_cache` to the `calculate_grade()` method, thanks [@pondermatic](https://github.com/pondermatic)!
+ Add an HTML title attribute to the admin setup wizard page.
+ Fix issue causing notices to be logged during quiz attempt deletion on the admin panel.

##### Deprecations

+ Method `LLMS_Admin_Setup_Wizard::scripts()` & `LLMS_Admin_Setup_Wizard::output_step_html()` are deprecated with no replacements.

##### LifterLMS REST API version 1.0.0-beta.15

+ Bugfix: Created lessons will now have the derivative `course_id` property set according to the ID of the lesson's parent section.
+ Bugfix: The `course_id` property of lessons is now properly marked as read-only.


v4.4.3 - 2020-09-16
-------------------

+ Bugfix: Fix engagement email duplicate check issue.
+ Bugfix: Fix transposition issue found in engagement email dupcheck debug log message.


v4.4.2 - 2020-09-08
-------------------

+ Bugfix: Fix lesson navigation regression introduced in 4.4.0.


v4.4.1 - 2020-09-04
-------------------

+ Bugfix: Delayed engagement emails will not be sent to students who's enrollment is not active in the related course or membership which triggered the email.
+ Bugfix: Fixed regression introduced in 4.4.0 preventing the `certificates.css` stylesheet from loading on certificate screens.
+ Update: Engagement email related logs will be logged to a separate logfile, `engagement-emails` in favor of the main `llms` log.


v4.4.0 - 2020-09-02
-------------------

##### Updates

+ Improved LifterLMS static asset registration, queuing, definitions, and management.
+ Added strict comparators in various areas of the codebase.

##### Changes to deprecated function logs and warnings

+ The `llms_deprecated_function()` method now uses `_deprecated_function()` (from the WP core) under the hood.
+ LifterLMS deprecation warnings are logged to the WP core `debug.log` file in favor of the LifterLMS log file.
+ LifterLMS deprecation warnings will now trigger a `E_USER_DEPRECATED` error when `WP_DEBUG` is enabled.

##### Bugfixes

+ Fixed a lesson navigation issue encountered when sections contain unpublished lessons.
+ Fixed an undefined variable notice encountered on the student dashboard.
+ Fixed an issue encountered when the `wp_login_url()` function returns an empty string.
+ Fixed a double slash found in an asset URI.

##### Deprecations

+ `LLMS_Frontend_Assets::is_inline_script_enqueued()` is deprecated in favor of `LLMS_Frontend_Assets::is_inline_enqueued()`.
+ `LLMS_Ajax::register_script()` is deprecated with no replacement.
+ `LLMS_Ajax::get_ajax_data()` is deprecated with no replacement.
+ Javascript AJAX nonce variable is moved from `wp_ajax_data.nonce` to `window.llms.ajax-nonce`.

##### Templates Updated

+ templates/checkout/form-gateways.php
+ templates/course/lesson-preview.php
+ templates/course/syllabus.php


v4.3.3 - 2020-08-17
-------------------

+ Fixed an issue causing legends of reporting charts to be truncated and only readable after a mouse hover.
+ Fixed an issue caused by passing `null` values to `wp_insert_post()`.
+ Fixed a javascript error encountered on LifterLMS settings screens.


v4.3.2 - 2020-08-10
-------------------

+ WP 5.5 compatibility: Automatically deregister "protected" post types from wp-sitemap.xml.


v4.3.1 - 2020-08-06
-------------------

+ When resetting tracking data cookies, set a "secure" cookie where possible.
+ Catch an unhandled error encountered when generating certificate exports.
+ When an error is encountered during certificate export generation, display an error notice instead of a general notice.


v4.3.0 - 2020-07-28
-------------------

##### Security Fix

+ Fixed an XSS issue on account edit and registration forms. Thanks to [Morningstar](https://twitter.com/0xMstar) for reporting this issue!

##### Bug fixes

+ Fixed an error encountered during customizer live theme preview encountered when Twenty-twenty is the current theme.
+ The `$type` property of the `LLMS_Abstract_Database_Store` is now set to a default placeholder value (`_db_record_`) in favor of an empty string.
+ Set the `$type` property of the `LLMS_Event` class to `event`.
+ Set the `$type` property of the `LLMS_Quiz_Attempt` class to `quiz_attempt`.
+ Set the `$type` property of the `LLMS_User_Post_Meta` class to `user_postmeta`.

##### Updates

+ Added a filter `llms_form_field_args` to allow extending form fields prior to HTML rendering.

##### Deprecations

The following filter hooks have been deprecated. These hooks were being called as the result of a bug (noted above) and should no longer be used. They will be removed in the next *major* version of LifterLMS.

+ `llms__created` has been deprecated, use `llms_{$type}_created` where `{$type}` is the database record type defined by the class property.
+ `llms__deleted` has been deprecated, use `llms_{$type}_deleted` where `{$type}` is the database record type defined by the class property.
+ `llms__updated` has been deprecated, use `llms_{$type}_updated` where `{$type}` is the database record type defined by the class property.


v4.2.0 - 2020-07-21
-------------------

##### Updates

+ Admins can now preview the checkout screen as visitors or students using the "View As" function from the WP Admin bar
+ Javascript cookies now set cookies with `sameSite` set to `strict` as recommended by Firefox/Mozilla.
+ Added filters to allow 3rd parties to use LifterLMS completion tracking APIs to "complete" external or non-LMS content.
+ Added "deep" orphan checks when checking the relationship between a quiz and a lesson.
+ Normalized the return structure in `LLMS_Post_Instructors::get_instructors()` when no instructor set, thanks [@nicolas-jaussaud](https://github.com/nicolas-jaussaud)!
+ Update LifterLMS rocket icon used in the WP Admin Bar in the "View As" area.

##### Bug fixes

+ When deleting a quiz attempt the related lesson will now be automatically marked as "Incomplete" when appropriate.
+ `LLMS_Abstract_User_Data::get_id()` now always returns an integer.
+ Fixed a 404 error resulting from settings tooltips referencing a missing icon asset.
+ Added logic to set the order status to 'cancelled' when an enrollment linked to an order is deleted.



v4.1.0 - 2020-07-06
-------------------

##### LifterLMS REST 1.0.0-beta.14

+ **Breaking**: `LLMS_REST_Controller::prepare_links()` now requires a second parameter, the `WP_REST_Request` for the current request. Any classes extending and overwriting this method must adjust their method signature to accommodate this change.
+ Bugfix: Fixed issue causing response objects to unintentionally include keys of remapped fields. This error occurs only when extending core controllers and attempting to exclude core fields.


v4.0.0 - 2020-06-25
-------------------

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


v3.41.1 - 2020-06-23
--------------------

+ Apply restrictions to post content and excerpts during WP REST requests.


v4.0.0-rc.1 - 2020-06-18
------------------------

View release notes at [https://make.lifterlms.com/2020/06/18/lifterlms-version-4-0-0-rc-1/](https://make.lifterlms.com/2020/06/18/lifterlms-version-4-0-0-rc-1/).


v3.41.0 - 2020-06-12
--------------------

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
--------------------------

View beta release notes at [https://make.lifterlms.com/2020/06/10/lifterlms-version-4-0-0-beta-3/](https://make.lifterlms.com/2020/06/10/lifterlms-version-4-0-0-beta-3/).


v3.40.0 - 2020-06-09
--------------------

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
--------------------------

View beta release notes at [https://make.lifterlms.com/2020/06/04/lifterlms-version-4-0-0-beta-2/](https://make.lifterlms.com/2020/06/04/lifterlms-version-4-0-0-beta-2/).


v4.0.0-beta.1 - 2020-06-01
--------------------------

View beta release notes at [https://make.lifterlms.com/2020/06/01/lifterlms-version-4-0-0-beta-1/](https://make.lifterlms.com/2020/06/01/lifterlms-version-4-0-0-beta-1/).


v3.39.0 - 2020-05-28
--------------------

+ Student Welcome notifications and user registered engagements now fire when users are created via the REST POST requests to the `/students` endpoint.
+ Bugfix: Error encountered when printing full-page certificates on certain themes.

##### LifterLMS REST 1.0.0-beta.12

+ Feature: Added the ability to filter student and instructor collection list requests by various user information fields.
+ Fix: Prevent infinite loops encountered when invalid API keys are utilized.
+ Fix: Add an action used to fire LifterLMS core engagement and notification emails


v3.38.2 - 2020-05-19
--------------------

+ Added a default question type ("choice") to prevent malformed questions from being inadvertently stored in the database.
+ When retrieving question data from the database, automatically fall back to the default question type value if no question type is saved.


v3.38.1 - 2020-05-11
--------------------

+ Update: Added methods for retrieving a list of posts associated with a membership.
+ Bug fix: Fixed an issue causing certificate backgrounds to be cropped or cut in certain circumstances.
+ Bug fix: Fixed an issue generating certificate downloads on servers where `mime_content_type()` does not exist.
+ Bug fix: Fixed an issue which caused bbPress course forum restrictions to stop working.


v3.38.0 - 2020-04-29
--------------------

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


v3.37.19 - 2020-04-20
---------------------

##### Updates

+ Added a new debugging tool to clear pending batches created by background processors.
+ Added a new method `LLMS_Abstract_Notification_View::get_object()` which can be used by notification views to override the loading of the post (or object) which triggered the notification.

##### Bug Fixes

+ Added localization to strings on the coupon admin screen. Thanks [parfilov](https://github.com/parfilov)!
+ Fixed issue encountered in metaboxes when the `$post` global variable is not set.


v3.37.18 - 2020-04-14
---------------------

+ Fix regression introduced in version 3.34.0 which prevented checkout success redirection to external domains.
+ Resolved a conflict with LifterLMS, Divi, and WooCommerce encountered when using the Divi frontend pagebuilder on courses and memberships.
+ Fixed issue causing localization issues when creating access plans, thanks [@mcguffin](https://github.com/mcguffin)!


v3.37.17 - 2020-04-10
---------------------

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


v3.37.16 - 2020-03-31
---------------------

+ Bugfix: Fix issue causing student dashboard notification view to work incorrectly.


v3.37.15 - 2020-03-27
---------------------

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


v3.37.14 - 2020-03-25
---------------------

+ Update: Added the ability to view the PHP error log file (as defined by `ini_get( 'error_log' )` ) on the LifterLMS -> Status -> Logs page.
+ Update: Added strict comparisons for various condition checks.
+ Bugfix: Fixed an issue where users might be redirected to the wrong course following a course import at the conclusion of the setup wizard.
+ Bugfix: Fixed issue with tracking event data being lost due to cookie size limitations.
+ Bugfix: Fixed issue potentially encountered when checking user capabilities for certificates and achievements.
+ Bugfix: Fixed an issue preventing additional instances of the JS `LLMS.Storage` class from being instantiated.


v3.37.13 - 2020-03-10
---------------------

+ Remove usage of internal functions marked as deprecated.


v3.37.12 - 2020-03-10
---------------------

##### Updates

+ Tested up to WordPress Core version 5.4.
+ Added support for post revisions for course, lesson, and membership post types.

##### Developer updates

+ Added strict comparisons for various condition checks.
+ Added a new filter, `llms_builder_{$post_type}_force_delete` which allows control over whether a post is moved to the trash or immediately deleted when trashed via the course builder.

##### Bugfixes

+ Fixed the name of the "actions" column on the quiz reporting screen.
+ Fixed PHP warnings resulting from functions used to exclude order notes from comment counts.
+ Fixed issue causing order notes to be included in the count displayed on the admin comments list despite their exclusion from the table itself.
+ Fixed PHP notice thrown on the WordPress menu editor interface encountered when student dashboard endpoints have been deleted or removed.
+ Fixed issue causing quotes to be encoded in various email, achievement, and certificate fields.

##### Deprecations

The following have been deprecated with no replacements and will be removed in the next major update:

+ `LLMS_Course_Factory::get_course()`
+ `LLMS_Course_Factory::get_lesson()`
+ `LLMS_Course_Factory::get_product()`
+ `LLMS_Course_Factory::get_quiz()`
+ `LLMS_Course_Factory::get_question()`
+ `LLMS_Course_Handler::get_users_not_enrolled()`


v3.37.11 - 2020-03-03
---------------------

##### Updates

+ Resolved a conflict with the "Starter Templates" plugin which made it impossible to edit quizzes while the plugin was enabled.

##### Bugfixes

+ Fixed an issue causing lesson post authors to be "lost" when adding an existing lesson to a course.
+ Fixed an issue causing php notices to be generated during existing lesson addition on the course builder.
+ Fixed an issue causing course bbPress forums to be lost when editing that course using the "Quick Edit" function from the courses table.

##### LifterLMS REST v1.0.0-beta.10

+ Added text domain to i18n functions that were missing the domain.
+ Added a "trigger" parameter to enrollment-related endpoints.
+ Added `llms_rest_enrollments_item_schema`, `llms_rest_prepare_enrollment_object_response`, `llms_rest_enrollment_links` filter hooks.
+ Fixed setting roles instead of appending them when updating user, thanks [@pondermatic](https://github.com/pondermatic)!
+ Fixed return when the enrollment to be deleted doesn't exist, returns `204` instead of `404`.
+ Fixed 'context' query parameter schema, thanks [@pondermatic](https://github.com/pondermatic)!


v3.37.10 - 2020-02-19
---------------------

+ Update: Exclude the privacy policy page from the sitewide restriction.
+ Update: Added filter `llms_enable_open_registration`.
+ Fix: Notices are printed on pages configured as a membership restriction redirect page.
+ Fix: Do not apply membership restrictions on the page set as membership's restriction redirect page.
+ Fix: Added flag to print notices when landing on the redirected page.


v3.37.9 - 2020-02-11
--------------------

+ Updated CSS classes used in privacy policy text suggestions per changes in WordPress core 5.3. Thanks [@garretthyder](https://github.com/garretthyder)!
+ Added privacy exported group descriptions. Thanks [@garretthyder](https://github.com/garretthyder)!
+ Added filters `llms_user_enrollment_allowed_post_types` & `llms_user_enrollment_status_allowed_post_types` which allow 3rd parties to enroll users into additional post types via core enrollment methods.
+ Added option for admin settings fields to show an asterisk for required fields.
+ Added option for integration plugins can now add automatically generated "Settings" link to the plugins screen.
+ Bugfix: Fixed an IE compatibility issue related to usage of `Object.assign()`.


v3.37.8 - 2020-01-21
--------------------

+ Fix: Student quiz attempts are now automatically deleted when a quiz is deleted.
+ Fix: "Orphaned" quizzes (those with no parent course and/or lesson) can be deleted from the Quiz reporting table.
+ Fix: Quiz IDs on the quiz reporting screen now link to the quiz within the course builder. If the quiz is an "orphan" there will be no link.


v3.38.0-beta.2 - 2019-12-19
---------------------------

+ Update LifterLMS Blocks to v1.7.3.


v3.38.0-beta.1 - 2019-12-13
---------------------------

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

+ Remove unused bootstrap transition and collapse scripts.
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

v3.37.7 - 2020-01-08
--------------------

+ Fix error resulting from undefined default value.
+ Fix PHP 7.4 deprecation notice.


v3.37.6 - 2019-12-12
--------------------

+ New transaction creation date is now specified using `llms_current_time()`.
+ Use the last successful transaction time to calculate from when the previously stored next payment date is in the future.
+ Fixed an issue causing transaction post titles to be recorded with missing data due to invalid `strftime()` placeholders.


v3.37.5 - 2019-12-09
--------------------

+ Update LifterLMS Blocks to v1.7.2: fixes a bug causing the block editor to encounter a fatal error when accessing custom post types that don't support custom fields.


v3.37.4 - 2019-12-06
--------------------

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


v3.37.3 - 2019-12-03
--------------------

+ Added an action `llms_certificate_generate_export` to allow modification of certificate exports before being stored on the server.
+ Don't unslash uploaded file `tmp_name`, thanks [@pondermatic](https://github.com/pondermatic)!
+ TwentyTwenty Theme Support: Hide site header and footer, and set a white body background in single certificates.
+ Renamed setting field IDs to be unique for open/close wrapper fields on the engagements and account settings pages.
+ Removed redundant functions defined in the `LLMS_Settings_Page` class to reduce code redundancy in account and engagement setting page classes.
+ The `LLMS_Settings_Page` base class now automatically defines actions to save and output settings content.


v3.37.2 - 2019-11-22
--------------------

+ LifterLMS notices will now be displayed on pages defined as a Course or Membership sales page.
+ TwentyTwenty Theme: Updated to use `background-color` property instead of `background` shorthand when adding custom elements to style.
+ Added filter `llms_sessions_end_idle_cron_recurrence` to allow customization of the recurrence of the idle session cleanup cronjob.
+ Added filter `llms_quiz_is_open` to allow customization of whether or not a quiz is available to a student.
+ When adding an client-side tracking events to the always make sure the server-side verification nonce is always set on the storage object.
+ The Course/Membership filter on the main students reporting screen now correctly limits post results based on instructor access.


v3.37.1 - 2019-11-13
--------------------

+ TwentyTwenty Theme: Fixed course information block misalignment.
+ Fixed conflict with WooCommerce resulting from the movement of the deprecated LifterLMS function `is_filtered()`.


v3.37.0 - 2019-11-11
--------------------

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


v3.36.5 - 2019-11-05
--------------------

+ Add filter: `llms_user_caps_edit_others_posts_post_types` to allow 3rd parties to utilize core methods for determining if a user can manage another users LMS content on the admin panel.


v3.36.4 - 2019-11-01
--------------------

+ Fixes a conflict with CartFlows introduced by a Divi theme compatibility fix added in 3.36.3. Is WordPress complicated or what?


v3.36.3 - 2019-10-24
--------------------

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


v3.36.2 - 2019-10-01
--------------------

##### Updates

+ Tested to WordPress 5.3.0-beta.2
+ Upgrade UI on student course reporting screens.
+ Added logic to physically remove from the membership level and remove enrollments data on related products, when deleting a membership enrollment.
+ Lesson metabox "start" drip method made available only if the parent course has a start date set.

##### Bugfixes

+ Fixed JS error when client-side event tracking settings aren't loaded, thanks [@wenchen](https://github.com/wenchen)!
+ Fixed PHP warning resulting from drip the "Course Start" lesson drip settings when no course start date exists.
+ Fixed fatal error encountered when reviewing an order placed with a payment gateway that's been deactivated.

##### Files Updated

+ assets/js/app/llms-tracking.js
+ includes/admin/post-types/meta-boxes/class.llms.meta.box.lesson.php
+ includes/models/model.llms.lesson.php
+ includes/models/model.llms.student.php
+ lifterlms.php

##### Templates Updated

+ templates/admin/post-types/order-details.php
+ templates/admin/reporting/tabs/students/courses-course.php


v3.36.1 - 2019-09-24
--------------------

##### Updates

+ Include SendWP Connector in LifterLMS Engagement Settings.
+ Removed usage of `WP_Error::has_errors()` to support WordPress version prior to 5.1.
+ Improve performances when checking if an event is valid in `LLMS_Events->is_event_valid()`.
+ Remove redundant check on `is_singular()` and `is_post_type_archive()` in `LLMS_Events->should_track_client_events()`.

##### Bugfixes

+ Fixed a compatibility issue with FitVids.js causing excess white space displayed around videos when using the library, WP plugin, or themes that utilize the library.
+ Fixed an issue allowing recurring charges to continue processing after the order or customer had been deleted from the site.
+ Fixed issue causing Membership Restriction settings from properly saving.
+ Fixed issue that allowed instructors to see all quizzes on a site when the instructor had either no courses or only empty courses (courses with no lessons).
+ Fixed "Last Seen" column displaying wrong date when the student last login date was saved as timestamp.
+ Fixed an issue causing popover notifications to be skipped (never displayed) as a result of redirects.


v3.36.0 - 2019-09-16
--------------------

##### User Interaction event and session Tracking

+ Added user interaction tracking for the following events:

  + User sign in and out.
  + Page load and exit (for LMS content)
  + Page focus and blur (for LMS content)
  + And more to come

+ Interaction events are grouped into sessions automatically. A session is "closed" after 30 minutes of inactivity or a log-out event.
+ Added "Last Seen" student reporting column which reports the last recorded activity for the student.

##### Enhancements

+ Automatically hydrate when calling LLMS_Abstract_Database_Store::to_array().
+ Added CSS to make course and lesson video embeds automatically responsive.

##### Bug Fixes

+ Correctly pass the `$remember` variable when using `llms_set_person_auth_cookie()`.
+ Fixed undefined index error when retrieving an unset value from an unsaved database model.
+ Fix issue causing quotes to be encoded in shortcodes used in course and membership restriction message settings fields.
+ Fix issue preventing manual updates of order dates (next payment, trial expiration, and access expiration) from being saved properly.


v3.35.2 - 2019-09-06
--------------------

+ When sanitizing settings, don't strip tags on editor and textarea fields that allow HTML.
+ Added JS filter `llms_lesson_rerender_change_events` to lesson editor view re-render change events.


v3.35.1 - 2019-09-04
--------------------

+ Fix instances of improper input sanitization and handling.
+ Include scripts, styles, and images for reporting charts and datepickers


v3.35.0 - 2019-09-04
--------------------

##### Security Notice

+ Fixed a security vulnerability disclosed by the WordPress plugin review team. Please upgrade immediately!

##### Updates

+ Explicitly setting css and js file versions for various static assets..
+ Added data sanitization methods in various form handlers.
+ Added nonce verification to various form handlers.

##### Bug fixes

+ Fixed some translation strings that had literal variables instead of placeholders.
+ Fixed undefined index error encountered when attempting to email a voucher export.
+ Fixed undefined index error when PHP file upload errors are encountered during a course import.

##### Deprecations

The following unused classes have been marked as deprecated and will be removed from LifterLMS in the next major release.

+ LLMS_Analytics_Memberships
+ LLMS_Analytics_Courses
+ LLMS_Analytics_Sales
+ LLMS_Meta_Box_Expiration
+ LLMS_Meta_Box_Video

##### Template Updates

+  [admin/reporting/tabs/courses/overview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/courses/overview.php)
+  [admin/reporting/tabs/memberships/overview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/memberships/overview.php)
+  [admin/reporting/tabs/quizzes/attempts.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/attempts.php)
+  [admin/reporting/tabs/quizzes/overview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/overview.php)
+  [admin/reporting/tabs/students/courses-course.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/students/courses-course.php)
+  [admin/reporting/tabs/students/courses.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/students/courses.php)
+  [loop/featured-image.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop/featured-image.php)
+  [myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/view-order.php)
+  [quiz/results.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results.php)
+  [single-certificate.php](https://github.com/gocodebox/lifterlms/blob/master/templates/single-certificate.php)
+  [single-no-access.php](https://github.com/gocodebox/lifterlms/blob/master/templates/single-no-access.php)
+  [taxonomy-course_cat.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_cat.php)
+  [taxonomy-course_difficulty.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_difficulty.php)
+  [taxonomy-course_tag.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_tag.php)
+  [taxonomy-course_track.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-course_track.php)
+  [taxonomy-membership_cat.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-membership_cat.php)
+  [taxonomy-membership_tag.php](https://github.com/gocodebox/lifterlms/blob/master/templates/taxonomy-membership_tag.php)


v3.34.5 - 2019-08-29
--------------------

+ Fixed logic issues preventing pending orders from being completed.

##### Templates Changed

+ [checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)

v3.34.4 - 2019-08-27
--------------------

+ Add a new admin settings field type, "keyval", used for displaying custom html alongside a setting.
+ Added filter `llms_order_can_be_confirmed`.
+ Always bind JS for the login form handler on checkout and registration screens.

##### Templates Changed

+ [checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)

##### LifterLMS REST API v1.0.0-beta.6

+ Fix issue causing certain webhooks to not trigger as a result of action load order.
+ Change "access_plans" to "Access Plans" for better human reading.


v3.34.3 - 2019-08-22
--------------------

+ During payment gateway order completion, use `llms_redirect_and_exit()` instead of `wp_redirect()` and `exit()`.

##### LifterLMS REST API v1.0.0-beta.5

+ Load all required files and functions when authentication is triggered.
+ Access `$_SERVER` variables via `filter_var` instead of `llms_filter_input` to work around PHP bug https://bugs.php.net/bug.php?id=49184.


v3.34.2 - 2019-08-21
--------------------

##### LifterLMS REST API v1.0.0-beta.4

+ Load authentication handlers as early as possible. Fixes conflicts with numerous plugins which load user information earlier than expected by the WordPress core.
+ Harden permissions associated with viewing student enrollment information.
+ Returns a 400 Bad Request when invalid dates are supplied.
+ Student Enrollment objects return student and post id's as integers instead of strings.
+ Fixed references to an undefined function.


v3.34.1 - 2019-08-19
--------------------

+ Update LifterLMS REST to v1.0.0-beta.3

##### Interface and Experience improvements during API Key creation

+ Better expose that API Keys are never shown again after the initial creation.
+ Allow downloading of API Credentials as a `.txt` file.
+ Add `required` properties to required fields.

##### Updates

+ Added the ability to CRUD webhooks via the REST API.
+ Conditionally throw `_doing_it_wrong` on server controller stubs.
+ Improve performance by returning early when errors are encountered for various methods.
+ Utilizes a new custom property `show_in_llms_rest` to determine if taxonomies should be displayed in the LifterLMS REST API.
+ On the webhooks table the "Delivery URL" is trimmed to 40 characters to improve table readability.

##### Bug fixes

+ Fixed a formatting error when creating webhooks with the default auto-generated webhook name.
+ On the webhooks table a translatable string is output for the status instead of the database value.
+ Fix an issue causing the "Last" page pagination link to display for lists with 0 possible results.
+ Don't output the "Last" page pagination link on the last page.



v3.34.0 - 2019-08-15
--------------------

##### LifterLMS REST API v1.0.0-beta.1

+ A robust REST API is now included in the LifterLMS core.
+ Create API Keys to consume and manage LifterLMS resources and students from external applications.
+ Create webhooks to pass LifterLMS resource data to external applications (like Zapier!).
+ The full API specification can be found at [https://gocodebox.github.io/lifterlms-rest/](https://gocodebox.github.io/lifterlms-rest/).

##### Student management capabilities

+ Explicit capabilities have been added to determine which users can create, view, update, and delete students.
+ Admins and LMS Managers have all student management capabilities.
+ Instructors and instructors assistants are granted limited view capabilities allowing them to only view students enrolled in their own courses/memberships.
+ Added the `list_users` capability to the "Instructor" role, allowing instructor's to better view and manage their assistant instructors.
+ The new capabilities are: `create_students`, `view_students`, `view_others_students`, `edit_students`, `edit_others_students`, `delete_students`, & `delete_others_students`.

##### Updates

+ Added new actions to help differentiate enrollment creation and update events.
+ Added methods and logic for managing user management of other users.
+ Added a filter `llms_table_get_table_classes` to LifterLMS admin tables which allows customization of the CSS classes applied to the `<table>` elements. Thanks  [@pondermatic](https://github.com/pondermatic)!
+ Added a filter `llms_install_get_schema` to the database schema to allow 3rd parties to run table installations alongside the core.
+ Added the ability to pull "raw" (unfiltered) data from the database via classes extending the `LLMS_Post_Model` abstract.
+ Added a `bulk_set()` method to the `LLMS_Post_Model` abstract allowing the updating of multiple properties in one command.
+ Added `comment_status`, `ping_status`, `date_gmt`, `modified_gmt`, `menu_order`, `post_password` as gettable\settable post properties via the `LLMS_Post_Model` abstract.
+ Links on reporting tables are now the proper color.
+ The `editable_roles` filter which determines which roles can manage which other roles is now always loaded (instead of being loaded only on the admin panel).
+ Updated LifterLMS Blocks to 1.5.2

##### Bug Fixes

+ Fixed an issue preventing the `user_url` property from being retrieved by the `get()` method of the `LLMS_Abstract_User_Data` class.
+ Fixed an issue causing the `LLMS_Instructors::get_assistants()` method to return assistants for the currently logged in user instead of the instructor of the instantiated object.
+ Fixed an issue which would allow LMS Managers to edit and delete site administrators.

##### Deprecations

**The following functions and methods have been marked as deprecated and will be removed from LifterLMS with the next major release.**

+ LLMS_Course::get_children_sections() use LLMS_Course::get_sections( 'posts' )" instead
+ LLMS_Course::get_children_lessons() use LLMS_Course::get_lessons( 'posts' )" instead
+ LLMS_Course::get_author()
+ LLMS_Course::get_author_id() use LLMS_Course::get( "author" ) instead
+ LLMS_Course::get_author_name()
+ LLMS_Course::get_sku() use LLMS_Course::get( "sku" ) instead
+ LLMS_Course::get_id() use LLMS_Course::get( "id" ) instead
+ LLMS_Course::get_title() use get_the_title() instead
+ LLMS_Course::get_permalink() use get_permalink() instead
+ LLMS_Course::get_user_postmeta_data()
+ LLMS_Course::get_user_postmetas_by_key()
+ LLMS_Course::get_checkout_url()
+ LLMS_Course::get_start_date() use LLMS_Course::get_date( "start_date" ) instead
+ LLMS_Course::get_end_date() use LLMS_Course::get_date( "end_date" ) instead
+ LLMS_Course::get_next_uncompleted_lesson()
+ LLMS_Course::get_lesson_ids() use LLMS_Course::get_lessons( "ids" ) instead
+ LLMS_Course::get_syllabus_sections() use LLMS_Course::get_sections() instead
+ LLMS_Course::get_short_description() use LLMS_Course::get( "excerpt" ) instead
+ LLMS_Course::get_syllabus() use LLMS_Course::get_sections() instead
+ LLMS_Course::get_user_enroll_date()
+ LLMS_Course::get_user_post_data()
+ LLMS_Course::check_enrollment()
+ LLMS_Course::is_user_enrolled() use llms_is_user_enrolled() instead
+ LLMS_Course::get_student_progress() use LLMS_Student::get_progress() instead
+ LLMS_Course::get_membership_link()


v3.33.2 - 2019-06-26
--------------------

+ It is now possible to send test copies of the "Student Welcome" email to yourself.
+ Improved information logged when an error is encountered during an email send.
+ Add backwards compatibility for legacy add-on integrations priority loading method.
+ Fixed undefined index notice when viewing log files on the admin status screen.


v3.33.1 - 2019-06-25
--------------------

##### Updates

+ Added method to retrieve the load priority of integrations.
+ The capabilities used to determine if uses can clone and export courses now check `edit_course` instead of `edit_post`.

##### Bug Fixes

+ Fixed an issue which would cause the "Net Sales" line to sometimes display as a bar on the sales revenue reporting chart.
+ Fixed an issue causing a PHP notice to be logged when viewing the sales reporting screen.
+ Fixed an issue causing backslashes to be added before quotation marks in access plan descriptions.
+ Integration classes are now loaded in the order defined by the integration class.
+ Fixed an issue causing a PHP error when viewing the admin logs screen when no logs exist.


v3.33.0 - 2019-05-21
--------------------

##### Updates

+ Added the ability for site administrators to delete (completely remove) enrollment records from the database.
+ Catalogs sorted by Order (`menu_order`) now have an additional sort (by post title) to improve ordering consistency for items with the same order, thanks [@pondermatic](https://github.com/pondermatic)!
+ Hooks in the dashboard order review template now pass the `LLMS_Order`.

##### LifterLMS Blocks

+ Updated to version 1.5.1
+ All blocks are now registered only for post types where they can actually be used.
+ Only register block visibility settings on static blocks. Fixes an issue causing core (or 3rd party) dynamic blocks from being managed within the block editor.

##### Bug Fixes

+ If an enrolled student accesses checkout for a course/membership they're already enrolled in they will be shown a message stating as much.
+ Removed a redundant check for the existence of an order on the dashboard order review template.
+ When an order is deleted, student enrollment records for that order will be removed. This fixes an issue causing admins to not be able to manage the enrollment status of a student enrolled via a deleted order.
+ Fix issue causing errors when using the `[lifterlms_lesson_mark_complete]` shortcode on course post types.
+ Fixed an issue causing quiz questions to generate publicly accessible permalinks which could be indexed by search engines.

##### Templates Changed

+ [course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/templates/myaccount/view-order.php)


v3.32.0 - 2019-05-13
--------------------

##### Updates

+ Added Membership reporting
+ Added the ability to restrict coupons to courses and memberships which are in draft or scheduled status.
+ When recurring payments are disabled, output a "Staging" bubble on the "Orders" menu item.
+ Recurring recharges now add order notes and trigger actions when gateway or recurring payment status errors are encountered.
+ When managing recurring payment status through the warning notice, stay on the same page and clear nonces instead of redirecting to the LifterLMS Settings screen.
+ Updated the Action Scheduler library to the latest version (2.2.5)
+ Exposed the Action Scheduler's scheduled actions interface as a tab on the LifterLMS Status page.

##### LifterLMS Blocks

+ Updated to version 1.4.1.
+ Fixed issue causing asset paths to have invalid double slashes.
+ Fixed issue causing frontend css assets to look for an unresolvable dependency.

##### Bug Fixes

+ Fixed an issue allowing instructors to view a list of students from courses and memberships they don't have access to.
+ WooCommerce compatibility filters added in 3.31.0 are now scheduled at `init` instead of `plugins_loaded`, resolves conflicts with several WooCommerce add-ons which utilize core WC functions before LifterLMS functions are loaded.


v3.31.0 - 2019-05-06
--------------------

##### Updates

+ Tested to WordPress 5.2
+ Adds explicit support for the twentynineteen default theme.
+ The main students reporting table can now be filtered to show only students enrolled in a specific course or membership.
+ Resolve conflict with WooCommerce (3.6 and later) resulting in 404s on the dashboard endpoints "lost password", "order history", and "edit account".
+ Adds a dynamic filter (`llms_notification_view{$trigger_id}_basic_options`) to basic (pop-over) notifications to allow configuration of their settings.
+ The filter `llms_plan_get_checkout_url` now passes a 3rd parameter: `$check_availability`
+ Improves `LLMS_Course_Data` and `LLMS_Quiz_Data` classes by adding shared functionality to a shared abstract, `LLMS_Abstract_Post_Data`
+ Changed access on class methods in `LLMS_Shortcode_Courses` from private to protected, thanks [@andrewvaughan](https://github.com/andrewvaughan)!

##### Bug fixes

+ Treats `post_excerpt` data as HTML instead of plain text. Fixes an issue resulting in HTML tags being stripped from lesson excerpts when duplicating a lesson in the course builder or importing lessons via the course importer.
+ Fix an issue allowing access plan sales prices to be set as negative values.

##### LifterLMS Blocks

+ Updated to LifterLMS Blocks 1.4.0.
+ Adds an "unmigration" utility to LifterLMS -> Status -> Tools & Utilities which can be used to remove LifterLMS blocks from courses and lessons which were migrated to the block editor structure.
+ This tool is only available when the Classic Editor plugin is installed and enabled and it will remove blocks from ALL courses and lessons regardless of whether or not the block editor is being utilized on that post.

##### Deprecations

+ `LLMS_Query::add_query_vars()` use `LLMS_Query::set_query_vars()` instead.


v3.30.3 - 2019-04-22
--------------------

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


v3.30.2 - 2019-04-09
--------------------

+ Added new filter to allow 3rd parties to determine if a `LLMS_Post_Model` field should be added to the `custom` array when converting the post to an array.
+ Added hooks and filters to the `LLMS_Generator` class to allow 3rd parties to easily generate content during course clone and import operations.
+ Fixed an issue causing all available courses to display when the [lifterlms_courses] shortcode is used with the "mine" parameter and the current user viewing the shortcode is not enrolled in any courses.
+ Fixed a PHP undefined variable warning present on the payment confirmation screen.

##### Template Updates

+ [templates/checkout/form-confirm-payment.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-confirm-payment.php)


v3.30.1 - 2019-04-04
--------------------

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


v3.30.0 - 2019-03-21
--------------------

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


v3.29.4 - 2019-03-08
--------------------

+ Fixed an issue preventing users with email addresses containing an apostrophe from being able to login.


v3.29.3 - 2019-03-01
--------------------

##### Bug Fixes

+ Removed attempts to validate & save access plan data when the Classic Editor "post" form is submitted.
+ Fix issue causing 1-click free-enrollment for logged in users to refresh the screen without actually performing an enrollment.

##### Template Updates

+ [product/free-enroll-form.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/free-enroll-form.php)


v3.29.2 - 2019-02-28
--------------------

+ Fix issue causing blank "period" values on access plans from being updated.
+ Fix an issue preventing paid access plans from being switched to "Free".


v3.29.1 - 2019-02-27
--------------------

+ Automatically reorder access plans when a plan is deleted.
+ Skip (don't create) empty plans passed to the access plan save method as a result of deleted access plans.


v3.29.0 - 2019-02-27
--------------------

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


v3.28.3 - 2019-02-14
--------------------

+ ❤❤❤ Happy Valentines Day or whatever ❤❤❤
+ Tested to WordPress 5.1
+ Fixed an issue causing JSON data saved by 3rd party plugins in course or lesson postmeta fields to be not duplicate properly during course duplications and imports.


v3.28.2 - 2019-02-11
--------------------

##### Updates

+ Updated default country list to remove non-existent countries and resolve capitalization issues, thanks [nrherron92](https://github.com/nrherron92)!

##### Bug fixes

+ Fixed an issue causing the email notification content getter to use the same filter as popover notifications.
+ Fixed an issue preventing default blog date & time settings from being used when displaying an access plan's access expiration date on course and membership pricing tables.
+ Fixed an issue causing 404s on paginated dashboard endpoints when the permalink structure is set to anything other than `%postname%`.

##### Deprecations

+ `LLMS_Query->set_dashboard_pagination()`


v3.28.1 - 2019-02-01
--------------------

+ Fixed an issues preventing exports to be accessible on Apache servers.
+ Fixed an issue causing servers with certain nginx rules to open CSV exports directly instead of downloading them.


v3.28.0 - 2019-01-29
--------------------

##### Updates

+ Updated reporting table export functions to provide immediate download prompts of the files. Exports are generated in real time and you *must* remain on the page while it generates. The good news is if your site had issues with email or cronjobs it'll no longer be an issue for you.
+ Updated lesson metabox to use icons for attached quizzes
+ Added an orange highlight to the admin "Add-Ons & More" menu item
+ Removed unused cron event.

##### LifterLMS Blocks

+ Updated LifterLMS Blocks to 1.3.4
+ Adds support for handling courses & lessons in "Classic Editor" mode as defined by the Divi page builder
+ Skips course and lesson migration when "Classic" mode is enabled.
+ Adds conditions to identify "Classic" mode when the Classic Editor plugin settings are configured to enforce classic (or block) mode for *all* posts.

##### Database Updates

+ Unschedules the aforementioned unused cron event.

##### Bug fixes

+ Fixed an issue preventing the temp directory old file cleanup cron from firing on schedule.
+ During plugin uninstallation the tmp cleanup cron will now be properly unscheduled.
+ Fixed an issue causing notifications on the student dashboard to appear on top of static headers or the WP Admin Bar when scrolling.
+ Fixed an issue preventing manual updating of customer and source information on orders resulting from unfocusable hidden form fields.
+ Fixed mismatched HTML tags on the Admin Add-Ons screen

##### Deprecations

+ Class method: `LLMS_Admin_Table::queue_export()`
+ Class: `LLMS_Processor_Table_To_Csv`


v3.27.0 - 2019-01-22
--------------------

###### Updates

+ Added the ability to add existing questions to a quiz in the course builder. This allows cloning of existing questions as well as attaching "orphaned" questions currently attached to no quizzes.
+ Added the ability to detach questions from quizzes. Coupled with adding existing questions, questions can now be easily moved between quizzes.
+ Added permalink capabilities to the builder to allow linking to specific items within the builder (a lesson, quiz, etc...).
+ Quizzes with 0 possible points will no longer show a Pass/Fail chart with a 0% (failing) grade on quiz results screens.
+ Replaced option `lifterlms_lock_down` which cannot be set via any setting with a filter to reduce database calls. This will have no effect on anyone unless you manually set this option to "no" via a database query. Having done this would allow the admin bar to be shown to students.

##### Bug Fixes

+ Fixed an issue causing the default "Redeem Voucher" and "My Orders" student dashboard endpoint slugs from not having the correct default values. Thanks [@tnorthcutt](https://github.com/tnorthcutt)!
+ Fixed an issue causing quotation marks in quiz question answers to show escaping slashes on results screens.
+ Fixed a bug preventing viewing quiz results for quizzes with questions that have been deleted.
+ Fixed a bug causing a PHP Notice to be output when registering a new user with a valid voucher.

##### Templates Changed

+ [quiz/results-attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt.php)


v3.26.4 - 2019-01-16
--------------------

+ Update to [LifterLMS Blocks 1.3.2](https://make.lifterlms.com/2019/01/15/lifterlms-blocks-version-1-3-1/), fixing an issue preventing template actions from being removed from migrated courses & lessons.


v3.26.3 - 2019-01-15
--------------------

##### Updates

+ Fix issue preventing course difficulty and course length from being edited when using the classic editor plugin.
+ Improved pagination methods on Student Dashboard Endpoints
+ "My Notifications" dashboard tab now consistently paginated like other dashboard endpoints
+ Update to [LifterLMS Blocks 1.3.1](https://make.lifterlms.com/2019/01/15/lifterlms-blocks-version-1-3-1/).

##### Bug Fixes

+ Fixed an issue preventing course difficulty and course length from being edited when using various page builders.
+ Fixed issues causing errors on quiz reporting screens for quiz attempts made by deleted users.

##### Deprecated Functions

+ `LLMS_Student_Dashboard::output_notifications_content()` replaced with `lifterlms_template_student_dashboard_my_notifications()`

##### Templates Changed

+ [myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-notifications.php)
+ [admin/reporting/tabs/quizzes/attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/attempt.php)


v3.26.2 - 2019-01-09
--------------------

+ Fast follow to fix incorrect version number pushed to the readme files for 3.26.1 which prevents upgrading to 3.26.1


v3.26.1 - 2019-01-09
--------------------

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


v3.26.0 - 2018-12-27
--------------------

+ Adds conditional support for page builders: Beaver Builder, Divi Builder, and Elementor.
+ Fixed issue causing LifterLMS core sales pages from outputting automatic content (like pricing tables) on migrated posts.
+ Student unenrollment calls always bypass cache during enrollment precheck.
+ Membership post type "name" label is now plural (as it is supposed to be).


v3.25.4 - 2018-12-17
--------------------

+ Adds a filter (`llms_blocks_is_post_migrated`) to allow determining if a course or lesson has been migrated to the WP 5.0 block editor.
+ Added a filter (`llms_dashboard_courses_wp_query_args`) to the WP_Query used to display courses on the student dashboard.
+ Fixed issue on course builder causing prerequisites to not be saved when the first lesson in a course was selected as the prereq.
+ Fixed issue on course builder causing lesson settings to be inaccessible without first saving the lesson to the database.


v3.25.3 - 2018-12-14
--------------------

+ Fixed compatibility issue with the Classic Editor plugin when it was added after a post was migrated to the new editor structure.


v3.25.2 - 2018-12-13
--------------------

+ Added new filters to the `LLMS_Product` model.
+ Fix issue with student dashboard login redirect causing a white screen on initial login.


v3.25.1 - 2018-12-12
--------------------

##### Updates

+ Editor blocks now display a lock icon when hovering/selecting a block which corresponds to the enrollment visibility settings of the block.
+ Removal of core actions is now handled by a general migrator function instead of by individual blocks.

##### Bug fixes

+ Fixed issue preventing strings from the lifterlms-blocks package from being translatable.
+ Fix issue causing block visibility options to not be properly set when enrollment visibility is first enabled for a block.
+ Fixed compatibility issue with Yoast SEO Premium redirect manager settings, thanks [@moorscode](https://github.com/moorscode)!
+ Fixed typo preventing tag size options (or filters) of course information block from functioning properly. Thanks [@tnorthcutt](https://github.com/tnorthcutt)!

##### Templates Changed

+ [templates/course/meta-wrapper-start.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/meta-wrapper-start.php)


v3.25.0 - 2018-12-05
--------------------

##### WordPress 5.0 Ready!

+ **Tested with WordPress core 5.0 (Gutenberg)!**
+ Editor Blocks: Course and Lesson layouts are now (preferably) powered by various editor blocks.
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


v3.24.3 - 2018-11-13
--------------------

##### Updates

+ Added user email, login, url, nicename, display name, first name, and last name as fields searched when searching orders. Thanks Thanks [@yojance](https://github.com/yojance)!

##### Bug Fixes

+ Fixed issue causing fatal errors encountered during certificate downloading caused by CSS `<link>` tags existing outside of the `<head>` element.
+ Certificates downloaded by users who can see the WP Admin Bar will no longer show the admin bar on the downloaded certificate
+ Fixed issue on iOS Safari causing multiple choice quiz questions to require a "long press" to be properly selected
+ Fixed issue causing access plan sales to end 36m and 1s prior to end of the day on the desired sale end date. Thanks [@eri-trabiccolo](https://github.com/eri-trabiccolo)!
+ Ensure that fallback url slugs for course & membership archives are translatable.


v3.24.2 - 2018-10-30
--------------------

+ Fix issue causing newline characters to be malformed on course builder description fields, resulting in `n` characters being output in strange places.


v3.24.1 - 2018-10-29
--------------------

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


v3.24.0 - 2018-10-23
--------------------

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


v3.23.0 - 2018-08-27
--------------------

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


v3.22.2 - 2018-08-13
--------------------

+ Fixed issue causing banners on general settings screen to cause a fatal error when api connection errors occurred
+ Improved CSS on setup wizard


v3.22.1 - 2018-08-06
--------------------

+ Fix issue causing themes to appear as requiring updates when using the LifterLMS Helper


v3.22.0 - 2018-07-31
--------------------

+ Frontend notifications are no longer powered by AJAX requests. This change will significantly reduce the number of requests made but will remove the ability for students to receive asynchronous notifications. This means that notifications will only be displayed on page load as notification polling will no longer occur while a student is on a page (while reading the content a lesson, for example).
+ Course and membership catalogs items in navigation menus will now have expected CSS classes to identify current item and current item parents
+ The admin panel add-ons screen has been reworked to be powered by the lifterlms.com REST api
+ Some visual changes have been made to the add-ons screen
+ The colors on the voucher screen on the admin panel have been updated to match the rest of the interfaces in LifterLMS


v3.21.1 - 2018-07-24
--------------------

+ Fixed issue causing visual issues on checkout summary when using coupons which apply discounts to a plan trial
+ Fixed issue causing `.mo` files stored in the `languages/lifterlms` safe directory from being loaded before files stored in the default location `languages/plugins`
+ Added methods to integration abstract to allow integration developers to automatically describe missing integration dependencies
+ Tested to WordPress 4.9.8

##### Template Updates

+ [templates/checkout/form-summary.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-summary.php)


v3.21.0 - 2018-07-18
--------------------

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


v3.20.0 - 2018-07-12
--------------------

+ Updated user interfaces on admin panel for courses and memberships with relation to "Enrolled" and "Non-Enrolled" student descriptions
+ "Enrolled Student Description" is now the default WordPress editor
+ "Non-Enrolled Student Description" is now the "Sales Page"
+ Additional options for sales pages (the content displayed to visitors and non-enrolled students) have been added:
  + Do nothing (show course description)
  + Show custom content (use a WYSIWYG editor to define content)
  + Redirect to a WordPress page (use custom templates and enhance page builder compatibility and capabilities)
  + Redirect to a custom URL (use a sales page hosted on another domain!)
+ Tested to WordPress 4.9.7

v3.19.6 - 2018-07-06
--------------------

+ Fix file load paths in OptimizePress plugin compatibility function


v3.19.5 - 2018-07-05
--------------------

+ Fixed bug causing `select2` multi-selects from functioning as multi-selects
+ Fixed visual issue with `select2` elements being set without a width causing them to be both too small and too large in various scenarios.
+ Fixed duplicate action on dashboard section template

##### Template Updates

+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)


v3.19.4 - 2018-07-02
--------------------

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
+ Fixed issue causing admin panel static assets to have a double slash (//) in the asset URI path
+ Fixed issue allowing users with `view_lifterlms_reports` capability (Instructors) to access sales & enrollment reporting screens. The `view_others_lifterlms_reports` capability (Admins & LMS Managers) is now required to view these reporting tabs.
+ Updated IDs of login and registration nonces to be unique. Fixes an issue causing Chrome to throw non-unique ID warnings in the developer console. Also, IDs are supposed to be unique _anyway_ but thanks for helping us out Google.


v3.19.3 - 2018-06-14
--------------------

+ Fix issue causing new quizzes to be unable to load questions list without reloading the builder


v3.19.2 - 2018-06-14
--------------------

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
+ Fixed filter `lifterlms_reviews_section_title` which was unusable due to the incorrect usage of `_e()` within the filter. Now using `__()` as expected.
+ Fixed issue causing course featured image to display in place of lesson feature images

##### Template Updates

+ [templates/course/lesson-preview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/lesson-preview.php)
+ [templates/course/outline-list-small.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/outline-list-small.php)
+ [templates/quiz/results-attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt.php)


v3.19.1 - 2018-06-07
--------------------

+ Fixed CSS specificity issue on admin panel causing white text on white background on system status pages


v3.19.0 - 2018-06-07
--------------------

##### Updates and enhancements

+ Added a "My Memberships" tab to the student dashboard
+ "My Memberships" preview area
+ Updated admin panel order status badges to match frontend order status badges
+ Added a new recurring order status "Pending Cancel." Orders in this state will allow students to access course / membership content until the next payment is due, on this date, instead of a recurring charge being made the order will move to "Cancelled" and the student's enrollment status will change to "Cancelled" removing their access to the course or membership.
+ When a student cancels an active recurring order from the student dashboard, the order will move to "Pending Cancellation" instead of "Cancelled"
+ Students can re-activate an order that's Pending Cancellation moving the expiration date to the next payment due date
+ Added the ability to edit the access expiration date for orders with limited access settings and for orders in the "pending-cancel" state
+ Added a filter to allow customization of the URL used to generate certificate downloads from
+ When viewing taxonomy archives for any course or membership taxonomy (categories, tags, and tracks), if a term description exists, it will be used instead of the default catalog description content defined on the catalog page.
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


v3.18.2 - 2018-05-24
--------------------

+ Improved integrations settings screen to allow each integration to have it's own settings tab (page) with only its own settings
+ Allow programmatic access to notification content when notification views are accessed via filters
+ Fixed issue causing subscription cancellation notifications to be sent to admins when new orders were created
+ Fixed warning message displayed prior to membership bulk enrollment
+ Fixed multibyte character encoding issue encountered during certificate exports


v3.18.1 - 2018-05-18
--------------------

+ Attached `llms_privacy_policy_form_field()` and `llms_agree_to_terms_form_field()` to an action hook `llms_registration_privacy`
+ Define minimum WordPress version requirement as 4.8.

##### Template Updates

+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)
+ [templates/global/form-registration.php](https://github.com/gocodebox/lifterlms/blob/master/templates/global/form-registration.php)


v3.18.0 - 2018-05-16
--------------------

##### Privacy & GDPR Compliance Tools

+ Added privacy policy notice on checkout, enrollment, and registration that integrates with the WP Core 4.9.6 Privacy Policy Page setting
+ Added settings to allow customization of the privacy policy and terms & conditions notices during checkout, enrollment, and registration
+ Added suggested Privacy Policy language outlining information gathered by a default LifterLMS site

+ During a WordPress Personal Data Export request the following LifterLMS information will be added to the export

  + All personal information gathered from registration, checkout, and enrollment forms
  + Course and membership enrollments, progress, and grades
  + Earned achievements and certificates
  + All order data

+ During a WordPress Personal Data Erasure request the following LifterLMS information will be erased

  + All personal information gathered from registration, checkout, and enrollment forms
  + Earned achievements and certificates
  + All notifications for or about the user
  + If the "Remove Order Data" setting is enabled, the order will be anonymized by removing student personal information from the order and, if the order is a recurring order, it will be cancelled.
  + If the "Remove Student LMS Data" setting is enabled, all student data related to course and membership activity will be removed

+ All of the above relies on features available in WordPress core 4.9.6

##### Updates and Enhancements

+ Tested up to WordPress 4.9.6
+ Improved pricing table UX for members-only access plans. An access plan button for a plan belonging to only one membership will click directly to the membership as opposed to opening a popover. Plan's with access via multiple memberships will continue to open a popover listing all availability options.
+ Added a "My Certificates" tab to the Student Dashboard
+ Certificates can be downloaded as HTML files (available when viewing a certificate or from the certificate reporting screen on the admin panel)
+ Admins can now delete certificates and achievements from reporting screens on the admin panel
+ Added additional information to certificate and achievement reporting tables
+ Expanded widths of admin settings page setting names to be a bit wider and more readable
+ Now conditionally hiding some settings when they are no longer relevant
+ Added daily cron automatically remove files from the `LLMS_TMP_DIR` which are more that 24 hours old
+ Removed unused template `content-llms_membership.php`
+ Added initialization actions for use by integration classes

##### Bug Fixes

+ Fixed issue causing coupon reports to always display "1" regardless of actual number of coupons used
+ Fixed issue causing new posts created via the Course Builder to always be created for user_id #1
+ Fixed issue causing "My Achievements" to display twice on the My Achievements student dashboard tab
+ Fixed issue preventing lessons from being completed when a quiz in draft mode was attached to the lesson
+ Fixed issue causing minified RTL stylesheets to 404

##### Template Updates

+ [templates/admin/post-types/order-details.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/post-types/order-details.php)
+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/master/templates/content-certificate.php)
+ [templates/global/form-registration.php](https://github.com/gocodebox/lifterlms/blob/master/templates/global/form-registration.php)
+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)


v3.17.8 - 2018-05-04
--------------------

##### Updates and Enhancements

+ Added admin email notification when student cancels a subscription
+ Quiz results will now display the question's description when reviewing results as a student and on the admin panel during grading
+ Add action hook fired when a student cancels a subscription (`llms_subscription_cancelled_by_student`)
+ Reduce unnecessary DB queries for integrations by checking for dependencies and then calling querying the options table to see if the integration has been enabled.
+ Updated the notifications settings table to be more friendly to the human eye

##### Bug Fixes

+ Fix admin scripts enqueue order. Fixes issue preventing manual student enrollment selection from functioning properly in certain scenarios.
+ Shift + Enter when in a question choice field now adds a return as expected instead of exiting the field
+ When pasting into question choice fields HTML from RTF documents will be automatically stripped
+ Ensure certificates print with a white background regardless of theme CSS
+ Fix issue causing themes with `overflow:hidden` on divs from cutting certificate background images
+ Upon export completion unlock tables regardless of mail success / failure
+ Resolve issue causing incorrect number of access plans to be returned on systems that have custom defaults set for `WP_Query` `post_per_page` parameter
+ Fix error occurring when all 3rd party integrations are disabled by filter, credit to [@Mte90](https://github.com/Mte90)!
+ Ensure `LLMS()->integrations()->integrations()` returns all integrations regardless of availability.
+ Updated `LLMS_Abstract_Options_Data` to have an option set method

##### Template Updates

+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt-questions-list.php)


v3.17.7 - 2018-04-27
--------------------

+ Fix issue preventing assignments passing grade requirement from saving properly
+ Fix issue preventing builder toggle switches from properly saving some switch field data
+ Fix with "Launch Builder" button causing it to extend outside the bounds of its container
+ Fix issue with builder radio select fields during view rerenders
+ Course Outline shortcode (and widget) now retrieve parent course of the current page more consistently with other shortcodes
+ Added ability to filter which custom post types which can be children of a course (allows course shortcodes & widgets to be used in assignment sidebars of custom content areas)


v3.17.6 - 2018-04-26
--------------------

+ Updated language on recurring orders with no expiration settings. Orders no longer say "Lifetime Access" and instead output no expiration information
+ Quiz editor on builder updated to be consistent visually and functionally to the lesson settings editor
+ Improved the builder field API to allow for radio element fields
+ Fix issue causing JS error on admin settings pages
+ Updated CSS for Certificates to be more generally compatible with theme styles when printed
+ Allow system print settings to control print layout for certificates by removing explicit landscape declarations
+ Now passing additional data to filters used to create custom columns on reporting screens
+ Remove unused JS files & Chosen JS library
+ Added filter to allow opting into alternate student dashboard order layout. Use `add_filter( 'llms_sd_stacked_order_layout', '__return_true' )` to stack the payment update sidebar below the main order information. This is disabled by default.
+ Achievement and Certificate basic notifications now auto-dismiss after 10 seconds like all other basic notifications
+ Deprecated Filter `llms_get_quiz_theme_settings` and added backwards compatible methods to transition themes using this filter to the new custom field api. For more information see new methods at https://lifterlms.com/docs/course-builder-custom-fields-for-developers/
+ Increased default z-index on notifications to prevent notifications from being hidden behind floating / static navigation menus


##### Template Updates

+ [templates/myaccount/my-orders.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-orders.php)
+ [templates/myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/view-order.php)


v3.17.5 - 2018-04-23
--------------------

##### Admin Settings Interface Improvements

+ Improved admin settings page interface to allow for section navigation
+ Updated checkout setting pages to utilize a separate section (page) for each available payment gateway
+ Added a table of payment gateways to see at a glance which gateways are enabled and allows drag and drop reordering of gateway display order
+ Moved dashboard endpoints to a separate section on the accounts settings area
+ Updated CSS on settings page to have more regular spacing between subtitles and settings fields
+ Added a "View" button next to any admin setting post/page selection field to allow quick viewing of the selected post
+ Purchase page setting field is now ajax powered like all other page selection settings
+ Renamed dashboard settings section titles to be more consistent with language in other areas of LifterLMS
+ All dashboard endpoints now automatically sanitized to be URL safe

##### Updates and Enhancements

+ Dashboard endpoints can now be deregistered by setting the endpoint slug to be blank on account settings

##### Bug Fixes

+ Fix issue causing 404s for various script files when SCRIPT_DEBUG is enabled
+ Fix issue with audio & video embeds to prevent fallback to default post attachments
+ Fix issue causing student selection boxes to malfunction due to missing dependencies when loaded over slow connections

##### Template Updates

+ [templates/myaccount/navigation.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/navigation.php)


v3.17.4 - 2018-04-17
--------------------

+ Added core RTL language support
+ Fixed fatal error on student management tables resulting from deleted admin users who manually enrolled students
+ Added filter to allow 3rd parties to disable achievement dupchecking (`llms_achievement_has_user_earned`)
+ Added {student_id} merge code which can be utilized on certificates
+ Added merge code insert button to certificates editor
+ Added filter to allow 3rd parties to disable certificate dupchecking (`llms_certificate_has_user_earned`)
+ Added filter to allow 3rd parties to add custom merge codes to certificates (`llms_certificate_merge_codes`)
+ Fix restriction check issue for lessons with drip or prerequisites on course outline widget / shortcode
+ Bumped WP tested to version to 4.9.5

##### Template Updates

+ [templates/course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)
+ [templates/course/outline-list-small.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/outline-list-small.php)


v3.17.3 - 2018-04-11
--------------------

+ Course and Membership instructor metabox search field now correctly states "Select an Instructor" instead of previous "Select a Student"
+ Added missing translation for "Select a Student" on admin panel student selection search fields
+ Fix issue causing reporting export CSVs to throw a SYLK interpretation error when opened in Excel
+ Fix issue causing drafted courses and memberships to be published when the "Update" button is clicked to save changes
+ Remove use of PHP 7.2 deprecated `create_function`
+ Fix errors resulting from quiz questions which have been deleted
+ Fix issue causing current date / time to display as the End Date for incomplete quiz attempts on quiz reporting screens

##### Template Updates

+ [templates/admin/reporting/tabs/quizzes/attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/attempt.php)
+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt-questions-list.php)


v3.17.2 - 2018-04-09
--------------------

+ Fixed issue preventing lesson video and audio embeds from being *removed* when using the course builder settings editor
+ Fixed issue causing question images to lose the image source
+ Updated student management table for courses and memberships to show the name (and a link to the user profile) of the site user who manually enrolled the student.
+ Add "All Time" reporting to various reporting filters
+ Added API for builder fields to enable multiple select fields
+ Fix memory leak related to assignments rendering on course builder
+ Fix issue causing course progress and enrollment checks to incorrectly display progress data cached for other users
+ Lesson progression actions (Mark Complete & Take Quiz buttons) will now always display to users with edit capabilities regardless of enrollment status

##### Template Updates

+ [templates/course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)
+ [templates/course/outline-list-small.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/outline-list-small.php)


v3.17.1 - 2018-03-30
--------------------

+ Refactored lesson completion methods to allow 3rd party customization of lesson completion behavior via filters and hooks.
+ Remove duplicate lesson completion notice implemented. Only popover notifications will display now instead of popovers and inline messages.
+ Object completion will now automatically prevent multiple records of completion from being recorded for a single object.
+ Lesson Mark Complete button and lessons completed by quiz now utilizes a generic trigger to mark lessons as complete: `llms_trigger_lesson_completion`.
+ Removed several unused functions from frontend forms class
+ Moved lesson completion form controllers to their own class

##### Templates updates

+ [templates/course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)


v3.17.0 - 2018-03-27
--------------------

##### Builder Updates

+ Moved action buttons for each lesson (for opening quiz and lesson editor) to be static below the lesson title as opposed to only being visible on hover
+ Added new audio and video status indicator icons for each lesson
+ Various status indicator icons will now have different icons in addition to different colors depending on their state
+ Replaced "pencil" icons that open the WordPress post editor with a small "WP" icon
+ Added several actions and filters to backend functions so that 3rd parties can hook into builder saves
+ Added lesson settings editing to the builder. Lesson settings can now be updated from settings metaboxes on the lesson post edit screen AND on the builder.
+ Added prerequisite validation for lessons to prevent accidental impossible prerequisite creating (eg: Lesson 5 can never be a prerequisite for Lesson 4)
+ Added functions and filters to allow 3rd parties to add custom fields to the builder. For more details see [an example](https://lifterlms.com/docs/course-builder-custom-fields-for-developers/).
+ Fixed issue causing changes made in "Text" mode on content editors wouldn't trigger save events
+ Fixed issue causing lesson prerequisites to not properly display on the course builder
+ Fixed CSS z-index issues related to builder field tooltip displays
+ Removed unused Javascript dependencies

##### Bug Fixes

+ Fixed typo on filter on quiz question image getter function

##### Updates

+ Performance improvements made to database queries and functions related to student enrollment status and student course progress queries. Thanks to [@mte90](https://github.com/Mte90) for raising issues and testing solutions related to these updates and changes!
+ Added PHP Requires plugin header (5.6 minimum)
+ Added HTTP User Agent data to the system report
+ [LifterLMS Assignments Beta](https://lifterlms.com/product/lifterlms-assignments?utm_source=LifterLMS%20Plugin&utm_medium=CHANGELOG&utm_campaign=assignments%20preorder) is imminent and this release adds functionality to the Builder which will be extended by Assignments upon when availability


v3.16.16 - 2018-03-19
---------------------

+ Fixed builder issue causing multiple question choices to be incorrectly selected
+ Fixed builder issue with media library uploads causing an error message to prevent new uploads before the quiz or question has been persisted to the database
+ Fixed builder issue preventing quizzes from being deleted before they were persisted to the database
+ Fixed builder issue causing autosaves to interrupt typing and reset lesson and section titles
+ Fixed JS console error related to LifterLMS JS dependency checks


v3.16.15 - 2018-03-13
---------------------

##### Quiz Results Improvements and fixes

+ Improved quiz result user and correct answer handling functions for more consistent HTML output
+ Result answers (correct and user) will display as lists
+ image question types will display without bullets and will "float" next to each other
+ Fixed issue causing quiz results with multiple answers from outputting all HTMLS with no spaces between them

##### Quiz Grading

+ Fixed issue causing advanced reorder and reorder question types from being graded incorrectly in some scenarios
+ Advanced fill in the blank questions are now case insensitive. Case sensitivity can be enabled with a filter: `add_filter( 'llms_quiz_grading_case_sensitive', '__return_true' )`

##### Fixes

+ Updated spacing and returns found in the email header and footer templates to prevent line breaks from occurring in undesirable places on previews of HTML emails in mobile email clients
+ Added options for themes to add layout support to quizzes where the custom field utilizes an underscore at the beginning of the field key
+ Fixed CSS issue causing blanks of fill in the blanks to not be visible on the course builder when using Chrome on Windows
+ Removed unnecessary `get_option()` call to unused option `lifterlms_permalinks`
+ Updated permissions required to see various LifterLMS post types to rely on `manage_lifterlms` capabilities as opposed to `manage_options`
  + This will only affect the LMS Manager core role or any custom role which was provided with the `manage_options` capability. Manages will now be able to access all LMS content and custom roles would now not be able to access LMS content
  + Affected content types are: Orders, Coupons, Vouchers, Engagements, Achievements, Certificates, and Emails
+ Several references to an option removed in LifterLMS 3.0 still existed in the codebase and have now been removed.
  + Option `lifterlms_course_display_banner` is no longer called or referenced
  + Template function `lifterlms_template_single_featured_image()` has been removed
  + Actions referencing `lifterlms_template_single_featured_image()` have been removed
  + Template function `lifterlms_get_featured_image_banner()` has been removed
  + Template `templates/course/featured-image.php` has been removed

##### Templates updates

+ [quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt-questions-list.php)


v3.16.14 - 2018-03-07
---------------------

+ Courses reporting table now includes courses with the "Private" status
+ Fixed issue causing some achievement notifications to be blank
+ Added tooltips to question choice add / delete icon buttons
+ Quiz results meta information elements now have unique CSS classes
+ Removed reliance PHP 7.2 deprecated function `create_function()`
+ Fixed invalid PHP 7.2 syntax creating a warning found on the setup wizard
+ Fixed undefined index error related to admin notices
+ Fixed untranslatable string on Users table ("No Memberships")
+ Fixed discrepancy between membership restrictions as presented to logged out users and logged in users who cannot access membership
+ Fixed FireFox and Edge issue causing changes to number inputs made via HTML5 input arrows from properly triggering save events


v3.16.13 - 2018-02-28
---------------------

+ Hotfix: Only create quizzes on the builder if quizzes exist on the lesson


v3.16.12 - 2018-02-27
---------------------

+ Quizzes can now be detached (removed from a lesson) or deleted (deleted from the lesson and the database) via the Course Builder
+ Improved question choice randomization to ensure randomized choices never display in their original order.
+ When a lesson is deleted, any quiz attached to the lesson will become an orphan
+ When a lesson is deleted, any lesson with this lesson as a prerequisite will have it's prerequisite data removed
+ When a quiz is deleted, all questions attached to the quiz will also be deleted
+ When a quiz is deleted, the lesson associated with the quiz will have those associations removed
+ Fixed grammar issue on restricted lesson tooltips when no custom message is stored on the course.
+ Updated functions causing issues in PHP 5.4 to work on PHP 5.4. This has been done to reduce frustration for users still using PHP 5.4 and lower; [This does not mean we advocate using software past the end of its life or that we support PHP 5.4 and lower](https://lifterlms.com/docs/minimum-system-requirements-lifterlms/).


v3.16.11 - 2018-02-22
---------------------

+ Course import/exports and lesson duplication now carry custom meta data from 3rd party plugins and themes
+ Added course completion date column to Course reporting students list
+ Restriction checks made against a quiz will now properly cascade to the quiz's parent lesson
+ Fixed issue preventing featured images from being exported with courses and lessons
+ Fixed duplicate lesson issue causing quizzes to be double assigned to the old and new lesson
+ Fixed issue allowing blog archive to be viewed by non-members when sitewide membership is enabled
+ Fixed builder issue causing data to be lost during autosaves if data was edited during an autosave
+ Fixed builder issue preventing lessons from moving between sections when clicking the "Prev" and "Next" section buttons
+ Added actions to `LLMS_Generator` to allow 3rd parties to extend core generator functionality


v3.16.10 - 2018-02-19
---------------------

+ Content added to the editor of course & membership catalog pages will now be output *above* the catalog loop
+ Fix issue preventing iframes and some shortcodes from working when added to a Quiz question description
+ Added new columns to the Quizzes reporting table to display Course and Lesson relationships
+ Improved the task handler of background updater to ensure upgrade functions that need to run multiple times can do so
+ Fixed JS Backup confirmation dialog on the background updater.
+ Add support for 32-bit systems in the `LLMS_Hasher` class
+ Fix issue causing HTML template content to be added to lessons when duplicating an existing lesson within the course builder

##### 3.16.0 migration improvements

+ Accommodates questions imported by 3rd party Excel to LifterLMS Quiz plugin. Fixes an issue where choices would have no correct answer designated after migration.
+ All migration functions now run on a loop. This improves progress reporting of the migration and prevents timeouts on mature databases with lots of quizzes, questions, and/or attempts.
+ Fix an issue that caused duplicate quizzes or questions to be created when the "Taking too long?" link was clicked


v3.16.9 - 2018-02-15
--------------------

+ Fix issue causing error on student dashboard when reviewing an order with an access plan that was deleted.
+ Fixed spelling error on course metabox
+ Fixed spelling error on frontend quiz interface
+ Fixed issues with 0 point questions:
  + Will no longer prevent quizzes from being automatically graded when a 0 point question is in an otherwise automatically gradable quiz
  + Point value not editable during review
  + Visual display on results displays with grey background not as an orange "pending" question
+ Table schema uses default database charset. Fixes an issue with databases that don't support `utf8mb4` charsets.
+ Updated `LLMS_Hasher` class for better compatibility with older versions of PHP


v3.16.8 - 2018-02-13
--------------------

##### Updates

+ Added theme compatibility API so theme developers can add layout options to the quiz settings on the course builder. For details on adding theme compatibility see: [https://lifterlms.com/docs/quiz-theme-compatibility-developers/](https://lifterlms.com/docs/quiz-theme-compatibility-developers/).
+ Quiz results "donut" chart had alternate styles for quizzes pending review (Dark grey text rather than red). You can target with the `.llms-donut.pending` CSS class to customize appearance.
+ Allow filtering when retrieving student answer for a quiz attempt question via `llms_quiz_attempt_question_get_answer` filter

##### Bug Fixes

+ Fix issues causing conditionally gradable question types (fill in the blank and scale) from displaying without a status icon or possible points when awaiting admin review / grading.
+ Fix issue preventing conditionally gradable question types (fill in the blank and scale) from being reviewable on the admin panel when the question is configured as requiring manual grading.
+ Fix analytics widget undefined index warning during admin-ajax calls. Thanks [@Mte90](https://github.com/Mte90)!
+ Fix issue causing `is_search()` to be called incorrectly. Thanks [@Mte90](https://github.com/Mte90)!
+ Fix issue preventing text / html formatting from saving properly for access plan description fields
+ Fix html character encoding issue on reporting widgets causing currency symbols to display as a character code instead of the symbol glyph.

##### Templates changed

+ templates/quiz/results-attempt-questions-list.php
+ templates/quiz/results-attempt.php


v3.16.7 - 2018-02-08
--------------------

+ Added manual saving methods for the course builder that passes data via standard ajax calls. Allows users (hosts) to disable the Heartbeat API but still save builder data.
+ Added an "Exit" button to the builder sidebar to allow exiting the builder back to the WP Edit Post screen for the current course
+ Added dashboard links to the WP Admin Bar to allow existing the course builder to various areas of the dashboard
+ Added data attribute to progress bars so JS (or CSS) can read the progress of a progress bar. Thanks [@dineshchouhan](https://github.com/dineshchouhan)!
+ Fixed issue causing newly created lessons to lose their assigned quiz
+ Fixed php `max_input_vars` issue causing a 400 Bad Request error when trying to save large courses in the course builder
+ Removed reliance on PHP bcmath functions


v3.16.6 - 2018-02-07
--------------------

+ Removed reliance on PHP Hashids Library in favor of a simpler solution with no PHP module dependencies
+ Added interfaces to allow customization of quiz url / slug
+ Fixed [audio] shortcodes added to quiz question descriptions
+ Fixed untranslatable strings on frontend of quizzes
+ Fix issue causing certificate notifications to display as empty
+ Fix issue preventing quiz pass/fail notifications from triggering properly for manually graded quizzes
+ Fix undefined index warning on quiz pass/fail notifications


v3.16.5 - 2018-02-06
--------------------

+ Fix issue preventing manually graded quiz review points from saving properly
+ Improved background updater to ensure scripts don't timeout during upgrades
+ Admin builder JS now minified for increased performance
+ Made frontend quiz and quiz-builder strings output via Javascript translatable


v3.16.4 - 2018-02-05
--------------------

+ Fix issue causing newly created quizzes to not be properly related to their parent lesson
+ Fix issue preventing quiz time limits from starting unless an attempt limit is also set
+ Fixes a WP Engine issue that prevented the builder from loading due to a blocked dependency


v3.16.3 - 2018-02-02
--------------------

+ When switching a quiz to "Published" it will now update the parent lesson to ensure it's recorded as having an enabled quiz.
+ Declared the WordPress heartbeat API script as a dependency for the Course Builder JS. It seems that some servers and hosts dequeue the heartbeat when not explicitly required. This resolves a saving issue on those hosts.
+ Added a Quiz Description content editor under quiz settings. This is the "Editor" from pre 3.16.0 quizzes and any content saved in these fields is now available in this description field
+ Fixed issue causing points percentage calculation tooltip on quiz builder to show the incorrect percentage value
+ Fix issue preventing lessons with no drip settings from being updated on the WP post editor
+ Fix issue causing 500 error on lesson settings metabox for lessons not attached to sections
+ Add a "Quiz Description" field to allow quiz post content to be edited on the quiz builder
+ Added a database migration script to ensure quizzes migrated from 3.16 and lower that had quiz post content to automatically have the optional quiz description to be enabled


v3.16.2 - 2018-02-02
--------------------

+ Add an update notice to 3.16.0 migration scripts to provide more information about the major update.
+ Removed quiz assignment fields on the lesson metabox to reduce confusion as quizzes are now managed exclusively on the quiz builder.
+ Ensure questions migrated during 3.16 updates retain their initial points value from the quiz.


v3.16.1 - 2018-02-01
--------------------

+ Ensure quizzes in draft mode are only accessible by those with edit access (instructors, admins, etc...)
+ Restore pre 3.16 actions and filters related to quiz start buttons
+ Remove legacy error message for quiz accessibility issues by site admins
+ Students who cannot access a quiz are redirected to the parent lesson if they attempt to access a quiz directly
+ Fix undefined index warning on wp-login.php related to LifterLMS js assets. Thanks [Mte90](https://github.com/Mte90)!
+ Update checkout error message to provide user with direction when they already have access to a course. Thanks [@andreasblumberg](https://github.com/andreasblumberg)!


v3.16.0 - 2018-02-01
--------------------

##### Quizzes

+ New question types: True/False, Picture Choice, and Non-question content
+ Picture & Multiple choice have options for multiple correct answers (checkbox-like questions)
+ You can now create questions with NO POINTS (maybe for surveys?)
+ Upgraded student quiz review interface
+ Upgraded instructor quiz attempt review interface
+ Admins may now leave remarks on questions directly
+ Improved data available related to quizzes and quiz attempts on reporting screens
+ Improved quiz user interface
+ Added a progress bar to the quiz interface
+ Shrunk the quiz timer
+ Added a question # counter on the quiz interface
+ Fixed issue causing randomized questions to get "lost" when navigating back through a quiz attempt
+ Improved error handling on quizzes
+ Overhauled quiz data structure for improved performance and scalability
+ Requires database migration and update: [3.16.0](https://lifterlms.com/docs/lifterlms-database-updates/#3160)

##### Course Builder Improvements

+ Quiz-building is now available on the course builder
+ Quiz and Question WordPress editors no longer available. Quizzes and Questions HAVE NOT DISAPPEARED, they've been improved and relocated
+ All hooks & filters attached to `the_content` and `the_title` are now being removed when loading the course builder. This should prevent infinite spinners on builder loading and builder AJAX calls due to third-parties accidentally outputting html during these events.

##### Updates

+ Added space between arrows and "Next" and "Previous" text on pagination lists. Thanks [sujaypawar](https://github.com/sujaypawar)!
+ Updated Quiz post type slug from "llms_quiz" to "quiz".
+ Updated default return of `llms_get_post()` to be `false` rather than a `WP_Post` object when a LifterLMS post cannot be located

##### Bug Fixes

+ Fixed a potential database read error related to database store abstract
+ Now passing Post ID as second parameter to the `the_title` filter called on post model getters


##### Removed templates

The following quiz templates have been removed. Customization of these templates causes quiz application functionality to break and they should not have been available for customization but were due to oversights. This has been corrected.

+ templates/content-single-question-after.php
+ templates/content-single-question-before.php
+ templates/quiz/next-question.php
+ templates/quiz/previous-question.php
+ templates/quiz/question-count.php
+ templates/quiz/quiz-question.php
+ templates/quiz/single-choice.php
+ templates/quiz/single-choice_ajax.php
+ templates/quiz/summary.php
+ templates/quiz/timer.php
+ templates/quiz/wrapper-end.php
+ templates/quiz/wrapper-start.php

##### Removed Functions

Various template functions related to quizzes were removed due to the deprecation of their related templates

+ `lifterlms_template_quiz_timer()`
+ `lifterlms_template_single_next_question()`
+ `lifterlms_template_single_prev_question()`
+ `lifterlms_template_single_single_choice()`
+ `lifterlms_template_single_single_choice_ajax()`
+ `lifterlms_template_single_question_count()`


v3.15.1 - 2017-12-05
--------------------

+ Ensure course & membership titles with HTML characters are decoded during reporting exports
+ Fix issue causing some courses to display in membership columns on reporting exports


v3.15.0 - 2017-12-04
--------------------

##### Reporting Updates (and CSV exports!)

+ Added course-level reporting table (see "Courses" tab of Reporting screen)
+ Updated the interface on reporting screen when reviewing a single student
+ Added reporting exports: students list, courses list, and list of students per course

##### Bug fixes

+ Fix error when `[lifterlms_course_continue_button]` shortcode is displayed to logged out or students not enrolled in the chosen course

##### Minor updates

+ Tested up to WordPress 4.9.1
+ Added background data processors to ensure reporting data stays up to date in close to real time
+ Add nocache constants and headers on student dashboard & checkout page to increase compatibility with caching plugins
+ Added filter to student dashboard courses query


v3.14.9 - 2017-11-27
--------------------

+ Tested up to WordPress 4.9
+ Fix error during uninstall related to missing file
+ Fix issue with rewinding quiz using "Previous Question" button
+ On final question of a quiz the "Next Lesson" button now says "Complete Quiz"
+ When completing a quiz, the loading message will now say "Grading Quiz" the entire time instead of "Loading Question" and then "Grading Quiz"
+ Fix issue causing the `<title>` element on course builder pages from being partially empty


v3.14.8 - 2017-11-06
--------------------

+ Lessons can be cloned via the "Clone" action from the lessons post table

##### Builder Improvements & Fixes

+ Add "Existing Lesson" functionality can now clone and attach the clone (when adding a lesson currently attached to a course) OR attach orphans
+ Lessons created via Course builder will have their slugs renamed the first time the lesson title is updated via the builder
+ No longer display notices on the course builder
+ Add extra space to the scrollable area on course builder
+ Removed logging and debugging functions from admin builder class
+ JS-generated error messages on the course builder are now translatable

##### Bug Fixes

+ Fix: Show all memberships on dashboard


v3.14.7 - 2017-10-25
--------------------

##### Navigation Menu Items

+ Add LifterLMS endpoints to your nav menu
+ Add Sign In and Sign Out links which display conditionally based on whether or not the visitor is logged in
+ Checkout the docs at [https://lifterlms.com/docs/lifterlms-navigation-menu-items/](https://lifterlms.com/docs/lifterlms-navigation-menu-items/)

##### Bug Fixes

+ Fix SQL query issue with orphaned lesson query on course builder
+ Fix undefined index warning occurring during theme switches
+ Fix issue causing duplicate error messages to display on certain servers


v3.14.6 - 2017-10-21
--------------------

+ Fix: `<iframes>` are no longer stripped when exporting or duplicating courses (this applies to lessons within the courses as well)
+ Fix: Achievements on student dashboard now output the correct achievement title
+ Fix: Courses on student dashboard ordered by Order attributes will obey settings correctly


v3.14.5 - 2017-10-14
--------------------

+ Course builder will persist open/collapsed state of sections when they are re-ordered
+ Course builder lessons in a section are draggable after reordering a section


v3.14.4 - 2017-10-13
--------------------

+ You were right and we were wrong & we are sorry. This update returns the ability to add existing lessons to a course via the course builder.
+ Lessons added to a section will no longer visually disappear when editing a section title on the course builder
+ BuddyPress integration BP template fixes


v3.14.3 - 2017-10-12
--------------------

+ Fix [lifterlms_my_account] shortcode issue affecting Divi theme users


v3.14.2 - 2017-10-11
--------------------

+ Instructor query utilizes correct `$wpdb->prefix` for filtering by role instead of `wp_` which will not work when the `$table_prefix` in wp-config.php is customized
+ include the admin notices class when running database update functions


v3.14.1 - 2017-10-10
--------------------

+ Fix `[lifterlms_my_achievements]` shortcode
+ Fix reference to deprecated core function related to checking the permissions of content restricted to a membership
+ Builder titles will be saved on all field focusout/blur events, not just tab & enter key presses
+ LifterLMS custom meta save metaboxes will not trigger actions during ajax requests
+ Fix issue displaying certificates on admin panel reporting screens


v3.14.0 - 2017-10-10
--------------------

+ Updated JS for 3.13 course builder to address issues on PHP 5.6 servers with asp_tags enabled
+ Normalized date returns with various dates related to enrollments, achievements, and certificates. These dates now utilize the WP Core `date_format` option.
+ Fixed strict comparison issue related to database query abstract (affected checks for last page & first page on admin reporting screens)
+ Added a new capability `llms_instructor` for admins, lms managers, instructors, and instructor's assistant to easily differentiate "instructors" from "students"
+ Fix `$wpdb->prepare` issue related to notification queries. Fixes WP 4.9-beta issue.

##### Student Dashboard Updates

+ Achievements on student dashboard now viewable in popover modal.
+ Achievements tab added to student dashboard
+ Courses, Memberships, Achievements, and Certificates have been updated to have a unified style
+ Courses & Memberships extend the default catalog tiles
+ Courses shortcode has new parameters useful for displaying a list of a specific users courses only. [More info](https://lifterlms.com/docs/shortcodes/#lifterlms_courses)

##### Deprecated functions

+ `LLMS_Student_Dashboard::output_courses_content()` replaced with `lifterlms_template_student_dashboard_my_courses( false )`
+ `LLMS_Student_Dashboard::output_dashboard_content` replaced with `lifterlms_template_student_dashboard_home()`

##### Template Updates

+ [achievements/loop.php](https://github.com/gocodebox/lifterlms/blob/master/templates/achievements/loop.php)
+ [achievements/template.php](https://github.com/gocodebox/lifterlms/blob/master/templates/achievements/template.php)
+ [certificates/loop.php](https://github.com/gocodebox/lifterlms/blob/master/templates/certificates/loop.php)
+ [certificates/preview.php](https://github.com/gocodebox/lifterlms/blob/master/templates/certificates/preview.php)
+ [loop.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop.php)
+ [loop/content.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop/content.php)
+ [loop/enroll-date.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop/enroll-date.php)
+ [loop/enroll-status.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop/enroll-status.php)
+ [loop/pagination.php](https://github.com/gocodebox/lifterlms/blob/master/templates/loop/pagination.php)
+ [myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)
+ [myaccount/dashboard.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard.php)
+ [myaccount/header.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/header.php)

##### Deleted Templates

+ /myaccount/my-achievements.php
+ /myaccount/my-courses.php
+ /myaccount/my-memberships.php


v3.13.1 - 2017-10-04
--------------------

+ Fix caching issue preventing quiz pass & fail engagements from triggering.
+ Fix issue causing the "Builder" link to display on the lesson post table screen.
+ Fix issue preventing new courses & memberships from being moved from draft -> published.
+ Fix `wpdb->prepare()` empty placeholder issue related to engagement queries. Fixes warning added in WP 4.9.
+ Add better version numbering to static assets to prevent caching issues during plugin updates


v3.13.0 - 2017-10-02
--------------------

##### An All New Course Builder

+ The "Course Outline" metabox found on the admin panel when editing any LifterLMS course has been savagely beaten. We stole its lunch money and we put it towards the construction of an all interface
+ Asynchronous loading: fixes issues where very large courses would drastically slow and possibly even time out the loading of the course edit screen
+ Course outline is now collapsible and expandable. This Fixes issues where it was very hard to move lessons and sections around on very large courses
+ In addition to the familiar (and now improved) drag and drop functionality, you may now also move sections and lessons up and down with button clicks. You can also move lessons between sections with button clicks
+ Add new lessons and sections with a click or drag a new lesson or section into the existing course
+ Edit section and lesson titles faster with inline title editing. No more modals with a potentially slow ajax load to update a title. Click the title, change it, and exit the field to automatically save!
+ Delete sections and lessons with the click of a button
+ Quick links to view (frontend) and edit (backend) lessons
+ Completely internationalized. Thanks for you patience translators!
+ Want to know more? Check out the [docs](https://lifterlms.com/docs/using-course-builder/).

##### New User Roles

+ Added new roles to enable you to provide access to LifterLMS (settings, courses building, etc...) without having to make an admin or mess with complicated code snippets.
+ New Roles:

  + LMS Manager: Do everything in LifterLMS and nothing with plugins, themes, core settings, and so on
  + Instructor: Create, update, and delete courses and memberships
  + Instructor's Assistant: Edit courses and memberships

+ More details and a full list of new LifterLMS capabilities are available [here](https://lifterlms.com/docs/roles-and-capabilities/).

##### Updates & Fixes

+ Tested up to WordPress 4.8.2
+ The "Lesson Tree" metabox has been replaced with a simplified version of the lesson tree and a link to the launch the Course Builder.
+ Course and membership categories and tags will now display on their respective post tables for sorting and filtering. They can be disabled on a per-user basis via the screen options.
+ Removed `var_dump()` from bbPress integration restriction check

##### Uninstall Script

+ Uninstall script now removes all the things LifterLMS creates in your database if a constant is defined. Read more [here](https://lifterlms.com/docs/remove-lifterlms-data-plugin-uninstallation/).

##### Database Update

+ Adds default Instructor data for all LifterLMS Courses & Memberships based off of the post author of the course or membership
+ [More information](https://lifterlms.com/docs/lifterlms-database-updates/#3130)

##### Template Updates

+ [admin/post-types/students.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/post-types/students.php)
+ [admin/reporting/tabs/students/courses.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/students/courses.php)

##### Deprecated Functions

+ The following AJAX functions are no longer utilized by LifterLMS core. If you are utilizing them find alternatives (they all exist). These will be remove in the next **major** release:

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

##### Removed Filters

+ The following filters have been removed and are no longer in use.

  + `lifterlms_admin_courses_access`: replaced with user capability `edit_courses`
  + `lifterlms_admin_membership_access`: replaced with user capability `edit_memberships`
  + `lifterlms_admin_reporting_access`: replaced with user capability `manage_lifterlms`
  + `lifterlms_admin_settings_access`: replaced with user capability `manage_lifterlms`
  + `lifterlms_admin_import_access`: replaced with user capability `manage_lifterlms`
  + `lifterlms_admin_system_report_access`: replaced with user capability `manage_lifterlms`


v3.12.2 - 2017-09-18
--------------------

##### Bug fixes

+ Fix issue with LifterLMS bbPress integration preventing course-restricted topics from being accessible by enrolled students
+ Fix an issue preventing students expired from courses via access expiration settings from being manually re-enrolled by admins

##### Deprecations

+ `LLMS_Student` class function `has_access` is scheduled for deprecation in next major release. Developers should switch to `LLMS_Student->is_enrolled()`


v3.12.1 - 2017-08-25
--------------------

+ Prevent duplicate loading of repeater metabox fields
+ Fix undefined warning related to quiz completion
+ Ensure that the bbPress course forums shortcode & widget properly cascade up when used on a lesson or quiz


v3.12.0 - 2017-08-17
--------------------

+ New quiz feature: randomize the order of quiz questions each attempt! Props to [Larry Groebe](https://github.com/larrygroebe)
+ Fixed logic error related to access checks when bubbling from quiz->lesson->course
+ Fixed JS loader check for tinyMCE editors in repeater fields
+ Fixed CSS issue related to tinyMCE editors in repeater fields
+ Fixed issue causing tinyMCE editors in repeater fields to stop working after reordering rows
+ LifterLMS alert box notices are now cleared during shutdown instead of immediately after rendering. Fixes some plugin compatibility issues.
+ Fix reference to invalid meta key on order notes admin screen.
+ Record order note when orders with a defined length complete
+ When a payment is scheduled for an order with a defined length, calculate end date if no end date is saved
+ Minor updates to the `LLMS_Abstract_Integration` class
+ Fix undefined reference error on 404 pages resulting from the preview manager.

##### bbPress Integration Updates

+ Add "Private" Course Forums which allows forums to be made available only to students enrolled in the associated course
+ Adds a shortcode and widget for outputting a list of forums associated with a course
+ Adds the ability to restrict the page set as the bbPress forum index (via bbPress settings) to be restricted to LifterLMS memberships
+ Adds engagement triggers to allow engagements to be fired when a student posts a reply or creates a new topic
+ Improves integration membership restriction check performance
+ Migrated to the `LLMS_Abstract_Integration` class. Visually changes the settings display but has no other impact
+ [More information](https://lifterlms.com/docs/lifterlms-and-bbpress/)

##### BuddyPress Integration Updates

+ Add the ability to restrict activity, group, and member directory pages to LifterLMS memberships.
+ Migrated to the `LLMS_Abstract_Integration` class. Visually changes the settings display but has no other impact
+ [More information](https://lifterlms.com/docs/lifterlms-and-bbpress/)

##### Database update

+ calculate and store end dates for orders created prior to version 3.11.0 which have a defined length and do not have a stored end date.
+ migrate bbPress and BuddyPress options to `LLMS_Abstract_Integration` naming convention
+ [More information](https://lifterlms.com/docs/lifterlms-database-updates/#3120)

##### Admin Post Table Upgrades

+ Lessons
  + Fix section titles which formerly were a dead link. Now they're just text
  + Add filtering the table by associated course
+ Quizzes
  + Display associated course and lesson columns with links
  + Add filtering by associated course and/or lesson
+ Quiz Questions
  + Display associated Quizzes with links
  + Add filtering by associated quiz

##### Template Updates

+ [admin/post-types/order-details.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/post-types/order-details.php)


v3.11.2 - 2017-08-14
--------------------

+ Tested up to WP Core 3.8.1

##### System Status and Reporting updates

+ System Report renamed to "Status"
+ Added information of template overrides to the system report
+ Added "Get Help" button linking to LifterLMS Ticketing submission page
+ Added "Logs" tab which allows for easy viewing & management of LifterLMS logs
+ Added "Tools and Utilities" tab and moved tools from the General Settings screen to this tab
+ Improved Session Reset tool


v3.11.1 - 2017-08-03
--------------------

+ New shortcode: `[lifterlms_course_continue_button]`. See [shortcode docs](https://lifterlms.com/docs/shortcodes/#lifterlms_course_continue_button) for more information.
+ New shortcode: `[lifterlms_lesson_mark_complete]`. See [shortcode docs](https://lifterlms.com/docs/shortcodes/#lifterlms_lesson_mark_complete) for more information.
+ Added filter `llms_product_pricing_table_enrollment_status` to allow forceful display of course/membership pricing tables regardless of user enrollment status.
+ Fix course author shortcode to allow usage outside of a course via the `course_id` parameter.

##### Template Updates

+ [product/pricing-table.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/pricing-table.php)
+ [product/course/progress.php](https://github.com/gocodebox/lifterlms/blob/master/templates/product/course/progress.php)


v3.11.0 - 2017-07-31
--------------------

+ New engagement trigger "Student purchases access plan" allows engagements to be triggered from a specific access plan!
+ Minor performance improvements to notification-related database queries
+ Fix issue causing payment gateways to always use test mode links from Orders on the admin panel
+ Added default email notification merge code for outputting an HTML divider
+ Added new actions to Dashboard template to allow adding custom content to course tiles on the dashboard

##### Template Updates

+ [myaccount/my-courses.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-courses.php)


v3.10.2 - 2017-07-14
--------------------

+ Fix fatal error related to purchase receipts for trashed or deleted orders
+ l10n "Reviews" tab title on course settings
+ Remove commented out sample preheader text from email header template which was displaying in some email clients.

##### Template Updates

+ [emails/header.php](https://github.com/gocodebox/lifterlms/blob/master/templates/emails/header.php)


v3.10.1 - 2017-07-12
--------------------

##### Bugfixes

+ Prevent errors related to attempting to display notification data related to deleted students
+ Fix errors related to displaying notifications for deleted post (courses, sections, lessons, quizzes, etc...)
+ Fix error causing email notifications being sent after related user has been deleted
+ Fix typo preventing `llms_form_field()` from outputting textareas

##### Updates

+ Add new filter `llms_allow_subscription_cancellation` useful for preventing students from self-cancelling their subscriptions on the student dashboard. [More info](https://lifterlms.com/docs/lifterlms-filters/#llms_allow_subscription_cancellation).
+ Add new API for querying students via AJAX select2 elements
+ Select2 Post Query elements can now query multiple post types simultaneously
+ Select2 Post Query elements can now support `<optgroup>`

###### i18n

+ Course option metabox for reviews is not translatable


v3.10.0 - 2017-07-05
--------------------

##### Recurring Order Management (for Admins)

+ Admins can now edit various pieces of data related to a recurring order from the order screen on the admin panel
  + Allow editing of the Next Payment Date
  + Allow editing of the Trial End Date (when a trial is active for the order)
  + Edit Payment Gateway and related gateway fields (Customer ID, Source ID, and Subscription ID)
+ If you're using LifterLMS Stripe or LifterLMS PayPal please update to the latest version of these add-ons to take advantage of these new features!

##### Recurring Order Management (for Students)

+ Students can now switch the payment method (source) for their recurring subscriptions from the student dashboard
+ Students can now cancel their recurring orders to prevent future payments on recurring orders
+ If you're using LifterLMS Stripe or LifterLMS PayPal please update to the latest version of these add-ons to take advantage of these new features!

##### Automatic Payment Retries (for supporting gateways)

+ LifterLMS Stripe and LifterLMS PayPal can now automatically retry failed payments to help recover lost revenue as a result of temporary declines to payment sources. Please see our documentation on this new feature [here](https://lifterlms.com/docs/automatic-retry-failed-payments/).
+ If you're using LifterLMS Stripe or LifterLMS PayPal please update to the latest version of these add-ons to take advantage of these new features!

##### Manual Payment Gateway Enhancements

+ The Manual Payment Gateway (bundled with LifterLMS Core) can now handle recurring payments. For more information on utilizing recurring payments with the Manual Gateway please see the [gateway documentation](https://lifterlms.com/docs/using-lifterlms-manual-payment-gateway/).

##### Updates and Fixes

+ Force SSL setting now applies to Student Dashboard screens. This is useful as Google now recommends any page where a password is submitted should be encrypted and allows gateway communication from student dashboard screen with APIs that require an SSL connection.
+ Fixed spelling error related to quizzes

##### Templates changed

**NEW**

+ [checkout/form-switch-source.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-switch-source.php)
+ [myaccount/view-order-transactions.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/view-order-transactions.php)

**UPDATED**

+ [admin/post-types/order-details.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/post-types/order-details.php)
+ [myaccount/my-orders.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-orders.php)
+ [myaccount/navigation.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/navigation.php)
+ [myaccount/view-order.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/view-order.php)
+ [quiz/summary.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/summary.php)


v3.9.5 - 2017-06-13
-------------------

+ Increased css z-index of basic notifications to prevent issues with themes that have high z-index on menus and other elements
+ Increased the frequency of basic notification heartbeat check from 10 to 20 seconds
+ Added filter to allow for customization of the notifications heartbeat interval, example [here](https://lifterlms.com/docs/lifterlms-filters/#llms_notifications_settings).
+ Fixed error related to password reset when the "Disable Usernames" account setting is disabled


v3.9.4 - 2017-06-12
-------------------

+ Fix hardcoded db reference to `wp_posts` table


v3.9.3 - 2017-06-09
-------------------

+ Fix typo in notifications query


v3.9.2 - 2017-06-07
-------------------

+ Tested up to WordPress 4.8
+ Fixed issue with merge codes on WP Editors for notifications, emails, etc...
+ Update notifications query to only return results related to posts which actually exist. Prevents errors occurring when reviewing achievements on the student dashboard for courses, lessons, etc which have been deleted/trashed.
+ Only display quiz time limit meta information when a time limit exists
+ Fix display of quiz question order (question x of x)
+ Improved logic powering quiz attempt grading for increased consistency, especially with regards to floats and rounding

##### Templates Changed

+ [quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/meta-information.php)
+ [quiz/question-count.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/question-count.php)


v3.9.1 - 2017-06-02
-------------------

+ Fix engagement triggers with relation to quizzes to properly receive 3.9 api updates
+ Fix quiz attempt counting issue resulting in the total attempts by a student always being one more than the actual value
+ Fix membership access plan restrictions tooltip


v3.9.0 - 2017-06-02
-------------------

##### Quizzes

+ All new quiz results interface for students
  + Donut charts are now animated
  + Donuts will be green for passing attempt and red for failing
  + Students can now review previous quiz attempts and summaries
  + Removed the juxtaposition of the current and best attempts to reduce confusion on the interface
  + Improved the consistency of the quiz meta information markup
  + Adjusted various pieces of language for an improved student experience
+ Improvements to the quiz taking experience
  + Added the LLMS_Spinner (seen on checkout screens and various places on the admin panel) and various loading messages when starting quiz, transitioning between questions, and completing a quiz
  + Better error handling and management should issues arise during a quiz
  + Better unload & beforeunload JS management to warn students when they attempt to leave a quiz in progress
+ Improved quiz data handling and management
  + Improved API calls and handlers related to taking quizzes for increased performance and consistency
  + quiz data can now be programmatically queried via consistent apis and data classes, see `LLMS_Student->quizzes()` and `LLMS_Quiz_Attempt`
+ Quizzes no longer rely on session and cookie data. All quiz data will always be saved directly to the database and related to the student. Fixes an issue on certain servers preventing student from starting quizzes.
+ Deprecated `LLMS_Quiz::start_quiz()`, `LLMS_Quiz::answer_question()`, and, `LLMS_Quiz::complete_quiz()`
  + Ajax handler functions of the same names should be used instead.
  + To programmatically "take" quizzes use related functions of similar names from the `LLMS_Quiz_Attempt` class

##### Templates changed

+ New
  + [quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/meta-information.php)

+ Updated
  + [admin/reporting/tabs/students/courses.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/students/courses.php)
  + [content-certificate.php](https://github.com/gocodebox/lifterlms/blob/master/templates/content-certificate.php)
  + [course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)
  + [myaccount/my-notifications.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/my-notifications.php)
  + [quiz/next-question.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/next-question.php)
  + [quiz/previous-question.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/previous-question.php)
  + [quiz/question-count.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/question-count.php)
  + [quiz/quiz-question.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/quiz-question.php)
  + [quiz/quiz-wrapper-end.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/quiz-wrapper-end.php)
  + [quiz/quiz-wrapper-start.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/quiz-wrapper-start.php)
  + [quiz/results.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results.php)
  + [quiz/return-to-lesson.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/return-to-lesson.php)
  + [quiz/single-choice_ajax.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/single-choice_ajax.php)
  + [quiz/start-button.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/start-button.php)
  + [quiz/summary.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/summary.php)

+ Removed
  + quiz/attempts.php - replaced by [quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/meta-information.php)
  + quiz/passing-percent.php - replaced by [quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/meta-information.php)
  + quiz/time-limit.php - replaced by [quiz/meta-information.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/meta-information.php)

##### Fixes

+ Student Dashboard notifications page will not display pagination links unless there's results to page through
+ Student Dashboard notifications page will now display a message when no notifications are found
+ Certificate previewing now takes into consideration the preview setting roles to allow admins (or other roles) to preview certificates
+ Made student name self fallback (you) i18n friendly


v3.8.1 - 2017-05-21
-------------------

+ Fix merge code issue related to course title on quiz notifications


v3.8.0 - 2017-05-20
-------------------

+ Automatic email and basic (on-screen) notifications for various events within LifterLMS
  + All notifications can be customized
  + Email notifications can be optionally sent to custom email address, course authors, and more
+ Students will automatically receive email receipts when making purchases and when recurring access plans rebill
+ Hidden Access Plans
+ Add a "Purchase Link" view button to access plans so admins can quickly grab the direct URL to an access plan
+ Notifications history screen on Student Dashboard to review past notifications that have been received
+ Updated LLMS_Email class and functionality
+ Email templates have been completely rewritten and styled
+ Updated and rewritten password reset flow
+ Earned certificates are only accessible by the student who earned the certificate
+ Added the functionality for image upload via options & settings api
+ Removed a handful of unused templates related to LifterLMS certificates that were replaced a long time ago but still existed in the codebase for unknown reasons.
+ Fixed filter on engagements settings page
+ Minor adjustments to language and settings order on Engagements settings screen for email settings
+ Email Header Image field is now an upload field as opposed to a "paste a url here" setting
+ Phone number recorded to order and displayed on order for admin panel during purchases
+ Order details now display full country name as opposed to the country code
+ Fix installation script to ensure admin can preview by default


v3.7.7 - 2017-05-16
-------------------

+ Updated a few strings on the admin panel to be translatable
+ Fix PHP warning output during plugin activation
+ Fix reporting issue related to outputting quiz question answers where the correct answer is the first available answer
+ Fix PHP 7.1 issue on the checkout screen
+ Removed some unnecessary files from vendor libraries


v3.7.6 - 2017-05-05
-------------------

+ New translations for new categories on Add-ons screen
+ Update to general settings which utilizes featured items from the general settings screen
+ Update readme & related meta files
+ Removed advert image files


v3.7.5 - 2017-05-02
-------------------

+ Upgrade WP Session Manager to latest version
+ Code style updates across most files in codebase to bring to most recent styling guidelines put forth by [WP Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)


v3.7.4 - 2017-04-26
-------------------

+ When cloned site detected automatically disable recurring_payments feature & trigger an action 3rd parties can hook into for custom 3rd party features
+ Add better JS dependency management to prevent issues where assets loaded in the wrong order
+ Fix issue where dismiss icon on LifterLMS admin notices was positioned poorly on non-LifterLMS admin screens
+ Fix issue preventing edit account form submission on student dashboard when password strength meter is disabled


v3.7.3 - 2017-04-21
-------------------

+ Fixed issues where Course Track checks were not functioning properly with relation to prerequisite associations
+ `LLMS_Generator` can now be used to generate course(s) from a raw array of course data using the SingleCourseGenerator and BulkCourseGenerator
+ `LLMS_Generator` default post status can be set at runtime using `set_default_post_status()`
+ Fixed an issue causing JS errors on the `wp-login.php` screen
+ Tested up to WordPress 4.7.4

### Template Updates

+ `course/prerequisites.php` - Prerequisite checks check for 'course_track' rather than 'track'


v3.7.2 - 2017-04-17
-------------------

+ Resolved a JS errors on admin panel resulting from overly strict asset loading added in 3.7.0


v3.7.1 - 2017-04-14
-------------------

+ Fix php notice when no roles are selected for preview management feature


v3.7.0 - 2017-04-13
-------------------

**Preview Management**

+ All new view management for users to make editing content easier for course builders
+ Admins may customize the roles of users who can access view management
+ Qualifying users can view content as an enrolled student or a non-enrolled visitor
+ Default view allows users to bypass all restrictions (drip, membership, enrollment, and so on) for easy course navigation and management
+ Thanks to [@fabianmarz](https://github.com/fabianmarz) and the team at and the team at [netzstrategen](https://github.com/netzstrategen) for their assistance with this feature!

**Improvements**

+ Edit Account Screen now utilizes updated APIs for better customization management
+ Improve intelligence of enqueued admin js & css files

**Fixes**

+ Fixed coupon calculation issue related to currencies using commas as the decimal separator
+ Properly display track related information when reviewing engagements on the admin panel
+ fixed issue preventing course tracks from being recorded as completed


v3.6.2 - 2017-04-10
-------------------

+ Fix issue preventing export of vouchers via email
+ added action `after_llms_mark_complete` to allow custom actions to happen after a course, lesson, etc... is marked complete


v3.6.1 - 2017-03-28
-------------------

+ Fix issue related to taking a quiz for the first time when no quiz data is available for a user
+ Fix issue when course outline shortcode is displayed on non LifterLMS post types


v3.6.0 - 2017-03-27
-------------------

+ Courses and Memberships now have settings to control their visibility in catalogs and search results. For more information visit the [knowledge base](https://lifterlms.com/docs/course-membership-visibility-settings/).
+ Courses are now a searchable post type. All existing courses will automatically remain excluded from search via new catalog visibility settings. New courses added after this date will be searchable unless the visibility is updated prior to publishing the course.
+ Added options (and filters) to allow customization of the order of courses displayed on the Student Dashboard
  + Existing behavior (ordered by enrollment date, most recent to least recent) will be preserved
  + New installations will default (by popular demand) to Order (Low to High) which will obey the "Order" settings of courses
  + Customize or update the order for your site by visiting LifterLMS -> Settings -> Accounts and changing the setting for "Courses Sorting" under "Account Dashboard"
+ New Shortcodes:
  + `[lifterlms_course_author]` -  Display the Course Author's name, avatar, and (optionally) biography. [Info & Usage](https://lifterlms.com/docs/shortcodes/#lifterlms_course_author)
  + `[lifterlms_course_continue]` - Display a progress bar and continue button for enrolled students only. [Info & Usage](https://lifterlms.com/docs/shortcodes/#lifterlms_course_continue)
  + `[lifterlms_course_meta_info]` - Display all meta information for a course. [Info & Usage](https://lifterlms.com/docs/shortcodes/#lifterlms_course_meta_info)
  + `[lifterlms_course_prerequisites]` - Display a notice describing unfulfilled prerequisites for a course. [Info & Usage](https://lifterlms.com/docs/shortcodes/#lifterlms_course_prerequisites)
  + `[lifterlms_course_reviews]` - Display reviews and review form for a LifterLMS Course. [Info & Usage](https://lifterlms.com/docs/shortcodes/#lifterlms_course_reviews)
  + `[lifterlms_course_syllabus]` - Display the course syllabus. [Info & Usage](https://lifterlms.com/docs/shortcodes/#lifterlms_course_syllabus)
+ "Back" & "Next" pagination links on Student Dashboard View Courses are now buttons instead of text links
+ Fixed an issue preventing pagination links from displaying on the "View Courses" page of the student dashboard when the endpoint slug was customized
+ Course and Membership taxonomy archive pages will now properly match the heights of tiles
+ Fixed typo in `lifterlms_get_enrollment_status_name` filter
+ Fixed typo in `lifterlms_get_order_status_name` filter
+ Reduced complexity and redundancy of `llms_get_enrolled_students()`


v3.5.3 - 2017-03-21
-------------------

+ Ensure that access plan subscription schedule details are fully translatable
+ Ensure "Services" title on admin add-ons screen can be translated
+ Fix "View All My Courses" link on Student Dashboard to obey endpoint slug customizations
+ Membership restriction checks only run on singular posts (not on archives)
+ Ensure `[lifterlms_course_outline]` and Course Syllabus widget can be used on Quizzes.
+ Fix reporting widgets for course & lesson completions to report the correct completion types only


v3.5.2 - 2017-03-16
-------------------

+ Fix course outline shortcode when used on a lesson
+ Fix custom html form fields produced by `llms_form_field()`


v3.5.1 - 2017-03-15
-------------------

+ Lessons marked as incomplete will now display as incomplete in the course outline generated by the above Course Syllabus Widget and the course outline shortcode
+ Updated course outline shortcode / course syllabus widget to utilize new APIs
+ The template at `templates/course/outline-list-small.php` updated to reflect above changes. If you're overriding this template please review the changes and update accordingly
+ Fix issue preventing course auto advance on lesson completion
+ Shortcodes added within `[lifterlms_hide_content]` will now be processed


v3.5.0 - 2017-03-13
-------------------

+ New course setting **Retake Lessons** allows students to mark lessons as "incomplete" after completing lessons. Admins may enable this site-wide setting under Settings -> Courses.
+ Course and Membership catalog per page settings will now only accept numbers
+ "Catalogs" settings tab has been split into "Course" and "Membership" settings
+ Settings added via filter `lifterlms_catalogs_settings` will be added to the "Course" settings tab and deprecated in the next major release
+ Default course and membership catalog courses per page changed to 9. Previous default was 10 which results in a 4th row on catalogs with only one item.
+ Tweaked size of LifterLMS admin tab menu items
+ Pass API Mode Context to links generated by LifterLMS payment gateways
+ Fixed typo on general settings screen
+ Moved LifterLMS Add-on Banners from General Settings to an Add-Ons menu
+ If required fields exist on checkout and are empty during free quick enrollment users will be redirected to the normal checkout page where they can enter required fields
+ Updated action scheduler lib to latest version. Minor changes, fixes compatibility with WooMemberships.
+ Recent activity stats widgets on general settings screen updated to be more reliable and accurate (and performant!)
+ Added 3 new widgets to enrollments reporting tab: courses completed, lessons completed, and user registrations


v3.4.8 - 2017-03-07
-------------------

+ Tested to WordPress Version 4.7.3
+ Fixed undefined index notice on admin panel
+ Added a real description to new `_nx()` functions
+ Access plan trial periods now allow proper translations


v3.4.7 - 2017-03-03
-------------------

+ Ensure run when the `lifterlms_db_version` option doesn't exist in the database


v3.4.6 - 2017-03-03
-------------------

+ Fixed a text domain typo preventing translation of "Correct Answer" on quiz results screen
+ Ensure access plan "periods" are translatable
+ Now using `date_i18n()` for certificate dates so that dates are properly localized
+ Load plugin textdomain during `init` rather than `plugins_loaded`


v3.4.5 - 2017-02-23
-------------------

+ Ensure free access plans are available to logged out users


v3.4.4 - 2017-02-22
-------------------

+ Added a popup to warn students when leaving a quiz they've already started
+ Enable removal of student quiz attempts by admins from student reporting screens
+ Fix an undefined error on quiz reporting screens for incomplete quizzes
+ Display incomplete (abandoned) quizzes as incomplete (instead of as still running) on the quiz reporting screen
+ Prevent logged in users from bypassing membership restrictions for free members-only access plans


v3.4.3 - 2017-02-20
-------------------

+ Fix issue with bbPress integration so that forums restricted to multiple memberships allow users of at least one membership that the forum is restricted to access topics within that forum
+ Ensure that the correct ajax url is used for quizzes, resolves issue for sites utilizing `FORCE_SSL_ADMIN`
+ Refactored database background update scripts for increased reliability & performance
+ Database update 3.3.0 moved to 3.4.3 in order to accommodate users who were unable to run the 3.3.0 update, please read the [3.4.3 database update notes](https://lifterlms.com/docs/lifterlms-database-updates/#343) for more information.
+ WIP: refactoring shortcodes to a more sane set of functions and classes


v3.4.2 - 2017-02-14
-------------------

+ Backwards compatible css for tooltips


v3.4.1 - 2017-02-14
-------------------

+ Password strength meter now functions correctly when using the [lifterlms_registration] shortcode
+ Ensure open registration with required voucher prevents registration with invalid vouchers
+ Lesson completion via quiz completion only recorded the first time the quiz is completed
+ Fix issue preventing membership catalog from obeying the catalog's ordering settings
+ Prevent duplicate engagements from being triggered
+ Admin tables can display percentages as a progress bar!
+ Students reporting table displays overall progress as a progress bar
+ Refactored frontend assets class to allow better management of inline scripts


v3.4.0 - 2017-02-10
-------------------

+ Enrollment for free access plans has improved based on your feedback. For more information see [https://lifterlms.com/docs/checkout-free-access-plans/](https://lifterlms.com/docs/checkout-free-access-plans/)
+ Upgraded Student Management Table for courses and memberships:
  + Allow searching students by name / email
  + Allow filtering of students by current status
  + Allow sorting of students by name, user id, status, and enrollment updated date
  + Added student's grade to the table (courses only)
  + Table pagination allows skipping to the first and last pages
  + Student names link to full student reporting screen
  + Student IDs added to the table. ID links to the WP User Edit screen which was previously accessible by clicking the student's name
  + Utilizing improved database queries for displaying data on the table
+ One-click bulk enrollment of all current members of a membership into an auto-enrollment course. More info [here](https://lifterlms.com/docs/membership-auto-enrollment/#bulk-enrollment)
+ Students reporting table pagination can now jump to first and last page
+ Students reporting table pagination now displays current page and total number of pages
+ Added new class `LLMS_Student_Query` which is modeled, loosely, off of the `WP_User_Query` and allows for querying student data in relation to courses
+ `LLMS_Admin_Table` abstract now supports filtering and jump to first and last page pagination options
+ `llms_get_enrolled_students` now utilizes `LLMS_Student_Query` and resolves a bug where some users returned by this query would be returned with the incorrect status.
+ Ensure `LLMS_Course::has_prerequisite( 'course' )` & `LLMS_Course::has_prerequisite( 'track' )` always return booleans
+ Made a small performance tweak for courses without audio / video embeds
+ Fix coupon expiration dates check to be more i18n friendly
+ Update `LLMS_Coupon` class to utilize 3.3.0 class property enhancements
+ added `llms_current_time`, a pluggable wrapper for `current_time()` to enable easier unit testing of date-related functions
+ Shortcodes within course restriction messages are now handled properly to output their intend content rather than the raw shortcode
+ Ensure the Page Attributes area is available on lessons so WordPress 4.7 custom post type page templates can be utilized


v3.3.1 - 2017-01-31
-------------------

+ Tested up to WordPress core 4.7.2
+ Added new engagement triggers for Quiz completion, quiz failure, and quiz passed.
+ Refactored Lesson Completion for sanity
+ Added function `llms_mark_complete()` for simple programmatic completion of courses, sections, lessons, and tracks. See [usage docs](https://github.com/gocodebox/lifterlms/blob/master/includes/functions/llms.functions.person.php#L146-L162) for more information.
+ Class function `LLMS_Lesson::mark_complete()` has been staged for deprecation. It will still function but developers should update code to use above function.
+ LifterLMS background updaters will now display a progress report on the admin panel to add some transparency to how the update is doing.
+ Added `author` support to `llms_membership` post type
+ Added a way to remove all LifterLMS-generated data during plugin uninstallation.
+ `llms_get_post()` will now work with any LifterLMS Post Model post types
+ Removed references to `LLMS_Activate` class which was removed back in 2.0.
+ Changed include method to session related classes for better handling via phpunit
+ Refactored some of the `LLMS_Install` class for reliability and test coverage
  + Changed order of table and option creation during installation. Prevents a database warning from being thrown during installation.
  + Added function for retrieving default difficulty categories added during installation
  + Added function for removing default categories added during installation
+ `llms_are_terms_and_conditions_required()` ensure the page id used in this function is an absint
+ Removed redundant function `LLMS_Lesson::single_mark_complete_text()`
+ Add css classes for buttons to be auto-width rather than the width of their containers
+ Fix ID of engagement email class. Allows some filters and actions to actually be used.
+ Properly display quiz failures as failures on the quiz results screen
+ `loop/feature-image.php` now works for unsupported PHP 5.5 and down
+ Fix issue with modifying section titles from within the course builder
+ Fix undefined warning resulting from admin notice "flash" being undefined on pre-existing saved notices
+ Updated template at `templates/course/complete-lesson-link.php` to include a few new CSS classes and utilize `llms_form_field()` to standardize buttons


v3.3.0 - 2017-01-23
-------------------

+ New course option allows displaying the video embed in place of the featured image on course tiles displayed on the course catalog screen
+ Courses can now be exported individually or in bulk. Export of a course includes all course content, sections, lessons, and quizzes.
+ Courses can now be duplicated. Duplication duplicates all course content, sections, lessons, and quizzes.
+ Upon completion of the Setup Wizard a sample course can be automatically installed.
+ Postmeta keys for Lessons and Sections which denote their relationship to their parents have been renamed for consistency, database upgrade 330 included in this release will rename the keys automatically. [Read more here](https://lifterlms.com/docs/lifterlms-database-updates/#330)
+ Update to `LLMS_Post_Model` to allow easier programmatic definition and handling of extending class properties
+ classes extending `LLMS_Post_Model` can now be serialized to json and converted to arrays programmatically
+ new function `llms_get_post()` allows easier instantiation of an `LLMS_Post_Model` instance
+ Added LifterLMS Database Version to the system report


v3.2.7 - 2017-01-16
-------------------

+ Fix float conversion of large numbers with relation to coupon price adjustments


v3.2.6 - 2017-01-16
-------------------

+ Tested up to WordPress Core 4.7.1
+ Fix the display of track-related engagements on the engagement admin screen
+ Fix float conversion of large numbers with relation to prices


v3.2.5 - 2017-01-10
-------------------

+ New shortcode: `[lifterlms_pricing_table]` allows pricing table display outside of a course or membership. See [https://lifterlms.com/docs/shortcodes/#lifterlms_pricing_table](https://lifterlms.com/docs/shortcodes/#lifterlms_pricing_table) for usage information.
+ New shortcode: `[lifterlms_access_plan_button]` allows custom buttons for individual access plans to be created outside of a pricing table. See [https://lifterlms.com/docs/shortcodes/#lifterlms_access_plan_button](https://lifterlms.com/docs/shortcodes/#lifterlms_access_plan_button) for usage information.
+ ensure every return from `llms_page_restricted` is filtered. Thanks to @matthalliday
+ Ensure purchase page can only load for valid access plans
+ Course / Membership taxonomy archives now obey orders defined by their respective catalog settings
+ Fix language of automatic validation error message for numeric field types
+ Fix translation function error causing course syllabus to display incorrect "x of x" text
+ Added correct text domain to an i18n string displayed on the checkout confirmation screen, thanks @ymashev
+ Ensure search result pages are viewable by members and non members regardless of result membership restrictions (unless site is restricted to sitewide membership)


v3.2.4 - 2017-01-03
-------------------

+ Fixed tooltips on lesson preview tiles (in course syllabus and on next/prev tiles inside lessons) to show the actual reason the lesson is inaccessible rather than always showing a generic enrollment message
+ Removed the language "You must enroll in this course to unlock this lesson" in favor of "You do not have permission to access to this content" as a restriction message fallback when no better message is available
+ "Quiz Results" title is now translatable
+ Removed deprecated JS file "llms-metabox-data.js" which controlled UI/X for 2.x subscription data on courses and memberships
+ Non LMS Content (pages, posts, forums, etc...) restricted to multiple memberships will now correctly allow users access to the content as long as they have access to at least one of the memberships
+ Fixed a redirect loop encountered if direct access to a lesson with an incomplete prerequisite was attempted


v3.2.3 - 2016-12-29
-------------------

+ Progress and Grade are now sortable columns on the student reporting table
+ Make enrollment statuses translatable for courses and memberships on the Student Dashboard
+ "Sign Out" text on student dashboard is now translatable, thanks @yumashev
+ Fixed prerequisite lesson display on lesson post tables
+ Ensure post archive (blog) is visible regardless of post membership restrictions
+ Moved lesson post table management functions to their own class
+ Unused section post table management functions removed


v3.2.2 - 2016-12-21
-------------------

+ Adds filter `llms_student_dashboard_login_redirect` allowing customization of the redirect upon login via the Student Dashboard
+ Adds a shortcode parameter, `login_redirect` to `[lifterlms_my_account]` allowing customization of the redirect upon login via the Student Dashboard
+ Adds a new tool under "Tools and Utilities" on the LifterLMS Settings screen which allows users to clear the cached student overall progress and overall grade data
+ Fixes a compatibility issue with the OptimizePress live editor
+ Adds a text domain to a translation function where none was present, rendering the string untranslatable


v3.2.1 - 2016-12-14
-------------------

+ Fix operator position on `is_complete` check


v3.2.0 - 2016-12-13
-------------------

##### LifterLMS Reporting Beta

+ Students overview displays broad information about your students in a searchable and sortable table
+ Review data about individual students including:
  + Membership enrollments and statuses
  + Course enrollments, status, and progress
  + Quiz attempts and and their submitted answers
  + Earned achievements and certificates
+ Sales and Enrollments analytics are now found under the "Reporting" screen
+ Feedback on the beta? Let us know at [https://lifterlms.com/docs/lifterlms-reporting-beta/](https://lifterlms.com/docs/lifterlms-reporting-beta/)

##### Other Updates & Fixes

+ Lesson completion checks now look for at least one record of the completed lesson as opposed to looking for exactly one
+ Fix positioning of teacher avatar on course/membership tiles
+ Remove explicit color definition from Student Dashboard navigation links for greater theme compatibility


v3.1.7 - 2016-12-06
-------------------

+ Added support for WordPress Twenty Seventeen theme
+ Improved the messaging and functions related to LifterLMS Sidebar support
+ Add alternate language for a quiz requiring 100% grade to pass
+ Added CSS class `.llms-button-primaray` to lesson "Mark as Complete" buttons
+ Add box-sizing css rule to LifterLMS form field elements. Fixes layout issues on themes that don't border-box everything.
+ Fix an issue that prevented the admin notice to enable/disable recurring payments from clearing when a button was pressed from screens other than the LLMS Settings screen
+ Fix next payment date error when viewing a cancelled recurring order on the student dashboard
+ Recurring payments now scheduled based on UTC time in accordance with the action scheduler which executes based on UTC rather than site timezone
+ Add existing lesson to course modal now relies on async search. Improves performance and prevents timeouts on sites with a 500+ lessons
+ Removed 2.x -> 3.x update notification message
+ Fix an issue with comment counting on PHP7
+ Updated action scheduler library to latest version


v3.1.6 - 2016-11-11
-------------------

+ Handle empty responses on analytics more responsibly
+ Fix typo preventing completed orders from displaying in analytics when using course / membership filters
+ Quiz builder now leverages llmsSelect2 rather than select2 directly. Resolves a number of theme and plugin compatibility issues.
+ Prevent bullets and weird margins on LifterLMS notices with slightly more specific CSS
+ Login error messages will now display regardless of whether or not open registration is enabled
+ Attempts to access quizzes are redirected or error messages are output when student is not enrolled.


v3.1.5 - 2016-11-10
-------------------

+ Fix Month display on Analytics Screen


v3.1.4 - 2016-11-10
-------------------

+ Progress bars are slightly more intelligent to prevent a widowed "%" on themes with larger base font sizes
+ LifterLMS Merge code button only displays where it's supposed to now
+ Fix issue where users removed from a membership were not properly removed from courses they were auto-enrolled into because of that membership
+ Fix analytics screen JS parsing error


v3.1.3 - 2016-11-04
-------------------

+ Added new action hooks to the course syllabus widget/shortcode template
+ Added a small text link on the student dashboard which links to the full courses list of the dashboard
+ Display order revenue for legacy orders instead of 0
+ Make the Order History table on the Student Dashboard responsive
+ Only display _published_ courses on the student dashboard
+ Fixes a conflict with WP Seo Premium's redirect manager which was creating access plan redirects
+ Reenable course review options on the admin panel
+ Updates review output method so reviews are now output via a removeable action


v3.1.2 - 2016-10-31
-------------------

+ Update all course and lesson templates to rely only on `global $post` rather than on `$course` and `$lesson` globals which are working inconsistently across environments
+ Fix typo related to the line-height of LifterLMS order notes on the admin panel. Thanks [@edent](https://github.com/edent)!


v3.1.1 - 2016-10-28
-------------------

+ Shortcode `[lifterlms_hide_content]` has some new functionality. See [documentation](https://lifterlms.com/docs/shortcodes/#lifterlms_hide_content) for usage and more information!
+ Fix logic when determining if terms and condition checkboxes should be displayed on checkout & open registration.
+ Define a placeholder on the Terms & Conditions page selection so it can be removed
+ Explicitly declare `LLMS_Lesson` on lesson audio/video embed templates instead of relying the global `$lesson`. Some environments appear to be losing the global.
+ Removed unused lesson template "full-description"


v3.1.0 - 2016-10-27
-------------------

+ New engagement triggers available to allow engagements to be fired when a student enrolls into a course or membership!
+ Add custom email addresses for to, cc, and bcc when sending email engagements
+ New Merge Code button for easy merging of custom merge codes when creating emails
+ Added post table data for LifterLMS Engagements
+ Added new filter `llms_email_engagement_date_format` which allows customization of the format of the `{current_date}` merge code available in LifterLMS Emails
+ Added explicit max width declaration to images within LLMS Catalogs to prevent image overflow. Fixes some theme compatibility issues.
+ Optimize course and lesson audio video templates for faster loads
+ Fix course & lesson video to load videos instead of duplicating audio embeds
+ Fix coupon usage query so that coupons cannot be used more than the maximum number of times. Also now displays the correct number of coupons used on the coupons post table.
+ Fix LLMS Engagement Email merge codes to work in subject line


v3.0.4 - 2016-10-20
-------------------

+ Added shortcode `[lifterlms_login]` so the login form can be displayed. Information usage at [https://lifterlms.com/docs/shortcodes/#lifterlms_login](https://lifterlms.com/docs/shortcodes/#lifterlms_login)
+ Added internal function `LLMS_Student->get_name()`
+ Three basic course difficulties will be automatically created on installation and upgrades
+ Updated course difficulty save methods to rely only on the taxonomy rather than the taxonomy and postmeta table
+ Updated admin settings screens to only flush rewrite rules on screens where it is necessary to update rewrites
+ Fix issue with customization of LifterLMS account endpoint URLs
+ Fix a conflict with [Redirection](https://wordpress.org/plugins/redirection/) url monitoring that was causing redirects to be created from Courses and Memberships to the site home page automatically whenever updating the post
+ Fix an undefined index warning on courses / memberships when updating post data
+ Remove confusing and invalid warning message from Membership post screen on admin panel


v3.0.3 - 2016-10-17
-------------------

+ Added filter `llms_show_preview_excerpt` which can be used to hide the excerpt on course syllabus or next/back preview tiles in lesson navigation
+ Fix logic so that only free lessons are marked as free lessons post 3.0 upgrade
+ Fix incorrect display of the "restricted" and "non-restricted" content areas for memberships
+ Fix undefined index warning output by membership metaboxes
+ Fix dead like under "Force SSL" checkout setting
+ Course & Membership tiles output by course or membership shortcodes now automatically match column heights like the default catalogs do.
+ Correctly register students as the "Student" Role
+ Database Upgrade script converts users with the role "studnet" to "student"


v3.0.2 - 2016-10-14
-------------------

+ Added action `lifterlms_before_student_dashboard_tab`
+ Added action `lifterlms_after_student_dashboard_greeting`
+ Added action `lifterlms_after_student_dashboard_tab`
+ Added action `lifterlms_sd_before_membership`
+ Added action `lifterlms_sd_after_membership`
+ Fix membership shortcode
+ Fix issue that prevented "Student Dashboard" from rendering if the page was set as the child of another page
+ Fix undefined function error in admin notices
+ Fix nonce errors resulting from admin notice html being served from the database rather than being dynamically generated
+ Fix db upgrade script which was enabling course time period for restrictions for all courses regardless of their pre 3.0 restriction settings
+ Fix db upgrade script that was causing empty sale dates to show start of unix epoch b/c they were empty strings
+ Fix Javascript parse error preventing section & lesson editing from within the course outline on the admin panel
+ Fix lesson icons from highlighting lesson settings like drip delay & quiz association
+ Updated course outline color scheme to match the 3.0 admin color scheme overhaul
+ `LLMS_Lesson::get_assigned_quiz()` will output deprecation warnings for those using debug mode. LLMS core no longer uses this function and will be deprecated in the next major release.
+ Handle enrollment status of legacy orders based on enrollment rather than enrollment AND order status


v3.0.1 - 2016-10-13
-------------------

+ Properly prefix `llms_is_ajax()` to prevent 500 errors when leaving HTTPS forced checkout screen
+ Fix student unenrollment from memberships which was leaving a trace of enrollment in the user_meta table
+ Update student dashboard nav list items to have more specific no styles to prevent "double discs" on various themes
+ Return course progress bar and "continue" button which was accidentally removed
+ Added core support for "Divi" theme sidebars


v3.0.0 - 2016-10-10
-------------------

**This is a massive update which _breaks_ backwards compatibility for many LifterLMS features. A database migration is also necessary for upgrading users to reformat certain pieces of information which are being accessed differently in 3.0.0**

**We strongly recommend that you backup your website before upgrading and, if possible, test LifterLMS 3.0.0 in a non-public-facing testing environment to ensure compatibility with your theme and other plugins and to ensure that 3.0.0 changes do not adversely affect your existing website.**

**Please thoroughly read the following changelog and, if necessary, submit support tickets or post in the forums with any questions _prior_ to upgrading. LifterLMS Support _cannot_ and _will not_ manually resolve migration issues which may arise from upgrading to 3.0.0.**

+ New shortcodes to be documented later, checkout "includes/class.llms.shortcodes.php" if you're feeling anxious
+ All kinds of CSS changes to make LifterLMS, in general, be a little less old looking
+ Added a number of CSS classes to various areas in the Checkout template at "templates/checkout/form-checkout.php"
+ Added a "Cancel" button that allows you to hide the coupon form if the user decides not to add a coupon
+ Removed jQuery animations from the coupon form toggle in favor of a CSS class toggle. If you decide you want some animations on the form add some CSS transitions to the `.llms-coupon-entry` element (and children) to change when the class `.active` is added or removed from the element.
+ Refactored JavaScript related to LifterLMS Checkout. Improvements are minimal (if any) but the file is now smaller and more readable! Yay code stuff.
+ Fixed some redundant text on single payment confirmation screen. ("Single payment of single payment of")
+ Added a link to memberships listed under "My Memberships" on the LifterLMS Account Screen
+ LifterLMS Order posts have been renamed in the database from "order" to "llms_order" to prevent any potential conflicts with other plugins. Automated database migration will handle the renaming of old orders.
+ Fixed undefined variable notice generated by Sections without any lessons inside of them
+ renamed function `add_query_var_product_id()` to `llms_add_query_var_product_id()`
+ added a class for interacting with a course TRACK, instantiated by a track term or term_id (`LLMS_Track`)
+ password strength meter and related settings / options via utilizing WordPress password strength functions available
+ cleaned up the lesson locked tooltips to be a bit more sane and also utilized in course navigation on individual lessons.
+ Updated admin menus for LifterLMS content to be more sane and organized and intuitive and so on and so forth

##### Payment Gateways

**NOTE: at this release, LifterLMS PayPal is the only payment gateway that will work with this release. We haven't started working on Stripe 4.0.0 which will work with LifterLMS 3.0.0**

+ Payment gateways powered by a new abstract gateway class
+ PayPal has been removed from LifterLMS and is available as premium extension

##### Frontend Notices

+ LifterLMS "Notices" have been rewritten, slightly.
+ Most templates have been updated
+ associated CSS has been updated
+ Some sanity has been added to the related functions

##### Post "Model" Concept / Overhaul

Updated classes for programmatically accessing all sorts of data related to custom post types registered by LifterLMS.

These post types currently include:

+ Access Plans -- a non-public post type associated with courses and memberships which store payment related information
+ Coupons (replaces includes/class.llms.coupon.php)
+ Courses (replaces includes/class.llms.course.php)
+ Lessons (replaces includes/class.llms.lesson.php)
+ Memberships
+ Orders (replaces includes/class.llms.order.php
+ Products -- can be instantiated from courses or memberships (replaces includes/class.llms.product.php)
+ Transaction -- a non-public post type associated with orders which store completed/attempted transaction data

##### Improved admin metabox methods (and related)

+ Updated custom LifterLMS Admin Metaboxes to have a more sane programmatic interface. This affects nearly all admin metabox classes in the plugin.
+ A set of methods and classes have been added to improve the programmatic interface around custom post type post tables. These can be found in "includes/admin/post-types/post-tables"

##### Coupons

+ New class `LLMS_Coupon` allows for easy getting & setting of coupon data.
+ Updated coupon post table to include relevant coupon information for all coupons at a glance
+ Refactored admin panel coupon metabox generation to utilize new model for saving data
+ Added translation functions to all strings in coupon settings screen
+ Added new coupon settings
  + _Expiration Date_ -- coupons cannot be applied to a purchase after the expiration date
  + _Payment Type_ -- coupons can only be applied to either single or recurring payment plans. Existing coupons will be treated as single payment coupons until updated by the Admin.
  + _First Payment Discount_ -- Applies only to recurring payment coupons. Determines the discount applied to the first payment of a recurring payment transaction.
  + _Recurring Payments Discount_ -- Applies only to recurring payment coupons. Determines the discount applied all payments (other than the first) of a recurring payment transaction.
  + _Description_ -- Record internal notes for a coupon visible only by admins on the admin panel
+ The "Coupon Code" field has been removed in favor of the WordPress Coupon Post Title being utilized as the code. After upgrading, an automated database migration will move all coupon code fields to the title. The title previously functioned as the coupon description. During the migration the existing title will be moved to the new description field.

##### Orders

+ Added Order Statuses
  + Completed - Single payment only. Denotes a successful transaction
  + Active - Recurring only. Denotes the subscription is active with no issues
  + Expired - Recurring only. Denotes the subscription has ended and is no longer active
  + Refunded - Denotes the order has been refunded.
  + Cancelled - Denotes the order has been cancelled manually by an admin.
  + Failed - Denotes payment has failed. For subscriptions a failed payment will switch from "active" to "failed"
  + Pending - Denotes that the order has been created but payment has not been completed yet
+ Admin panel order table new features:
  + The following columns are now sortable in ascending and descending orders: Order, Product, and Date
  + Added totals based on order type (single or recurring) to the "Total" column
  + Added an order status column for quick status review
+ Order notes available for internal and system notes. powered by WP comments. lots of inspiration (and code) from WooCommerce, thank you <3
+ Added a bunch of currency settings (as well as right-side currency and decimal-less currency support!)

##### New Templates

+ __Pricing Table__ at "templates/product/pricing-table.php" utilized by courses and memberships for displaying access plan information. Replaces "templates/membership/purchase-link.php" and "templates/course/purchase-link.php"
+ __Course Taxonomy Templates__ at "templates/course/categories.php", "templates/course/tags.php", and "templates/course/tracks.php" display comma separated lists for course custom taxonomy terms
+ __Course Prerequisite Template__ at "templates/course/prerequisites.php" displays prerequisite information (course and tracks) for a given course.
+ __Meta Wrapper__ templates at "templates/course/meta-wrapper-end.php" and "templates/course/meta-wrapper-start.php" wrap some HTML around various meta data output about a course
+ Significantly updated checkout process with all kinds of new templates including:
  + templates/checkout/form-gateways.php
  + templates/checkout/form-summary.php
+ __Unified "Lesson Preview"__ at "templates/course/lesson-preview.php" displays "buttons" in course syllabus (on course page) and in course navigation (on lesson pages)
+ Various template hook priority changes in order to make adding content between default LifterLMS areas easier

##### Deleted Templates

+ templates/checkout/form-checkout-cc.php
+ templates/checkout/form-pricing.php

##### New & Updated Admin Interfaces & Templates

+ Significantly improved, changed, or brand new templates for metaboxes for various post types:
  + templates/admin/post-types/order-details.php
  + templates/admin/post-types/order-transactions.php
  + templates/admin/post-types/product-access-plan.php
  + templates/admin/post-types/product.php

##### New Functions

+ `llms_confirm_payment_url()` - Retrieve the URL used for confirming LifterLMS Payments
+ `llms_cancel_payment_url()`  - Retrieve the URL users are directed to when cancelling a payment

##### Install Script

+ Removed some legacy default options that were being created and are no longer required for new installations.
+ Removed unused `update_courses_archive()` function & related hook

##### Select2

Now utilizing a forked version of Select2 to prevent 3.5.x conflicts we've been plagued with

##### Deprecated

+ Removed filter `lifterlms_get_price_html`, use `lifterlms_get_single_price_html` instead
+ Removed unused `LLMS_Product->get_price_suffix_html()` function
+ Removed `LLMS_Product->set_price_html_as_value()` because we didn't like it anymore, don't use anything instead.
+ Removed `add_query_var_course_id()` function
+ Removed `displaying_sidebar_in_post_types()` function with the `LLMS_Sidebars::replace_default_sidebars()` function
+ Filter `lifterlms_order_process_pending_redirect` has been replaced with `lifterlms_order_process_payment_redirect`
+ Action `lifterlms_order_process_begin` has been deprecated
+ Removed  `lifterlms_order_process_complete` action
+ Replaced `LLMS_Course::check_enrollment()` with various new utilities. See `llms_is_user_enrolled()` for fastest use.
+ Officially removed the `LLMS_Language` class
+ Officially removed the `PluginUpdateChecker` class stubs we created to prevent updating issues with LifterLMS extensions during our transition to 2.0.0. This library has caused nothing but pain for everyone on our team and many of our users. It's gone now, forever.
+ Removed function `lifterlms_template_single_price()` and replaced with `lifterlms_template_pricing_table()`
+ Removed templates at "includes/course/price.php" and "includes/membership/price.php" in favor of "includes/product/pricing-table.php"
+ Removed `LLMS_Person::create_new_person()` in favor of `LLMS_Person_Handler::register()` or `llms_create_new_person()`
+ Removed `LLMS_Person->set_user_login_timestamp_on_register()` and are simply adding the metadata during registration
+ Removed `lifterlms_register_post` action hook which fired after new user registration validation, this has been replaced with `lifterlms_user_registration_after_validation`
+ Removed `lifterlms_new_person_data` and `lifterlms_new_person_address` filters, replaced with `lifterlms_user_registration_data`
+ Removed `LLMS_Person::login_user()` in favor of `LLMS_Person_Handler::login()`
+ background updater
+ system report facelift + inclusion of all new settings via `LLMS_Data` class
+ Fix setup wizard styles to follow update admin panel styles
+ add links to last step of setup wizard for documentation and demo
+ removed a bunch of deprecated coupon-related functions
+ added a "force ssl" option to ensure checkout is secured
+ added settings and options around recurring payments and staging sites to prevent duplicate charges when testing on a cloned site
+ Check course restrictions automatically when checking lesson
+ Added user_id to all access function checks to allow for checks for non current user
+ course restriction messages display regardless of enrollment status
+ check memberships and lock purchase of members only access plans
+ Fixed titles of course closed and open messages on the course restrictions options
+ record a start date for access plans based off when order moves to complete or active for the first time
+ automatically expire limited access plans
+ gave a quick facelift & unification to a lot of admin panel elements
+ Color consistency updated according to LLMS brand guide
+ Unified front and backend button classes
+ Updated all frontend buttons to have consistent classes
+ Removed the "FREE" lesson SVG in favor of simple text which allows translating
+ Install & activation overhauls. Resolves [#179](https://github.com/gocodebox/lifterlms/issues/179)
+ jQuery MatchHeight lib unignored
+ A bunch of settings pages updated and a bunch of settings deprecated
+ Gateways setting page removed
+ Memberships & Courses page combined into "Catalogs" settings
+ Added a data getting class used by the tracker class
+ added a new page creation function with better intelligence that (hopefully) prevents duplicate pages from being created during core page installation
+ new default country setting
+ all order status changes recorded as order notes
+ pending orders can be completed after failed payments
+ better handling for gateways with fields
+ JS spinners support multiples via start & stop!
+ Updated (and semi-finished) analytics
+ achievement metabox converted
+ minor updates to voucher class
+ Added a "post state" visible on the Pages posts table identifying if the page is saved as a LifterLMS page (EG: Checkout Page)
+ Fixed copy/paste error of duplicate enrollment closed message on course restrictions tab
+ Removed WC integration in favor of WC
+ Upgrade "back to course" template to new lesson API
+ Renamed `course/parent_course.php` to `course/parent-course.php` for template naming consistency
+ use `strict` when auto generating usernames when creating from email addresses, resolves [#182](https://github.com/gocodebox/lifterlms/issues/182)

##### 3.0.0 Auto Upgrader

+ lots of postmeta data rekeyed
+ intelligently generated defaults for various pieces of new meta data on courses, lessons, and memberships
+ automatically generate access plans from existing course and membership data
+ update existing orders to pull semi-accurate data into analytics based on new database structure
+ cleans database of a ton of deprecated options and postmeta data

##### Deprecated

+ function `llms_is_user_member()`, use `llms_is_user_enrolled()` instead
+ function `llms_check_course_date_restrictions()`
+ function `quiz_restricted()`
+ function `membership_page_restricted()`
+ function `is_topic_restricted()`
+ function `llms_get_post_memberships()`
+ function `llms_get_parent_post_memberships()`
+ function `parent_page_restricted_by_membership()`
+ function `outstanding_prerequisite_exists()`
+ function `find_prerequisite()`
+ function `llms_get_course_enrolled_date()`
+ function `llms_get_lesson_start_date()`
+ function `lesson_start_date_in_future()`
+ function `page_restricted_by_membership_alert()`
+ function `llms_does_user_memberships_contain_course()`
+ class `LLMS_Checkout`
+ function `LLMS()->checkout()`

##### Auto Enrollment

+ Course auto enrollment for Memberships has been restored
+ Works exactly the same as previously except auto-enrollment is not dependent on a course "belonging to" the membership via membership restrictions. This is because membership restrictions no longer apply to courses

##### Analytics

+ Charts! I'm really excited about this. I know we still need more data but please say nice things to me, I worked really hard on these little charts.
+ Updated styles & interface

##### bbPress

+ Restrict individual forums (and their topics) to LifterLMS Membership levels

##### BuddyPress

+ Fixes broken course display on bp profile
+ Adds memberships subpage to bp profile

##### notices

+ Admin notices class for managing admin notices, it's pretty neat!

##### Student Management on Courses and Memberships

+ All new and improved student management interface for managing student enrollments from courses and memberships

##### Deprecated

+ filter: `llms_meta_fields_course_main`, replace with `llms_metabox_fields_lifterlms_course_options`

##### Manual Payments

+ Manual Payment Gateway can now be enabled on the frontend!
+ When a manual payment is recorded the user will be redirected to a view order page where they will be prompted to make a manual payment
+ Define the payment instructions on the admin panel "Checkout Settings"
+ Once you verify payment, head to the pending order and hit the "Record a Manual Payment" button to record the transaction
+ Upon recording the order status will be upgraded to "Complete" and the user will be enrolled automatically

##### Student Dashboard Upgrades

+ More sane template hooks and functions
+ Pagination on Courses endpoint (view only a preview on the main dashboard)
+ Orders history & view orders screens!

Deprecated options (and related functions where applicable) for the following course & membership options:

  + `lifterlms_button_purchase_membership_custom_text`
  + `lifterlms_course_display_outline_lesson_thumbnails`
  + `lifterlms_course_display_author`
  + `lifterlms_course_display_banner`
  + `lifterlms_course_display_difficulty`
  + `lifterlms_course_display_length`
  + `lifterlms_course_display_categories`
  + `lifterlms_course_display_tags`
  + `lifterlms_course_display_tracks`
  + `lifterlms_lesson_nav_display_excerpt`
  + `lifterlms_course_display_outline`
  + `lifterlms_course_display_outline_titles`
  + `lifterlms_course_display_outline_lesson_thumbnails`
  + `lifterlms_display_lesson_complete_placeholders`
  + `redirect_to_checkout`

In all scenarios either a `add_filter` (returning false) or a `remove_action()` can be used to replicate the option.


v3.0.0-beta.4 - 2016-09-01
--------------------------

+ fix issue with course prereq checks
+ next payment due date visible on order admin view
+ trial end date visible on order admin view

##### Free Access Plans

+ "Free" access plans now defined as such based on a checkbox rather than by entering 0 into the price
+ Only single payment access plans can be free (a free recurring payment makes no sense but we can certainly discuss this if you disagree with me)
+ trials are disabled with free plans (because trials only apply to recurring plans)
+ sales are disabled for free access plans

##### Checkout Form JS API

+ unified JS checkout handler
+ allows extensions to enqueue validation or pre-submission JS functions that should run prior to checkout form submission

##### Manual Payment Gateway

+ handles purchase of access plans marked ar FREE & orders that are discounted to 100% via coupons

##### Open Enrollment

+ Open Enrollment allows users to register on the account dashboard without purchasing a course
+ Voucher settings are available to customize whether vouchers should be optional or required during open registration
+ Better error reporting around voucher usage during enrollment

##### Deprecated Functions

+ `llms_get_coupon()`
+ `get_section_id()`
+ `check_course_capacity()`

##### Quizzes

+ Updated admin metaboxes to use new metabox abstract class
+ display 0 instead of negative attempts on quiz summary
+ updated logic in start button template

##### Emails (for engagements)

+ Admin metabox updated to new API
+ Postmeta data migration:
  + `_email_subject` renamed to `_llms_email_subject`
  + `_email_heading` renamed to `_llms_email_heading`


v2.7.12 - 2016-09-22
--------------------

+ Added a new filter on content returned after port permission checks
+ Added additional information to plugin update message in preparation for major 3.0 release
+ Updated plugin contributor metadata


v2.7.11 - 2016-07-22
--------------------

+ Removed a duplicate action hook on course archive loop.
+ Switched registration template include to use a more sane function
+ Added updated banner adds with prettier ones. Wooooooo.


v2.7.10 - 2016-07-19
--------------------

+ Fix undefined noticed related to LifterLMS custom post type archive filtering
+ Fix filter which was supposed to allow custom engagement types to be queried & triggered by engagements automatically but was passing data incorrectly


v2.7.9 - 2016-07-11
-------------------

+ We are now properly storing delayed engagement trigger data.
+ Fixed an issue with our engagement query functions that caused, in very rare circumstances, the extra engagements to be triggered during an engagement trigger due to a lack of specificity in our query
+ Fixed an undefined property notice related to email engagements when the email had no subject or header
+ Fixed a typo in the description of a translation function.
+ Added an engagement debug logging function. You can log all sorts of data related to engagements by adding `define( 'LLMS_ENGAGEMENT_DEBUG', true );` to your `wp-config.php` file.
+ Allow course title shortcode to be used on course pages (and quizzes too). Documentation incorrectly said it was available on courses so we've fixed the function to allow for use on courses.


v2.7.8 - 2016-07-05
-------------------

+ Bugfix: Restore access to quiz results on quiz completion


v2.7.7 - 2016-07-01
-------------------

##### Russian

+ LifterLMS is now 100% Translated into Russian thanks to our new Russian Translation Editor [@kellerpt](https://profiles.wordpress.org/kellerpt/)

##### l18n

+ All transition messages between questions during a Quiz are now translatable.
+ LifterLMS subpages below the LifterLMS icon on the admin panel will now always display regardless of how you've chosen to translate the menu items. Hopefully puts to rest a long-standing i18n issue.

###### Bug fixes

+ Attempting to access a quiz when not enrolled in the associated course and having not properly started the quiz now results in a useful error message rather than a PHP warning.
+ We've adjusted the way we're adding a admin panel "separator" to reduce conflicts with other plugins that have menu items with the same position as our separator (51).
+ Added new logic to display an error message (instead of nothing) if there's an error during question loading.
+ Resolve issue with course progress bar when added to a quiz sidebar (assuming your theme has sidebar support on your quizzes).
+ Updated version number in the changelog for last version (it was supposed to be 2.7.6)


v2.7.6 - 2016-06-28
-------------------

+ Students manually removed by Memberships by using the "Students" tab of a LifterLMS Membership will now be fully removed from the membership.
+ Updated a few time-related strings to be l18n friendly. These items were all around Quiz time reporting and quiz time limits.
+ Updated testing information, tested up to WP 4.5.3
+ Fixed date of last release on changelog. It had the wrong date. Does that really matter?
+ Updated readme.txt description area, we have a new youtube video! Yassss.


v2.7.5 - 2016-06-13
-------------------

##### New features
+ Added an "id" parameter to both LifterLMS Courses and LifterLMS Memberships shortcodes

##### i18n
+ Allow date translation on quiz results screen by using `date_i18n()` instead of `date()`
+ Allow date translation on my courses screen by using `date_i18n()` instead of `date()`
+ Ensure course status "Enrolled" is translatable on my courses screen

##### Fixes
+ Thanks to [@kjohnson](https://github.com/kjohnson) who fixed undefined index warnings & errors which occurred when viewing the last lesson in a section when the next section contained no lessons.
+ Resolved an issue where formatting for "Restricted Access Description" course content would not display proper formatting.
+ Fixed an issue with the "FREE" stamp for a free lesson caused layout issues.
+ Removed the "is-complete" css class from incorrectly being added to lesson preview tiles for free lessons
+ Fix an escaping issue when rendering Course titles inside LifterLMS notices. Prevents "\'s" from displaying when "'s" should be displaying (and similar issues).


v2.7.4 - 2016-05-26
-------------------

+ Fixed a bug with the new localization methods from 2.7.3
+ Removed bundled it_IT translation files in favor of official language pack available at [https://translate.wordpress.org/projects/wp-plugins/lifterlms/language-packs](https://translate.wordpress.org/projects/wp-plugins/lifterlms/language-packs).
+ Removed bundled en_US translation files because LifterLMS is in English so the files are unnecessary.
+ Fixed a few mis-labeled filters applied when registering LifterLMS Custom Post Types
+ Adjusted the default supported features of LifterLMS Quizzes and Questions
  + Quizzes now support custom fields as per user request
  + Commenting, thumbnails, and excerpts are no longer "supported" as they were never intended to be and were never correctly implemented.
    + If you are relying on any of these features for your quizzes or questions please use the following filters to re-implement these features: `lifterlms_register_post_type_quiz` or `lifterlms_register_post_type_question`. These will allow you filter the default arguments LifterLMS passes to the WordPress function `register_post_type()`


v2.7.3 - 2016-05-23
-------------------

+ Added a separate filter for login redirects `lifterlms_login_redirect` and added the user_id as a second parameter available to the filter.
+ Added second parameter to `lifterlms_registration_redirect` to allow access to the registered user's user_id.
+ Fixed a timestamp conversion issue on Course sale price checks that caused indefinite sales (those with no date restrictions) to appear not on sale during certain periods of time. The period would differ depending on the server's timezone settings and the time of visit.
+ Added a "Pointer" when hovering quiz summary accordion to allow for a slightly more obvious user experience that the elements are expandable.
+ Added some new localization methods to ensure strings that only appear in Javascript files will be translator friendly. This initially fixes a few issues on the Quiz Summary page and during quiz taking where strings only appeared in Javascript and were, therefore, completely inaccessible to translators.


v2.7.2 - 2016-05-19
-------------------

+ In course syllabus widget & shortcodes free lessons will now be clickable links.
+ Record `llms_last_login` timestamp in usermeta when a user registers.


v2.7.1 - 2016-05-09
-------------------

##### Enrollment & Voucher Checks

+ Enrollment functions will now automatically check to ensure that users are not already enrolled in a course or membership before enrolling. This addresses an issue which would create double enrollment for user redeeming a voucher for a product they were already enrolled in.
+ Vouchers will now automatically check to see if the user has already redeemed this voucher before allowing the user to redeem it. This would have caused multiple enrollments and would allow one user to eat up an entire voucher by using it over and over again for funsies. A voucher can now *only* be redeemed once by a user as intended.
+ `llms_is_user_enrolled()` now allows developers to check membership enrollment. Previously this function would only check enrollment of Courses despite what the documentation stated.

##### Translation

+ 3 strings have had translation functions added to them. This makes LifterLMS voucher redemptions translatable!

##### Bugs & Fixes

+ Fix javascript dependency & enqueueing issue on admin panel which prevented LifterLMS settings from saving correctly in various places
+ Removed inline CSS from "next lesson button" on quiz completion / summary screen. This was overriding some default styles and making the button very thin and gross.


v2.7.0 - 2016-05-05
-------------------

##### LifterLMS Custom User Fields Exposed

+ Custom fields added during registration via LifterLMS account settings are now exposed on the admin panel via the student's WordPress user profile
+ All custom fields that are available (billing and phone) are editable on the WordPress user profile by anyone with profile edit access regardless of LifterLMS settings. If the settings are disabled (eg not required for registration) you can still add this information manually to a user's profile. This is useful if you require the information and then disable it later, you would still be able to access the information on the admin panel but would no longer be required for user's during registration.
+ A few new filters added to help developers customize the experience here. Check out the documentation at [https://lifterlms.com/docs/lifterlms-filters/#admin-user-custom-fields](https://lifterlms.com/docs/lifterlms-filters/#admin-user-custom-fields)

##### Membership Manual Add & Remove Student Functions

+ Duplicated "Students" tab from the Course admin screen to Memberships
  + Students can be manually added to a membership by an admin
  + Students can be removed manually from a membership by an admin

##### Updates

+ Added the ability for students to edit their phone number via their account settings page if the phone number registration option is enabled on the site.

##### Fixes

+ Fixed a few spelling errors on LifterLMS admin panel order screens
+ Fixed a typo on meta data for LifterLMS admin created (manual) orders


v2.6.3 - 2016-05-02
-------------------

+ Removed redirecting action from WooCommerce integration that was causing issues on multiple product purchase checkouts with larger databases.
+ Added a new payment action `lifterlms_order_complete` which runs at the same time as some previous actions during payment processing but servers a different purpose. This is mostly in preparation for a forthcoming AffiliateWP integration.
+ Fixed an issue with LifterLMS certificate background image that caused the wrong dimensions to be returned when outputting a LifterLMS certificate background image


v2.6.2 - 2016-04-27
-------------------

+ Fix class conflict in collapsible course outline widget template which caused some UX issues.
+ Added new filters run during course & lesson sidebar registration to allow customization of LifterLMS sidebars
  + `lifterlms_register_course_sidebar`
  + `lifterlms_register_lesson_sidebar`
+ Removed a stray logging function.
+ Cleaned up some undefined variable warnings & notices on the quiz summary template
+ Fixed an issue appearing when registering users did not submit the optional phone number which caused a PHP notice
+ LifterLMS Orders generated by WooCommerce will now have a payment method of "WooCommerce". This also addresses an undefined notice produced during WooCommerce order completion because a LifterLMS Payment Method wasn't being defined.


v2.6.1 - 2016-04-26
-------------------

+ Fix class conflict in collapsible course outline widget template which caused some UX issues.


v2.6.0 - 2016-04-25
-------------------

##### Collapsible Course Outline Widget

+ By request we've added an option to make your course outline widgets collapsible!
+ View feature [Documentation](https://lifterlms.com/docs/course-syllabus-widget/)
+ New translations available related to feature. I think it's 4 strings.

##### Bug Fixes

+ Removed an unused CSS selector that caused some issues on the admin panel. This resolves an issue identified with the Page Builder by SiteOrigin plugin. The selector was very generic (`.title`) and may have caused issues with other themes or plugins using that class.
+ Resolved an issue that prevented post update, save, and publishing messages for core post types (posts, pages) from displaying properly.


v2.5.1 - 2016-04-22
-------------------

+ Fixed session handler initialization as it was being initialized prior to user data availability.
+ Staged `LLMS_Language` class  for deprecation in favor of WordPress translation functions `__()`, `_e()`, etc... **If you're a developer you'll start seeing warning's on screen or in your logs if you're using this function, it will be completely removed in the next MAJOR release (3.0.0)**
+ Added a new function to handle the deprecation warning above (`llms_deprecated_function`) and now that we have this function we'll start deprecating all the things. Just kidding, or am I?
+ This gives translators access to 69 new strings that were previously untranslatable! However, this number might be inaccurate +/- 5 strings. I only counted it once and I don't feel like the exact number is important enough for a recount to ensure accuracy. /shrug


v2.5.0 - 2016-04-15
-------------------

**Admin Panel Order Table Updates**

+ Several visual improvements to the table
+ Exposed the following fields on the table
  + Order number
  + Customer name (with a link to their WP profile)
  + Customer email (mailto link)
  + Payment gateway used (this is filterable per gateway as well so gateways can improve the functionality here in the future)
+ Added a link to the product edit page from the product column
+ Free orders will now display as "Free" as opposed to {currency}0.00
+ Removed the not-so-useful "Order" column which was a long ugly string of data that was displayed in other columns already
+ Removed the "Password Protected" flag since *all* orders are always automatically password protected for added security. This flag distracts from the interface so we've removed it. Orders _are_ still password protected though.
+ Numerous strings that were previously not translatable have been made translatable on this screen
+ A few new strings that previously didn't exist are now available for translation

**Fixes and other small changes**

+ Fixed a translation issue on the LifterLMS menu that we thought we fixed in the last release but have now really fixed (probably).
+ Fixed a few small issues with engagements as they related to external engagements triggered by other plugins and LifterLMS extensions.
+ Tired of seeing a banner for a plugin you've already installed? We have your back! The general settings area will now only display banners for plugins that aren't installed.
+ Fixed various javascript issues, mostly removed `console.log()` statements.
+ Fixed a spelling error on the membership admin panel settings screen


v2.4.1 - 2016-04-07
-------------------

+ Tested and compatible with WordPress 4.5 Release Candidate.
+ Fixed a pagination issue related to updates to the quiz builder from 2.4.0 which would cause results to return incorrect results on the last page of paginated results in the "Add Question" dropdown.
+ Added translation functions to LifterLMS Menu Items. Resolves an issue where translated LifterLMS installations might not see all the menu items under the LifterLMS Icon.
+ Italian translation updates courtesy of [@AndreaBarghigiani](https://github.com/AndreaBarghigiani)
+ On some themes the "Next Lesson" button was displayed while quizzes were being taken. We now *always* hide the next lesson button when a quiz is being taken.
+ Adjusted some static functions to be non static in `class.llms.post-types.php`
+ Added a function to ensure support for post thumbnails on LifterLMS custom post types
+ If a user views a course that is available to them because it belongs to a membership level they are a member of, course pricing information will no longer be visible. This addresses a confusing user experience issue. Previously it _appeared_ like payment for a course was still required even though it really wasn't.
+ Fixed undefined variable warning on quiz summary screen
+ Resolve an issue with quiz timer that caused issues on time display if the time limit was set to a fraction of a minute (eg 1.5 minutes)
+ resolved an undefined variable warning resulting from courses still holding a reference to a membership after the membership has been deleted or trashed


v2.4.0 - 2016-03-29
-------------------

##### Performance Improvements on the LifterLMS Quiz Builder

+ Completely rewrote Javascript associated with building a LifterLMS Quiz. Our users have been identifying some performance issues and slowness when working with larger databases. We've refactored the Javascript and our related database queries to allow faster quiz building and fewer timeouts when working in the quiz builder.
+ Fixed a bunch of undefined variables that would produce PHP warnings in various quiz templates
+ Added validation to quiz questions on the admin panel to prevent the same question from being added to a quiz multiple times.
+ Fixed an issue that prevented quizzes from correctly marking the lesson as completed when the quiz was passed.
+ Added three new actions now available for developers to hook into.
  + `lifterlms_quiz_completed` called upon completion of a quiz (regardless of grade)
  + `lifterlms_quiz_passed` called when a quiz is completed with a passing grade
  + `lifterlms_quiz_failed` called when a quiz is completed with a failing grade
+ Course Progress and Course Syllabus shortcodes (and widgets) now work on Quiz pages
+ Completed Metabox refactor for the LifterLMS Quiz post type and removed `LLMS_Meta_Box_Quiz_General` class. All functions now exist in `LLMS_Meta_Box_Quiz`
+ Added validation to the Quiz general settings
  + Cannot only enter numbers in attempts, percentage, and time limit fields
  + Cannot enter a negative number or a number greater than 100 in the percentage field
+ Removed the membership restriction metabox from quiz admin and question admin screens

##### Other fixes

+ Fixed an issue that caused multiple certificates awarded for the same Course or Lesson to not properly display on the My Account page.
+ Removed an event bound to the publishing of a LifterLMS Question that called a function that didn't exist and caused a Javascript error on the console (but didn't actually cause any problems)
+ Removed a warning message that would display on sidebars when a shortcode was being displayed in a place that it couldn't function. We now simply don't display any content if the shortcode can't function.
+ Resolved an issue that prevent users from "purchasing" products when using a 100% coupon and the Stripe payment gateway. Users experiencing this issue should also update to Stripe 3.0.1.
+ Fixed an AJAX related issue that was incompatible with PHP7
+ Added the ability to have a "max" value on LifterLMS Admin Metabox number fields


v2.3.0 - 2016-03-24
-------------------

##### Engagements Refactoring (lots of bugfixes, performance improvements, more hook & filter friendly)

+ We've completely rewritten the LifterLMS Engagement Handler methods (`class LLMS_Engagements`) and added some new engagement actions.
+ The rewrite unifies engagement handling into one function that can be easily hooked into by plugin and theme developers.
+ We've moved any engagement related data out of the main `LifterLMS` class
+ Fixed the broken engagement delay functionality which now runs of `wp_schedule_single_event`. This makes the function more reliable and also keeps it within the traditional WordPress architecture.
+ Added an additional check before sending emails or triggering any engagements that will prevent the achievement from being awarded or the email from being sent if the post is in not published. This fixes an issue that caused emails in the trash from still being emailed.
+ Removed the unused `LLMS_Engagements` class and file
+ Added two new engagement trigger events "Membership Purchased" and "Course Purchased"
+ Deprecated actions -- Removes some redundancy because the triggering actions (`lifterlms_course_completed` triggered the notification action, instead `lifterlms_course_completed` simply triggers the engagement now).
  + `lifterlms_lesson_completed_notification`
  + `lifterlms_section_completed_notification`
  + `lifterlms_course_completed_notification`
  + `lifterlms_course_track_completed_notification`
  + `lifterlms_course_completed_notification`
  + `lifterlms_user_purchased_product_notification`
  + `lifterlms_created_person_notification`

##### Bug and Issue fixes

+ Adjusted the size of the LifterLMS Admin Menu Icon. It was super big because of, perhaps, some overcompensation. It caused an issue on Gravity Forms admin pages for some reason (we didn't ever determine why) but we've resolved it by using an appropriately sized icon.
+ Fixed a CSS issue that caused some weirdness on the course archive page on mobile devices
+ Fixed an issue with automated membership expirations
+ Fixed a function that should have been called statically in `LLMS_Ajax` class
+ Fixed a ton of issues related to the triggering of engagements and cleaned up a lot of classes and functions associated with them.
+ Properly instantiate `LifterLMS` singleton via LLMS() function and prevent direct instantiation of the class via `new LifterLMS()`.
+ Removed the deprecated 'class.llms.email.person.new.php' file as it was rendered useless a long time ago and caused some duplicate emails.


v2.2.3 - 2016-03-15
-------------------

##### Translations

+ Added translation functions around quite a few untranslated strings. Thanks to the team at [Netzstrategen](http://netzstrategen.com)
+ Added German translation .mo and .po files again thanks to the team at [Netzstrategen](http://netzstrategen.com)

##### Student Enrollment Functions

We've refactored a bit of our code related to how to programmatically enroll a student in a course or membership during registration and purchase.

A new class `LLMS_Student` makes working with a LifterLMS student (user) a bit easier. We'll begin exposing user meta data through this class as we continue to improve the usability of the codebase for other developers.

We've also created a simple enrollment function `llms_enroll_student()` which enables programmatic enrollment to LifterLMS courses or memberships. This was previously handled in a pretty schizophrenic manner and this unifies various ways of enrollment into one clean function. All enrollment moving forward will use this functions.

The enrollment function calls a new action as well as calling existing enrollment-related actions:

+ `before_llms_user_enrollment` - called immediately prior to beginning the user enrollment function
+ `llms_user_enrolled_in_course` (previously existing)
+ `llms_user_added_to_membership_level` (previously existing)

This also addresses an issue that prevented the `llms_user_enrolled_in_course` action from being called when a user was auto-enrolled in a course because they joined a membership level that included auto-enrollment in one or more courses.

##### Bug and Issue fixes

+ Fixed an inconsistency in the way membership IDs were being saved to the postmeta table that would cause courses to not *appear* restricted on the Membership Enrollment tab, even though they were actually restricted and functioning correctly.
+ New lines are now preserved in the quiz question clarification text areas, thanks to @atimmer
+ Escape HTML in the quiz question description fields on the admin panel to allow outputting html without rendering it, thanks @atimmer
+ Fixed an issue related to the outputting of restricted course and membership content which caused errors on certain themes
+ added a clearfix to the `.llms-lesson-preview` element on the course syllabus template
+ Removed the `class.llms.person.handler.php` file as it wasn't actually being used by anything anywhere and contained no functions
+ Removed some unused and deprecated class functions from the LLMS Student Metabox class
+ Fixed an undefined javascript error resulting from code cleanup in 2.2.2. This issue prevented Vouchers from being published. The code has been further cleaned.


v2.2.2 - 2016-03-15
-------------------

##### One step closer to a public GitHub repository

We've made a massive syntactical update to almost every file in the codebase for a (finally) unified and clearly defined coding standard. This puts us one step closer to beginning to open our GitHub repo publicly and accepting pull requests and contributions from developers everywhere.

Okay, we haven't exactly _clearly_ defined it yet. We're working off a modified version of the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/).

Notable exceptions are related to file names because Thomas Levy didn't have the energy to rename a bunch of files as well as ignoring the Yoda Conditions standards. We'll be fixing these deviations in the future.

##### Quizzes

+ Created new time calculation and humanizing functions related to the display of quiz time on quiz results pages
+ Quizzes will now display hours, minutes, and seconds depending on the time it took to take the quiz
+ Timing calculations are more accurate and quizzes that are completed in less than 60 seconds will not bug out and display incredibly long lengths
+ Resolved an issue that occasionally prevented quiz data from saving during the last question causing the quiz to hang in an uncompletable state
+ Quiz questions now have a default point value of 1, thanks @atimmer
+ Quiz question answers now accept valid HTML as per `wp_kses_post`, thanks again to @atimmer

##### Translations

+ Thanks to @AndreaBarghigiani and the team at [codeat](http://codeat.co/) LifterLMS now ships with Italian language files!

##### Issue and bug resolutions

+ Fixed a restriction issue that would happen when individual lessons were restricted to a membership level
+ Fixed an issue with the `[lifterlms_my_account]` shortcode that was preventing the shortcode from working on the Divi theme.
+ Engagements will now only be triggered if they are "Published". Resolves an issue where draft or trashed engagements were still firing.
+ Fixed CSS overflow on LifterLMS Meta boxes. Fixes an issue where select boxes would be hidden inside a metabox.
+ Changed the ConvertKit extension banner image on the LifterLMS general settings page and replaced added a link to the extension now that it's available.
+ Added a link to the new ConvertKit extension to the .org readme
+ When restricting an entire site to a membership level the page selected as the "Terms and Conditions" page in LifterLMS settings will automatically bypass Membership restriction settings. This will allow your unregistered users to actually read the T&C that they're confirming during registration.
+ CSS fix for `has-icon` class on course syllabus
+ Fixed a PHP warning that displayed when purchasing a membership with no auto-enrollment courses
+ Fixed an undefined variable warning in the WooCommerce integration class
+ Fixed a few templating issues related to certificates
+ Added a few new CSS rules that should make certificates more compatible across various themes
+ Added a css class to LifterLMS Next Lesson buttons, `llms-next-lesson`
+ Updated the scheduled event name for cleaning up LifterLMS session data from the WP database. It had a conflicting name with the scheduled event for expiring LifterLMS memberships.


v2.2.1 - 2016-03-07
-------------------

+ Added a few actions to the `class.llms.voucher.php` class.


v2.2.0 - 2016-03-04
-------------------

##### Translations

+ We've updated our .pot file for the first time in quite a while. We're really sorry for de-emphasizing translation. An updated .pot file will now accompany each version of LifterLMS whenever a translatable string is adjusted or when a new string is added.
+ We've also made it easier to include custom translations. Read our [Translation Guide](https://lifterlms.readme.io/docs/getting-started-with-translation).

##### Certificate Background Images

_We've completely rewritten the certificates template (but it's all backwards compatible)._

+ New filters are available to make customizing the certificate template easier for developers. All new filters are documented at [https://lifterlms.readme.io/docs/functions-certificates](https://lifterlms.readme.io/docs/functions-certificates).
+ A new WordPress Image Size is now available and will be used for generating the image used by default when uploading certificates to the media library. Fore more information on these new settings visit [https://lifterlms.com/docs/certificate-background-image-sizes/](https://lifterlms.com/docs/certificate-background-image-sizes/).

##### Course and Membership Pricing & Sales

+ Sale price start and end date are now completely optional.
  + Provide neither a start date nor an end date to have a sale run indefinitely
  + Provide a start date with no end date to have a sale start at a pre-determined time with no pre-determined ending
  + Provide an end date with no start date to have a sale end a a pre-determined date but start immediately
  + Provide a start date and an end date to have a sale run for a pre-determined period of time
+ Optimized the `LLMS_Product` class to provide more reliable and extendable use of the class
+ The templates related to pricing functions have been refactored. Affected templates include: "templates/course/price.php", "templates/loop/price.php", "templates/membership/price.php"
+ Many people complained about the size of the `.llms-price` element on course and membership tiles on loop pages. We removed the inflated size and will now default to your theme for sizing. You selector remains the same if you wish to customize the size of the price text.

##### Coupon Updates

+ Coupons can (finally) be removed after being applied!
+ Coupons can now be restricted to specific courses and/or memberships
+ Percentage based coupons can no longer be created with a value larger than 100%
+ Added numeric restrictions to usage and coupon amount fields on the admin panel
+ Fixed a programmatic error that prevented product restrictions from being entirely removed
+ Fixed a few instances where hardcoded a US Dollar symbol ($) where a dynamic currency symbol should have been displayed.

##### Wow Bad Syntax, Very Typo, Such Grammar, So Undefined

+ Fixed a typo in filter associated with modifying the registration of the lesson post type (`lifterlms_register_post_type_lesson`)
+ Fixed a grammatical error in a Membership restriction message
+ Fixed a syntax error in "/templates/course/outline-list-small.php" that prevented the `done` CSS class from being properly applied to completed lessons
+ Fixed a few typos and grammatical errors on the Course and Membership settings metaboxes
+ Fixed an undefined variable in "templates/course/syllabus.php"
+ Fixed an issue on the system report that prevented the "Courses Page" from being reported properly
+ Fixed an issue that caused PHP warnings on the admin panel for students or WP users with no LifterLMS menu permissions
+ Fixed an installation warning caused by a reference to an undefined class variable
+ Fixed an HTML character encoding issue that caused `&ndash;` to display on the admin panel when viewing LifterLMS Orders
+ Fixed an undefined variable found during engagement triggering for non-email engagements.

##### Additional, less exciting updates

+ Added input type restrictions to course & membership price fields.
+ The "Emails" LifterLMS Settings Tab has been renamed "Engagements." All Email settings are found under this tab as well as some new settings related to other kinds of LifterLMS engagements.
+ Added `the_content` filter to the content of emails sent by LifterLMS
+ Fixed some CSS issues on Voucher screens
+ Updated Courses settings retrieval function to retrieve the correct "shop" page id
+ Added translation functions to voucher export meta box class
+ Vouchers Export metabox will only allow export after a voucher has been published. This prevents an issue caused by attempting to export voucher codes before they were saved in the database via the publish / save action.
+ Vouchers can no longer be saved with a use of "0"
+ added a CSS class for various syllabus outputs that notes that the lesson has an icon. Previously CSS relied on "is-complete" to output styles for having an icon but with the addition of placeholders the "is-complete" is used only to note that the lesson is completed and "has-icon" is a more semantic class that applies to both complete and incomplete lessons with an icon.
+ Removed the membership restriction metabox from some post types where it shouldn't have been displaying.
+ admin select fields now have an option `allow_null` (default to "true") which can be set to `false` in order to prevent the output of the default "None" option


v2.1.1 - 2016-02-15
-------------------

##### System Report

+ A new LifterLMS Admin Page is available which reports information about various server, WordPress, and LifterLMS settings that will help expedite support requests.
+ More information about the system report is available at [https://lifterlms.com/docs/how-to-use-the-lifterlms-system-report/](https://lifterlms.com/docs/how-to-use-the-lifterlms-system-report/)

##### Additional Updates

+ Fixed a javascript issue which prevented users from saving vouchers
+ Cleaned up formatting in a large number of included PHP files


v2.0.5 - 2016-02-15
-------------------

+ PayPal requests now using HTTP Version 1.1 in preparation for June 2016 [TLS 1.2 and HTTP/1.1 Updates](https://www.paypal-knowledge.com/infocenter/index?page=content&widgetview=true&id=FAQ1914&viewlocale=en_US). This resolves user's inability to begin PayPal checkout when using Sandbox mode.
+ Updated deprecated function opt out to run off a constant that can be defined in `wp-config.php` instead of using a filter that is hard to use in the way that it is intended.


v2.0.4 - 2016-02-15
-------------------

+ Fixed a typo on the `class_exists` check in the deprecated functions file
+ added a filter so that progressive users can opt out of loading the deprecated functions file


v2.0.3 - 2016-02-12
-------------------

+ Removed an unused quiz stub


v2.0.2 - 2016-02-11
-------------------

+ Bugfix: removed a progressive syntax array that caused fatal errors on older versions of PHP


v2.0.1 - 2016-02-11
-------------------

##### Updated General Settings Screen

+ Improved the general settings interface to be more visually appealing and to provide some ad space to alert customers to other LifterLMS products and information.
+ Moved Currency options to the Checkout settings screen

##### Bug Fixes

+ Properly initialized jQuery on the vouchers metabox admin scripts
+ removed some php shortcut echos (`<?= $var; ?>`)
+ Resolve issue where courses that are available with a membership or on it's own outside of the membership would prevent users from accessing content if they were not a member.
+ Fixed a few files where undefined variables were being referenced and generating php notices
+ removed an call to a WordPress core function that has never existed. Not sure what we were thinking there...

###### Enhancements

+ Updated CSS to provide better course syllabus layout on smaller screens
+ Added validation to prevent against duplicate voucher code creation


v2.0.0 - 2016-02-04
-------------------

##### Auto-advancing lessons

+ We've heard your feedback and added a new global course option which will auto-advance a student to the next lesson upon lesson completion.

##### Bug Fixes

+ Added spaces between numbers and "of" on the counter for course syllabus templates
+ Removed a template hook that was creating duplicate lesson thumbnails on quite a few themes

##### Membership Admin Improvements

Visit the "Enrollment" tab on any membership to see some new additions to make managing your memberships easier.

+ You can now add courses to and remove courses from a Membership from the Membership itself
+ You can now opt to automatically enroll students in a course (or multiple courses) when they sign up for a membership by checking "Auto Enroll" next to the course on the Membership enrollment tab

##### Student Enrollment & Removal on Courses Admin Screen

We've updated the Students tab interface for performance and usability!

+ AJAX enabled searching by student name and or email
+ Increased performance for course page load by only calling student information when needed. This resolves a bug identified by users with large user databases and/or low-powered servers.
+ Allow for addition or removal of several students at a time.

##### Syllabus Template

+ Added a Course setting to optionally enable Lesson Thumbnails on the Course Syllabus
+ Added a Course setting Display greyed out lesson completion checkmark icons on lessons not competed in the course syllabus
+ Reworded CSS on the course syllabus to rely on floats rather than absolute positioning, should allow for more robust customization with less frustration
+ Refactored the syllabus template at "templates/course/syllabus.php" for better performance and readability

##### Updates and enhancements

+ User email is now displayed on the "Students" table on student analytics screens
+ Membership now has it's own admin menu
+ Reordered the LifterLMS admin menu and submenu items
+ Removed membership specific taxonomies from courses
+ Removed course specific taxonomies from memberships
+ Coupon code is now a required field when creating a coupon
+ "Humbled" the metabox on all post types that restricts the post to a membership. The metabox would previously gain priority over the WordPress publishing actions metabox. The priority has been reduced to "default" and will to fall into line with all other metaboxes on the screen and appear based on registration priority. If you can't find the metabox, SCROLL DOWN! If you want to put it back up on the top, you can simply drag it up there and WordPress will save your preference.

##### Deprecated Classes

We've added a "deprecated" file which holds a few stubs for classes and functions deprecated below as to prevent fatal errors. The functions and classes in the deprecated class are classes which we know are being utilized by approved LifterLMS extensions and will allow users to upgrade LifterLMS without upgrade extensions without breaking their websites!

+ `LLMS_Activate` which as previously used to activate the plugin for updates via the LifterLMS Update Server and is no longer required.
+ PUC (plugin update checker) Library has been completely removed as it is no longer required for plugin updates.
+ `LLMS_Analytics_Dashboard` was removed as it was a stub that was never used and shouldn't have ever been released as a part of the LifterLMS codebase. I can't believe no one reported this bug!

##### Deprecated Functions

+  `lifterlms_template_section_syllabus()`

**The following are officially deprecated and removed to prevent WooCommerce compatibility conflicts**

+ `is_shop()` replaced by `is_llms_shop()`
+ `is_account_page()` replaced by `is_llms_account_page()`
+ `is_checkout()` replaced by `is_llms_checkot()`

##### Deprecated Templates

+ templates/course/section_syllabus.php

##### New Account Dashboard Filters

*[View documentation for more information](https://lifterlms.readme.io/docs/filters-account)*

+ `lifterlms_account_greeting`
+ `lifterlms_my_courses_title`
+ `lifterlms_my_courses_enrollment_status_html`
+ `lifterlms_my_courses_start_date_html`
+ `lifterlms_my_courses_course_button_text`
+ `lifterlms_my_certificates_title`

##### New Checkout Page Filters:

*[View documentation for more information](https://lifterlms.readme.io/docs/filters-checkout)*

+ `lifterlms_checkout_user_logged_in_output`
+ `lifterlms_checkout_user_not_logged_in_output`

##### New Course Filters:

*[View documentation for more information](https://lifterlms.readme.io/docs/filters-course)*

+ `lifterlms_product_purchase_account_redirect`
+ `lifterlms_product_purchase_redirect_membership_required`
+ `lifterlms_product_purchase_checkout_redirect`
+ `lifterlms_product_purchase_membership_redirect`
+ `lifterlms_lesson_complete_icon`


v1.5.0 - 2016-01-22
-------------------

##### WooCommerce Integration Enhancements

__NOTE: The following enhancements only apply when the WooCommerce Integration is enabled__

**Always redirect to the WooCommerce Cart when a SKU Matched Product can be found**

+ LifterLMS Products (courses and memberships) which are SKU matched to a WooCommerce product will now automatically add the related WooCommerce product to the WooCommerce shopping cart and then automatically redirect the visitor to the WooCommerce cart when the visitor attempts to enroll in a course or membership from the LifterLMS course or membership page.
+ If no WooCommerce product is found via a SKU match, the user will proceed to the LifterLMS checkout.
+ This will enable you to determine which Cart you want a user to use on a product by product basis. You may sell certain courses via WooCommerce and others via LifterLMS (should you choose to do so).

**Multiple Item Checkout**

+ When a WooCommerce order is complete user's will now be automatically enrolled in **all** courses and/or memberships in the WooCommerce order. This improves upon a previously limitation that would only allow WooCommerce checkout with one LifterLMS product at a time.
+ The products in the order will be intelligently SKU matched to LifterLMS Courses or Memberships.
+ You may also mix and match between WooCommerce products matched to LifterLMS products and those which are not matched to LifterLMS products. For example, your customers may now buy a Course via SKU matching as well as a T-Shirt that is not matched to a LifterLMS course via a SKU.

##### Other Fixes and improvements

+ Fixed a bug that caused quiz results to display for users who had never taken the quiz.
+ Added Wistia as an oEmbed provider to fix an issue related to default oembed handling in WordPress 4.4.
+ added a `.cc_cvv` class that mimics the existing `#cc_cvv` styles to allow gateway extensions to change the ID of the field in their credit card forms
+ Added support for new 1.4.5 capability fixes to be also be reflected under the "+New" menu item in the WP Admin Bar. There are no changes to the filters, the capability filters will simply also remove restricted post types from the admin bar now (as they should).
+ Tested and compatible up to WordPress 4.4.1

##### Deprecations

**The following functions have been staged for deprecation in LifterLMS 2.0!**

+ Setup the `is_account_page()` function to be replaced by `is_llms_account_page()` function. The original causes conflicts when WooCommerce is installed as WooCommerce includes a core function by the same name. All references to `is_account_page()` in LifterLMS have been removed and the original has been left to prevent issues with developers currently relying on the LifterLMS version of the function.
+ Setup the `is_checkout()` function to be replaced by `is_llms_checkout()` function. The original causes conflicts when WooCommerce is installed as WooCommerce includes a core function by the same name. All references to `is_checkout()` in LifterLMS have been removed and the original has been left to prevent issues with developers currently relying on the LifterLMS version of the function.


v1.4.5 - 2016-01-13
-------------------

+ Significant improvements to LifterLMS admin permissions as well as a hardening of permissions. Previously LifterLMS admin screens and menus were available to any users with `edit_posts` capabilities. This has been changed to `manage_options`. Filters for all screens and menus have been added with this release. If you're site currently relies on users with `edit_posts` to be able to access LifterLMS settings and analytics screens you must utilize these new filters in order to maintain their access. Please see full documentation on the new filters at [https://lifterlms.readme.io/docs/filters-admin-menu-and-screen-permissions](https://lifterlms.readme.io/docs/filters-admin-menu-and-screen-permissions). **Please consider testing your changes outside of production before updating to LifterLMS 1.4.5 in production.**
+ Allow "Payment Method" to be translated on the "Confirm Payment" screen
+ Allow the name of the payment gateway to be filtered on the "Confirm Payment" screen
+ Added pagination support to lifterlms membership archive pages
+ Fixed a bug related to some required global variables for quizzes and lessons being incorrectly set on certain hosts
+ updated readme file to remove incomplete documentation
+ Added Chosen multi-select options to admin panel metaboxes (settings and posts)
+ Added two new actions that developers can hook into:
  + `llms_user_enrolled_in_course`, called when users are enrolled in a course. Usage details available [here](https://lifterlms.readme.io/docs/actions-user#llms_user_enrolled_in_course).
  + `llms_user_added_to_membership_level`, called when users are added to a membership level. Usage details available [here](https://lifterlms.readme.io/docs/actions-user#llms_user_added_to_membership_level).


v1.4.4 - 2015-12-21
-------------------

##### Updates

+ My account page can now (optionally) display a list of memberships a student is currently enrolled in
+ Student analytics on the admin panel display student's Memberships
+ Student analytics on the admin panel will now display student's progress through courses in addition to their current enrollment status.
+ Custom taxonomy archive templates for Course tags, categories, tracks, and difficulties now exist and properly function.
+ Custom taxonomy archive templates for Membership categories and tags now exist and properly function.
+ Added the `[lifterlms_memberships]` shortcode which was documented but never implemented. Details on usage available at [https://lifterlms.readme.io/docs/short-codes#memberships-lifterlms_memberships](https://lifterlms.readme.io/docs/short-codes#memberships-lifterlms_memberships)
+ Added basic styles to LifterLMS pagination HTML elements (elements with class `.llms-pagination`) which formerly had no associated CSS.

##### Deprecations

+ Setup the `is_shop()` function to be replaced by `is_llms_shop()` function. The original causes conflicts when WooCommerce is installed as WooCommerce includes a core function by the same name. All references to `is_shop()` in LifterLMS have been removed and the original has been left to prevent issues with developers currently relying on the LifterLMS version of the function. It *will* be removed in the next major update (2.0) and will be noted as an officially deprecated feature at that time.

##### Bug fixes

+ Fixed pagination issues when using the `[lifterlms_courses]` shortcode
+ Fixed an issue with the `is_shop()` function that prevented courses per page option from functioning properly on the default course archive page
+ Student analytics profile on admin panel will display the correct number of memberships the student is enrolled in.
+ Fixed a small CSS issue that caused extra white space to be displayed above Course or Membership tiles on archive pages when using the WordPress Twentyfifteen default theme

##### Miscellaneous

+ Account settings screen displays the correct title ("Account Settings" it previously said "Archive Settings")
+ Made language changes to the LifterLMS settings intro screen copy
+ Added link to CourseClinic on settings intro screen
+ Added link to LifterLMS documentation on the settings intro screen


v1.4.3 - 2015-12-11
-------------------

+ Fixed an issue that could prevent some older servers from being able to run LifterLMS


v1.4.2 - 2015-12-10
-------------------

+ Tested and compatible with WordPress version 4.4
+ BugFixes: fixed issue in `llms_featured_img()` that was preventing the `$size` variable from being passed to the WP core function being utilized.
+ BugFixes: correctly handling conflicts with Plugin Update library


v1.4.1 - 2015-12-02
-------------------
+ Feature: Custom single price text - Display custom text for the single price on the courses and course page. Custom field does not require a single payment price be set. IE: Free!
+ Feature: Custom Purchase Course Button Text Option. Change the text of the Take This Course button in Settings->Courses.
+ Feature: New Become A Member button on courses that are restricted to memberships.
+ Feature: Custom Become A Member Text Option. Change the text of the become a member button in Settings->Courses.
+ Feature: Paypal Debug Mode. Enable debug mode in Settings->Gateways to view responses from Paypal API when errors occur.
+ Updates: Updated support links in Settings->General.
+ Updates: added minor styling to course page to increase margin and padding for some themes.
+ Updates: Achievement content now available to pull into custom templates. The Achievement content is not by default displayed but can now be used in custom templates.
+ BugFixes: Resolved issue with no default price selected at checkout when only recurring option existed.
+ BugFixes: Lesson prerequisite now alert the user and provide a link to redirect the user to the next required lesson in the course.
+ BugFixes: Paypal errors now return error message instead of white screen when Paypal API fails.
+ BugFixes: Corrected JavaScript error with modals on course edit page in Internet Explorer 11.


v1.4.0 - 2015-10-29
-------------------
+ Feature: Free lessons - demo lessons that can be taken at any time by any user
+ Feature: Guest lessons - demo lessons that can be taken by a non-logged in user
+ Feature: Random quiz question - quiz questions can now be set to be in user set order or random order
+ Updates: Automatically registers appropriate sidebars for Genesis theme
+ Updates: Backend file cleanup
+ Updates: Text cleanup
+ Updates: Adds greater localization support (more strings to translate! yay!)
+ Updates: Cleans up some unnecessary console.log() calls
+ Updates: Removes mass of commented out code (cleaner reading)
+ Updates: 'Next Lesson' button added after successful completion of quiz
+ Updates: 'Next Lesson' button at bottom of lesson properly gets starting lesson of next section at the end of the previous section
+ Updates: 'Previous Lesson' button at bottom of lesson will now properly get last lesson of previous section (if applicable)
+ Updates: Move Registration Form to global templates to allow users to disable registration on login page but use registration form on custom page.
+ BugFixes: WordPress pages are now properly restricted by memberships
+ BugFixes: Fixes bug that caused order screen to act up if user was deleted
+ BugFixes: Resolves nasty little bug that caused syllabus numbers to be out of whack
+ BugFixes: Resolved error with WooCommerce integration where courses would not always register the user
+ BugFixes: Corrected CSS conflict with Bridge theme settings page


v1.3.10 - 2015-10-15
--------------------
+ Updates: Clarifies some prerequisite text
+ Updates: Quiz questions are now randomized!
+ Updates: Fixes small CSS issue
+ BugFixes: Resolves fatal errors with a small subset of premium themes


v1.3.9 - 2015-10-5
------------------
+ BugFixes: Removes conflict with Yoast SEO
+ BugFixes: Fixes CSS issues with box-sizing takeover
+ Feature: New Settings Tile: Session Management. Found at LifterLMS->Settings->General.
+ Feature: Clear User Session Tool. You can now clear all LifterLMS user session data from your site in LifterLMS->Settings->General
+ Updates: Backend code cleanup


v1.3.8 - 2015-10-02
-------------------
+ BugFixes: Fixes Random error notices
+ Updates: Updates email template handler


v1.3.7 - 2015-09-25
-------------------
+ Updates: Adds Spanish translation
+ Updates: Adds new filter 'lifterlms_single_payment_text' to customize single payment string on checkout
+ Updates: Student analytics now indicate which courses a student has completed
+ BugFixes: Resolved security issue with WordPress searches and lessons
+ BugFixes: Fixes analytics bug that potentially arises after a course is deleted


v1.3.6 - 2015-09-18
-------------------
+ BugFixes: Fixes pesky Zend Error that plagued some unfortunate victims
+ BugFixes: Students can now be properly deleted from the course
+ BugFixes: Fixes random class redeclaration error messages
+ Updates: Adds new filter 'lifterlms_quiz_passed' to customize 'Passed' text after quiz
+ Updates: Adds new filter 'lifterlms_quiz_failed' to customize 'Failed' text after quiz


v1.3.5 - 2015-09-11
-------------------
+ Revisions: Fixes typos
+ Updates: Adds sidebar functionality to various themes


v1.3.4 - 2015-09-04
-------------------
+ BugFixes: Fixes bug with featured image on course page
+ BugFixes: Fixes issue with lesson completed percentage on analytics page


v1.3.3 - 2015-09-01
-------------------
+ Updates: Removes deprecated plugin updater
+ Updates: Adds Course Track prerequisite
+ Updates: Various text fixes
+ BugFixes: Fixes lesson name on prerequisite notification
+ BugFixes: Fixes critical error with WordPress customizer


v1.3.2 - 2015-08-30
-------------------
+ Hotfix: resolves issues with sidebar shortcodes
+ Updates: Text clarifications


v1.3.1 - 2015-08-28
-------------------
+ Hotfix: resolves issue with ajax url


v1.3.0 - 2015-08-28
-------------------
+ Improved popover behavior in course creation.
+ BugFixing. Prevent multiple lesson and section form submission
+ Fixed typos at backend quiz page
+ Fixed check for update bug when plugin isn't properly activated.
+ BugFixing, quiz post type should show author metabox
+ Added course category filter to lifter_lms shortcode
+ BugFixing, typo in [lifterlms_course_progress shortcode]
+ BugFixing, Analytics shouldn't fetch students meta info from users were deleted.
+ Adds in basic review functionality
+ Updates plugin-updater to remedy PHP conflicts
+ Fixes date bug in Analytics
+ Cleans up jQuery console messages
+ Adds in course tracks


v1.2.8 - 2015-07-17
-------------------
+ Updated Portuguese translation file
+ Fixed issue where quiz score could not be equal to required grade.
+ New Feature: Quiz Results Summary. Display the quiz results to the user on quiz completion.
+ New feature: Clarification. Display information about correct and incorrect answers to users
+ New Feature: Display correct answers to user on quiz completion
+ Removed ability to add negative time limit to quiz
+ New Membership feature: Make membership archive links go directly to checkout. Setting allows you to skip membership sales page and send users directly to registration and checkout.
+ Sidebar support for prototype theme
+ Sidebar support for X theme
+ Sidebar support for WooCanvas
+ New Shortcode: [lifterlms_hide_content]: Use to restrict content on a page, course or lesson to a specific membership. Pass the post id of the membership you want to restrict the content to. Example: [lifterlms_hide_content membership="5"]
+ New updates to gulp build process
+ Class autoloading and LLMS namespace introduced for more efficient coding.


v1.2.7 - 2015-06-05
-------------------
+ Minor bug fix with lesson redirect to quiz
+ Minor change to global Course object instantiation.
+ Bug Fix: Remove student from course
+ Bug Fix: Appearance Menus missing select field (THANKS ANDREA!)
+ New Course Setting: Hide Course Outline on course page
+ New Shortcode: [lifterlms_course_outline] - displays course outline with settings (see documentation)
+ Membership metabox design update
+ Certificate metabox design update
+ Achievement metabox design update
+ Lesson metabox design update
+ Emails metabox design update
+ Coupons metabox design update
+ Update to certificate design (better alignment and theme functionality)
+ Better theme sidebar support
+ More awesome control for developers building new settings for LifterLMS
+ Advanced filter system for metabox fields with finite control for 3rd party developers.
+ Woocommerce conflict correction to archive templates
+ Style updates to allow themes better control on design


v1.2.6 - 2015-04-28
-------------------
+ Corrected issue with lesson re-order on save
+ corrected html formatting issue on purchase page
+ corrected html formatting issue on course page


v1.2.5 - 2015-04-23
-------------------
+ Corrected excerpt to not pull in lesson navigation
+ Modified metabox api for better extension integration
+ Corrected issue with order not displaying all information if coupon was not applied to order


v1.2.4 - 2015-04-22
-------------------
+ Moved All Course metaboxes to global Course Options Metabox
+ Move Enrolled and Non-Enrolled user wysiwyg post editors to Options Metabox
+ Removed Course Syllabus metabox, Added Course Outline Metabox
+ Set priority of Course Outline and Course Options Metabox to top
+ Added ability to Create new section to Course Outline
+ Added ability to Create new lesson to Course Outline
+ Added ability to add existing Lesson to Course Outline
+ Added Lesson duplicate functionality when adding lesson previously assigned to another course.
+ Added ability to drag lessons between sections in Course Outline
+ Added ability to edit Section Title in Course Outline
+ Added ability to edit lesson title and excerpt in Course Outline
+ Added New Style and Design for better usability to Course Outline
+ Added Lesson Icon with tooltip to Course Outline: Prerequisite - shows if prerequisite exists and displays name of prerequisite
+ Added Lesson Icon with tooltip to Course Outline: Quiz - shows if quiz is assigned to course and displays name of quiz
+ Added Lesson Icon with tooltip to Course Outline: Drip Content - shows if drip days are set and # of days
+ Added Lesson Icon with tooltip to Course Outline: Content - displays if lesson has content added.
+ Added Course Outline Metabox to Lesson Post Editor: Allows you to assign lesson to section and view entire course tree. Links to Course and all other lessons in course.
+ Style Update: backgrounds on frontend. Removed all references to white background on front end elements
+ Corrected Restriction for course in past. Updated course in past message to display as Course ended instead of Course not available until.
+ Added restriction message when user attempts to visit a restricted lesson.
+ Updated course syllabus sidebar widget to not display lessons as links if user is not enrolled in course.
+ Added ability to use Attribute Order for sorting Courses and Memberships on Archive pages.
+ Added support for selling memberships with Woocommerce. LifterLMS now checks memberships for SKU matches in addition to Courses when products are purchased using WooCommerce.
+ Added gulp for scss, js and svg management
+ Added svg sprite and svg class for managing svg elements on front and backend.
+ Added better language translation support for strings
+ Refactored Ajax Classes for cleaner, faster development
+ Refactored metabox build class for cleaner, faster development
+ Refactored Course syllabus to reduce query size for larger, complex courses
+ Added Handler classes for Lessons, Sections, Courses and Posts
+ Refactored Course get / set methods to reduce database queries


v1.2.3 - 2015-03-12
-------------------
+ Achievement design and functionality updates
+ Achievement shortcode added
+ Better searching added to engagement screen
+ Achievement bug fixes
+ On screen error reporting added to activation for trouble shooting
+ Custom engagement methods added to certificate, achievement and sections
+ Corrected new user registration engagement bug
+ LifterLMS access reduced from manage_options to edit_posts
+ Filters added to analytics to allow custom development
+ Engagement bug fix: Section and Lesson bug select
+ Syllabus bug corrected: No longer displays lessons in section box if no sections exist.
+ Removed depreciated achievement template
+ Membership Bug fix: Membership restriction will now only display on single posts.


v1.2.2 - 2015-02-23
-------------------
+ Corrected drip content bug
+ Added Ajax functionality to quiz
+ rounded quiz grades
+ Added quiz time limit setting to Quiz
+ Added quiz timer to quiz, front end
+ Quiz allowed attempts field now allows unlimited attempts
+ Set Ajax lesson delete method to not return empty lesson value
+ Set next and previous questions to display below quiz question
+ Decoupled Single option select question type from quiz to allow for more question types
+ Added Quiz time limit to display on Quiz page
+ Added functionality to automatically complete quiz when quiz timer reaches 0
+ Moved Quiz functionality methods from front end forms class to Quiz class

v1.2.1 - 2015-02-19
-------------------
+ Updated settings page theming
+ Added Set up Quick Start Guide
+ Added Plugin Deactivation Option
+ Updated language POT file
+ Added Portuguese language support. Thank you Fernando Cassino for the translation :)


v1.2.0 - 2015-02-17
-------------------
+ Admin Course Analytics Dashboard Page. View at LifterLMS->Analytics->Course
+ Admin Sales Analytics Dashboard Page. View at LifterLMS->Analytics->Sales
+ Admin Memberships Analytics Dashboard Page. View at LifterLMS->Analytics->Memberships
+ Admin Students Search Page. View at LifterLMS->Students
+ Admin Student Profile Page ( View user information related to courses and memberships )
+ Lesson and Course Sidebar Widgets ( Syllabus, Course Progress )
+ Course Syllabus: Lesson blocks greyed out. Clicking lesson displays message to take course.
+ Misc. Front end bug fixes
+ Misc. Admin bug fixes
+ Course and Lesson prerequisites: Can no longer select a prerequisite without marking "Has Prerequisite"
+ Admin CSS updates
+ Better Session Management
+ Number and Date formatting handled by separate classes to provide consistent date formats across system
+ Zero dollar coupon management: Coupons that set total to 0 will bypass payment gateway, generate order and enroll users.
+ Better coupon verification.
+ Better third party payment gateway support. Third party gateway plugins are now easier to develop and integrate.
+ User Registration: Phone Number Registration field option now available in Accounts settings page.


v1.1.2 - 2014-12-18
-------------------
+ Moved Sidebar registration from plugin install to init


v1.1.1 - 2014-12-16
-------------------
+ Added user registration settings to require users to agree to Terms and Conditions on user registration
+ Added comments to all classes methods and functions
+ Removed unused and depreciated methods
+ Added Lesson and Course Sidebar Widget Areas
+ Fixed bug with course capacity option
+ Fixed bug with endpoint rewrite
+ Added localization POT file and us_EN.po translation file


v1.1.0 - 2014-12-08
-------------------
+ Updated HTML / CSS on Registration form
+ Added Coupon Creation
+ Added Coupon support for checkout processing
+ Added Credit Card Support processing support
+ Added Form filters for external integration
+ Added Form templates for external integration
+ Added Account Setting: Require First and Last Name on registration
+ Added Account Setting: Require Billing Address on registration
+ Added Account Setting: Require users to validate email address (double entry)
+ Added password validation (double entry) on user registration / account creation
+ Added Quiz Question post type and associated metaboxes
+ Added Quiz post type and associated metaboxes
+ Added ability to assign a quiz to a lesson
+ Added front end quiz functionality
+ Added Course capacity (limit # of students)

### User Admin Table
+ Added Membership Custom Column that displays user's membership information
+ Added "Last Login" custom column that displays user's last login date/time

### User Roles
+ Updated user role from "person" to "student"
+ Added temporary migration function to transition any register users with "person" role to "student" role
+ Added "Student" role install function


### BUDDYPRESS
+ BuddyPress Screen Permission Fix
+ Added two additional screens to BuddyPress: Certificates and Achievements

### MISC
+ Added llms options for course archive pagination and added course archive page pagination template
+ Added user statistics shortcode


v1.0.5 - 2014-11-12
-------------------

+ Fixed a mis-placed parenthesis in templates/course/lesson-navigation.php related to outputting excerpt in navigation option
+ Changed theme override template directory from /llms to /lifterlms
+ Update the position & name of the "My Courses" Menu in BuddyPress Compatibility file
+ New meta_key _parent_section added for easier connection and quicker queries.
+ Section sorting on course syllabus
+ Edit links added to course syllabus
+ Assign section to course and view associated lessons metabox added to sections
+ Assign lesson to section and view associated lessons metabox added to lessons
+ Assigned Course, Assigned Section, Prerequisite and Membership Required added to lesson edit grid
+ Assigned Course added to section edit grid'
+ New membership setting: Restrict Entire Site by Membership Level (allows site restriction to everything but membership purchase and account).
+ Updated template overriding to check child & parent themes
+ Updated template overriding to apply filters to directories to check for overrides to allow themes and plugins to add their own directories


v1.0.4 - 2014-11-04
-------------------

+ Templating bug fix
+ Added shortcode and autop support to course and lesson content / excerpt


v1.0.3 - 2014-11-04
-------------------

+ Major Templating Update!
+ Removed Course, Lesson and Membership single lesson templates.
+ Course and Section content templates now filter through WP content


v1.0.2 - 2014-10-31
-------------------

+ Added lesson short description to previous lesson preview links -- it was rendering on "Next" but not "Previous"
+ Added a class to course shop links wrapper to signify the course has been completed
+ Removed an unnecessary CSS rule related to the progress bar


v1.0.2 - 2014-10-30
-------------------

+ Fixed SSL certificate issues when retrieving data from https://lifterlms.com
+ Added rocket settings icon back into repo


v1.0.1 - 2014-10-30
-------------------

+ Updated activation endpoint url to point towards live server rather than dev


v1.0.0 - 2014-10-30
-------------------

+ Initial public release.
