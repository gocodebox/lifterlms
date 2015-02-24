lifterLMS
==========

LIFTER LMS

###Shortcodes
* [lifterlms_my_account]
  *adds entire account page
  *Accepts no arguments
* [courses]
**Accepts arguments: order, orderby and per_page


###Debug: lms_log($message)
*Logs message to wp-contents/debug.log

####Examples
*log_me(array('This is a message' => 'for debugging purposes'));
*log_me('This is a message for debugging purposes');





CHANGELOG
=========

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