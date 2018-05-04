== Changelog ==


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


= v3.17.3 - 2018-04-11 =
------------------------

+ Course and Membership instructor metabox search field now correcty states "Select an Instructor" instead of previous "Select a Student"
+ Added missing translation for "Select a Student" on admin panel student selection search fields
+ Fix issue causing reporting export CSVs to throw a SYLK interpretation error when opened in Excel
+ Fix issue causing drafted courses and memberships to be published when the "Update" button is clicked to save changes
+ Remove use of PHP 7.2 deprecated `create_function`
+ Fix errors resulting from quiz questions which have been deleted
+ Fix issue causing current date / time to display as the End Date for incomplete quiz attempts on quiz reporting screens

##### Template Updates

+ [templates/admin/reporting/tabs/quizzes/attempt.php](https://github.com/gocodebox/lifterlms/blob/master/templates/admin/reporting/tabs/quizzes/attempt.php)
+ [templates/quiz/results-attempt-questions-list.php](https://github.com/gocodebox/lifterlms/blob/master/templates/quiz/results-attempt-questions-list.php)


= v3.17.2 - 2018-04-09 =
------------------------

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


= v3.17.1 - 2018-03-30 =
------------------------

+ Refactored lesson completion methods to allow 3rd party customization of lesson completion behavior via filters and hooks.
+ Remove duplicate lesson completion notice implemented. Only popover notifications will display now instead of popovers and inline messages.
+ Object completion will now automatically prevent multiple records of completion from being recorded for a single object.
+ Lesson Mark Complete button and lessons completed by quiz now utilizes a generic trigger to mark lessons as complete: `llms_trigger_lesson_completion`.
+ Removed several unused functions from frontend forms class
+ Moved lesson completion form controllers to their own class

##### Templates updates

+ [templates/course/complete-lesson-link.php](https://github.com/gocodebox/lifterlms/blob/master/templates/course/complete-lesson-link.php)


= v3.17.0 - 2018-03-27 =
------------------------

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


= v3.16.16 - 2018-03-19 =
-------------------------

+ Fixed builder issue causing multiple question choices to be incorrectly selected
+ Fixed builder issue with media library uploads causing an error message to prevent new uploads before the quiz or question has been persistend to the database
+ Fixed builder issue preventing quizzes from being deleted before they were persisted to the database
+ Fixed builder issue causing autosaves to interrupt typing and reset lesson and section titles
+ Fixed JS console error related to LifterLMS JS dependency checks