Releasing LifterLMS Builds
==========================

This document outlines the workflow used by LifterLMS core maintainers to build and publish LifterLMS releases.

This document assumes you have already installed LifterLMS for development following the [Installing for Development guide](./installing.md).

This build process relies on the use of the `llms-dev` CLI. The CLI is not available publicly. Any core contributor will be provided access to the CLI as needed.


## 1. Pre-release Tests

1. Ensure the release passes all automated testing. Use `composer run tests-run`.
2. Ensure the release passes coding standard checks. Use `composer run check-cs-errors`.

_Note: files in the `tmp` directory used during tests are currently parsed by the certain gulp tasks. Remove the `tmp` directory before proceeding to the next steps._


## 2. Build the Release

### 2A. Generate the Changelog

1. Automatically generate the changelog from `@since [version]` tags in the codebase: `llms-dev log:write`.
2. Update the changelog, grouping changes into relevant headings (Updates, Bug Fixes, Deprecations, & Templates Updated).
3. Remove redundant, irrelevant, and superfluous entries.

### 2B. Update file version numbers

Replace all `[version]` tags with the release version number: run `llms-dev ver:update`. Use the `-i` or `-F` flags to update the release according to the next version number.

### 2C. Generate Static Assets and Language Files

Run `gulp build`.


## 3. Generate the Distribution Archive

Run `llms-dev archive`.


## 4. Run pre-release tests on the archived

Install and activate the zip file on a temporary sandbox site.

  1. Run the setup wizard.
  2. Import sample course
  3. Enroll a student into the course.
  4. Complete a lesson.

_This manual testing ensures no (unlikely but possible) errors occurred in the build steps above._


## 4. Publish the Release

Run `llms-dev publish:gh`.

The following steps are performed automatically by the above task:

1. Publish to GitHub

    A. The contents of the distribution archive is force-pushed to the `trunk` branch.
    B. A new release tag draft is created for the current version number using `trunk` as the commit target.
    C. The distribution archive is uploaded to the release.
    D. The release is published.
    E. A webhook ping notifies the `llms-releaser` server which performs the remaining steps of the release:

2. Publish to WordPress plugin repository

    A. Create a new SVN tag using the release asset (distribution archive) as the base.
    B. Update the `trunk` branch to match the new tag.

3. A changelog blog post is published to make.lifterlms.com.

4. The number is updated at LifterLMS.com

5. The distribution archive is synced to the release asset bucket in AWS S3 as a backup.


## 5. Update documentation at developer.lifterlms.com

Via SSH run `./updateDocs.sh` and follow the prompts.
