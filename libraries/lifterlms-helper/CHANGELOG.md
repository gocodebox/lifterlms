LifterLMS Helper Changelog
==========================

v3.5.4 - 2024-07-30
-------------------

##### Bug Fixes

+ Avoid conflicts with other plugins using the translation loader library.


v3.5.3 - 2024-07-19
-------------------

##### New Features

+ Check for translations in add-ons when available.

##### Bug Fixes

+ Fix "View Details" showing for core LifterLMS. [#52](https://github.com/gocodebox/lifterlms-helper#52)


v3.5.2 - 2024-07-09
-------------------

##### Security Fixes

+ Adding additional security checks.


v3.5.1 - 2024-04-16
-------------------

##### New Features

+ Obfuscates license keys. [#47](https://github.com/gocodebox/lifterlms-helper#47)


v3.5.0 - 2023-02-28
-------------------

##### Updates and Enhancements

+ Updated the appearance of the license dropdown.

##### Bug Fixes

+ Fixed incorrect HTML code for single Add On displayed on the LifterLMS > Add-ons & more screen.

##### Developer Notes

+ Added new paramater `$force`, `false` by default, to the static method `LLMS_Helper_Keys::activate_keys()`. It'll allow to force a remote call instead of using ccached results.

##### Performance Improvements

+ Cache results from the activate keys calls to the LifterLMS license API. This prevents issues where sites are pinging the license server too often, specifically if they are "cloned" sites.


v3.4.2 - 2022-04-01
-------------------

##### Bug Fixes

+ Fixed an issue where adding new license keys with an end-of-line symbol after the last key would result in an invalid license key error.
+ Fixed an issue that caused PHP warnings in the "Plugins -> Add New" page because the `plugin` property was missing. [#36](https://github.com/gocodebox/lifterlms-helper/issues/36)


v3.4.1 - 2021-08-17
-------------------

+ Fixed undefined index error encountered when programmatically deactivating a key that was not previously activated on the site.


v3.4.0 - 2021-08-04
-------------------

##### Localization updates

+ Only runs localization functions when loaded as an independent plugin.
+ Replace the textdoman 'lifterlms-helper' with 'lifterlms'.

##### Updates

+ Use `llms_helper()` in favor of deprecated `LLMS_Helper()` in various locations.

##### Bugfix

+ Don't attempt to run migrations from versions less than 3.0.0 during first run when loaded as a library.


v3.3.1 - 2021-07-26
-------------------

+ Load `llms_helper()->upgrader` WP_CLI context in preparation for forthcoming the `lifterlms-cli`.


v3.3.0 - 2021-06-14
-------------------

+ This plugin is now included by default via the LifterLMS core in versions 5.0+. Installing this plugin directly will use the plugin version instead of the version included with the core. Direct installation is likely only required for development purposes when using LifterLMS 5.0+.
+ The main function `llms_helper()` is declared conditionally when the class `LifterLMS_Helper` is not yet declared.
+ Added a constant `LLMS_HELPER_DISABLE` which allows disabling of the plugin.
+ Distribution release zips now include a `composer.json` file to allow for installation via composer.


v3.2.1 - 2021-06-03
-------------------

##### Updates

+ Flush cached update and add-on data when adding or removing license keys and when changing channel subscription for a package.
+ Enable updating to beta versions of packages that don't require a license when no license is present.


v3.2.0 - 2020-12-02
-------------------

##### Updates

+ Moved the class `LifterLMS_Helper` class to its own file from `lifterlms-helper.php`.
+ Use `self::$instance` in favor of `self::$_instance`.
+ Use `llms()` in favor of deprecated `LLMS()`.
+ Use `llms_filter_input()` to access `$_POST` data in various places.
+ Use strict comparison for `in_array()`.

##### Bug fixes

+ Fixed usage of incorrect textdomain in various places.

##### Deprecations

+ Replaced usage of protected class property `$instance` in favor of `$_instance` in various singleton classes.
+ Function `LLMS_Helper()` is deprecated in favor of `llms_helper()`.
+ File `includes/model-llms-helper-add-on.php` is deprecated, use `includes/models/class-llms-helper-add-on.php` instead.


v3.1.0 - 2020-05-22
-------------------

+ Load changelogs from the make.lifterlms.com release notes archive in favor of from static html files.
+ Remove reliance on `file_get_contents()` causing errors on servers without access to the function.


v3.0.2 - 2018-08-29
-------------------

+ Fixed fatal errors encountered as a result of failed API calls
+ Fixed broken links output on the plugins update screen when an add-on is unlicensed and has an update available
+ Fixed issue causing non-beta versions of the LifterLMS core to be served from LifterLMS.com instead of from WordPress.org


v3.0.1 - 2018-08-02
-------------------

+ Fixed an issue causing key migration to run on the frontend resulting in a fatal error related to missing admin-only functions
+ Fixed an issue causing multiple submitted keys to not work properly on certain environments
+ Fixed issue causing installation script to make an activation API call even when no keys exist
+ Improved installation script message to only display a migration message when keys are actually migrated


v3.0.0 - 2018-08-01
-------------------

+ **This is nearly a complete rewrite of the codebase. Things have moved but no features have been removed.**
+ Requires LifterLMS version 3.22.0 or later
+ License key activation is now on a per-site basis as opposed to a per product basis. This means that if you have a license key for a bundle you don't have to enter the key for each add-on, you enter the key only once and it will activate ALL the add-ons.
+ The "Licenses" tab has been removed and your add-ons and licenses are now managed via LifterLMS -> Add-ons & More
+ A migration script exists to move license keys from previous versions of the helper to this version. After upgrading check LifterLMS -> Add-ons & More to ensure your keys were successfully migrated.
+ You can now install add-ons through the this plugin without having to download and install them manually. Enter your license key(s) and select the add-ons you wish to install to have them installed automatically. You can bulk install as well.
+ You can now subscribe to beta channels of LifterLMS and any LifterLMS add-ons. Visit the LifterLMS -> Status -> Betas screen to subscribe to betas. Always use betas at your own risk, by nature they're unstable!
+ Uses the LifterLMS.com v3 REST api for all API calls
+ Added RTL language support
+ Added i18n support
+ Removed and replaced various functions
+ Fixes many bugs and almost certainly introduces some new ones


v2.5.1 - 2017-11-08
-------------------

+ Fix issue causing false activations which cannot be deactivated due to blank activation keys


v2.5.0 - 2017-07-18
-------------------

+ Allow add-ons to be bulk deactivated
+ Integrates with LifterLMS site clone detection in order to automatically activate plugins on your new URL when cloning to staging / production.
+ Following clone detection if activation fails the plugin will no longer show the add-ons as activated (since they're not activated on the new URL)
+ Minor admin-panel performance improvements
+ Now uses minified JS and CSS assets
+ Now fully translateable!


v2.4.3 - 2017-02-09
-------------------

+ Handle undefined errors during post plugin install from zip file


v2.4.2 - 2017-01-20
-------------------

+ Handle failed api calls gracefully


v2.4.1 - 2016-12-30
-------------------

+ Cache add-on list prior to filtering


v2.4.0 - 2016-12-20
-------------------

+ Added a unified Helper sceen accessible via LifterLMS -> Settings -> Helper
+ Activate multiple addons simultaneously via one API call
+ Site deactivation now deactivates from remote activation server in addition to local deactivation
+ Upgraded database key handling prevents accidental duplicate activation attempts
+ Fixed several undefined index warnings
+ Normalized option fields keys


v2.3.1 - 2016-10-12
-------------------

+ Fixes issue with theme upgrade post install not working resulting in themes existing in the wrong directory after an upgrade


v2.3.0 - 2016-10-10
-------------------

+ Significantly upgrades the speed of version checks. Previously checked each LifterLMS Add-on separately, now makes one API call to retreive versions of all installed LifterLMS Add-ons.
+ Adds support for the Universe Bundle which is one key associated with multiple products


v2.2.0 - 2016-07-06
-------------------

+ After updates, clear cached update data so the upgrade doesn't still appear as pending
+ After changing license keys, clear cahced data so the next upgrade attempt will not fail again (unless it's still supposed to fail)
+ After updating the currently active theme, correctly reactivate the theme


v2.1.0 - 2016-06-14
-------------------

+ Prevent hijacking the LifterLMS Core lightbox data when attempting to view update details on the plugin update screen.
+ Added [Parsedown](https://github.com/erusev/parsedown) to render Markdown style changelogs into HTML when viewing extension changelogs in the the lightbox on plugin update screens.


v2.0.0 - 2016-04-08
-------------------

+ Includes theme-related APIs for serving updates for themes
+ Better error reporting and handling
+ A few very exciting performance enhancements


v1.0.2 - 2016-03-07
-------------------

+ Fixed an undefined variable which produced a php warning when `WP_DEBUG` was enabled
+ Resolved an issue that caused the LifterLMS Helper to hijack the "details" and related plugin screens that display inside a lightbox in the plugins admin page.
+ Added a .editorconfig file
+ Added changelog file


v1.0.1 - 2016-02-11
-------------------

+ Actual public release


v1.0.0 - 2016-02-10
-------------------

+ Initial public release
