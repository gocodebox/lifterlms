== Changelog ==


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


= v3.16.15 - 2018-03-13 =
-------------------------

##### Quiz Results Improvements and fixes

+ Improved quiz result user and correct answer handling functions for more consistent HTML output
+ Result answers (correct and user) will display as lists
+ image question types will display without bullets and will "float" next to each other
+ Fixed issue causing quiz results with multiple answers from outputting all HTMLS with no spaces between them

##### Quiz Grading

+ Fixed issue causing advanced reorder and reorder question types from being graded incorrectly in some scenarios
+ Advanced fill in the blank questions are now case insensitive. Case sensitivity can be enabled with a filter: `add_filter( 'llms_quiz_grading_case_sensitive', '__return_true' )`

##### Fixes

+ Updated spacing and returns found in the email header and footer templates to prevent line breaks from occurring in undesireable places on previews of HTML emails in mobile email clients
+ Added options for themes to add layout support to quizzes where the custom field utilizes an underscore at the beginning of the field key
+ Fixed CSS issue causing blanks of fill in the blanks to not be visible on the course builder when using Chrome on Windows
+ Removed unnecessary `get_option()` call to unused option `lifterlms_permalinks`
+ Updated permissions required to see various LifterLMS post types to rely on `manage_lifterlms` capabilites as opposed to `manage_options`
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


= v3.16.14 - 2018-03-07 =
-------------------------

+ Courses reporting table now includes courses with the "Private" status
+ Fixed issue causing some achievment notifications to be blank
+ Added tooltips to question choice add / delete icon buttons
+ Quiz results meta information elements now have unique CSS classes
+ Removed reliance PHP 7.2 deprecated function `create_function()`
+ Fixed invalid PHP 7.2 syntax creating a warning found on the setup wizard
+ Fixed undefined index error related to admin notices
+ Fixed unstanslateable string on Users table ("No Memberships")
+ Fixed discrepancy between membership restrictions as presented to logged out users and loggend in users who cannot access membership
+ Fixed FireFox and Edge issue causing changes to number inputs made via HTML5 input arrows from properly triggering save events


= v3.16.13 - 2018-02-28 =
-------------------------

+ Hotfix: Only create quizzes on the builder if quizzes exist on the lesson


= v3.16.12 - 2018-02-27 =
-------------------------

+ Quizzes can now be detached (removed from a lesson) or deleted (deleted from the lesson and the database) via the Course Builder
+ Improved question choice randomization to ensure randomized choices never display in their original order.
+ When a lesson is deleted, any quiz attached to the lesson will become an orphan
+ When a lesson is deleted, any lesson with this lesson as a prerequisite will have it's prerequisite data removed
+ When a quiz is deleted, all questions attached to the quiz will also be deleted
+ When a quiz is deleted, the lesson associated with the quiz will have those associations removed
+ Fixed grammar issue on restricted lesson tooltips when no custom message is stored on the course.
+ Updated functions causing issues in PHP 5.4 to work on PHP 5.4. This has been done to reduce frustration for users still using PHP 5.4 and lower; [This does not mean we advocate using software past the end of its life or that we support PHP 5.4 and lower](https://lifterlms.com/docs/minimum-system-requirements-lifterlms/).