@lifterlms/llms-e2e-test-utils CHANGELOG
========================================

v2.2.0 - 2020-08-06
-------------------

+ `createCourse()` now uses `createPost()`.
+ `createUser()` will now return the `WP_User` ID of the created user.

+ Added new utility functions:

  + `createMembership()`: Create and publish a new membership.
  + `createPost()`: Create a publish a new post (of a defined post type).
  + `enrollStudent()`: Enroll a user account into a course or membership.
  + `importCourse()`: Import a course export file into the test environment.
  + `setSelect2Option()`: Set the value of a select field powered by select2.js
