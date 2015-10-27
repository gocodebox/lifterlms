lifterLMS
==========

LIFTER LMS

###Shortcodes
* [lifterlms_my_account]
  *adds entire account page
  *Accepts no arguments
* [courses]
**Accepts arguments: order, orderby and per_page


###Debug: llms_log($message)
*Logs message to wp-contents/debug.log

####Examples
*log_me(array('This is a message' => 'for debugging purposes'));
*log_me('This is a message for debugging purposes');





CHANGELOG
=========
v1.4.0
-------------------
+ Feature: Free lessons - demo lessons that can be taken at any time by any user
+ Feature: Guest lessons - demo lessons that can be taken by a non-logged in user
+ Feature: Random quiz question - quiz questions can now be set to be in user set order or random order
+ Updates: Automatically registers appropriate sidebars for Genesis theme
+ Updates: Backend file cleanup
+ Updates: Text cleanup
+ Updates: Adds greater localization support (more strings to translate! yay!)
+ Updates: Cleans up some unneccessary console.log() calls
+ Updates: Removes mass of commented out code (cleaner reading)
+ Updates: 'Next Lesson' button added after successful completion of quiz
+ Updates: 'Next Lesson' button at bottom of lesson properly gets starting lesson of next section at the end of the previous section
+ Updates: 'Previous Lesson' button at bottom of lesson will now properly get last lesson of previous section (if applicable)
+ BugFixes: WordPress pages are now properly restricted by memberships
+ BugFixes: Fixes bug that caused order screen to act up if user was deleted
+ BugFixes: Resolves nastly little bug that caused syllabus numbers to be out of whack

v1.3.10 - 2015/10/15
-------------------
+ Updates: Clarifies some prerequisite text
+ Updates: Quiz questions are now randomized!
+ Updates: Fixes small CSS issue
+ BugFixes: Resolves fatal errors with a small subset of premium themes

v1.3.9 - 2015/10/5
-------------------
+ BugFixes: Removes conflict with Yoast SEO
+ BugFixes: Fixes CSS issues with box-sizing takeover
+ Feature: New Settings Tile: Session Management. Found at LifterLMS->Settings->General.
+ Feature: Clear User Session Tool. You can now clear all LifterLMS user session data from your site in LifterLMS->Settings->General
+ Updates: Backend code cleanup

v1.3.8 - 2015/10/02
-------------------
+ BugFixes: Fixes Random error notices
+ Updates: Updates email template handler

v1.3.7 - 2015/09/25
-------------------
+ Updates: Adds Spanish translation
+ Updates: Adds new filter 'lifterlms_single_payment_text' to customize single payment string on checkout
+ Updates: Student analytics now indicate which courses a student has completed
+ BugFixes: Resolved security issue with WordPress searches and lessons
+ BugFixes: Fixes analytics bug that potentially arises after a course is deleted

v1.3.6 - 2015/09/18
-------------------
+ BugFixes: Fixes pesky Zend Error that plagued some unfortunate victims
+ BugFixes: Students can now be properly deleted from the course
+ BugFixes: Fixes random class redeclaration error messages
+ Updates: Adds new filter 'lifterlms_quiz_passed' to customize 'Passed' text after quiz
+ Updates: Adds new filter 'lifterlms_quiz_failed' to customize 'Failed' text after quiz

v1.3.5 - 2015/09/11
-------------------
+ Revisions: Fixes typos
+ Updates: Adds sidebar functionality to various themes

v1.3.4 - 2015/09/04
-------------------
+ BugFixes: Fixes bug with featured image on course page
+ BugFixes: Fixes issue with lesson completed percentage on analytics page

v1.3.3 - 2015/09/01
-------------------
+ Updates: Removes depricated plugin updater
+ Updates: Adds Course Track prerequisite
+ Updates: Various text fixes
+ BugFixes: Fixes lesson name on prerequisite notification
+ BugFixes: Fixes critical error with WordPress customizer

v1.3.2 - 2015/08/30
-------------------
+ Hotfix: resolves issues with sidebar shortcodes
+ Updates: Text clarifications

v1.3.1 - 2015/08/28
-------------------
+ Hotfix: resolves issue with ajax url

v1.3.0 - 2015/08/28
-------------------
+ Improved popopver behavior in course creation.
+ BugFixnig. Prevent multiple lesson and section form submition
+ Fixed typos at backend quiz page
+ Fixed check for update bug when plugin isn't properly activated.
+ BugFixing, quiz post type should show author metabox
+ Added course category filter to lifter_lms shortcode
+ BugFixing, typo in [lifterlms_course_progess shortcode]
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
+ More awesome control for devlopers building new settings for LifterLMS
+ Advanced filter system for metabox fields with finite control for 3rd party developers.
+ Woocommerce confict correction to archive templates
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
+ Added abiliyt to Create new lesson to Course Outline
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
+ Achievemnt shortcode added
+ Better searching added to engagement screen
+ Achievement bug fixes
+ On screen error reporting added to activation for trouble shooting
+ Custom engagement methods added to certificate, achievement and sections
+ Corrected new user registration engagement bug
+ LifterLMS access reduced from manage_options to edit_posts
+ Filters added to analytics to allow custom developement
+ Engagment bug fix: Section and Lesson bug select
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
+ Number and Date formatting handled by seperate classes to provide consistant date formats across system
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
+ Added user statisticc shortcode


v1.0.5 - 2014-11-12
-------------------

+ Fixed a mis-placed parenthesis in templates/course/lesson-navigation.php related to outputting excerpt in navigation option
+ Changed theme override template directory from /llms to /lifterlms
+ Update the positiong & name of the "My Courses" Menu in BuddyPress Compatibility file
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
---------------------

+ Added lesson short description to previous lesson preview links -- it was rendering on "Next" but not "Previous"
+ Added a class to course shop links wrapper to signify the course has been completed
+ Removed an uncessary CSS rule related to the progress bar


v1.0.2 - 2014-10-30
-------------------

+ Fixed SSL certificate issues when retreiving data from https://lifterlms.com
+ Added rocket settings icon back into repo


v1.0.1 - 2014-10-30
-------------------

+ Updated activation endpoint url to point towards live server rather than dev