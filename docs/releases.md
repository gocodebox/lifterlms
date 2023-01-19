Releasing LifterLMS Builds
==========================

This document outlines the workflow used by LifterLMS core maintainers to build and publish LifterLMS releases.

This document assumes you have already installed LifterLMS for development following the [Installing for Development guide](./installing.md).

## 1. Build the Release

Prepare the release: `npm run dev release prepare`:

When running this command, the following happens:

1. Determines the version number based on the significance values found in `.changelogs/` files. Unless `-F` is passed to the command to force a specific version number.
2. Write the changelog entries to `CHANGELOG.md`.
3. Updates version numbers of placeholder `[version]` tags, `package.json`, etc...
4. Runs the release build command, `npm run build`.

## 2. Run tests and coding standards checks

1. Ensure phpunit tests pass: `composer run tests-run`.
2. Ensure phpcs checks pass: `composer run check-cs-errors`.
3. Ensure e2e tests pass: `npm run test`.
4. Ensure eslint checks pass: `npm run lint:js`.

## 3. Commit and push

After building and testing the built release, all changes should be committed and pushed to GitHub.

## 4. Generate the Distribution Archive

Run `npm run dev release archive`.

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
    A. The contents of the distribution archive is force-pushed to the `release` branch.
    B. A new release tag draft is created for the current version number using `release` as the commit target.
    C. The distribution archive is uploaded to the release.
    D. The release is published.
    E. A webhook ping notifies the `llms-releaser` server which performs the remaining steps of the release:
2. Publish to WordPress plugin repository
    A. Create a new SVN tag using the release asset (distribution archive) as the base.
    B. Update the `trunk` branch to match the new tag.
3. A changelog blog post is published to make.lifterlms.com.
4. The number is updated at LifterLMS.com
5. The distribution archive is synced to the release asset bucket in AWS S3 as a backup.
