== Changelog ==


= v3.17.0 - 2018-03-27 =
-------------------------

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


= v3.16.11 - 2018-02-22 =
-------------------------

+ Course import/exports and lesson duplication now carry custom meta data from 3rd party plugins and themes
+ Added course completion date column to Course reporting students list
+ Restriction checks made against a quiz will now properly cascade to the quiz's parent lesson
+ Fixed issue preventing featured images from being exported with courses and lessons
+ Fixed duplicate lesson issue causing quizzes to be double assigned to the old and new lesson
+ Fixed issue allowing blog archive to be viewed by non-members when sitewide membership is enabled
+ Fixed builder issue causing data to be lost during autosaves if data was edited during an autosave
+ Fixed builder issue preventing lessons from moving between sections when clicking the "Prev" and "Next" section buttons
+ Added actions to `LLMS_Generator` to allow 3rd parties to extend core generator functionality


= v3.16.10 - 2018-02-19 =
-------------------------

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


= v3.16.9 - 2018-02-15 =
------------------------

+ Fix issue causing error on student dashboard when reviewing an order with an access plan that was deleted.
+ Fixed spelling error on course metabox
+ Fixed spelling error on frontend quiz interface
+ Fixed issues with 0 point questions:
  + Will no longer prevent quizzes from being automatically graded when a 0 point question is in an otherwise automatically gradeable quiz
  + Point value not editable during review
  + Visual display on results displays with grey background not as an orange "pending" question
+ Table schema uses default database charset. Fixes an issue with databases that don't support `utf8mb4` charsets.
+ Updated `LLMS_Hasher` class for better compatibility with older versions of PHP


= v3.16.8 - 2018-02-13 =
------------------------

##### Updates

+ Added theme compatibility API so theme developers can add layout options to the quiz settings on the course builder. For details on adding theme compatibility see: [https://lifterlms.com/docs/quiz-theme-compatibility-developers/](https://lifterlms.com/docs/quiz-theme-compatibility-developers/).
+ Quiz results "donut" chart had alternate styles for quizzes pending review (Dark grey text rather than red). You can target with the `.llms-donut.pending` CSS class to customize appearance.
+ Allow filtering when retrieving student answer for a quiz attempt question via `llms_quiz_attempt_question_get_answer` filter

##### Bug Fixes

+ Fix issues causing conditionally gradeable question types (fill in the blank and scale) from displaying without a status icon or possible points when awaiting admin review / grading.
+ Fix issue preventing conditionally gradeable question types (fill in the blank and scale) from being reviewable on the admin panel when the question is configured as requiring manual grading.
+ Fix analytics widget undefined index warning during admin-ajax calls. Thanks [@Mte90](https://github.com/Mte90)!
+ Fix issue causing `is_search()` to be called incorrectly. Thanks [@Mte90](https://github.com/Mte90)!
+ Fix issue preventing text / html formatting from saving properly for access plan description fields
+ Fix html character encoding issue on reporting widgets causing currency symbols to display as a charcter code instead of the symbol glyph.

##### Templates changed

+ templates/quiz/results-attempt-questions-list.php
+ templates/quiz/results-attempt.php