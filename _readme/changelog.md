== Changelog ==


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


= v3.18.1 - 2018-05-18 =
------------------------

+ Attached `llms_privacy_policy_form_field()` and `llms_agree_to_terms_form_field()` to an action hook `llms_registration_privacy`
+ Define minimum WordPress version requirement as 4.8.

##### Template Updates

+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)
+ [templates/global/form-registration.php](https://github.com/gocodebox/lifterlms/blob/master/templates/global/form-registration.php)


= v3.18.0 - 2018-05-16 =
------------------------

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
+ Fixid issue causing new posts created via the Course Builder to always be created for user_id #1
+ Fixed issue causing "My Achievements" to display twice on the My Achievements student dashboard tab
+ Fixed issue preventing lessons from being completed when a quiz in draft mode was attached to the lesson
+ Fixed issue causing minified RTL stylesheets to 404

##### Template Updates

+ [templates/admin/post-types/order-details.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/post-types/order-details.php)
+ [templates/checkout/form-checkout.php](https://github.com/gocodebox/lifterlms/blob/master/templates/checkout/form-checkout.php)
+ [templates/content-certificate.php](https://github.com/gocodebox/lifterlms/blob/master/templates/content-certificate.php)
+ [templates/global/form-registration.php](https://github.com/gocodebox/lifterlms/blob/master/templates/global/form-registration.php)
+ [templates/myaccount/dashboard-section.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/dashboard-section.php)


= v3.17.8 - 2018-05-04 =
------------------------

##### Updates and Enchancements

+ Added admin email notification when student cancels a subscription
+ Quiz results will now display the question's description when reviewing results as a student and on the admin panel during grading
+ Add action hook fired when a student cancels a subscription (`llms_subscription_cancelled_by_student`)
+ Reduce unnecessary DB queries for integrations by checking for dependencies and then calling querying the options table to see if the integration has been enabled.
+ Updated the notifications settings table to be more friendly to the human eye

##### Bug Fixes

+ Fix admin scripts enqueue order. Fixes issue preventing manual student enrollment selection from functioning properly in certain scenarios.
+ Shift + Enter when in a question choice field now adds a return as expected instead of exiting the field
+ When pasting into question choice fields HTML from RTF documents will be automatically stripped
+ Ensure certificates print with a white brackground regardless of theme CSS
+ Fix issue causing themes with `overflow:hidden` on divs from cutting certificate background images
+ Upon export completion unlock tables regardless of mail success / failure
+ Resolve issue causing incorrect number of access plans to be returned on systems that have custom defaults set for `WP_Query` `post_per_page` parameter
+ Fix error occurring when all 3rd party integrations are disabled by filter, credit to [@Mte90](https://github.com/Mte90)!
+ Ensure `LLMS()->integrations()->integrations()` returns all integrations regardless of availability.
+ Updated `LLMS_Abstract_Options_Data` to have an option set method

##### Template Updates

+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt-questions-list.php)


= v3.17.7 - 2018-04-27 =
------------------------

+ Fix issue preventing assignments passing grade requirement from saving properly
+ Fix issue preventing builder toggle switches from properly saving some switch field data
+ Fix with "Launch Builder" button causing it to extend outside the bounds of its container
+ Fix issue with builder radio select fields during view rerenders
+ Course Outline shortcode (and widget) now retrieve parent course of the current page more consistently with other shortcodes
+ Added ability to filter which custom post types which can be children of a course (allows course shortcodes & widgets to be used in assignment sidebars of custom content areas)


= v3.17.6 - 2018-04-26 =
------------------------

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


= v3.17.5 - 2018-04-23 =
------------------------

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
+ Fix issue with audio & video embeds to prexvent fallback to default post attachments
+ Fix issue causing student selection boxes to malfunction due to missing dependencies when loaded over slow connections

##### Template Updates

+ [templates/myaccount/navigation.php](https://github.com/gocodebox/lifterlms/blob/master/templates/myaccount/navigation.php)


= v3.17.4 - 2018-04-17 =
------------------------

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