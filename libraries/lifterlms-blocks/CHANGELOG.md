LifterLMS Blocks Changelog
==========================

v2.5.8 - 2024-09-13
-------------------

##### Bug Fixes

+ Avoid an unsaved error prompt after saving the details of an access plan. [#232](https://github.com/gocodebox/lifterlms-blocks/issues/232)


v2.5.7 - 2024-08-09
-------------------

##### Bug Fixes

+ Fixed issues with featured video thumbnails.


v2.5.6 - 2024-07-30
-------------------

##### Bug Fixes

+ Avoids breaking the output of certain blocks that contain forms.


v2.5.5 - 2024-07-09
-------------------

##### Security Fixes

+ Adds additional security checks and escaping.


v2.5.4 - 2024-04-25
-------------------

##### Bug Fixes

+ Re-adds the Launch Course Builder button to the top of the Course Edit page. [#220](https://github.com/gocodebox/lifterlms-blocks/issues/220)


v2.5.3 - 2024-04-17
-------------------

##### New Features

+ Added LifterLMS icon to LifterLMS Blocks block category.

##### Bug Fixes

+ Fixed Lesson Progression (Mark Complete) button appearance in editor.


v2.5.2 - 2023-11-01
-------------------

##### Bug Fixes

+ Fixed an issue when duplicating a LifterLMS preset form field. [#169](https://github.com/gocodebox/lifterlms-blocks#169)

##### Developer Notes

+ Improved compatibility with PHP 8.2.


v2.5.1 - 2023-06-13
-------------------

##### Bug Fixes

+ Fixes Launch Course Builder buttons not working when WordPress was installed in a subdirectory.


v2.5.0 - 2023-06-06
-------------------

##### New Features

+ Replaced font-based block icons with SVG icons.

##### Updates and Enhancements

+ Changes to Launch Course Builder buttons and Course Builder meta-box.
+ Update default icon color to `currentColor`.
+ Updated minimum LifterLMS core version to 7.2.0.

##### Bug Fixes

+ Fixed issue when new Course/Membership visibility options were added via JS filter hook. [#190](https://github.com/gocodebox/lifterlms-blocks#190)

##### Developer Notes

+ Deprecated LLMS_Blocks_Course_Syllabus_Block class. The Syllabus Block is now implemented in the LifterLMS core plugin.


v2.4.3 - 2022-06-09
-------------------

##### Bug Fixes

+ Fixed an issue that prevented editing form confirmation fields when running WordPress 6.0. [#170](https://github.com/gocodebox/lifterlms-blocks#170)
+ Fixed field columns sizing in the block editor.


v2.4.2 - 2022-04-07
-------------------

##### Bug Fixes

+ Fixed issue where the User Login form field was shown to logged-in users. [gocodebox/lifterlms#2071](https://github.com/gocodebox/lifterlms#2071)


v2.4.1 - 2022-03-30
-------------------

##### Bug Fixes

+ Fixed issue when adding two custom fields of the same type resulting in the first changing its usermeta key. [#160](https://github.com/gocodebox/lifterlms-blocks/issues/160)


v2.4.0 - 2022-02-25
-------------------

##### Updates and Enhancements

+ Components added to `window.llms.components` are now aware of components added to the object from other sources.

##### Bug Fixes

+ Fixed access to non-existing variable when current user tries to edit course/membership instructors without proper permissions. [#140](https://github.com/gocodebox/lifterlms-blocks#140)


v2.3.2 - 2022-02-22
-------------------

##### Updates and Enhancements

+ Added an option to specify a custom checkout form title for free access plans.


v2.3.1 - 2022-01-26
-------------------

##### Updates and Enhancements

+ Resolved PHP 8.1 deprecation warnings.


v2.3.0 - 2022-01-25
-------------------

##### New Features

+ Added the llms/php-template block, used by the Site Editor to load php templates.

##### Updates and Enhancements

+ Adds support for WordPress 5.9.
+ The minimum required WordPress version is now 5.5.


v2.2.1 - 2021-09-29
-------------------

+ Bugfix: Fixed deprecated filter warning encountered when using certain development versions of the WordPress core.


v2.2.0 - 2021-07-19
-------------------

##### Updates

+ **Increases minimum WordPress Core version requirement to version 5.4!**.
+ Tested and compatible with WordPress core 5.8
+ Don't load block editor assets on the "blockified" widgets screen.
+ Remove timeouts and subscription debouncing used by blocks watcher which handles the `llms/user-info-fields` redux store.
+ Stop debouncing the blocks watcher.

##### Bug fixes

+ Confirm group blocks now configure the block's id, name, and match attributes instead of being configured in the block render via the `blocks/form-fields/group-data` module.
+ Don't define the `match` attribute during creation of a user password block.


v2.1.1 - 2021-07-08
-------------------

+ Fixed issue causing visibility controls to display for blocks which have no visibility attributes defined.


v2.1.0 - 2021-06-28
-------------------

##### Updates

+ Adjusted priority of block editor JS assets to load at priority `5` instead of `999`. Resolves plugin conflicts encountered when using block-level visibility on blocks registered after visibility filters are applied.
+ Removed usage of [react-sortable-hoc](https://github.com/clauderic/react-sortable-hoc) and replaced with [dndkit](https://github.com/clauderic/dnd-kit) for drag and drop UX within the editor.
+ Refactored the instructors sidebar (on courses and memberships) as well as the option shorting (for fields with options) to utilize `dndkit`.

##### Bugfixes

+ Fixed an issue encountered on password confirmation fields when adjusting the minimum password length option on the user password block.


v2.0.1 - 2021-06-21
-------------------

+ Use non-unique error notice IDs for reusable multiple error notice.


v2.0.0 - 2021-06-21
-------------------

##### Updates

+ Adds LifterLMS User Information form building via the block editor.
+ Initially compatibility for WordPress 5.8 (full site editing). Ensures core functionality but doesn't add any exciting features.
+ Improve the visual feedback inside the editor for a block with visibility restrictions.
+ Added reusable block support for form fields.
+ Adds a user information (`[llms-user]`) shortcode inserter to rich text block toolbars.
+ Use rich text `allowedFormats` in favor of deprecated `formattingControls`
+ Improved localization of Javascript files.

##### Bug Fixes

+ Fixed issue encountered when using lesson progression blocks outside of a lesson, thanks [@reedhewitt](https://github.com/reedhewitt)!
+ Fixed fatal errors encountered if LifterLMS core isn't active when this plugin is activated.
+ Currently selected instructors are excluded from queries for instructor users.
+ Fixed issue encountered on courses and memberships when attempting to edit instructor information.

##### Backwards Incompatible Changes

+ Major refactor of all field-related blocks.
+ The names of many field blocks have changed.
+ Use `getDisallowedBlocks()` in favor of removed `getBlacklist()` in `block-visibility/check`.
+ Blocks restricted to specific posts have had the post object stored on the block attribute reduced to include only the minimum required properties.
+ The `Search`, `SearchPost`, and `SearchUser` components have had major changes to make them more extendable.
+ Don't render InspectorControls since the block doesn't have any actual settings.


v2.0.0-rc.2 - 2021-06-18
------------------------

+ Only load the plugin if LifterLMS is loaded
+ Update version checking method.
+ Fixed typo causing errors on WP 5.6 and earlier.
+ Fix WP 5.7 compatibility issues
+ Fixed issue encountered when using lesson progression blocks outside of a lesson, thanks [@reedhewitt](https://github.com/reedhewitt)!


v2.0.0-rc.1 - 2021-06-15
------------------------

+ Fixes issue encountered when adding a confirm group
+ Stop using merge codes in the password block
+ Improve block duplication handlers
+ Prevent confirm fields from being manually pasted outside of a confirm group
+ Adds the `llms/user-information-fields` redux store to allow for better field validation and handling
+ Improves and adds field attribute validation
+ Use rich text `allowedFormats` in favor of deprecated `formattingControls`
+ Remove the now unnecessary `uuid` field block attribute.
+ Adds WP core 5.8 compatibility on the widget and customizer screens.
+ Exclude LifterLMS field block reusables from the widgets reusable blocks screen.
+ Adds backwards compatibility for WordPress < 5.6


v2.0.0-beta.6 - 2021-06-01
--------------------------

+ (Re-)introduces user information shortcode through a block editor rich text area format button.
+ Prevent usage the "User Login" block on account edit forms (usersnames cannot be edited in WordPress).
+ Only prevent form posts from being made "draft" status on the "core" forms.
+ Modifies field localization data strategy for field validation and others.


v2.0.0-beta.5 - 2021-05-18
--------------------------

+ Add WP core 5.8 compatibility for deprecated filter `block_categories`.
+ Fixed issue encountered on courses and memberships when attempting to edit instructor information.
+ Added validation to ensure all fields have unique HTML name attributes.
+ Simplified field data storage interface to enable saving only to the usermeta table.


v2.0.0-beta.4 - 2021-05-07
--------------------------

+ Fixed error encountered when opening the block editor options menu on an `llms_form` post type.
+ Added UUID generation to all form field blocks.
+ Fixed visual issues encountered with form field blocks on wide screens in the block editor.
+ Fixed issue preventing column widths from being set after switching from a stacked layout to a columns layout for a field group.
+ Added CSS classes to various option elements in the block editor
+ Moved most inline css in the editor into a static file
+ Fixed issue encountered when reverting a form to it's default
+ Fixed dynamic block rendering errors encountered when the block is restricted to specific courses/memberships.
+ Added CSS to make input placeholder text look like a placeholder


v2.0.0-beta.3 - 2021-04-26
--------------------------

+ All form field blocks refactored and many were removed or renamed.
+ Added column support to form field blocks.
+ Added reusable block support to form field blocks.
+ Removed support for block visibility on required field blocks (email and password).
+ Added reusable block filtering to only show "supported" reusable blocks when editing a form.
+ Added utility function support for reusable blocks.
+ Fixed issues related to visual rendering of checkboxes / radio elements on custom fields.


v2.0.0-beta.2 - 2021-03-22
--------------------------

+ Fixed block editor visual issues encountered on certain blocks when block-level visibility restrictions are enabled.


v2.0.0-beta.1 - 2021-03-22
--------------------------

+ Improved Javascript localization.
+ Updated JS source files to follow (slightly modified) eslint standards as defined by `@wordpress/eslint-plugin/recommended`.
+ Disabled import of incomplete module `./formats/merge-codes`.
+ Improved the information displayed for a restricted block.
+ Don't render `InspectorControls` for the Course Syllabus block since it doesn't have any actual settings to inspect.
+ Improved the Search, SearchPost, and SearchUser components and made backwards incompatible changes to their usage.


v1.12.0 - 2021-01-07
--------------------

+ Various form and field updates in preparation for LifterLMS 5.0.0.


v1.11.1 - 2021-01-05
--------------------

+ Update the hook used for the Instructors block when displayed on membership post types.


v1.11.0 - 2020-12-29
--------------------

+ Allow the "Instructors" block to be used for memberships, thanks [@alaa-alshamy](https://github.com/alaa-alshamy)!


v1.10.0 - 2020-11-24
--------------------

+ Use the `LLMS_Assets` class to define, register, and enqueue plugin assets.
+ Added Javascript localization for block editor scripts.


v1.9.1 - 2020-04-29
-------------------

+ Fix course progress block template used when migrating a course to the block editor.


v1.9.0 - 2020-04-29
-------------------

+ Converted the course progress block into a dynamic block. Fixes an issue allowing the progress block to be visible to non-enrolled students.
+ Added a filter on the output of the Pricing Table block: `llms_blocks_render_pricing_table_block`.


v1.8.0 - 2020-04-28
-------------------

##### Updates

+ Improved script dependencies definitions.
+ Updated asset paths for consistency with other LifterLMS projects.
+ Updated various WP Core references that have been deprecated (maintains backwards compatibility).
+ The Lesson Progression block is no longer rendered server-side in the block editor (minor performance improvement).

##### Changes to the Classic Editor Block

+ The classic editor block will no longer show block visibility settings because it is impossible to use those settings to filter the block on the frontend.
+ In order to apply visibility settings to the classic editor block, place the Classic Editor within a "Group" block and apply visibility settings to the Group.

##### Bug fixes

+ Fixed an issue encountered when using the WP Core "Table" block.
+ Fixed a few areas where `class` was being used instead of `className` to define CSS classes on elements in the block editor.
+ Fixed a user-experience issues encountered on the Course Information block when all possible information is disabled.
+ Fixed an issue causing visibility attributes to render on blocks that don't support them.
+ Fixed an issue preventing 3rd party blocks from modifying default block visibility settings.
+ Fixed a spelling error visible inside the block editor.
+ Fixed an issue causing the "Course Progress" block to be shown to non-enrolled students and visitors.
+ Removed redundant CSS from frontend.
+ Stop outputting editor CSS on the frontend.
+ Dynamic blocks with no content to render will now only output their empty render messages inside the block editor, not on the frontend.


v1.7.3 - 2019-12-19
-------------------

+ Move form ready event from domReady to block registration to ensure blocks are exposed before blocks are parsed.


v1.7.2 - 2019-12-09
-------------------

+ Bug fix: fix issue causing the block editor to encounter a fatal error when using custom post types that don't support custom fields.


v1.7.1 - 2019-12-05
-------------------

+ Bug fix: Fixed a WordPress 5.3 issues with JSON data affecting the ability to save course/membership instructors.
+ Update: Added filter, `llms_block_supports_visibility` to allow modification of the return of the check.
+ Update: Disabled block visibility on registration & account forms to prevent a potentially confusing form creation experience.
+ Update: Added block editor rendering for password type fields.


v1.7.0 - 2019-11-08
-------------------

##### Updates

+ Membership post types can now use the LifterLMS Pricing Table block.
+ Membership post types are automatically migrated to the block editor (use the pricing table block instead of the pricing table action).
+ Added a block editor template for the Membership post type.
+ The block 'llms/form-field-redeem-voucher' is now only available on registration forms.

##### Bug Fixes

+ Backwards compatibility fixes for WP Core 5.2 and earlier.
+ Perform post migrations on `current_screen` instead of `admin_enqueue_scripts`.
+ Fix an issue causing "No HTML Returned" to be displayed in place of the Lesson Progression block on free lessons when viewed by a logged-out user.
+ Import `InspectorControls` from `wp.blockEditor` and fallback to `wp.editor` to maintain backwards compatibility.
+ Fall back to `wp.editor` for `RichText` import when `wp.blockEditor` is not found.
+ Import from `wp.editor` when `wp.blockEditor` is not available.
+ Return early during renders on WP Core 5.2 and earlier where the `PluginDocumentSettingPanel` doesn't exist.


v1.6.0 - 2019-10-24
-------------------

+ Feature: Added form field blocks for use on the Forms manager.
+ Feature: Add logic for `logged_in` and `logged_out` block visibility options.
+ Update: Added isDisabled property to Search component.
+ Update: Adjusted priority of `render_block` filter to 20.
+ Bug fix: Import `InspectorControls` from `wp.blockEditor` in favor of deprecated `wp.editor`
+ Bug fix: Automatically store course/membership instructor with `post_author` data when the post is created.
+ Bug fix: Pass style rules as camelCase.


v1.5.2 - 2019-08-14
-------------------

+ Only enable REST for authenticated users with the `lifterlms_instructor` capability.


v1.5.1 - 2019-05-17
-------------------

+ Only register block visibility settings on static blocks. Fixes an issue causing core (or 3rd party) dynamic blocks from being managed within the block editor.


v1.5.0 - 2019-05-16
-------------------

+ All blocks are now registered only for post types where they can actually be used.


v1.4.1 - 2019-05-13
-------------------

+ Fixed double slashes in asset path of CSS and JS files, thanks [@pondermatic](https://github.com/pondermatic)!


v1.4.0 - 2019-04-26
-------------------

+ Added an "unmigration" utility to LifterLMS -> Status -> Tools & Utilities which can be used to remove LifterLMS blocks from courses and lessons which were migrated to the block editor structure. This tool is only available when the Classic Editor plugin is installed and enabled and it will remove blocks from ALL courses and lessons regardless of whether or not the block editor is being utilized on that post.


v1.3.8 - 2019-03-19
-------------------

+ Explicitly import jQuery when used within blocks.


v1.3.7 - 2019-02-27
-------------------

+ Fixed an issue preventing "Pricing Table" blocks from displaying on the admin panel when the current user was enrolled in the course or no payment gateways were enabled on the site.


v1.3.6 - 2019-02-22
-------------------

+ Updated the editor icons to use the new LifterLMS Icon
+ Change method for Pricing Table block re-rendering to prevent an issue resulting it always appearing that the post has unsaved data.


v1.3.5 - 2019-02-21
-------------------

+ Automatically re-renders Pricing Table blocks when access plans are saved or deleted via the course / membership access plan metabox.


v1.3.4 - 2019-01-30
-------------------

+ Add support for the Divi Builder's "Classic Editor" mode
+ Skip post migration when "Classic" mode is enabled


v1.3.3 - 2019-01-23
-------------------

+ Add conditions to check for Classic Editor settings configured to enforce classic/block for all posts.


v1.3.2 - 2019-01-16
-------------------

+ Fix issue preventing template actions from being removed from migrated courses & lessons.


v1.3.1 - 2019-01-15
-------------------

+ Move post migration checks to a callable function `llms_blocks_is_post_migrated()`


v1.3.0 - 2019-01-09
-------------------

+ Add course and membership catalog visibility settings into the block editor.
+ Fixed issue preventing the course instructors metabox from displaying when using the classic editor plugin.

v1.2.0 - 2018-12-27
-------------------

+ Add conditional support for page builders: Beaver Builder, Divi Builder, and Elementor.
+ Fixed issue causing LifterLMS core sales pages from outputting automatic content (like pricing tables) on migrated posts.


v1.1.2 - 2018-12-17
-------------------

+ Add a filter to the migration check on lessons & courses.


v1.1.1 - 2018-12-14
-------------------

+ Fix issue causing LifterLMS Core Actions to be removed when using the Classic Editor plugin.


v1.1.0 - 2018-12-12
-------------------

+ Editor blocks now display a lock icon when hovering/selecting a block which corresponds to the enrollment visibility settings of the block.
+ Removal of core actions is now handled by a general migrator function instead of by individual blocks.
+ Fix issue causing block visibility options to not be properly set when enrollment visibility is first enabled for a block.


v1.0.1 - 2018-12-05
-------------------

+ Made plugin url relative


v1.0.0 - 2018-12-05
-------------------

+ Initial public release
