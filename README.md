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

v1.1.0
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