LifterLMS E2E Test Utils Changelog
==================================

Unreleased
----------

+ Update: `activateTheme()` to remove reliance on `@wordpress/e2e-test-utils` `activateTheme()`.


v3.3.0 - 2022-03-08
-------------------

+ New functions:
  + `activateTheme()`
  + `getPostTitleSelector()`
  + `getPostTitleTextContent()`
  + `openSidebarPanelTab()`
  + `publishPost()`
  + `toggleSidebarPanel()`
  + `updatePost()`
  + `visitPostPermalink()`
+ Update: `createCertificate()` now creates certificates in the block editor.
+ Update: `createPost()` now sets the post's content programmatically in favor of passing it through a query string variable.
+ Update: `fillField()` now waits for the selector prior to focusing on it.
+ Update: `clickAndWait()` now returns a `Promise` in favor of void.


v3.2.0 - 2022-01-31
-------------------

+ Added: New function `wpVersionCompare()` used to run version comparisons against the currently tested version of WordPress.
+ Fixed: Tests failing when running `runSetupWizard()` on WordPress >= 5.9.
+ Update: `@wordpress/e2e-test-utils` to version [6.0.0](https://github.com/WordPress/gutenberg/blob/trunk/packages/e2e-test-utils/CHANGELOG.md#600-2022-01-27).


v3.1.0 - 2021-12-07
-------------------

+ Added new functions `highlightNode()` and `setCheckboxSetting()`.


v3.0.0 - 2021-11-05
-------------------

+ **[Breaking]** Minimum required Puppeteer version raised from 3.0.0 to 5.3.0.
+ Use `waitForTimeout()` in favor of deprecated `waitFor()`.
+ Wait for select2 to be loaded before attempting to open it and wait for select2 dropdown to close after selecting an option.


v3.0.0-beta.1 - 2021-09-10
--------------------------

+ **[Breaking]** Minimum required Puppeteer version raised from 3.0.0 to 5.3.0.
+ Use `waitForTimeout()` in favor of deprecated `waitFor()`.
+ Wait for select2 to be loaded before attempting to open it and wait for select2 dropdown to close after selecting an option.


v2.3.1 - 2021-10-05
-------------------

+ Wait for select2 to be loaded before attempting to open it and wait for select2 dropdown to close after selecting an option.


v2.3.0 - 2021-06-22
-------------------

+ Bugfix: Focus on the search selector prior to typing in select2 search fields.


v2.2.2 - 2021-02-04
-------------------

+ `click()` now always uses `waitForSelector()`. before clicking the element.
+ Use `waitForSelector()` in favor of `waitFor()` when creating access plans.


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
