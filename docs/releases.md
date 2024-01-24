Releasing LifterLMS Builds
==========================

This document outlines the workflow used by LifterLMS core maintainers to build and publish LifterLMS releases.

This document assumes you have already installed LifterLMS for development following the [Installing for Development guide](./installing.md).

## 0. Get ready

Make sure you have your local repository up to date. If your origin is set to the gocodebox/lifterlms repo, these commands will get you up to date.

1. `git checkout dev` and `git pull`
2. `git checkout trunk` and `git pull`

Make sure you are back on the dev branch.

1. `git checkout dev`

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

0. Ensure phpunit tests are installed: `composer run tests-install`
1. Ensure phpunit tests pass: `composer run tests-run`.
2. Ensure phpcs checks pass: `composer run check-cs-errors`.
3. Ensure e2e tests pass: `npm run test`.
4. Ensure eslint checks pass: `npm run lint:js`.

## 3. Commit and push

After building and testing the built release, all changes should be committed and pushed to GitHub.

1. `git commit -a`
2. Enter something like "build version 7.1.1" for the commit message.

## 4. Generate the Distribution Archive

Run `npm run dev release archive -- -i`.

This is a more pedantic version of `npm run dev release archive` that will allow to easily inspect 
the archive: once created, the archive will be unpacked into the `dist` directory so that its content
could be easily inspected. E.g. make sure it doesn't contain undesired files such as unwanted dependencies
into the `vendor` directory, and so on.

## 5. Run pre-release tests on the archived

Install and activate the zip file on a temporary sandbox site.

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
