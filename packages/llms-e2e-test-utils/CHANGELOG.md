LifterLMS E2E Test Utils CHANGELOG
==================================

v2.1.2 - 2020-08-06
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
