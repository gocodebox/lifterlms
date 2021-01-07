LifterLMS Blocks Changelog
==========================

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
