LifterLMS E2E Test Utils CHANGELOG
==================================

v2.2.1 - 2021-01-19
-------------------

+ Options object is now optional for the createUser() function.
+ Added `args.voucher` to enable voucher usage during registration via the registerStudent() function.


v2.2.0 - 2020-11-16
-------------------

+ `createCourse()` now uses generic `createPost()` for course creation.
+ `createUser()` now returns the WP_User ID in the return object.
+ `importCourse()` has been updated to accommodate changes in LifterLMS core version 4.8.0.
+ `runSetupWizard()` has been updated to accommodate setup wizard changes in LifterLMS core version 4.8.0.


v2.1.3 - 2020-08-06
-------------------
+ `logoutUser()`: Wait 1 second before navigating to logout page.
+ `visitSettingsPage()`: Don't add null values to the query string.

v2.1.1 - 2020-08-06
-------------------

+ `createCourse()` now uses `createPost()`.
+ `createUser()` will now return the `WP_User` ID of the created user.

+ Added new utility functions:

  + `createMembership()`: Create and publish a new membership.
  + `createPost()`: Create a publish a new post (of a defined post type).
  + `enrollStudent()`: Enroll a user account into a course or membership.
  + `importCourse()`: Import a course export file into the test environment.
  + `setSelect2Option()`: Set the value of a select field powered by select2.js
