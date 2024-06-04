Releasing LifterLMS Builds
==========================

This document outlines the workflow used by LifterLMS core maintainers to build and publish LifterLMS releases.

This document assumes you have already installed LifterLMS for development following the [Installing for Development guide](./installing.md).

## 0. Get ready

Make sure you have your local repository up to date. If your origin is set to the gocodebox/lifterlms repo, these commands will get you up to date.

1. `git checkout dev` and `git pull`
2. `git checkout trunk` and `git pull`

Make sure your @lifterlms/dev package in package.json is on the latest version. If it needs to be updated, update it, commit to the dev branch, and then run `npm install`.

Make sure you are back on the dev branch.

1. `git checkout dev`

Make sure you have installed composer requirements via `composer install`.

Make sure you have the latest `@lifterlms` JS packages. Note that this will update node_modules using the latest published/stable version of the packages, and won't include any updates made to those packages by this release itself.

1. `npm install`

Make sure that the dev version (or trunk since it will merge automatically) are tested up to the latest version of WordPress.

For Add-ons, also confirm that the plugin headers include appropriate values for LLMS minimum version and LLMS tested up to as follows:

1. Adjust these lines in the header of the main plugin .php file.

* ` * Tested up to: 6.4.1` (this is the WordPress tested up to value)
* ` * LLMS requires at least: 6.0.0` (only update this value if you are sure that the update breaks backwards compatibility)
* ` * LLMS tested up to: 7.5.0 ` (this should be updated to the latest LifterLMS stable version)

## 1. Build the Release

Prepare the release: `npm run dev release prepare`:

When running this command, the following happens:

1. Determines the version number based on the significance values found in `.changelogs/` files. Unless `-F` is passed to the command to force a specific version number.
2. Write the changelog entries to `CHANGELOG.md`.
3. Updates version numbers of placeholder `[version]` tags, `package.json`, etc...
4. Runs the release build command, `npm run build`.

## 2. Run tests and coding standards checks

0. Ensure phpunit tests are installed: `composer run tests-install`.
1. Ensure phpunit tests pass: `composer run tests-run`.
2. Ensure phpcs checks pass: `composer run check-cs-errors`.
3. Ensure e2e tests pass: `npm run test`.
4. Ensure eslint checks pass: `npm run lint:js`.

## 3. Commit and push

After building and testing the built release, all changes should be committed and pushed to GitHub.

1. `git commit -a`
2. Enter something like "build version 7.1.1" for the commit message.
3. `git push`

## 4. Generate the Distribution Archive

Run `npm run dev release archive -- -i`.

This is a more pedantic version of `npm run dev release archive` that will allow to easily inspect 
the archive: once created, the archive will be unpacked into the `dist` directory so that its content
could be easily inspected. E.g. make sure it doesn't contain undesired files such as unwanted dependencies
into the `vendor` directory, and so on.

## 5. Run pre-release tests on the archived

Install and activate the zip file on a temporary sandbox site.

Note: If you are reusing a testing site that already has LifterLMS installed, you can add this line to your wp-config.php and then uninstall and delete LifterLMS from the plugins screen and it will delete all of the LifterLMS data.

`define( 'LLMS_REMOVE_ALL_DATA', true );`

  1. Run the setup wizard.
  2. Import sample course
  3. Enroll a student into the course.
  4. Complete a lesson.

_This manual testing ensures no errors occurred in the build steps above._

## 6. Publish the Release

Run `npm run dev release create`.

The following steps are performed automatically by the above task:

1. Publish to GitHub
    1. The contents of the distribution archive is force-pushed to the `release` branch.
    1. A new release tag draft is created for the current version number using `release` as the commit target.
    1. The distribution archive is uploaded to the release.
    1. The release is published.
    1. A webhook ping notifies the `llms-releaser` server which performs the remaining steps of the release:
1. Publish to WordPress plugin repository
    1. Create a new SVN tag using the release asset (distribution archive) as the base.
    1. Update the `trunk` branch to match the new tag.
1. A changelog blog post is published to make.lifterlms.com.
1. The number is updated at LifterLMS.com
1. The distribution archive is synced to the release asset bucket in AWS S3 as a backup.

## 7. Update Trunk

After everything is complete, the final version of should be committed and pushed to GitHub trunk branch. It is possible this can also be done on GitHub.com directly by create a Pull Request from  `dev` to `trunk`

1. `git checkout trunk`
2. `git merge dev`
3. `git push`

## 8. Push to WordPress.org (If Needed)

As of this writing, only the core LifterLMS plugin, the LifterLMS Labs plugin, and the Lite LMS Prgress Tracker are hosted on wordpress.org.

Note: The feature of the llms-releaser server that pushes updates to wordpress.org has been disabled due to some bugs there. The steps below can be used to "manually" push a release to wordpress.org.

1. If you don't have a lifterlms-svn folder, create it. (The first time you create this, it will take many minutes to download.)
  1. Navigate to your plugins folder.
  1. `mkdir lifterlms-svn`
  1. `cd lifterlms-svn`
  1. `svn co http://plugins.svn.wordpress.org/lifterlms .`
1. Make sure your svn repo is up to date with the remote repo by running `svn update`.
1. Make room for the update by clearing out trunk: `rm -f -f trunk/*`
1. Copy the new dist files into trunk. `cp -r -f ../lifterlms/dist/lifterlms/* trunk/`
1. Check what has changed: `svn status`
1. svn add any new files
  1. If there are a lot of files to add, you can use `svn add --force trunk/*`
1. svn rm any deleted files
  1. If there are a lot of files to remove, you can use `svn st | grep ^! | awk '{print " --force "$2}' | xargs svn rm`
1. Run `svn status` one more time to review changes and make sure all files are being properly modified, added, or removed from the repo.

These next step is optional for point releases, but should be done for major and minor releases and whenever the deployment process is updated enough to warrant a double check.

1. Update stable version in trunk readme to point to the last stable version.
  1. `nano trunk/readme.txt`
  1. Change stable to previous version. (not this version)

1. Commit to SVN
  1. `svn commit -m "7.6.2 - bug fixes and enhancements"`
1. Run svn status again to make sure there are no files that still need to be added.
  1. `svn status`
1. Create a tag for the new version
  1. `svn cp trunk/ tags/7.6.2`
  1. `svn commit -m "tag for new version"`
1. Wait (about 15min) for each commit to go out to WP repo.  

If you updated the stable version to point to the previous version, test then update to the latest version.

1. Test trunk by visiting [the Advanced Tab of the plugin page](https://wordpress.org/plugins/lifterlms/advanced/)
  1. scroll to the bottom of the page
  1. Choose "Development Version" from the dropdown.
  1. Click download.
  1. Install the zip on a fresh dev site and run the standard set up and enroll test or any other tests you want.
1. If the test goes well, update the stable tag to the latest version.
  1. `nano trunk/readme.txt`
  1. `nano tags/7.6.2/readme.txt`
  1. `commit -m "updating stable version"`


